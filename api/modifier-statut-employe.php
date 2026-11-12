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

// Vérifier les droits
require __DIR__ . '/../includes/auth.php';

// Seul un administrateur peut gérer les comptes
verifierAcces(['administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données
$employeId = $_POST['employe_id'] ?? '';
$actif = $_POST['actif'] ?? '';
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

// Vérifier l'identifiant de l'employé
if (
    !is_string($employeId) ||
    !ctype_digit($employeId) ||
    (int) $employeId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de l’employé invalide.'
    ]);

    exit;
}

// Vérifier le statut demandé
if (
    !is_string($actif) ||
    ($actif !== '0' && $actif !== '1')
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Statut du compte invalide.'
    ]);

    exit;
}

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Rechercher le compte employé
    $requeteEmploye = $dbConnection->prepare(
        "SELECT
            id,
            nom,
            prenom,
            actif
         FROM compte
         WHERE id = :id
         AND role = 'employe'
         FOR UPDATE"
    );

    $requeteEmploye->execute([
        'id' => $employeId
    ]);

    $employe = $requeteEmploye->fetch(PDO::FETCH_ASSOC);

    // Vérifier que le compte existe
    if (!$employe) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Compte employé introuvable.'
        ]);

        exit;
    }

    // Vérifier si le statut est déjà celui demandé
    if ((int) $employe['actif'] === (int) $actif) {

        $dbConnection->rollBack();

        echo json_encode([
            'success' => true,
            'message' => 'Le compte possède déjà ce statut.'
        ]);

        exit;
    }

    // Mettre à jour le statut
    $requeteModification = $dbConnection->prepare(
        "UPDATE compte
         SET actif = :actif
         WHERE id = :id
         AND role = 'employe'"
    );

    $requeteModification->execute([
        'actif' => $actif,
        'id' => $employeId
    ]);

    // Valider la modification
    $dbConnection->commit();

    // Préparer le message
    if ($actif === '1') {

        $message = 'Le compte employé a été réactivé.';

    } else {

        $message = 'Le compte employé a été désactivé.';
    }

    // Retourner le résultat
    echo json_encode([
        'success' => true,
        'message' => $message,
        'employe_id' => (int) $employeId,
        'actif' => (int) $actif
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le statut du compte.'
    ]);
}
