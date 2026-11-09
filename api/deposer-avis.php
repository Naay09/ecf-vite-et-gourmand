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

// Vérifier que le client est connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Récupérer les informations
$commandeId = $_POST['commande_id'] ?? '';
$note = $_POST['note'] ?? '';
$commentaire = $_POST['commentaire'] ?? '';
$jeton = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($jeton) ||
    empty($_SESSION['csrf_compte']) ||
    !hash_equals($_SESSION['csrf_compte'], $jeton)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier les données reçues
if (
    !is_string($commandeId) ||
    !is_string($note) ||
    !is_string($commentaire)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);

    exit;
}

// Vérifier l'identifiant de commande
$commandeId = filter_var(
    $commandeId,
    FILTER_VALIDATE_INT
);

// Vérifier la note
$note = filter_var(
    $note,
    FILTER_VALIDATE_INT
);

if ($commandeId === false || $commandeId < 1) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

if ($note === false || $note < 1 || $note > 5) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'La note doit être comprise entre 1 et 5.'
    ]);

    exit;
}

// Vérifier le commentaire
$commentaire = trim($commentaire);

if ($commentaire === '' || mb_strlen($commentaire) > 1000) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Veuillez saisir un commentaire de 1000 caractères maximum.'
    ]);

    exit;
}

try {

    // Vérifier que la commande appartient au client
    $requete = $dbConnection->prepare(
        "SELECT statut_courant
         FROM commande
         WHERE id = :commande_id
           AND compte_id = :compte_id"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
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

    // Autoriser uniquement les commandes terminées
    if ($commande['statut_courant'] !== 'terminee') {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Vous pourrez déposer un avis lorsque votre commande sera terminée.'
        ]);

        exit;
    }

    // Vérifier qu'aucun avis n'existe déjà
    $requete = $dbConnection->prepare(
        "SELECT id
         FROM avis
         WHERE commande_id = :commande_id"
    );

    $requete->execute([
        'commande_id' => $commandeId
    ]);

    $avisExistant = $requete->fetch(PDO::FETCH_ASSOC);

    if ($avisExistant) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Vous avez déjà déposé un avis pour cette commande.'
        ]);

        exit;
    }

    // Enregistrer l'avis
    $requete = $dbConnection->prepare(
        "INSERT INTO avis (
            commande_id,
            note,
            commentaire,
            moderation
        ) VALUES (
            :commande_id,
            :note,
            :commentaire,
            'en_attente'
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'note' => $note,
        'commentaire' => $commentaire
    ]);

    // Réponse positive
    echo json_encode([
        'success' => true,
        'message' => 'Votre avis a été enregistré et sera publié après validation.',
        'avis_id' => $dbConnection->lastInsertId()
    ]);

} catch (PDOException $erreur) {

    // Une contrainte UNIQUE peut aussi empêcher
    // deux avis envoyés simultanément
    if ($erreur->getCode() === '23000') {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Un avis existe déjà pour cette commande.'
        ]);

        exit;
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’enregistrer votre avis.'
    ]);
}
