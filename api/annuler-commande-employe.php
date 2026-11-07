
<?php

header('Content-Type: application/json; charset=utf-8');

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

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Récupérer les informations
$commandeId = $_POST['commande_id'] ?? '';
$contactId = $_POST['contact_id'] ?? '';
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
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier les identifiants
if (
    !is_string($commandeId) ||
    !is_string($contactId)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiants invalides.'
    ]);

    exit;
}

$commandeId = filter_var(
    $commandeId,
    FILTER_VALIDATE_INT
);

$contactId = filter_var(
    $contactId,
    FILTER_VALIDATE_INT
);

if (
    $commandeId === false ||
    $contactId === false ||
    $commandeId < 1 ||
    $contactId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiants invalides.'
    ]);

    exit;
}

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Récupérer la commande et verrouiller sa ligne
    $requete = $dbConnection->prepare(
        "SELECT
            statut_courant,
            menu_id
         FROM commande
         WHERE id = :id
         FOR UPDATE"
    );

    $requete->execute([
        'id' => $commandeId
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier que la commande existe
    if (!$commande) {

        $dbConnection->rollBack();

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Commande introuvable.'
        ]);

        exit;
    }

    // Normaliser le statut actuel
    $statut = $commande['statut_courant'];

    // Définir les statuts depuis lesquels on peut annuler
    $statutsAnnulables = [
        'en_attente',
        'accepte',
        'en_preparation',
        'en_cours_de_livraison'
    ];

    // Vérifier que l'annulation est autorisée
    if (!in_array($statut, $statutsAnnulables, true)) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette commande ne peut plus être annulée.'
        ]);

        exit;
    }

    // VÉRIFIER LE CONTACT PRÉALABLE

    $requete = $dbConnection->prepare(
        "SELECT id
         FROM contact_commande
         WHERE id = :contact_id
           AND commande_id = :commande_id
           AND compte_id = :compte_id
           AND action = 'annulation'
           AND utilise_le IS NULL
         FOR UPDATE"
    );

    $requete->execute([
        'contact_id' => $contactId,
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    $contact = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {

        $dbConnection->rollBack();

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Aucun contact préalable valide pour cette annulation.'
        ]);

        exit;
    }

    // 1. ANNULER LA COMMANDE

    $requete = $dbConnection->prepare(
        "UPDATE commande
         SET statut_courant = :statut
         WHERE id = :id"
    );

    $requete->execute([
        'statut' => 'annulee',
        'id' => $commandeId
    ]);

    // 2. ENREGISTRER L'ANNULATION DANS L'HISTORIQUE

    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut (
            commande_id,
            compte_id,
            statut,
            date_modification
        ) VALUES (
            :commande_id,
            :compte_id,
            :statut,
            NOW()
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id'],
        'statut' => 'annulee'
    ]);

    // 3. RESTITUER LE STOCK DU MENU

    $requete = $dbConnection->prepare(
        "UPDATE menu
         SET stock_disponible = stock_disponible + 1
         WHERE id = :menu_id"
    );

    $requete->execute([
        'menu_id' => $commande['menu_id']
    ]);

    if ($requete->rowCount() !== 1) {
        throw new Exception('Impossible de restituer le stock.');
    }

    // 4. MARQUER LE CONTACT COMME UTILISÉ

    $requete = $dbConnection->prepare(
        "UPDATE contact_commande
         SET utilise_le = NOW()
         WHERE id = :id"
    );

    $requete->execute([
        'id' => $contactId
    ]);

    // Valider toutes les modifications
    $dbConnection->commit();

    // Réponse positive
    echo json_encode([
        'success' => true,
        'message' => 'La commande a été annulée avec succès.',
        'commande_id' => $commandeId,
        'nouveau_statut' => 'annulee'
    ]);

} catch (Throwable $erreur) {

    // Annuler les modifications en cas d'erreur
    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’annuler la commande.'
    ]);
}
