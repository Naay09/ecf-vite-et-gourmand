<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Démarrer ou récupérer la session existante
session_start();

// Vérifier si un utilisateur possède une session
if (!isset($_SESSION['compte_id'])) {

    echo json_encode([
        'connecte' => false
    ]);

    exit;
}

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

try {

    // Récupérer les informations actuelles du compte
    $requete = $dbConnection->prepare(
        "SELECT
            nom,
            prenom,
            role,
            actif
         FROM compte
         WHERE id = :id"
    );

    $requete->execute([
        'id' => $_SESSION['compte_id']
    ]);

    $compte = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier si le compte existe et est actif
    if (!$compte || (int) $compte['actif'] !== 1) {

        // Détruire les données de session
        $_SESSION = [];

        // Supprimer le cookie de session
        if (ini_get('session.use_cookies')) {

            $parametres = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametres['path'],
                $parametres['domain'],
                $parametres['secure'],
                $parametres['httponly']
            );
        }

        // Détruire la session PHP
        session_destroy();

        echo json_encode([
            'connecte' => false
        ]);

        exit;
    }

    // Actualiser les informations de session
    $_SESSION['prenom'] = $compte['prenom'];
    $_SESSION['nom'] = $compte['nom'];
    $_SESSION['role'] = $compte['role'];

    // Confirmer la connexion
    echo json_encode([
        'connecte' => true,
        'prenom' => $compte['prenom'],
        'nom' => $compte['nom'],
        'role' => $compte['role']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    // Ne pas considérer la session comme valide si la vérification MySQL échoue

    http_response_code(503);

    echo json_encode([
        'connecte' => false,
        'message' => 'Impossible de vérifier la session.'
    ]);
}
