
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

// Vérifier la connexion et le rôle client
require_once __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Vérifier le jeton de sécurité
$jeton = $_POST['csrf_token'] ?? '';

if (
    !is_string($jeton) ||
    !isset($_SESSION['csrf_compte']) ||
    !hash_equals($_SESSION['csrf_compte'], $jeton)
) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Session invalide. Veuillez actualiser la page.'
    ]);
    exit;
}

// Récupérer les informations du formulaire
$prenom = $_POST['prenom'] ?? '';
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$adresse = $_POST['adresse'] ?? '';

// Vérifier le type des données
$valeurs = [$prenom, $nom, $email, $telephone, $adresse];

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
    $adresse === ''
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

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

try {

    // Mettre à jour uniquement le compte connecté
    $requete = $dbConnection->prepare(
        'UPDATE compte
         SET prenom = :prenom,
             nom = :nom,
             email = :email,
             telephone = :telephone,
             adresse = :adresse
         WHERE id = :id'
    );

    $requete->execute([
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'adresse' => $adresse,
        'id' => $_SESSION['compte_id']
    ]);

    // Actualiser les informations de session
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom'] = $nom;

    echo json_encode([
        'success' => true,
        'message' => 'Vos informations ont bien été mises à jour.'
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
            'message' => 'Impossible de modifier vos informations.'
        ]);
    }
}
