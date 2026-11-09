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

// Vérifier les droits de l'utilisateur connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données du formulaire
$platId = $_POST['plat_id'] ?? '';
$nom = $_POST['nom'] ?? '';
$description = $_POST['description'] ?? '';
$categorie = $_POST['categorie'] ?? '';
$allergenes = $_POST['allergenes'] ?? [];
$jeton = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($jeton) ||
    empty($_SESSION['csrf_employe']) ||
    !hash_equals($_SESSION['csrf_employe'], $jeton)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier l'identifiant du plat
if (
    !is_string($platId) ||
    !ctype_digit($platId) ||
    (int) $platId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant du plat invalide.'
    ]);

    exit;
}

// Vérifier les données reçues
if (
    !is_string($nom) ||
    !is_string($description) ||
    !is_string($categorie) ||
    !is_array($allergenes)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données du formulaire invalides.'
    ]);

    exit;
}

// Nettoyer les champs
$nom = trim($nom);
$description = trim($description);

// Vérifier le nom
if ($nom === '' || mb_strlen($nom) > 150) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le nom du plat est obligatoire (150 caractères maximum).'
    ]);

    exit;
}

// Vérifier la description
if (mb_strlen($description) > 1000) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'La description ne peut pas dépasser 1000 caractères.'
    ]);

    exit;
}

// Vérifier la catégorie
if (!in_array($categorie, ['entree', 'plat', 'dessert'], true)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Catégorie invalide.'
    ]);

    exit;
}

// Vérifier les allergènes
$idsAllergenes = [];

foreach ($allergenes as $allergeneId) {

    if (
        !is_string($allergeneId) ||
        !ctype_digit($allergeneId) ||
        (int) $allergeneId < 1
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Identifiant d’allergène invalide.'
        ]);

        exit;
    }

    $idsAllergenes[] = (int) $allergeneId;
}

// Supprimer les doublons
$idsAllergenes = array_unique($idsAllergenes);

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Vérifier que le plat existe
    $requeteVerification = $dbConnection->prepare(
        "SELECT id
         FROM plat
         WHERE id = :plat_id
         FOR UPDATE"
    );

    $requeteVerification->execute([
        'plat_id' => $platId
    ]);

    $platExistant = $requeteVerification->fetch(PDO::FETCH_ASSOC);

    if (!$platExistant) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Plat introuvable.'
        ]);

        exit;
    }

    // Modifier les informations du plat
    $requete = $dbConnection->prepare(
        "UPDATE plat
         SET
            nom = :nom,
            description = :description,
            categorie = :categorie
         WHERE id = :plat_id"
    );

    $requete->execute([
        'nom' => $nom,
        'description' => $description,
        'categorie' => $categorie,
        'plat_id' => $platId
    ]);

    // Supprimer les anciennes associations d'allergènes
    $requeteSuppression = $dbConnection->prepare(
        "DELETE FROM plat_allergene
         WHERE plat_id = :plat_id"
    );

    $requeteSuppression->execute([
        'plat_id' => $platId
    ]);

    // Préparer l'insertion des nouveaux allergènes
    $requeteInsertion = $dbConnection->prepare(
        "INSERT INTO plat_allergene (plat_id, allergene_id)
         VALUES (:plat_id, :allergene_id)"
    );

    // Enregistrer les allergènes sélectionnés
    foreach ($idsAllergenes as $allergeneId) {

        $requeteInsertion->execute([
            'plat_id' => $platId,
            'allergene_id' => $allergeneId
        ]);
    }

    // Valider la transaction
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Le plat a été modifié avec succès.'
    ]);

} catch (PDOException $erreur) {

    // Annuler les modifications en cas d'erreur
    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le plat.'
    ]);
}
