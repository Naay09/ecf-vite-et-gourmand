
<?php

require_once __DIR__ . '/../config/db-connection.php';

// Récupérer les valeurs des filtres
$recherche = $_GET['recherche'] ?? '';
$theme = $_GET['theme'] ?? '';
$regime = $_GET['regime'] ?? '';
$convives = $_GET['convives'] ?? '';
$modePrix = $_GET['mode_prix'] ?? 'max';
$prixMin = $_GET['prix_min'] ?? '';
$prixMax = $_GET['prix_max'] ?? '';

// ==========================================
// VALIDATION DES FILTRES
// ==========================================

// Vérifier que les paramètres sont du texte
$valeurs = [
    $recherche, $theme, $regime, $convives,
    $modePrix, $prixMin, $prixMax
];

foreach ($valeurs as $valeur) {
    if (!is_string($valeur)) {
        http_response_code(400);
        exit('Paramètre invalide.');
    }
}

// Supprimer les espaces inutiles
$recherche = trim($recherche);
$theme = trim($theme);
$regime = trim($regime);

// Vérifier la longueur des textes
if (
    strlen($recherche) > 200 ||
    strlen($theme) > 100 ||
    strlen($regime) > 100
) {
    http_response_code(400);
    exit('Texte trop long.');
}

// Vérifier le mode de prix
if ($modePrix !== 'max' && $modePrix !== 'range') {
    http_response_code(400);
    exit('Mode de prix invalide.');
}


// Vérifier le nombre de convives
if ($convives !== '') {
    if (
        !is_numeric($convives) ||
        (float) $convives != (int) $convives ||
        $convives < 6 ||
        $convives > 100
    ) {
        http_response_code(400);
        exit('Nombre de convives invalide.');
    }
}

// Vérifier le prix maximum
if ($prixMax !== '') {
    if (
        !is_numeric($prixMax) ||
        $prixMax < 0 ||
        $prixMax > 120
    ) {
        http_response_code(400);
        exit('Prix maximum invalide.');
    }
}

// Vérifier la fourchette de prix
if ($modePrix === 'range') {
    if (
        $prixMin === '' ||
        $prixMax === '' ||
        !is_numeric($prixMin) ||
        $prixMin < 0 ||
        $prixMin > 120 ||
        (float) $prixMin > (float) $prixMax
    ) {
        http_response_code(400);
        exit('Fourchette de prix invalide.');
    }
}


// Requête SQL de base
$sql = " SELECT
        m.id,
        m.titre,
        m.description,
        m.nb_personnes_min,
        m.prix_personne,
        m.stock_disponible,
        t.nom AS theme,
        r.nom AS regime,
        i.url AS image_url,
        i.texte_alternatif

    FROM menu AS m

    JOIN theme AS t ON m.theme_id = t.id

    JOIN regime AS r ON m.regime_id = r.id

    LEFT JOIN image_menu AS i
        ON i.menu_id = m.id AND i.ordre = 0

    WHERE 1 = 1
";

// Tableau des paramètres SQL
$parametres = [];

// Recherche par nom ou description
if ($recherche !== '') {
    $sql .= " AND (m.titre LIKE :recherche1
                   OR m.description LIKE :recherche2)";

    $parametres['recherche1'] = '%' . $recherche . '%';
    $parametres['recherche2'] = '%' . $recherche . '%';
}

// Filtre par thème
if ($theme !== '') {
    $sql .= " AND t.nom = :theme";
    $parametres['theme'] = $theme;
}

// Filtre par régime
if ($regime !== '') {
    $sql .= " AND r.nom = :regime";
    $parametres['regime'] = $regime;
}

// Filtre par nombre de convives
if ($convives !== '') {
    $sql .= " AND m.nb_personnes_min <= :convives";
    $parametres['convives'] = (int) $convives;
}

// Filtre par prix maximum
if ($prixMax !== '') {
    $sql .= " AND m.prix_personne <= :prix_max";
    $parametres['prix_max'] = $prixMax;
}

// Filtre par fourchette de prix
if ($modePrix === 'range' && $prixMin !== '') {
    $sql .= " AND m.prix_personne >= :prix_min";
    $parametres['prix_min'] = $prixMin;
}

// Trier les menus
$sql .= " ORDER BY m.id";


// Préparer la requête SQL
$stmt = $dbConnection->prepare($sql);

// Exécuter avec les paramètres
$stmt->execute($parametres);

// Récupérer tous les menus correspondants
$menus = $stmt->fetchAll();

// Vérifier si des menus correspondent aux filtres
if (count($menus) === 0) {
    echo 
    '<div class="col-12 text-center py-5">
        Aucun menu ne correspond à votre recherche.
    </div>';
    exit;
}

// Afficher les cartes des menus
require __DIR__ . '/../includes/cartes-menu.php';

