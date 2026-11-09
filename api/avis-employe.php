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

// Créer un jeton CSRF si nécessaire
if (empty($_SESSION['csrf_employe'])) {
    $_SESSION['csrf_employe'] = bin2hex(random_bytes(32));
}

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les avis en attente de modération
    $requete = $dbConnection->prepare(
        "SELECT
            a.id,
            a.commande_id,
            a.note,
            a.commentaire,
            a.date_creation,
            a.moderation,
            c.prenom_client,
            c.nom_client,
            m.titre AS menu_titre

        FROM avis a

        INNER JOIN commande c
            ON a.commande_id = c.id

        INNER JOIN menu m
            ON c.menu_id = m.id

        WHERE a.moderation = :moderation

        ORDER BY a.date_creation ASC, a.id ASC"
    );

    $requete->execute([
        'moderation' => 'en_attente'
    ]);

    // Récupérer les résultats
    $avis = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Envoyer la réponse JSON
    echo json_encode([
        'success' => true,
        'avis' => $avis,
        'csrf_token' => $_SESSION['csrf_employe']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les avis.'
    ]);
}
