
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

    // Récupérer les menus avec leur thème et leur régime
    $requete = $dbConnection->prepare(
        "SELECT
            m.id,
            m.titre,
            m.description,
            m.nb_personnes_min,
            m.prix_personne,
            m.stock_disponible,
            m.conditions,
            m.delai_commande_jours,
            m.theme_id,
            m.regime_id,
            t.nom AS theme_nom,
            r.nom AS regime_nom

         FROM menu m

         LEFT JOIN theme t
            ON m.theme_id = t.id

         LEFT JOIN regime r
            ON m.regime_id = r.id

         ORDER BY m.titre ASC"
    );

    $requete->execute();

    $menus = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer les informations au JavaScript
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
