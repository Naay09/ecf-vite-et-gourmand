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

// Vérifier que le client est connecté
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Fonction pour afficher une erreur
function erreurModification($dbConnection, $message, $code = 400) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    http_response_code($code);

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);

    exit;
}

// Vérifier le jeton CSRF
$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrfToken) ||
    !isset($_SESSION['csrf_compte']) ||
    !hash_equals($_SESSION['csrf_compte'], $csrfToken)
) {
    erreurModification($dbConnection, 'Session de formulaire invalide.', 403);
}

// Champs obligatoires
$champs = [
    'commande_id',
    'prenom_client',
    'nom_client',
    'email_client',
    'telephone_client',
    'adresse_livraison',
    'ville_livraison',
    'date_prestation',
    'heure_livraison',
    'nb_personnes'
];

foreach ($champs as $champ) {

    if (
        !isset($_POST[$champ]) ||
        !is_string($_POST[$champ]) ||
        trim($_POST[$champ]) === ''
    ) {
        erreurModification($dbConnection, 'Veuillez remplir tous les champs.');
    }
}

// Récupérer les informations
$commandeId = $_POST['commande_id'];

$prenom = trim($_POST['prenom_client']);
$nom = trim($_POST['nom_client']);
$email = trim($_POST['email_client']);
$telephone = trim($_POST['telephone_client']);

$adresse = trim($_POST['adresse_livraison']);
$ville = trim($_POST['ville_livraison']);

$datePrestation = $_POST['date_prestation'];
$heureLivraison = $_POST['heure_livraison'];
$nbPersonnes = $_POST['nb_personnes'];

// Vérifier les identifiants et les convives
if (
    !ctype_digit($commandeId) ||
    (int) $commandeId < 1 ||
    !ctype_digit($nbPersonnes) ||
    (int) $nbPersonnes < 1
) {
    erreurModification($dbConnection, 'Commande ou nombre de convives invalide.');
}

// Vérifier les coordonnées
if (
    strlen($prenom) > 100 ||
    strlen($nom) > 100 ||
    strlen($email) > 255 ||
    strlen($telephone) > 20 ||
    strlen($adresse) > 255 ||
    strlen($ville) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    erreurModification($dbConnection, 'Coordonnées invalides.');
}

// Vérifier l'heure
if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $heureLivraison)) {
    erreurModification($dbConnection, 'Heure de livraison invalide.');
}

// Vérifier la date
$fuseau = new DateTimeZone('Europe/Paris');

$date = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $datePrestation,
    $fuseau
);

if (!$date || $date->format('Y-m-d') !== $datePrestation) {
    erreurModification($dbConnection, 'Date de prestation invalide.');
}

// Vérifier la distance
$distance = 0;

if (strtolower($ville) !== 'bordeaux') {

    $distanceSaisie = $_POST['distance_livraison_km'] ?? null;

    if (
        !is_string($distanceSaisie) ||
        !is_numeric($distanceSaisie) ||
        !is_finite((float) $distanceSaisie) ||
        (float) $distanceSaisie < 0.1
    ) {
        erreurModification($dbConnection, 'Distance de livraison invalide.');
    }

    $distance = round((float) $distanceSaisie, 1);
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer la commande et son menu
    $requete = $dbConnection->prepare(
        "SELECT
            c.id,
            c.statut_courant,
            m.prix_personne,
            m.nb_personnes_min,
            m.delai_commande_jours
         FROM commande c
         INNER JOIN menu m ON c.menu_id = m.id
         WHERE c.id = :commande_id
         AND c.compte_id = :compte_id
         FOR UPDATE"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier que la commande appartient au client
    if (!$commande) {
        erreurModification($dbConnection, 'Commande introuvable.', 404);
    }

    // Vérifier que la commande est encore en attente
    if ($commande['statut_courant'] !== 'en_attente') {
        erreurModification(
            $dbConnection,
            'Cette commande ne peut plus être modifiée.',
            409
        );
    }

    // Vérifier le minimum de convives
    $minimum = (int) $commande['nb_personnes_min'];

    if ((int) $nbPersonnes < $minimum) {
        erreurModification(
            $dbConnection,
            'Le nombre minimum de convives n’est pas respecté.'
        );
    }

    // Vérifier le délai du menu
    $delai = (int) $commande['delai_commande_jours'];

    $dateMinimum = new DateTimeImmutable('today', $fuseau);
    $dateMinimum = $dateMinimum->modify('+' . $delai . ' days');

    if ($date < $dateMinimum) {
        erreurModification(
            $dbConnection,
            'La date de prestation ne respecte pas le délai du menu.'
        );
    }

    // Recalculer le prix du menu
    $prixMenu = round(
        (float) $commande['prix_personne'] * (int) $nbPersonnes,
        2
    );

    // Recalculer la remise
    $remise = 0;

    if ((int) $nbPersonnes >= $minimum + 5) {
        $remise = round($prixMenu * 0.10, 2);
    }

    // Recalculer les frais de livraison
    $fraisLivraison = 0;

    if (strtolower($ville) !== 'bordeaux') {
        $fraisLivraison = round(5 + ($distance * 0.59), 2);
    }

    // Calculer le nouveau total
    $total = round(
        $prixMenu - $remise + $fraisLivraison,
        2
    );

    // Enregistrer les nouvelles informations
    $requete = $dbConnection->prepare(
        "UPDATE commande SET
            prenom_client = :prenom,
            nom_client = :nom,
            email_client = :email,
            telephone_client = :telephone,
            adresse_livraison = :adresse,
            ville_livraison = :ville,
            date_prestation = :date_prestation,
            heure_livraison = :heure_livraison,
            nb_personnes = :nb_personnes,
            distance_livraison_km = :distance,
            prix_menu = :prix_menu,
            remise = :remise,
            frais_livraison = :frais_livraison,
            total = :total
         WHERE id = :commande_id
         AND compte_id = :compte_id
         AND statut_courant = 'en_attente'"
    );

    $requete->execute([
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'adresse' => $adresse,
        'ville' => $ville,
        'date_prestation' => $datePrestation,
        'heure_livraison' => $heureLivraison,
        'nb_personnes' => $nbPersonnes,
        'distance' => $distance,
        'prix_menu' => $prixMenu,
        'remise' => $remise,
        'frais_livraison' => $fraisLivraison,
        'total' => $total,
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    // Valider les modifications
    $dbConnection->commit();

    // Synchroniser la commande avec MongoDB
    try {

        require __DIR__ . '/../includes/synchroniser-commande-mongodb.php';

        synchroniserCommandeMongoDB((int) $commandeId, $dbConnection);

    } catch (Throwable $erreurMongoDB) {

        // Ne pas annuler une modification MySQL déjà validée
        error_log(
            'Erreur de synchronisation MongoDB : '
            . $erreurMongoDB->getMessage()
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'Votre commande a été modifiée.',
        'commande_id' => (int) $commandeId,
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
