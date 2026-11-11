<?php

header('Content-Type: application/json; charset=utf-8');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les horaires dans l'ordre de la semaine
    $requete = $dbConnection->prepare(
        "SELECT
            jour,
            ouverture,
            fermeture,
            ferme
         FROM horaires
         ORDER BY FIELD(
            jour,
            'lundi',
            'mardi',
            'mercredi',
            'jeudi',
            'vendredi',
            'samedi',
            'dimanche'
         )"
    );

    $requete->execute();

    $horaires = $requete->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'horaires' => $horaires
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les horaires.'
    ]);
}
