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

// Créer un jeton CSRF si nécessaire
if (empty($_SESSION['csrf_employe'])) {
    $_SESSION['csrf_employe'] = bin2hex(random_bytes(32));
}

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les allergènes
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            nom
         FROM allergene
         ORDER BY nom ASC"
    );

    $requete->execute();

    $allergenes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'allergenes' => $allergenes,
        'csrf_token' => $_SESSION['csrf_employe']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les allergènes.'
    ]);
}
