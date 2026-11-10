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

// Vérifier les droits
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer l'identifiant du menu
$menuId = $_GET['menu_id'] ?? '';

// Vérifier l'identifiant
if (
    !is_string($menuId) ||
    !ctype_digit($menuId) ||
    (int) $menuId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant du menu invalide.'
    ]);

    exit;
}

try {

    // Récupérer les plats associés au menu
    $requete = $dbConnection->prepare(
        "SELECT
            p.id,
            p.nom,
            p.categorie

         FROM menu_plat mp

         INNER JOIN plat p
            ON mp.plat_id = p.id

         WHERE mp.menu_id = :menu_id"
    );

    $requete->execute([
        'menu_id' => $menuId
    ]);

    $plats = $requete->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'plats' => $plats
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les plats du menu.'
    ]);
}