<?php

header('Content-Type: application/json; charset=utf-8');

// Vérifier que la requête utilise POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier que le client est connecté et autorisé
require __DIR__ . '/../includes/auth.php';

verifierAcces(['utilisateur']);

// Connexion à la base de données
require __DIR__ . '/../config/db-connection.php';

// Fonction pour renvoyer une erreur
function erreurCommande($message, $code = 400) {

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
    erreurCommande('Session de formulaire invalide.', 403);
}

// Vérifier les champs obligatoires
$champs = [
    'menu_id',
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
        erreurCommande('Veuillez remplir tous les champs obligatoires.');
    }
}

// Récupérer les données
$menuId = $_POST['menu_id'];
$prenom = trim($_POST['prenom_client']);
$nom = trim($_POST['nom_client']);
$email = trim($_POST['email_client']);
$telephone = trim($_POST['telephone_client']);
$adresse = trim($_POST['adresse_livraison']);
$ville = trim($_POST['ville_livraison']);
$datePrestation = $_POST['date_prestation'];
$heureLivraison = $_POST['heure_livraison'];
$nbPersonnes = $_POST['nb_personnes'];

// Vérifier les identifiants et le nombre de personnes
if (
    !ctype_digit($menuId) ||
    (int) $menuId < 1 ||
    !ctype_digit($nbPersonnes) ||
    (int) $nbPersonnes < 1
) {
    erreurCommande('Menu ou nombre de personnes invalide.');
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
    erreurCommande('Les coordonnées sont invalides.');
}

// Vérifier le format de l'heure
if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $heureLivraison)) {
    erreurCommande('Heure de livraison invalide.');
}

// Vérifier le format de la date
$fuseau = new DateTimeZone('Europe/Paris');

$date = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $datePrestation,
    $fuseau
);

if (!$date || $date->format('Y-m-d') !== $datePrestation) {
    erreurCommande('Date de prestation invalide.');
}

// Vérifier la distance de livraison
$distance = 0;

if (strtolower($ville) !== 'bordeaux') {

    $distanceSaisie = $_POST['distance_livraison_km'] ?? null;

    if (
        !is_string($distanceSaisie) ||
        !is_numeric($distanceSaisie) ||
        (float) $distanceSaisie <= 0 ||
        !is_finite((float) $distanceSaisie)
    ) {
        erreurCommande('Distance de livraison invalide.');
    }

    $distance = round((float) $distanceSaisie, 1);
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer le menu dans la base de données
    $requete = $dbConnection->prepare(
        "SELECT
            id,
            titre,
            prix_personne,
            nb_personnes_min,
            delai_commande_jours,
            stock_disponible
         FROM menu
         WHERE id = :id
         FOR UPDATE"
    );

    $requete->execute([
        'id' => $menuId
    ]);

    $menu = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        $dbConnection->rollBack();
        erreurCommande('Ce menu est introuvable.');
    }

    // Vérifier le stock
    if ($menu['stock_disponible'] < 1) {
        $dbConnection->rollBack();
        erreurCommande('Ce menu n’est plus disponible.');
    }

    // Vérifier le minimum de convives
    $minimum = (int) $menu['nb_personnes_min'];

    if ((int) $nbPersonnes < $minimum) {
        $dbConnection->rollBack();
        erreurCommande('Le nombre minimum de convives n’est pas respecté.');
    }

    // Vérifier le délai de réservation
    $delai = (int) $menu['delai_commande_jours'];

    $dateMinimum = new DateTimeImmutable('today', $fuseau);

    $dateMinimum = $dateMinimum->modify('+' . $delai . ' days');

    if ($date < $dateMinimum) {
        $dbConnection->rollBack();
        erreurCommande('La date de prestation est trop proche.');
    }

    // Calculer le prix du menu
    $prixMenu = round(
        (float) $menu['prix_personne'] * (int) $nbPersonnes,
        2
    );

    // Calculer la remise
    $remise = 0;

    if ((int) $nbPersonnes >= $minimum + 5) {
        $remise = round($prixMenu * 0.10, 2);
    }

    // Calculer les frais de livraison
    $fraisLivraison = 0;

    if (strtolower($ville) !== 'bordeaux') {
        $fraisLivraison = round(5 + ($distance * 0.59), 2);
    }

    // Calculer le total
    $total = round($prixMenu - $remise + $fraisLivraison, 2);

    // Enregistrer la commande
    $requete = $dbConnection->prepare(
        "INSERT INTO commande (
            compte_id,
            menu_id,
            nom_client,
            prenom_client,
            email_client,
            telephone_client,
            adresse_livraison,
            ville_livraison,
            date_prestation,
            heure_livraison,
            nb_personnes,
            distance_livraison_km,
            prix_menu,
            remise,
            frais_livraison,
            total,
            statut_courant
        ) VALUES (
            :compte_id,
            :menu_id,
            :nom,
            :prenom,
            :email,
            :telephone,
            :adresse,
            :ville,
            :date_prestation,
            :heure_livraison,
            :nb_personnes,
            :distance,
            :prix_menu,
            :remise,
            :frais_livraison,
            :total,
            'en_attente'
        )"
    );

    $requete->execute([
        'compte_id' => $_SESSION['compte_id'],
        'menu_id' => $menuId,
        'nom' => $nom,
        'prenom' => $prenom,
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
        'total' => $total
    ]);

    // Récupérer l'identifiant de la nouvelle commande
    $commandeId = $dbConnection->lastInsertId();

    // Enregistrer son premier statut dans l'historique
    $requete = $dbConnection->prepare(
        "INSERT INTO historique_statut (
            commande_id,
            compte_id,
            statut,
            date_modification
        ) VALUES (
            :commande_id,
            :compte_id,
            'en_attente',
            NOW()
        )"
    );

    $requete->execute([
        'commande_id' => $commandeId,
        'compte_id' => $_SESSION['compte_id']
    ]);

    // Diminuer le stock uniquement s'il est disponible
    $requeteStock = $dbConnection->prepare(
        "UPDATE menu
        SET stock_disponible = stock_disponible - 1
        WHERE id = :menu_id
        AND stock_disponible > 0"
    );

    $requeteStock->execute([
        'menu_id' => $menuId
    ]);

    // Vérifier qu'une unité de stock a bien été retirée
    if ($requeteStock->rowCount() === 0) {

        throw new Exception(
            'Ce menu est actuellement indisponible.'
        );
    }

    // Valider toutes les opérations
    $dbConnection->commit();

    
    // ENVOYER L'E-MAIL DE CONFIRMATION

    require __DIR__ . '/../includes/mail-service.php';
   
    // Préparer les informations de l'e-mail
    $sujet = 'Confirmation de votre commande n°' . $commandeId;

    $dateMail = $date->format('d/m/Y');

    $prixMail = number_format($prixMenu, 2, ',', ' ');
    $remiseMail = number_format($remise, 2, ',', ' ');
    $livraisonMail = number_format($fraisLivraison, 2, ',', ' ');
    $totalMail = number_format($total, 2, ',', ' ');

    // Construire le message en texte brut
    $message = "Bonjour " . $prenom . ",\n\n"
        . "Nous vous confirmons l'enregistrement de votre commande n°" . $commandeId . ".\n\n"
        . "RÉCAPITULATIF DE VOTRE COMMANDE\n\n"
        . "Menu : " . $menu['titre'] . "\n"
        . "Nombre de convives : " . $nbPersonnes . "\n"
        . "Date de prestation : " . $dateMail . "\n"
        . "Heure de livraison : " . $heureLivraison . "\n\n"
        . "Adresse de livraison :\n"
        . $adresse . "\n"
        . $ville . "\n\n"
        . "DÉTAIL DU PRIX\n\n"
        . "Prix du menu : " . $prixMail . " €\n"
        . "Remise : - " . $remiseMail . " €\n"
        . "Frais de livraison : " . $livraisonMail . " €\n"
        . "Total de la commande : " . $totalMail . " €\n\n"
        . "Votre commande est actuellement en attente de validation par notre équipe.\n\n"
        . "Merci pour votre confiance.\n\n"
        . "L'équipe Vite & Gourmand";


    // Envoyer l'e-mail
    $emailEnvoye = false;

    try {

        $resultatMail = envoyerMail($email, $sujet, $message);
        $emailEnvoye = ($resultatMail !== false);

    } catch (Throwable $erreur) {

        error_log(
            'Erreur e-mail commande ' .
            $commandeId . ' : ' . $erreur->getMessage()
        );

    }

    echo json_encode([
        'success' => true,
        'message' => 'Votre commande a été enregistrée.',
        'commande_id' => $commandeId,
        'email_envoye' => $emailEnvoye
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’enregistrer la commande.'
    ]);
}
