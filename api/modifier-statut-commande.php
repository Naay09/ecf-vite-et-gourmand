<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier les droits de l'utilisateur connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données envoyées par JavaScript
$commandeId = $_POST['commande_id'] ?? '';
$nouveauStatut = $_POST['nouveau_statut'] ?? '';
$jeton = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($jeton) ||
    empty($_SESSION['csrf_employe']) ||
    !hash_equals($_SESSION['csrf_employe'], $jeton)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier l'identifiant de la commande
if (
    !is_string($commandeId) ||
    !ctype_digit($commandeId) ||
    (int) $commandeId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

// Définir les transitions autorisées
$transitions = [
    'en_attente' => ['accepte'],
    'accepte' => ['en_preparation'],
    'en_preparation' => ['en_cours_de_livraison'],
    'en_cours_de_livraison' => ['livre'],
    'livre' => ['terminee', 'en_attente_retour_materiel']
];


// Vérifier que le statut demandé est autorisé
$statutsAutorises = [
    'accepte',
    'en_preparation',
    'en_cours_de_livraison',
    'livre',
    'terminee',
    'en_attente_retour_materiel'
];

if (
    !is_string($nouveauStatut) ||
    !in_array($nouveauStatut, $statutsAutorises, true)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Statut demandé invalide.'
    ]);

    exit;
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer et verrouiller la commande
    $requete = $dbConnection->prepare(
        "SELECT statut_courant,
        prenom_client,
        email_client
         FROM commande
         WHERE id = :id
         FOR UPDATE"
    );

    $requete->execute([
        'id' => $commandeId
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Récupérer le statut actuel
    $statutActuel = strtolower(
        trim($commande['statut_courant'])
    );

    // Gérer l'ancienne écriture "accepté"
    $statutActuel = str_replace(
        ['é', 'è', 'ê'],
        'e',
        $statutActuel
    );

    // Vérifier que la transition est autorisée
    if (
        !isset($transitions[$statutActuel]) ||
        !in_array($nouveauStatut, $transitions[$statutActuel], true)
    ) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Ce changement de statut n’est pas autorisé.'
        ]);

        exit;
    }

    // Mettre à jour le statut de la commande
    $requete = $dbConnection->prepare(
        "UPDATE commande
         SET statut_courant = :statut
         WHERE id = :id"
    );

    $requete->execute([
        'statut' => $nouveauStatut,
        'id' => $commandeId
    ]);

    // Enregistrer le changement dans l'historique
    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut
            (commande_id, compte_id, statut, date_modification)
         VALUES
            (:commande_id, :compte_id, :statut, NOW())"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id'],
        'statut' => $nouveauStatut
    ]);

    // Valider les modifications
    $dbConnection->commit();

    /* ENVOYER UN E-MAIL SI LA COMMANDE EST TERMINÉE */

    $emailEnvoye = null;

    if ($nouveauStatut === 'terminee') {

        $emailEnvoye = false;

        try {

            // Charger notre service d'envoi d'e-mails
            require_once __DIR__ . '/../includes/mail-service.php';

            // Préparer les informations
            $numeroCommande = (int) $commandeId;

            $prenomClient = htmlspecialchars(
                $commande['prenom_client'],
                ENT_QUOTES,
                'UTF-8'
            );

            // Objet de l'e-mail
            $sujet = 'Votre commande Vite & Gourmand est terminée';

            // Contenu de l'e-mail
        
            $message = "Bonjour,\n\n"
                . "Votre commande n°" . $numeroCommande . " est désormais terminée.\n\n"
                . "Nous vous remercions d'avoir choisi Vite & Gourmand pour votre prestation.\n\n"
                . "Nous serions ravis de connaître votre avis sur votre expérience.\n"
                . "Vous pourrez le déposer depuis votre espace client.\n\n"
                . "À bientôt,\n"
                . "L'équipe Vite & Gourmand";

            // Envoyer l'e-mail
            $resultatMail = envoyerMail(
                $commande['email_client'],
                $sujet,
                $message
            );

            if ($resultatMail === false) {
                throw new Exception("L'envoi de l'e-mail a échoué.");
            }

            $emailEnvoye = true;

        } catch (Throwable $erreurMail) {

            error_log(
                'Erreur e-mail commande : ' .
                $erreurMail->getMessage()
            );
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $emailEnvoye === false
            ? 'Le statut a été mis à jour, mais l’e-mail n’a pas pu être envoyé.'
            : 'Le statut a été mis à jour.',
        'nouveau_statut' => $nouveauStatut,
        'email_envoye' => $emailEnvoye
    ]);

} catch (PDOException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le statut.'
    ]);
}
