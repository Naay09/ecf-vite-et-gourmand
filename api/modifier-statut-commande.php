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

// Récupérer la description du matériel prêté
$descriptionMateriel = $_POST['description_materiel'] ?? '';

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

// Vérifier la description si un prêt de matériel est nécessaire
if ($nouveauStatut === 'en_attente_retour_materiel') {

    if (!is_string($descriptionMateriel)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Description du matériel invalide.'
        ]);

        exit;
    }

    $descriptionMateriel = trim($descriptionMateriel);

    if ($descriptionMateriel === '' || mb_strlen($descriptionMateriel) > 1000) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Veuillez renseigner le matériel prêté (1000 caractères maximum).'
        ]);

        exit;
    }
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer et verrouiller la commande
    $requete = $dbConnection->prepare(
        "SELECT
            statut_courant,
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
    $statutActuel = $commande['statut_courant'];

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

    // Enregistrer le prêt de matériel
    if ($nouveauStatut === 'en_attente_retour_materiel') {

        // Date du prêt
        $datePret = new DateTimeImmutable(
            'now',
            new DateTimeZone('Europe/Paris')
        );

        // Date limite : 10 jours calendaires après le prêt
        $dateLimite = $datePret->modify('+10 days');

        // Préparer la requête
        $requete = $dbConnection->prepare(
            "INSERT INTO pret_materiel (
                commande_id,
                description,
                date_pret,
                date_limite_retour
            ) VALUES (
                :commande_id,
                :description,
                :date_pret,
                :date_limite_retour
            )"
        );

        // Enregistrer le prêt
        $requete->execute([
            'commande_id' => $commandeId,
            'description' => $descriptionMateriel,
            'date_pret' => $datePret->format('Y-m-d H:i:s'),
            'date_limite_retour' => $dateLimite->format('Y-m-d')
        ]);
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

    
// ENVOYER UN E-MAIL SELON LE NOUVEAU STATUT

$emailEnvoye = null;

if (
    $nouveauStatut === 'terminee' ||
    $nouveauStatut === 'en_attente_retour_materiel'
) {

    $emailEnvoye = false;

    // CAS 1 : COMMANDE TERMINÉE
    if ($nouveauStatut === 'terminee') {

        $sujet = 'Votre commande Vite & Gourmand est terminée';

        $message = "Bonjour,\n\n"
            . "Votre commande n°" . $commandeId . " est désormais terminée.\n\n"
            . "Nous vous remercions d'avoir choisi Vite & Gourmand pour votre prestation.\n\n"
            . "Nous serions ravis de connaître votre avis sur votre expérience.\n"
            . "Vous pourrez le déposer depuis votre espace client.\n\n"
            . "À bientôt,\n"
            . "L'équipe Vite & Gourmand";

    }

    // CAS 2 : MATÉRIEL À RESTITUER
    if ($nouveauStatut === 'en_attente_retour_materiel') {

        // Formater la date limite
        $dateLimiteFormatee = $dateLimite->format('d/m/Y');

        $sujet = 'Restitution de matériel - Vite & Gourmand';

        $message = "Bonjour,\n\n"
            . "Dans le cadre de votre commande n°" . $commandeId . ", du matériel vous a été prêté.\n\n"
            . "Matériel à restituer :\n"
            . $descriptionMateriel . "\n\n"
            . "Vous disposez de 10 jours pour restituer ce matériel.\n"
            . "Date limite de restitution : " . $dateLimiteFormatee . ".\n\n"
            . "Pour organiser la restitution, veuillez contacter Vite & Gourmand.\n\n"
            . "Conformément à nos conditions générales de vente, des frais de 600 € sont prévus en cas de non-restitution dans le délai indiqué.\n\n"
            . "Nous vous remercions de votre compréhension.\n\n"
            . "L'équipe Vite & Gourmand";
    }

    // ENVOYER L'E-MAIL
    try {

        require_once __DIR__ . '/../includes/mail-service.php';

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
