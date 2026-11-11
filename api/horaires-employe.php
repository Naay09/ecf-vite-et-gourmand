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

    // Récupérer les horaires du lundi au dimanche
    $requete = $dbConnection->prepare(
        "SELECT
            id,
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

    // Exécuter la requête
    $requete->execute();

    // Récupérer les résultats
    $horaires = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les horaires au format JSON
    echo json_encode([
        'success' => true,
        'horaires' => $horaires,
        'csrf_token' => $_SESSION['csrf_employe']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les horaires.'
    ]);
}
