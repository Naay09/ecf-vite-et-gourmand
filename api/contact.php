
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

// Démarrer une session PHP
session_start();

// Vérifier si un message a déjà été envoyé récemment
if (isset($_SESSION['dernier_envoi_contact'])) {

    $tempsEcoule = time() - $_SESSION['dernier_envoi_contact'];

    if ($tempsEcoule < 60) {

        http_response_code(429);

        echo json_encode([
            'success' => false,
            'message' => 'Veuillez patienter une minute avant un nouvel envoi.'
        ]);

        exit;
    }
}

// Protection anti-spam
$siteWeb = $_POST['site_web'] ?? '';

if (!is_string($siteWeb) || trim($siteWeb) !== '') {

    // Simuler une réponse positive sans envoyer d'e-mail
    echo json_encode([
        'success' => true,
        'message' => 'Votre message a bien été envoyé.'
    ]);

    exit;
}

// Charger le service d'envoi d'e-mails
require_once __DIR__ . '/../includes/mail-service.php';

// Liste des champs du formulaire
$champs = [
    'prenom',
    'nom',
    'email',
    'telephone',
    'titre',
    'date_evenement',
    'convives',
    'message'
];

$donnees = [];

// Récupérer et nettoyer les valeurs
foreach ($champs as $champ) {

    $valeur = $_POST[$champ] ?? '';

    if (!is_string($valeur)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Données invalides.'
        ]);
        exit;
    }

    $donnees[$champ] = trim($valeur);
}

// Vérifier les champs obligatoires
if (
    $donnees['prenom'] === '' ||
    $donnees['nom'] === '' ||
    $donnees['email'] === '' ||
    $donnees['titre'] === '' ||
    $donnees['message'] === ''
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez remplir tous les champs obligatoires.'
    ]);
    exit;
}

// Vérifier le format de l'adresse e-mail
if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Adresse e-mail invalide.'
    ]);
    exit;
}

// Vérifier la longueur des champs principaux
if (
    strlen($donnees['prenom']) > 100 ||
    strlen($donnees['nom']) > 100 ||
    strlen($donnees['email']) > 254 ||
    strlen($donnees['titre']) > 150 ||
    strlen($donnees['message']) > 5000
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Un ou plusieurs champs sont trop longs.'
    ]);
    exit;
}

// Vérifier le nombre de convives
if ($donnees['convives'] !== '') {

    $convives = $donnees['convives'];

    if (
        !is_numeric($convives) ||
        (float) $convives != (int) $convives ||
        (int) $convives < 1
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nombre de convives invalide.'
        ]);
        exit;
    }
}

// Vérifier les champs facultatifs
if (
    strlen($donnees['telephone']) > 30 ||
    strlen($donnees['date_evenement']) > 10
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);
    exit;
}

// Préparer le contenu de l'e-mail
$sujet = 'Nouvelle demande de contact - Vite & Gourmand';

$message = "Nouvelle demande de contact\n\n"
    . "Prénom : " . $donnees['prenom'] . "\n"
    . "Nom : " . $donnees['nom'] . "\n"
    . "E-mail : " . $donnees['email'] . "\n"
    . "Téléphone : " . $donnees['telephone'] . "\n"
    . "Date souhaitée : " . $donnees['date_evenement'] . "\n"
    . "Convives : " . $donnees['convives'] . "\n\n"
    . "Objet : " . $donnees['titre'] . "\n\n"
    . "Message :\n" . $donnees['message'];

// Récupérer l'adresse de réception
$config = require __DIR__ . '/../config/mail.php';
$destinataire = $config['contact_email'];

// Envoyer l'e-mail avec notre fonction commune
$resultat = envoyerMail($destinataire, $sujet, $message);

// Retourner une réponse à JavaScript
if ($resultat) {

    // Enregistrer le moment du dernier envoi réussi
    $_SESSION['dernier_envoi_contact'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'Votre message a bien été envoyé.'
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'L’envoi du message a échoué.'
    ]);
}
