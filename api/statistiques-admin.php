<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Autoriser uniquement GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier les droits
require __DIR__ . '/../includes/auth.php';

verifierAcces(['administrateur']);

// Connexions aux deux bases
require __DIR__ . '/../config/db-connection.php';
require __DIR__ . '/../config/mongodb.php';

// Récupérer les filtres
$menuId = $_GET['menu_id'] ?? '';
$dateDebut = $_GET['date_debut'] ?? '';
$dateFin = $_GET['date_fin'] ?? '';

// Vérifier les types
if (
    !is_string($menuId) ||
    !is_string($dateDebut) ||
    !is_string($dateFin)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Filtres invalides.'
    ]);

    exit;
}

// Vérifier le menu
if (
    $menuId !== '' &&
    (!ctype_digit($menuId) || (int) $menuId < 1)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Menu invalide.'
    ]);

    exit;
}

// Fuseau horaire
$fuseau = new DateTimeZone('Europe/Paris');

// Fonction de vérification des dates
function verifierDateStatistiques($valeur, $fuseau) {

    if ($valeur === '') {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $valeur,
        $fuseau
    );

    if (!$date || $date->format('Y-m-d') !== $valeur) {
        return false;
    }

    return $date;
}

// Vérifier les dates
$debut = verifierDateStatistiques($dateDebut, $fuseau);
$fin = verifierDateStatistiques($dateFin, $fuseau);

if ($debut === false || $fin === false) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Date invalide.'
    ]);

    exit;
}

// Vérifier l'ordre des dates
if ($debut !== null && $fin !== null && $debut > $fin) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'La date de début doit précéder la date de fin.'
    ]);

    exit;
}

try {

    // 1. RÉCUPÉRER LES MENUS DEPUIS MYSQL

    $requeteMenus = $dbConnection->prepare(
        "SELECT id, titre
         FROM menu
         ORDER BY titre ASC"
    );

    $requeteMenus->execute();

    $menus = $requeteMenus->fetchAll(PDO::FETCH_ASSOC);


    // 2. COMPTER LES COMMANDES DANS MONGODB

    $filtresMongo = [
        'statut' => ['$ne' => 'annulee']
    ];

    // Filtre par menu
    if ($menuId !== '') {

        $filtresMongo['menu_id'] = (int) $menuId;
    }

    // Filtre par date
    if ($debut !== null || $fin !== null) {

        $filtresMongo['date_creation'] = [];

        if ($debut !== null) {

            $filtresMongo['date_creation']['$gte'] =
                new MongoDB\BSON\UTCDateTime(
                    $debut->getTimestamp() * 1000
                );
        }

        if ($fin !== null) {

            // Inclure toute la journée de fin
            $finExclusive = $fin->modify('+1 day');

            $filtresMongo['date_creation']['$lt'] =
                new MongoDB\BSON\UTCDateTime(
                    $finExclusive->getTimestamp() * 1000
                );
        }
    }

    // Regrouper les commandes par menu
    $pipeline = [
        [
            '$match' => $filtresMongo
        ],
        [
            '$group' => [
                '_id' => '$menu_id',
                'nombre_commandes' => ['$sum' => 1]
            ]
        ]
    ];

    // Exécuter la requête MongoDB
    $commandeMongo = new MongoDB\Driver\Command([
        'aggregate' => $mongoCollection,
        'pipeline' => $pipeline,
        'cursor' => new stdClass()
    ]);

    $resultatsMongo = $mongoConnection->executeCommand(
        $mongoDatabase,
        $commandeMongo
    );

    // Stocker les résultats par identifiant de menu
    $commandesParMenu = [];

    foreach ($resultatsMongo as $resultat) {

        $id = (int) $resultat->_id;

        $commandesParMenu[$id] =
            (int) $resultat->nombre_commandes;
    }


    // 3. CALCULER LE CHIFFRE D'AFFAIRES MYSQL

    $sql = "SELECT
                menu_id,
                SUM(total) AS chiffre_affaires
            FROM commande
            WHERE statut_courant = 'terminee'";

    $parametres = [];

    // Filtre par menu
    if ($menuId !== '') {

        $sql .= " AND menu_id = :menu_id";

        $parametres['menu_id'] = (int) $menuId;
    }

    // Filtre par date de début
    if ($debut !== null) {

        $sql .= " AND date_creation >= :date_debut";

        $parametres['date_debut'] =
            $debut->format('Y-m-d H:i:s');
    }

    // Filtre par date de fin
    if ($fin !== null) {

        $sql .= " AND date_creation < :date_fin";

        $parametres['date_fin'] =
            $fin->modify('+1 day')->format('Y-m-d H:i:s');
    }

    $sql .= " GROUP BY menu_id";

    $requeteCA = $dbConnection->prepare($sql);

    $requeteCA->execute($parametres);

    $resultatsCA = $requeteCA->fetchAll(PDO::FETCH_ASSOC);

    $caParMenu = [];

    foreach ($resultatsCA as $resultat) {

        $caParMenu[(int) $resultat['menu_id']] =
            (float) $resultat['chiffre_affaires'];
    }


    // 4. ASSEMBLER LES STATISTIQUES

    $statistiquesMenus = [];

    $totalCommandes = 0;
    $totalCA = 0;

    foreach ($menus as $menu) {

        $id = (int) $menu['id'];

        // Appliquer le filtre par menu
        if ($menuId !== '' && $id !== (int) $menuId) {
            continue;
        }

        $nombre = $commandesParMenu[$id] ?? 0;
        $chiffreAffaires = $caParMenu[$id] ?? 0;

        $statistiquesMenus[] = [
            'menu_id' => $id,
            'titre' => $menu['titre'],
            'nombre_commandes' => $nombre,
            'chiffre_affaires' => round($chiffreAffaires, 2)
        ];

        $totalCommandes += $nombre;
        $totalCA += $chiffreAffaires;
    }


    // 5. RETOURNER LE RÉSULTAT

    echo json_encode([
        'success' => true,
        'total_commandes' => $totalCommandes,
        'chiffre_affaires_total' => round($totalCA, 2),
        'liste_menus' => $menus,
        'menus' => $statistiquesMenus
    ]);

} catch (Throwable $erreur) {

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de récupérer les statistiques.'
    ]);
}
