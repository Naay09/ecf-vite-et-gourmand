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

// Seul un administrateur peut consulter les employés
verifierAcces(['administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer uniquement les comptes employés
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            nom,
            prenom,
            email,
            actif
         FROM compte
         WHERE role = :role
         ORDER BY nom ASC, prenom ASC"
    );

    $requete->execute([
        'role' => 'employe'
    ]);

    $employes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Préparer le jeton CSRF pour les futures actions
    if (empty($_SESSION['csrf_admin'])) {

        $_SESSION['csrf_admin'] = bin2hex(
            random_bytes(32)
        );
    }

    // Retourner la liste au format JSON
    echo json_encode([
        'success' => true,
        'employes' => $employes,
        'csrf_token' => $_SESSION['csrf_admin']
    ]);

} catch (Throwable $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les employés.'
    ]);
}
