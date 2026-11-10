<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

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

try {

    // Récupérer les thèmes
    $requeteThemes = $dbConnection->prepare(
        "SELECT id, nom
         FROM theme
         ORDER BY nom ASC"
    );

    $requeteThemes->execute();

    $themes = $requeteThemes->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les régimes
    $requeteRegimes = $dbConnection->prepare(
        "SELECT id, nom
         FROM regime
         ORDER BY nom ASC"
    );

    $requeteRegimes->execute();

    $regimes = $requeteRegimes->fetchAll(PDO::FETCH_ASSOC);

    // Créer un jeton CSRF si nécessaire
    if (empty($_SESSION['csrf_employe'])) {
        $_SESSION['csrf_employe'] = bin2hex(random_bytes(32));
    }

    // Envoyer la réponse JSON
    echo json_encode([
        'success' => true,
        'themes' => $themes,
        'regimes' => $regimes,
        'csrf_token' => $_SESSION['csrf_employe']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les thèmes et les régimes.'
    ]);
}
