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

// Vérifier les droits d'accès
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer toutes les commandes
    $requete = $dbConnection->query(
        "SELECT
            c.id,
            c.date_creation,
            c.date_prestation,
            c.heure_livraison,
            c.prenom_client,
            c.nom_client,
            c.email_client,
            c.telephone_client,
            c.adresse_livraison,
            c.ville_livraison,
            c.nb_personnes,
            c.total,
            c.statut_courant,
            m.titre AS menu_titre

        FROM commande c

        LEFT JOIN menu m ON c.menu_id = m.id

        ORDER BY c.date_creation DESC, c.id DESC"
    );

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
