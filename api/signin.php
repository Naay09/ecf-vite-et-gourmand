<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Accepter uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Récupérer les informations du formulaire
$email = $_POST['email'] ?? '';
$motDePasse = $_POST['password'] ?? '';

// Vérifier les données reçues
if (!is_string($email) || !is_string($motDePasse)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);

    exit;
}

$email = trim($email);

// Vérifier les champs obligatoires
if ($email === '' || $motDePasse === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Veuillez remplir tous les champs.'
    ]);

    exit;
}

// Vérifier le format de l'e-mail
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254 ||
    strlen($motDePasse) > 1024
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiants invalides.'
    ]);

    exit;
}

// Connexion à la base de données
require_once __DIR__ . '/../config/db-connection.php';

try {

    // Rechercher le compte avec son adresse e-mail
    $requete = $dbConnection->prepare(
        'SELECT id, nom, prenom, email, mot_de_passe_hash, role, actif
         FROM compte
         WHERE email = :email'
    );

    $requete->execute([
        'email' => $email
    ]);

    $compte = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier le compte, son mot de passe et son état
    if (
        !$compte ||
        !password_verify($motDePasse, $compte['mot_de_passe_hash']) ||
        (int) $compte['actif'] !== 1
    ) {

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Adresse e-mail ou mot de passe incorrect.'
        ]);

        exit;
    }

    // Démarrer une session PHP
    session_start();

    // Renouveler l'identifiant de session
    session_regenerate_id(true);

    // Enregistrer les informations du compte dans la session
    $_SESSION['compte_id'] = $compte['id'];
    $_SESSION['prenom'] = $compte['prenom'];
    $_SESSION['nom'] = $compte['nom'];
    $_SESSION['role'] = $compte['role'];

    // Confirmer la connexion
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie.',
        'role' => $compte['role']
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de la connexion.'
    ]);
}
