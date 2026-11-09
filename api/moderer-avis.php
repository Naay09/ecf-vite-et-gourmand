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

// Récupérer les informations
$avisId = $_POST['avis_id'] ?? '';
$moderation = $_POST['moderation'] ?? '';
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

// Vérifier l'identifiant de l'avis
if (
    !is_string($avisId) ||
    !ctype_digit($avisId) ||
    (int) $avisId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de l’avis invalide.'
    ]);

    exit;
}

// Vérifier le statut demandé
if (
    !is_string($moderation) ||
    !in_array($moderation, ['valide', 'refusee'], true)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Décision de modération invalide.'
    ]);

    exit;
}

try {

    // Mettre à jour uniquement un avis en attente
    $requete = $dbConnection->prepare(
        "UPDATE avis
         SET moderation = :moderation
         WHERE id = :avis_id
           AND moderation = 'en_attente'"
    );

    $requete->execute([
        'moderation' => $moderation,
        'avis_id' => $avisId
    ]);

    // Vérifier que l'avis a bien été modéré
    if ($requete->rowCount() !== 1) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cet avis est introuvable ou a déjà été modéré.'
        ]);

        exit;
    }

    // Préparer le message de confirmation
    if ($moderation === 'valide') {

        $message = 'L’avis a été validé.';

    } else {

        $message = 'L’avis a été refusé.';
    }

    // Réponse positive
    echo json_encode([
        'success' => true,
        'message' => $message,
        'moderation' => $moderation
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modérer cet avis.'
    ]);
}
