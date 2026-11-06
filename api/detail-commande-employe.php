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

// Vérifier les autorisations
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Créer un jeton CSRF pour les actions de l'employé
if (empty($_SESSION['csrf_employe'])) {
    $_SESSION['csrf_employe'] = bin2hex(random_bytes(32));
}

// Connexion à MySQL
require __DIR__ . '/../config/db-connection.php';

// Récupérer l'identifiant de la commande
$id = $_GET['id'] ?? '';

if (!is_string($id) || !ctype_digit($id) || (int) $id < 1) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

try {

    // Récupérer la commande et son menu
    $requete = $dbConnection->prepare(
        "SELECT
            c.*,
            m.titre AS menu_titre
         FROM commande c
         LEFT JOIN menu m ON c.menu_id = m.id
         WHERE c.id = :id"
    );

    $requete->execute([
        'id' => $id
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Récupérer l'historique des statuts
    $requete = $dbConnection->prepare(
        "SELECT
            h.statut,
            h.date_modification,
            a.prenom,
            a.nom
         FROM historique_statut h
         LEFT JOIN compte a ON h.compte_id = a.id
         WHERE h.commande_id = :commande_id
         ORDER BY h.date_modification ASC"
    );

    $requete->execute([
        'commande_id' => $id
    ]);

    $historique = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer les informations
    echo json_encode([
        'success' => true,
        'commande' => $commande,
        'historique' => $historique,
        'csrf_token' => $_SESSION['csrf_employe']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer la commande.'
    ]);
}
