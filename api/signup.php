<?php

header('Content-Type: application/json; charset=utf-8');

// Accepter uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
    exit;
}

// Connexion à la base de données
require_once __DIR__ . '/../config/db-connection.php';
require_once __DIR__ . '/../includes/mail-service.php';

// Récupérer les informations du formulaire
$prenom = $_POST['prenom'] ?? '';
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$adresse = $_POST['adresse'] ?? '';
$motDePasse = $_POST['mot_de_passe'] ?? '';
$confirmation = $_POST['confirmation_mot_de_passe'] ?? '';

// Vérifier que les valeurs sont des chaînes de caractères
$valeurs = [
    $prenom, $nom, $email, $telephone,
    $adresse, $motDePasse, $confirmation
];

foreach ($valeurs as $valeur) {
    if (!is_string($valeur)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Données invalides.'
        ]);
        exit;
    }
}

// Supprimer les espaces inutiles
$prenom = trim($prenom);
$nom = trim($nom);
$email = trim($email);
$telephone = trim($telephone);
$adresse = trim($adresse);

// Vérifier les champs obligatoires
if (
    $prenom === '' ||
    $nom === '' ||
    $email === '' ||
    $telephone === '' ||
    $adresse === '' ||
    $motDePasse === '' ||
    $confirmation === ''
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont obligatoires.'
    ]);
    exit;
}

// Vérifier l'adresse e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Adresse e-mail invalide.'
    ]);
    exit;
}

// Vérifier la longueur des champs
if (
    strlen($prenom) > 100 ||
    strlen($nom) > 100 ||
    strlen($email) > 254 ||
    strlen($telephone) > 30 ||
    strlen($adresse) > 255
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Un ou plusieurs champs sont trop longs.'
    ]);
    exit;
}

// Vérifier le mot de passe
if (
    strlen($motDePasse) < 10 ||
    strlen($motDePasse) > 72 ||
    !preg_match('/[A-Z]/', $motDePasse) ||
    !preg_match('/[a-z]/', $motDePasse) ||
    !preg_match('/[0-9]/', $motDePasse) ||
    !preg_match('/[^A-Za-z0-9\s]/', $motDePasse)
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe ne respecte pas les critères demandés.'
    ]);
    exit;
}

// Vérifier la confirmation
if ($motDePasse !== $confirmation) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Les mots de passe ne correspondent pas.'
    ]);
    exit;
}

try {

    // Vérifier si l'e-mail existe déjà
    $requete = $dbConnection->prepare(
        'SELECT id FROM compte WHERE email = :email'
    );

    $requete->execute([
        'email' => $email
    ]);

    if ($requete->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette adresse e-mail est déjà utilisée.'
        ]);
        exit;
    }

    // Protéger le mot de passe
    $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

    // Enregistrer le nouveau compte
    $requete = $dbConnection->prepare(
        "INSERT INTO compte
        (prenom, nom, email, telephone, adresse,
         mot_de_passe_hash, role, actif)
        VALUES
        (:prenom, :nom, :email, :telephone, :adresse,
         :hash, 'utilisateur', 1)"
    );

    $requete->execute([
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'adresse' => $adresse,
        'hash' => $hash
    ]);

    // E-mail de bienvenue
$sujet = 'Bienvenue chez Vite & Gourmand';

$messageBienvenue = "Bonjour " . $prenom . ",\n\n"
    . "Bienvenue chez Vite & Gourmand !\n\n"
    . "Votre compte client a bien été créé.\n"
    . "Vous pouvez désormais vous connecter à votre espace personnel.\n\n"
    . "À très bientôt,\n"
    . "L'équipe Vite & Gourmand";

// Envoyer l'e-mail au nouveau client
$emailEnvoye = envoyerMail($email, $sujet, $messageBienvenue);

// Enregistrer une erreur si l'e-mail n'a pas pu être envoyé
if (!$emailEnvoye) {
    error_log('E-mail de bienvenue non envoyé après inscription.');
}

    // Réponse en cas de réussite
    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Votre compte a bien été créé.'
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    if ($erreur->getCode() === '23000') {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette adresse e-mail est déjà utilisée.'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la création du compte.'
        ]);
    }
}
