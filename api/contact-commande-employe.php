<?php

header('Content-Type: application/json; charset=utf-8');

// Autoriser uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier les droits d'accès
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Récupérer les informations
$commandeId = $_POST['commande_id'] ?? '';
$action = $_POST['action'] ?? '';
$canal = $_POST['canal'] ?? '';
$motif = $_POST['motif'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($csrfToken) ||
    empty($_SESSION['csrf_employe']) ||
    !hash_equals($_SESSION['csrf_employe'], $csrfToken)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Session de formulaire invalide.'
    ]);

    exit;
}

// Vérifier les types des données
if (
    !is_string($commandeId) ||
    !is_string($action) ||
    !is_string($canal) ||
    !is_string($motif)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);

    exit;
}

// Vérifier l'identifiant de commande
$commandeId = filter_var($commandeId, FILTER_VALIDATE_INT);

if ($commandeId === false || $commandeId < 1) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

// Vérifier l'action demandée
if (!in_array($action, ['modification', 'annulation'], true)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Action invalide.'
    ]);

    exit;
}

// Vérifier le mode de contact
if (!in_array($canal, ['telephone', 'email'], true)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Mode de contact invalide.'
    ]);

    exit;
}

// Vérifier le motif
$motif = trim($motif);

if ($motif === '' || strlen($motif) > 1000) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Veuillez renseigner un motif valide.'
    ]);

    exit;
}

try {

    // Vérifier que la commande existe
    $requete = $dbConnection->prepare(
        "SELECT statut_courant
         FROM commande
         WHERE id = :id"
    );

    $requete->execute([
        'id' => $commandeId
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Vérifier que la commande n'est pas clôturée
    $statut = strtolower(trim($commande['statut_courant']));

    if (in_array($statut, ['terminee', 'terminée', 'annule', 'annulé', 'annulee'], true)) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette commande est déjà clôturée.'
        ]);

        exit;
    }

    // Enregistrer le contact dans MySQL
    $requete = $dbConnection->prepare(
        "INSERT INTO contact_commande (
            commande_id,
            compte_id,
            canal,
            motif,
            action,
            date_heure
        ) VALUES (
            :commande_id,
            :compte_id,
            :canal,
            :motif,
            :action,
            NOW()
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id'],
        'canal' => $canal,
        'motif' => $motif,
        'action' => $action
    ]);

    // Récupérer l'identifiant du contact
    $contactId = $dbConnection->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Le contact avec le client a été enregistré.',
        'contact_id' => $contactId
    ]);

} catch (PDOException $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’enregistrer le contact.'
    ]);
}
