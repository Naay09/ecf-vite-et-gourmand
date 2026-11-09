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

// Vérifier la connexion
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

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

    // Récupérer uniquement une commande du client connecté
    $requete = $dbConnection->prepare(
        "SELECT
            c.*,
            m.titre AS menu_titre,
            m.prix_personne,
            m.nb_personnes_min,
            m.delai_commande_jours
         FROM commande c
         LEFT JOIN menu m ON c.menu_id = m.id
         WHERE c.id = :id
         AND c.compte_id = :compte_id"
    );

    $requete->execute([
        'id' => $id,
        'compte_id' => $_SESSION['compte_id']
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
            statut,
            date_modification
         FROM historique_statut
         WHERE commande_id = :commande_id
         ORDER BY date_modification ASC"
    );

    $requete->execute([
        'commande_id' => $id
    ]);

    $historique = $requete->fetchAll(PDO::FETCH_ASSOC);

    /* VÉRIFIER SI UN AVIS EXISTE DÉJÀ */

    // Préparer la requête
    $requeteAvis = $dbConnection->prepare(
        "SELECT
            id,
            note,
            commentaire,
            moderation
        FROM avis
        WHERE commande_id = :commande_id"
    );

    // Exécuter la requête
    $requeteAvis->execute([
        'commande_id' => $commande['id']
    ]);

    // Récupérer l'avis éventuel
    $avis = $requeteAvis->fetch(PDO::FETCH_ASSOC);

    // Créer le jeton CSRF si nécessaire
    if (empty($_SESSION['csrf_compte'])) {
        $_SESSION['csrf_compte'] = bin2hex(random_bytes(32));
    }

    // Envoyer les informations à JavaScript
    echo json_encode([
        'success' => true,
        'commande' => $commande,
        'historique' => $historique,
        'avis' => $avis,
        'csrf_token' => $_SESSION['csrf_compte']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer le détail de la commande.'
    ]);
}
