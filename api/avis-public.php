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

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer uniquement les avis validés
    $requete = $dbConnection->prepare(
        "SELECT
            a.id,
            a.note,
            a.commentaire,
            a.date_creation,
            c.prenom_client,
            m.titre AS menu_titre

        FROM avis a

        INNER JOIN commande c
            ON a.commande_id = c.id

        INNER JOIN menu m
            ON c.menu_id = m.id

        WHERE a.moderation = :moderation

        ORDER BY a.date_creation DESC, a.id DESC

        LIMIT 6"
    );

    // Exécuter la requête
    $requete->execute([
        'moderation' => 'valide'
    ]);

    // Récupérer les avis
    $avis = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'avis' => $avis
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les avis clients.'
    ]);
}
