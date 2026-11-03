
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

// Récupérer les informations du formulaire
$jeton = $_POST['jeton'] ?? '';
$motDePasse = $_POST['mot_de_passe'] ?? '';
$confirmation = $_POST['confirmation_mot_de_passe'] ?? '';

// Vérifier les types des données
if (
    !is_string($jeton) ||
    !is_string($motDePasse) ||
    !is_string($confirmation)
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);
    exit;
}

// Vérifier le format du jeton
if (!preg_match('/^[a-f0-9]{64}$/', $jeton)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Lien de réinitialisation invalide.'
    ]);
    exit;
}

// Vérifier le nouveau mot de passe
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

// Calculer l'empreinte du jeton
$jetonHash = hash('sha256', $jeton);

// Connexion à la base de données
require_once __DIR__ . '/../config/db-connection.php';

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Vérifier le jeton et sa validité
    $requete = $dbConnection->prepare(
        "SELECT r.id, r.compte_id
         FROM reinitialisation_mot_de_passe r
         INNER JOIN compte c ON r.compte_id = c.id
         WHERE r.jeton_hash = :jeton_hash
         AND r.date_expiration > NOW()
         AND r.date_utilisation IS NULL
         AND c.actif = 1
         AND c.role = 'utilisateur'
         FOR UPDATE"
    );

    $requete->execute([
        'jeton_hash' => $jetonHash
    ]);

    $demande = $requete->fetch(PDO::FETCH_ASSOC);

    // Refuser un jeton invalide, expiré ou déjà utilisé
    if (!$demande) {

        $dbConnection->rollBack();

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Ce lien est invalide ou a expiré.'
        ]);

        exit;
    }

    // Hacher le nouveau mot de passe
    $hash = password_hash($motDePasse, PASSWORD_DEFAULT);

    // Modifier le mot de passe du compte
    $requete = $dbConnection->prepare(
        'UPDATE compte
         SET mot_de_passe_hash = :hash
         WHERE id = :id'
    );

    $requete->execute([
        'hash' => $hash,
        'id' => $demande['compte_id']
    ]);

    // Invalider les liens de réinitialisation du compte
    $requete = $dbConnection->prepare(
        'UPDATE reinitialisation_mot_de_passe
         SET date_utilisation = NOW()
         WHERE compte_id = :compte_id
         AND date_utilisation IS NULL'
    );

    $requete->execute([
        'compte_id' => $demande['compte_id']
    ]);

    // Valider les modifications
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Votre mot de passe a bien été modifié.'
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le mot de passe.'
    ]);
}
