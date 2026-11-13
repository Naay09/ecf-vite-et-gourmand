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
require __DIR__ . '/../config/db-connection.php';

// Protection contre les tentatives de connexion répétées
$limiteTentatives = 5;

// Identifier l'adresse e-mail sans la stocker en clair
$emailHash = hash('sha256', strtolower($email));

// Récupérer l'adresse IP du visiteur
$adresseIp = $_SERVER['REMOTE_ADDR'] ?? '';

try {

    // Compter les échecs récents pour cet e-mail ou cette adresse IP
    $requeteTentatives = $dbConnection->prepare(
        "SELECT
            SUM(CASE WHEN email_hash = :email_hash THEN 1 ELSE 0 END) AS echecs_email,
            SUM(CASE WHEN adresse_ip = :adresse_ip THEN 1 ELSE 0 END) AS echecs_ip
        FROM tentative_connexion
        WHERE date_tentative >= DATE_SUB(
            NOW(),
            INTERVAL 15 MINUTE
        )"
    );

    $requeteTentatives->execute([
        'email_hash' => $emailHash,
        'adresse_ip' => $adresseIp
    ]);

    $tentatives = $requeteTentatives->fetch(PDO::FETCH_ASSOC);

    // Bloquer si la limite est atteinte
    if (
        (int) $tentatives['echecs_email'] >= $limiteTentatives ||
        (int) $tentatives['echecs_ip'] >= $limiteTentatives
    ) {

        http_response_code(429);

        echo json_encode([
            'success' => false,
            'message' => 'Trop de tentatives de connexion. Réessayez dans quelques minutes.'
        ]);

        exit;
    }

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
        // Enregistrer la tentative échouée
        $requeteEchec = $dbConnection->prepare(
            "INSERT INTO tentative_connexion
                (email_hash, adresse_ip)
            VALUES
                (:email_hash, :adresse_ip)"
        );

        $requeteEchec->execute([
            'email_hash' => $emailHash,
            'adresse_ip' => $adresseIp
        ]);

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Adresse e-mail ou mot de passe incorrect.'
        ]);

        exit;
    }

    // Effacer les échecs de cet e-mail après connexion réussie
    $requeteNettoyage = $dbConnection->prepare(
        "DELETE FROM tentative_connexion
            WHERE email_hash = :email_hash"
    );

    $requeteNettoyage->execute([
        'email_hash' => $emailHash
    ]);

    // Démarrer une session PHP sécurisée
    require __DIR__ . '/../includes/session.php';

    // Renouveler l'identifiant de session
    session_regenerate_id(true);

    // Enregistrer les informations du compte dans la session
    $_SESSION['compte_id'] = $compte['id'];
    $_SESSION['prenom'] = $compte['prenom'];
    $_SESSION['nom'] = $compte['nom'];
    $_SESSION['role'] = $compte['role'];
    $_SESSION['empreinte_mot_de_passe'] = hash('sha256', $compte['mot_de_passe_hash']);

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
