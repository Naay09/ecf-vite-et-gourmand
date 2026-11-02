
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Autoriser uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Récupérer la session
session_start();

// Vider les informations de session
$_SESSION = [];

// Supprimer le cookie de session
if (ini_get('session.use_cookies')) {

    $parametres = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 3600,
        $parametres['path'],
        $parametres['domain'],
        $parametres['secure'],
        $parametres['httponly']
    );
}

// Détruire la session
session_destroy();

// Confirmer la déconnexion
echo json_encode([
    'success' => true,
    'message' => 'Déconnexion réussie.'
]);
