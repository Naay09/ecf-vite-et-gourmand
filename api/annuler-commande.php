
<?php

header('Content-Type: application/json; charset=utf-8');

// Autoriser uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier que le client est connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Connexion à MySQL
require __DIR__ . '/../config/db-connection.php';

// Récupérer les informations
$commandeId = $_POST['commande_id'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($csrfToken) ||
    !isset($_SESSION['csrf_compte']) ||
    !hash_equals($_SESSION['csrf_compte'], $csrfToken)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Session de formulaire invalide.'
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
        'message' => 'Commande invalide.'
    ]);

    exit;
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer la commande du client
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            menu_id,
            statut_courant
         FROM commande
         WHERE id = :commande_id
         AND compte_id = :compte_id
         FOR UPDATE"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier que la commande existe
    if (!$commande) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Vérifier que la commande est encore en attente
    if ($commande['statut_courant'] !== 'en_attente') {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette commande ne peut plus être annulée.'
        ]);

        exit;
    }

    // Modifier le statut de la commande
    $requete = $dbConnection->prepare(
        "UPDATE commande
         SET statut_courant = 'annulee'
         WHERE id = :commande_id
         AND statut_courant = 'en_attente'"
    );

    $requete->execute([
        'commande_id' => $commandeId
    ]);

    if ($requete->rowCount() !== 1) {
        throw new RuntimeException('Annulation impossible.');
    }

    // Enregistrer l'annulation dans l'historique
    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut (
            commande_id,
            compte_id,
            statut,
            date_modification
        ) VALUES (
            :commande_id,
            :compte_id,
            'annulee',
            NOW()
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    // Restituer une disponibilité au menu
    $requete = $dbConnection->prepare(
        "UPDATE menu
         SET stock_disponible = stock_disponible + 1
         WHERE id = :menu_id"
    );

    $requete->execute([
        'menu_id' => $commande['menu_id']
    ]);

    if ($requete->rowCount() !== 1) {
        throw new RuntimeException('Stock impossible à restituer.');
    }

    // Valider toutes les modifications
    $dbConnection->commit();


    // Synchroniser l'annulation avec MongoDB
    try {

        require_once __DIR__ . '/../includes/synchroniser-commande-mongodb.php';

        synchroniserCommandeMongoDB($commandeId, $dbConnection);

    } catch (Throwable $erreurMongo) {

        // Ne pas annuler la commande MySQL en cas d'erreur MongoDB
        error_log(
            'Erreur synchronisation MongoDB commande ' .
            $commandeId . ' : ' . $erreurMongo->getMessage()
        );

    }

    echo json_encode([
        'success' => true,
        'message' => 'Votre commande a bien été annulée.',
        'commande_id' => (int) $commandeId
    ]);


} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’annuler la commande.'
    ]);
}

