
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Démarrer ou récupérer la session existante
session_start();

// Vérifier si un utilisateur est connecté
if (isset($_SESSION['compte_id'])) {

    echo json_encode([
        'connecte' => true,
        'prenom' => $_SESSION['prenom'],
        'nom' => $_SESSION['nom'],
        'role' => $_SESSION['role']
    ]);

} else {

    echo json_encode([
        'connecte' => false
    ]);

}
