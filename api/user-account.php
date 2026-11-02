
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Autoriser uniquement la méthode GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
    exit;
}

// Vérifier que le client est connecté
require_once __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Créer un jeton de sécurité pour le formulaire
if (!isset($_SESSION['csrf_compte'])) {
    $_SESSION['csrf_compte'] = bin2hex(random_bytes(32));
}

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Récupérer l'identifiant du client connecté
$compteId = $_SESSION['compte_id'];

try {

    // Rechercher les informations du client
    $requete = $dbConnection->prepare(
        'SELECT prenom, nom, email, telephone, adresse
         FROM compte
         WHERE id = :id'
    );

    $requete->execute([
        'id' => $compteId
    ]);

    $compte = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$compte) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Compte introuvable.'
        ]);
        exit;
    }

    // Renvoyer les informations à JavaScript
    echo json_encode([
        'success' => true,
        'compte' => $compte,
        'csrf_token' => $_SESSION['csrf_compte']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de charger les informations du compte.'
    ]);
}
