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

    // Récupérer tous les plats
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            nom,
            description,
            categorie
         FROM plat
         ORDER BY
            FIELD(categorie, 'entree', 'plat', 'dessert'),
            nom ASC"
    );

    $requete->execute();

    $plats = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les allergènes associés aux plats
    $requeteAllergenes = $dbConnection->prepare(
        "SELECT
            pa.plat_id,
            a.id,
            a.nom
         FROM plat_allergene pa
         INNER JOIN allergene a
            ON pa.allergene_id = a.id
         ORDER BY a.nom ASC"
    );

    $requeteAllergenes->execute();

    $allergenes = $requeteAllergenes->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter les allergènes à chaque plat
    foreach ($plats as &$plat) {

        $plat['allergenes'] = [];

        foreach ($allergenes as $allergene) {

            if ($allergene['plat_id'] == $plat['id']) {

                $plat['allergenes'][] = [
                    'id' => $allergene['id'],
                    'nom' => $allergene['nom']
                ];
            }
        }
    }

    unset($plat);

    // Réponse JSON
    echo json_encode([
        'success' => true,
        'plats' => $plats
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les plats.'
    ]);
}
