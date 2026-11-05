
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Autoriser uniquement GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier que le client est connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les commandes du client connecté
    $requete = $dbConnection->prepare(
        "SELECT
            c.id,
            c.menu_id,
            m.titre AS menu_titre,
            c.date_prestation,
            c.heure_livraison,
            c.nb_personnes,
            c.adresse_livraison,
            c.ville_livraison,
            c.prix_menu,
            c.remise,
            c.frais_livraison,
            c.total,
            c.statut_courant
         FROM commande c
         LEFT JOIN menu m ON c.menu_id = m.id
         WHERE c.compte_id = :compte_id
         ORDER BY c.id DESC"
    );

    $requete->execute([
        'compte_id' => $_SESSION['compte_id']
    ]);

    $commandes = $requete->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'commandes' => $commandes
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les commandes.'
    ]);
}




