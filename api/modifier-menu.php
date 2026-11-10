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

// Vérifier les droits de l'utilisateur
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données du formulaire
$menuId = $_POST['menu_id'] ?? '';

$titre = $_POST['titre'] ?? '';
$description = $_POST['description'] ?? '';

$themeId = $_POST['theme_id'] ?? '';
$regimeId = $_POST['regime_id'] ?? '';

$nbPersonnesMin = $_POST['nb_personnes_min'] ?? '';
$stockDisponible = $_POST['stock_disponible'] ?? '';

// Stock au moment de l'ouverture du formulaire
$stockInitial = $_POST['stock_initial'] ?? '';

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

// Vérifier les types des données
$valeurs = [
    $menuId,
    $titre,
    $description,
    $themeId,
    $regimeId,
    $nbPersonnesMin,
    $stockDisponible,
    $stockInitial,
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

// Nettoyer les textes
$titre = trim($titre);
$description = trim($description);
$conditions = trim($conditions);

// Vérifier les champs texte
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

// Vérifier les identifiants et les entiers positifs
$entiers = [
    $menuId,
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
            'message' => 'Un identifiant ou un nombre est invalide.'
        ]);

        exit;
    }
}

// Vérifier le stock disponible
if (
    !ctype_digit($stockDisponible) ||
    filter_var(
        $stockDisponible,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 2147483647]]
    ) === false
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le stock doit être un nombre entier positif ou égal à zéro.'
    ]);

    exit;
}

// Vérifier le stock initial
if (
    !ctype_digit($stockInitial) ||
    filter_var(
        $stockInitial,
        FILTER_VALIDATE_INT,
        ['options' => [
            'min_range' => 0,
            'max_range' => 2147483647
        ]]
    ) === false
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Stock initial invalide.'
    ]);

    exit;
}

// Vérifier le délai
if (!ctype_digit($delaiCommande)) {

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

    // Vérifier que le menu existe
    $requeteMenu = $dbConnection->prepare(
        "SELECT id, stock_disponible
        FROM menu
        WHERE id = :menu_id
        FOR UPDATE"
    );

    $requeteMenu->execute([
        'menu_id' => $menuId
    ]);
    
    // Récupérer le menu et son stock actuel
    $menu = $requeteMenu->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {

        throw new InvalidArgumentException(
            'Le menu est introuvable.'
        );
    }

    // Comparer le stock actuel au stock initial
    if ((int) $menu['stock_disponible'] !== (int) $stockInitial) {

        throw new InvalidArgumentException(
            'Le stock de ce menu a changé depuis ' .
            'l’ouverture du formulaire. ' .
            'Fermez la fenêtre et ouvrez-la de nouveau ' .
            'pour récupérer le stock actuel.'
        );
    }

    // Vérifier le thème
    $requeteTheme = $dbConnection->prepare(
        "SELECT id FROM theme WHERE id = :id"
    );

    $requeteTheme->execute([
        'id' => $themeId
    ]);

    if (!$requeteTheme->fetch()) {

        throw new InvalidArgumentException(
            'Thème introuvable.'
        );
    }

    // Vérifier le régime
    $requeteRegime = $dbConnection->prepare(
        "SELECT id FROM regime WHERE id = :id"
    );

    $requeteRegime->execute([
        'id' => $regimeId
    ]);

    if (!$requeteRegime->fetch()) {

        throw new InvalidArgumentException(
            'Régime introuvable.'
        );
    }

    // Préparer les trois plats
    $platsSelectionnes = [
        ['id' => $entreeId, 'categorie' => 'entree'],
        ['id' => $platId, 'categorie' => 'plat'],
        ['id' => $dessertId, 'categorie' => 'dessert']
    ];

    // Vérifier les plats et leurs catégories
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
                'Un plat est introuvable ou appartient à une autre catégorie.'
            );
        }
    }

    // Modifier les informations générales
    $requeteModification = $dbConnection->prepare(
        "UPDATE menu
         SET
            titre = :titre,
            description = :description,
            theme_id = :theme_id,
            regime_id = :regime_id,
            nb_personnes_min = :nb_personnes_min,
            stock_disponible = :stock_disponible,
            prix_personne = :prix_personne,
            delai_commande_jours = :delai_commande_jours,
            conditions = :conditions
         WHERE id = :menu_id"
    );

    $requeteModification->execute([
        'titre' => $titre,
        'description' => $description,
        'theme_id' => $themeId,
        'regime_id' => $regimeId,
        'nb_personnes_min' => $nbPersonnesMin,
        'stock_disponible' => $stockDisponible,
        'prix_personne' => $prixPersonne,
        'delai_commande_jours' => $delaiCommande,
        'conditions' => $conditions,
        'menu_id' => $menuId
    ]);

    // Récupérer les plats actuellement associés au menu
    $requetePlatsActuels = $dbConnection->prepare(
        "SELECT plat_id
        FROM menu_plat
        WHERE menu_id = :menu_id"
    );

    $requetePlatsActuels->execute([
        'menu_id' => $menuId
    ]);

    $platsActuels = $requetePlatsActuels->fetchAll(
        PDO::FETCH_COLUMN
    );

    // Récupérer les trois nouveaux identifiants
    $nouveauxPlats = [
        $entreeId,
        $platId,
        $dessertId
    ];

    // Trier les tableaux pour pouvoir les comparer
    sort($platsActuels);
    sort($nouveauxPlats);

    // Modifier les associations uniquement si nécessaire
    if ($platsActuels != $nouveauxPlats) {

        // Supprimer les anciennes associations
        $requeteSuppression = $dbConnection->prepare(
            "DELETE FROM menu_plat
            WHERE menu_id = :menu_id"
        );

        $requeteSuppression->execute([
            'menu_id' => $menuId
        ]);

        // Préparer les nouvelles associations
        $requeteAssociation = $dbConnection->prepare(
            "INSERT INTO menu_plat (menu_id, plat_id)
            VALUES (:menu_id, :plat_id)"
        );

        // Enregistrer les trois plats sélectionnés
        foreach ($nouveauxPlats as $idPlat) {

            $requeteAssociation->execute([
                'menu_id' => $menuId,
                'plat_id' => $idPlat
            ]);
        }
    }

    // Valider les modifications
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Le menu a été modifié avec succès.'
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

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier le menu.'
    ]);
}
