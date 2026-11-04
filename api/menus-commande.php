
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les menus qui ont encore du stock
    $requete = $dbConnection->query(
        "SELECT
            id,
            titre,
            prix_personne,
            nb_personnes_min,
            stock_disponible,
            delai_commande_jours
        FROM menu
        WHERE stock_disponible > 0
        ORDER BY titre ASC"
    );

    $menus = $requete->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'menus' => $menus
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les menus.'
    ]);
}
