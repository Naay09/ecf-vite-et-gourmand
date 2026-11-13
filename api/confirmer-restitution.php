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

// Récupérer les informations
$commandeId = $_POST['commande_id'] ?? '';
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
if (!is_string($commandeId)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant invalide.'
    ]);

    exit;
}

$commandeId = filter_var($commandeId, FILTER_VALIDATE_INT);

if ($commandeId === false || $commandeId < 1) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Récupérer et verrouiller la commande
    $requete = $dbConnection->prepare(
        "SELECT
            statut_courant,
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

    // Vérifier le statut de la commande
    if ($commande['statut_courant'] !== 'en_attente_retour_materiel') {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette commande n’attend pas de restitution.'
        ]);

        exit;
    }

    // Récupérer le prêt de matériel
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            date_retour
         FROM pret_materiel
         WHERE commande_id = :commande_id
         FOR UPDATE"
    );

    $requete->execute([
        'commande_id' => $commandeId
    ]);

    $pret = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier que le prêt existe
    if (!$pret) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Aucun prêt de matériel trouvé.'
        ]);

        exit;
    }

    // Vérifier que le matériel n'a pas déjà été restitué
    if ($pret['date_retour'] !== null) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Ce matériel a déjà été restitué.'
        ]);

        exit;
    }

    // 1. ENREGISTRER LA RESTITUTION

    $requete = $dbConnection->prepare(
        "UPDATE pret_materiel
         SET date_retour = NOW()
         WHERE id = :id"
    );

    $requete->execute([
        'id' => $pret['id']
    ]);

    // 2. TERMINER LA COMMANDE

    $requete = $dbConnection->prepare(
        "UPDATE commande
         SET statut_courant = :statut
         WHERE id = :id"
    );

    $requete->execute([
        'statut' => 'terminee',
        'id' => $commandeId
    ]);

    // 3. ENREGISTRER LE NOUVEAU STATUT DANS L'HISTORIQUE

    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut (
            commande_id,
            compte_id,
            statut,
            date_modification
        ) VALUES (
            :commande_id,
            :compte_id,
            :statut,
            NOW()
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id'],
        'statut' => 'terminee'
    ]);

    // Valider les modifications
    $dbConnection->commit();

    // SYNCHRONISER LA COMMANDE AVEC MONGODB

    try {

        // Charger la fonction de synchronisation
        require_once __DIR__ . '/../includes/synchroniser-commande-mongodb.php';

        // Mettre à jour le statut dans MongoDB
        synchroniserCommandeMongoDB($commandeId, $dbConnection);

    } catch (Throwable $erreurMongo) {

        // Ne pas annuler la restitution enregistrée dans MySQL
        error_log(
            'Erreur synchronisation MongoDB commande ' .
            $commandeId . ' : ' . $erreurMongo->getMessage()
        );

    }

    // ENVOYER L'E-MAIL DE FIN DE COMMANDE

    $emailEnvoye = false;

    try {

        require_once __DIR__ . '/../includes/mail-service.php';

        $sujet = 'Votre commande Vite & Gourmand est terminée';

        $message = "Bonjour,\n\n"
            . "Votre commande n°" . $commandeId . " est désormais terminée.\n\n"
            . "Nous vous confirmons la bonne restitution du matériel prêté.\n\n"
            . "Nous vous remercions d'avoir choisi Vite & Gourmand pour votre prestation.\n\n"
            . "Nous serions ravis de connaître votre avis sur votre expérience.\n"
            . "Vous pourrez le déposer depuis votre espace client.\n\n"
            . "À bientôt,\n"
            . "L'équipe Vite & Gourmand";

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
            'Erreur e-mail restitution : ' .
            $erreurMail->getMessage()
        );
    }

    // Réponse positive
    echo json_encode([
        'success' => true,
        'message' => $emailEnvoye
            ? 'Le matériel a été restitué et la commande est terminée.'
            : 'La commande est terminée, mais l’e-mail n’a pas pu être envoyé.',
        'nouveau_statut' => 'terminee',
        'email_envoye' => $emailEnvoye
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’enregistrer la restitution.'
    ]);
}
