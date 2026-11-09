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

// Récupérer les données
$platId = $_POST['plat_id'] ?? '';
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

// Vérifier l'identifiant du plat
if (
    !is_string($platId) ||
    !ctype_digit($platId) ||
    (int) $platId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant du plat invalide.'
    ]);

    exit;
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Vérifier que le plat existe
    $requete = $dbConnection->prepare(
        "SELECT id
         FROM plat
         WHERE id = :plat_id
         FOR UPDATE"
    );

    $requete->execute([
        'plat_id' => $platId
    ]);

    $plat = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$plat) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Plat introuvable.'
        ]);

        exit;
    }

    // Vérifier les associations avec les menus
    $requeteMenus = $dbConnection->prepare(
        "SELECT COUNT(*)
         FROM menu_plat
         WHERE plat_id = :plat_id"
    );

    $requeteMenus->execute([
        'plat_id' => $platId
    ]);

    $nombreMenus = (int) $requeteMenus->fetchColumn();

    // Bloquer la suppression si le plat est utilisé
    if ($nombreMenus > 0) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Ce plat est utilisé dans un ou plusieurs menus. Il ne peut pas être supprimé.'
        ]);

        exit;
    }

    // Supprimer les associations avec les allergènes
    $requeteAllergenes = $dbConnection->prepare(
        "DELETE FROM plat_allergene
         WHERE plat_id = :plat_id"
    );

    $requeteAllergenes->execute([
        'plat_id' => $platId
    ]);

    // Supprimer le plat
    $requeteSuppression = $dbConnection->prepare(
        "DELETE FROM plat
         WHERE id = :plat_id"
    );

    $requeteSuppression->execute([
        'plat_id' => $platId
    ]);

    // Valider la transaction
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Le plat a été supprimé avec succès.'
    ]);

} catch (PDOException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de supprimer le plat.'
    ]);
}
