<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier les autorisations
require __DIR__ . '/../includes/auth.php';

// Seul un administrateur peut créer un employé
verifierAcces(['administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Service d'envoi d'e-mails
require_once __DIR__ . '/../includes/mail-service.php';

// Récupérer les informations
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$motDePasse = $_POST['mot_de_passe'] ?? '';
$jeton = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($jeton) ||
    empty($_SESSION['csrf_admin']) ||
    !hash_equals($_SESSION['csrf_admin'], $jeton)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier les types
if (
    !is_string($nom) ||
    !is_string($prenom) ||
    !is_string($email) ||
    !is_string($motDePasse)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Informations invalides.'
    ]);

    exit;
}

// Nettoyer les champs
$nom = trim($nom);
$prenom = trim($prenom);
$email = strtolower(trim($email));

// Vérifier les champs obligatoires
if (
    $nom === '' ||
    $prenom === '' ||
    mb_strlen($nom) > 100 ||
    mb_strlen($prenom) > 100
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le nom et le prénom sont obligatoires.'
    ]);

    exit;
}

// Vérifier l'adresse e-mail
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Adresse e-mail invalide.'
    ]);

    exit;
}

// Vérifier le mot de passe
if (
    strlen($motDePasse) < 12 ||
    strlen($motDePasse) > 72
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe doit contenir entre 12 et 72 caractères.'
    ]);

    exit;
}

try {

    // Vérifier si l'adresse e-mail est déjà utilisée
    $requeteVerification = $dbConnection->prepare(
        "SELECT id
         FROM compte
         WHERE email = :email
         LIMIT 1"
    );

    $requeteVerification->execute([
        'email' => $email
    ]);

    if ($requeteVerification->fetch()) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette adresse e-mail est déjà utilisée.'
        ]);

        exit;
    }

    // Hacher le mot de passe
    $motDePasseHash = password_hash(
        $motDePasse,
        PASSWORD_DEFAULT
    );

    // Créer le compte employé
    $requeteCreation = $dbConnection->prepare(
        "INSERT INTO compte (
            nom,
            prenom,
            email,
            mot_de_passe_hash,
            role,
            actif
        )
        VALUES (
            :nom,
            :prenom,
            :email,
            :mot_de_passe_hash,
            'employe',
            1
        )"
    );

    $requeteCreation->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'mot_de_passe_hash' => $motDePasseHash
    ]);

    // Le compte a été enregistré
    $employeId = $dbConnection->lastInsertId();

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de créer le compte employé.'
    ]);

    exit;
}

// Préparer l'e-mail de notification
$sujet = 'Création de votre compte Vite & Gourmand';

$message = "Bonjour " . $prenom . ",\n\n"
    . "Un compte employé a été créé pour vous sur "
    . "l'application Vite & Gourmand.\n\n"
    . "Votre identifiant de connexion est : "
    . $email . "\n\n"
    . "Pour obtenir votre mot de passe, "
    . "veuillez vous rapprocher de votre administrateur.\n\n"
    . "Cordialement,\n"
    . "L'équipe Vite & Gourmand";

// Envoyer l'e-mail après la création du compte
$emailEnvoye = false;

try {

    $resultatMail = envoyerMail(
        $email,
        $sujet,
        $message
    );

    $emailEnvoye = $resultatMail !== false;

} catch (Throwable $erreur) {

    error_log($erreur->getMessage());
}

// Retourner le résultat
echo json_encode([
    'success' => true,
    'message' => $emailEnvoye
        ? 'Le compte employé a été créé et un e-mail a été envoyé.'
        : 'Le compte employé a été créé, mais l’e-mail n’a pas pu être envoyé.',
    'employe_id' => $employeId,
    'email_envoye' => $emailEnvoye
]);
