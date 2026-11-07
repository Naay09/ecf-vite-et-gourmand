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

// Vérifier l'accès à l'espace Employé
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion à MySQL
require __DIR__ . '/../config/db-connection.php';

// Vérifier la présence des champs
$champs = [
    'commande_id',
    'contact_id',
    'csrf_token',
    'prenom_client',
    'nom_client',
    'email_client',
    'telephone_client',
    'date_prestation',
    'heure_livraison',
    'nb_personnes',
    'adresse_livraison',
    'ville_livraison',
    'distance_livraison_km'
];

foreach ($champs as $champ) {

    if (!isset($_POST[$champ]) || !is_string($_POST[$champ])) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Données manquantes ou invalides.'
        ]);

        exit;
    }
}

// Vérifier le jeton CSRF
if (
    empty($_SESSION['csrf_employe']) ||
    !hash_equals($_SESSION['csrf_employe'], $_POST['csrf_token'])
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Récupérer les identifiants
$commandeId = filter_var($_POST['commande_id'], FILTER_VALIDATE_INT);
$contactId = filter_var($_POST['contact_id'], FILTER_VALIDATE_INT);

if (
    $commandeId === false ||
    $contactId === false ||
    $commandeId < 1 ||
    $contactId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant invalide.'
    ]);

    exit;
}

// Récupérer les informations du formulaire
$prenom = trim($_POST['prenom_client']);
$nom = trim($_POST['nom_client']);
$email = trim($_POST['email_client']);
$telephone = trim($_POST['telephone_client']);

$datePrestation = $_POST['date_prestation'];
$heureLivraison = $_POST['heure_livraison'];

$nbPersonnes = filter_var(
    $_POST['nb_personnes'],
    FILTER_VALIDATE_INT
);

$adresse = trim($_POST['adresse_livraison']);
$ville = trim($_POST['ville_livraison']);

$distance = filter_var(
    $_POST['distance_livraison_km'],
    FILTER_VALIDATE_FLOAT
);

// Vérifier les informations du client
if (
    $prenom === '' ||
    $nom === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $telephone === '' ||
    $adresse === '' ||
    $ville === ''
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Veuillez renseigner correctement les champs.'
    ]);

    exit;
}

// Vérifier le nombre de personnes et la distance
if (
    $nbPersonnes === false ||
    $nbPersonnes < 1 ||
    $distance === false ||
    !is_finite($distance) ||
    $distance < 0
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Nombre de personnes ou distance invalide.'
    ]);

    exit;
}

// Vérifier la date
$date = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $datePrestation
);

if (
    !$date ||
    $date->format('Y-m-d') !== $datePrestation
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Date de prestation invalide.'
    ]);

    exit;
}

// Vérifier l'heure
if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $heureLivraison)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Heure de livraison invalide.'
    ]);

    exit;
}

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Récupérer la commande et les informations du menu
    $requete = $dbConnection->prepare(
        "SELECT
            c.statut_courant,
            c.date_prestation,
            m.prix_personne,
            m.nb_personnes_min,
            m.delai_commande_jours
         FROM commande c
         INNER JOIN menu m ON c.menu_id = m.id
         WHERE c.id = :id
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

    // Vérifier que la commande peut encore être modifiée
    $statut = $commande['statut_courant'];

    $statutsModifiables = [
        'en_attente',
        'accepte',
        'en_preparation',
        'en_cours_de_livraison'
    ];

    if (!in_array($statut, $statutsModifiables, true)) {

        $dbConnection->rollBack();

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Cette commande ne peut plus être modifiée.'
        ]);

        exit;
    }

    // Vérifier le contact préalable
    $requete = $dbConnection->prepare(
        "SELECT id
         FROM contact_commande
         WHERE id = :contact_id
           AND commande_id = :commande_id
           AND compte_id = :compte_id
           AND action = 'modification'
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
            'message' => 'Aucun contact préalable valide pour cette modification.'
        ]);

        exit;
    }

    // Vérifier le nombre minimum de personnes
    $minimumPersonnes = (int) $commande['nb_personnes_min'];

    if ($nbPersonnes < $minimumPersonnes) {

        $dbConnection->rollBack();

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le nombre minimum de personnes n’est pas respecté.'
        ]);

        exit;
    }

    // Vérifier le délai du menu si la date est modifiée
    $ancienneDate = substr($commande['date_prestation'], 0, 10);

    if ($datePrestation !== $ancienneDate) {

        $delai = (int) $commande['delai_commande_jours'];

        $dateMinimale = (new DateTimeImmutable('today'))
            ->modify('+' . $delai . ' days');

        if ($date < $dateMinimale) {

            $dbConnection->rollBack();

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Le délai de commande du menu n’est pas respecté.'
            ]);

            exit;
        }
    }

    // CALCUL DU PRIX

    $prixPersonne = (float) $commande['prix_personne'];

    $prixMenu = round(
        $prixPersonne * $nbPersonnes,
        2
    );

    // Remise de 10 % si minimum + 5 personnes
    $remise = 0;

    if ($nbPersonnes >= $minimumPersonnes + 5) {
        $remise = round($prixMenu * 0.10, 2);
    }

    // Calcul des frais de livraison
    $fraisLivraison = 0;

    if (strtolower($ville) !== 'bordeaux') {

        $fraisLivraison = round(
            5 + ($distance * 0.59),
            2
        );
    }

    // Total de la commande
    $total = round(
        $prixMenu - $remise + $fraisLivraison,
        2
    );

    // ENREGISTRER LES MODIFICATIONS

    $requete = $dbConnection->prepare(
        "UPDATE commande SET
            prenom_client = :prenom,
            nom_client = :nom,
            email_client = :email,
            telephone_client = :telephone,
            date_prestation = :date_prestation,
            heure_livraison = :heure_livraison,
            nb_personnes = :nb_personnes,
            adresse_livraison = :adresse,
            ville_livraison = :ville,
            distance_livraison_km = :distance,
            prix_menu = :prix_menu,
            remise = :remise,
            frais_livraison = :frais_livraison,
            total = :total
         WHERE id = :id"
    );

    $requete->execute([
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'date_prestation' => $datePrestation,
        'heure_livraison' => $heureLivraison,
        'nb_personnes' => $nbPersonnes,
        'adresse' => $adresse,
        'ville' => $ville,
        'distance' => $distance,
        'prix_menu' => $prixMenu,
        'remise' => $remise,
        'frais_livraison' => $fraisLivraison,
        'total' => $total,
        'id' => $commandeId
    ]);

    // Marquer le contact comme utilisé
    $requete = $dbConnection->prepare(
        "UPDATE contact_commande
         SET utilise_le = NOW()
         WHERE id = :id"
    );

    $requete->execute([
        'id' => $contactId
    ]);

    // Valider les deux modifications ensemble
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'La commande a été modifiée avec succès.',
        'commande_id' => $commandeId,
        'total' => $total
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier la commande.'
    ]);
}
