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

// Récupérer les données envoyées par JavaScript
$commandeId = $_POST['commande_id'] ?? '';
$nouveauStatut = $_POST['nouveau_statut'] ?? '';
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

// Vérifier l'identifiant de la commande
if (
    !is_string($commandeId) ||
    !ctype_digit($commandeId) ||
    (int) $commandeId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant de commande invalide.'
    ]);

    exit;
}

// Définir les transitions autorisées
$transitions = [
    'en_attente' => ['accepte'],
    'accepte' => ['en_preparation'],
    'en_preparation' => ['en_cours_de_livraison'],
    'en_cours_de_livraison' => ['livre'],
    'livre' => ['terminee', 'en_attente_retour_materiel']
];


// Vérifier que le statut demandé est autorisé
$statutsAutorises = [
    'accepte',
    'en_preparation',
    'en_cours_de_livraison',
    'livre',
    'terminee',
    'en_attente_retour_materiel'
];

if (
    !is_string($nouveauStatut) ||
    !in_array($nouveauStatut, $statutsAutorises, true)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Statut demandé invalide.'
    ]);

    exit;
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer et verrouiller la commande
    $requete = $dbConnection->prepare(
        "SELECT statut_courant
         FROM commande
         WHERE id = :id
         FOR UPDATE"
    );

    $requete->execute([
        'id' => $commandeId
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Récupérer le statut actuel
    $statutActuel = strtolower(
        trim($commande['statut_courant'])
    );

    // Gérer l'ancienne écriture "accepté"
    $statutActuel = str_replace(
        ['é', 'è', 'ê'],
        'e',
        $statutActuel
    );

    // Vérifier que la transition est autorisée
    if (
        !isset($transitions[$statutActuel]) ||
        !in_array($nouveauStatut, $transitions[$statutActuel], true)
    ) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Ce changement de statut n’est pas autorisé.'
        ]);

        exit;
    }

    // Mettre à jour le statut de la commande
    $requete = $dbConnection->prepare(
        "UPDATE commande
         SET statut_courant = :statut
         WHERE id = :id"
    );

    $requete->execute([
        'statut' => $nouveauStatut,
        'id' => $commandeId
    ]);

    // Enregistrer le changement dans l'historique
    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut
            (commande_id, compte_id, statut, date_modification)
         VALUES
            (:commande_id, :compte_id, :statut, NOW())"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id'],
        'statut' => $nouveauStatut
    ]);

    // Valider les modifications
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Le statut a été mis à jour.',
        'nouveau_statut' => $nouveauStatut
    ]);

} catch (PDOException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le statut.'
    ]);
}
