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
$titre = $_POST['titre'] ?? '';
$description = $_POST['description'] ?? '';
$themeId = $_POST['theme_id'] ?? '';
$regimeId = $_POST['regime_id'] ?? '';
$nbPersonnesMin = $_POST['nb_personnes_min'] ?? '';
$stockDisponible = $_POST['stock_disponible'] ?? '';
$prixPersonne = $_POST['prix_personne'] ?? '';
$delaiCommande = $_POST['delai_commande_jours'] ?? '';
$conditions = $_POST['conditions'] ?? '';

$entreeId = $_POST['entree_id'] ?? '';
$platId = $_POST['plat_id'] ?? '';
$dessertId = $_POST['dessert_id'] ?? '';

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

// Vérifier que les données ont le bon type
$valeurs = [
    $titre,
    $description,
    $themeId,
    $regimeId,
    $nbPersonnesMin,
    $stockDisponible,
    $prixPersonne,
    $delaiCommande,
    $conditions,
    $entreeId,
    $platId,
    $dessertId
];

foreach ($valeurs as $valeur) {

    if (!is_string($valeur)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Données du formulaire invalides.'
        ]);

        exit;
    }
}

// Nettoyer les champs texte
$titre = trim($titre);
$description = trim($description);
$conditions = trim($conditions);

// Vérifier le titre et la description
if (
    $titre === '' ||
    mb_strlen($titre) > 150 ||
    $description === '' ||
    mb_strlen($description) > 1000 ||
    mb_strlen($conditions) > 1000
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le titre, la description ou les conditions sont invalides.'
    ]);

    exit;
}

// Vérifier les identifiants et les nombres entiers
$entiers = [
    $themeId,
    $regimeId,
    $nbPersonnesMin,
    $entreeId,
    $platId,
    $dessertId
];

foreach ($entiers as $entier) {

    if (
        !ctype_digit($entier) ||
        (int) $entier < 1
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Une sélection ou un nombre est invalide.'
        ]);

        exit;
    }
}

// Vérifier le stock disponible
if (
    !ctype_digit($stockDisponible) ||
    (int) $stockDisponible > 2147483647
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le stock doit être un nombre entier positif ou égal à zéro.'
    ]);

    exit;
}

// Vérifier le délai
if (
    !ctype_digit($delaiCommande)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Délai de commande invalide.'
    ]);

    exit;
}

// Vérifier le prix
if (
    !is_numeric($prixPersonne) ||
    !is_finite((float) $prixPersonne) ||
    (float) $prixPersonne <= 0 ||
    !preg_match('/^\d+(\.\d{1,2})?$/', $prixPersonne)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Prix par personne invalide.'
    ]);

    exit;
}

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Vérifier le thème sélectionné
    $requeteTheme = $dbConnection->prepare(
        "SELECT id FROM theme WHERE id = :id"
    );

    $requeteTheme->execute([
        'id' => $themeId
    ]);

    // Vérifier le régime sélectionné
    $requeteRegime = $dbConnection->prepare(
        "SELECT id FROM regime WHERE id = :id"
    );

    $requeteRegime->execute([
        'id' => $regimeId
    ]);

    if (
        !$requeteTheme->fetch() ||
        !$requeteRegime->fetch()
    ) {
        throw new InvalidArgumentException(
            'Le thème ou le régime sélectionné est introuvable.'
        );
    }

    // Vérifier les trois plats et leurs catégories
    $platsSelectionnes = [
        ['id' => $entreeId, 'categorie' => 'entree'],
        ['id' => $platId, 'categorie' => 'plat'],
        ['id' => $dessertId, 'categorie' => 'dessert']
    ];

    $requetePlat = $dbConnection->prepare(
        "SELECT id
         FROM plat
         WHERE id = :id
           AND categorie = :categorie"
    );

    foreach ($platsSelectionnes as $platSelectionne) {

        $requetePlat->execute([
            'id' => $platSelectionne['id'],
            'categorie' => $platSelectionne['categorie']
        ]);

        if (!$requetePlat->fetch()) {

            throw new InvalidArgumentException(
                'Un plat sélectionné est introuvable ou ne correspond pas à sa catégorie.'
            );
        }
    }

    // Enregistrer le nouveau menu
    $requeteMenu = $dbConnection->prepare(
        "INSERT INTO menu (
            titre,
            description,
            theme_id,
            regime_id,
            nb_personnes_min,
            prix_personne,
            stock_disponible,
            conditions,
            delai_commande_jours
        )
        VALUES (
            :titre,
            :description,
            :theme_id,
            :regime_id,
            :nb_personnes_min,
            :prix_personne,
            :stock_disponible,
            :conditions,
            :delai_commande_jours
        )"
    );

    $requeteMenu->execute([
        'titre' => $titre,
        'description' => $description,
        'theme_id' => $themeId,
        'regime_id' => $regimeId,
        'nb_personnes_min' => $nbPersonnesMin,
        'prix_personne' => $prixPersonne,
        'stock_disponible' => $stockDisponible,
        'conditions' => $conditions,
        'delai_commande_jours' => $delaiCommande
    ]);

    // Récupérer l'identifiant du menu
    $menuId = $dbConnection->lastInsertId();

    // Préparer l'association des plats
    $requeteAssociation = $dbConnection->prepare(
        "INSERT INTO menu_plat (menu_id, plat_id)
         VALUES (:menu_id, :plat_id)"
    );

    // Enregistrer les trois associations
    foreach ($platsSelectionnes as $platSelectionne) {

        $requeteAssociation->execute([
            'menu_id' => $menuId,
            'plat_id' => $platSelectionne['id']
        ]);
    }

    // Valider toutes les opérations
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Le menu et ses plats ont été enregistrés.',
        'menu_id' => $menuId
    ]);

} catch (InvalidArgumentException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $erreur->getMessage()
    ]);

} catch (PDOException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible d’enregistrer le menu.'
    ]);
}