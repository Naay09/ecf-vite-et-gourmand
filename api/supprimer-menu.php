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

// Vérifier les autorisations
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données
$menuId = $_POST['menu_id'] ?? '';
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

// Vérifier l'identifiant du menu
if (
    !is_string($menuId) ||
    !ctype_digit($menuId) ||
    (int) $menuId < 1
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Identifiant du menu invalide.'
    ]);

    exit;
}

// Conserver les chemins des images du menu
$cheminsImages = [];

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Vérifier que le menu existe
    // et verrouiller sa ligne
    $requeteMenu = $dbConnection->prepare(
        "SELECT id, titre
         FROM menu
         WHERE id = :menu_id
         FOR UPDATE"
    );

    $requeteMenu->execute([
        'menu_id' => $menuId
    ]);

    $menu = $requeteMenu->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {

        throw new InvalidArgumentException(
            'Ce menu est introuvable.'
        );
    }

    // Vérifier si le menu possède des commandes
    $requeteCommandes = $dbConnection->prepare(
        "SELECT COUNT(*)
         FROM commande
         WHERE menu_id = :menu_id"
    );

    $requeteCommandes->execute([
        'menu_id' => $menuId
    ]);

    $nombreCommandes = (int) $requeteCommandes->fetchColumn();

    if ($nombreCommandes > 0) {

        throw new InvalidArgumentException(
            'Ce menu ne peut pas être supprimé car il est associé à des commandes.'
        );
    }

    // Récupérer les chemins des images
    $requeteImages = $dbConnection->prepare(
        "SELECT url
         FROM image_menu
         WHERE menu_id = :menu_id"
    );

    $requeteImages->execute([
        'menu_id' => $menuId
    ]);

    $cheminsImages = $requeteImages->fetchAll(
        PDO::FETCH_COLUMN
    );

    // Supprimer les associations avec les plats
    $requetePlats = $dbConnection->prepare(
        "DELETE FROM menu_plat
         WHERE menu_id = :menu_id"
    );

    $requetePlats->execute([
        'menu_id' => $menuId
    ]);

    // Supprimer les références aux images
    $requeteSuppressionImages = $dbConnection->prepare(
        "DELETE FROM image_menu
         WHERE menu_id = :menu_id"
    );

    $requeteSuppressionImages->execute([
        'menu_id' => $menuId
    ]);

    // Supprimer le menu
    $requeteSuppressionMenu = $dbConnection->prepare(
        "DELETE FROM menu
         WHERE id = :menu_id"
    );

    $requeteSuppressionMenu->execute([
        'menu_id' => $menuId
    ]);

    // Valider les suppressions dans MySQL
    $dbConnection->commit();

    // Nettoyer les fichiers importés par l'application
    foreach ($cheminsImages as $url) {

        // Ne traiter que les images du dossier dédié
        if (strpos($url, 'images/menus/') !== 0) {
            continue;
        }

        $nomFichier = basename($url);

        // Refuser les chemins comportant des sous-dossiers inattendus
        if ($url !== 'images/menus/' . $nomFichier) {
            continue;
        }

        // Vérifier que le fichier n'est plus référencé
        $requeteReference = $dbConnection->prepare(
            "SELECT COUNT(*)
             FROM image_menu
             WHERE url = :url"
        );

        $requeteReference->execute([
            'url' => $url
        ]);

        if ((int) $requeteReference->fetchColumn() > 0) {
            continue;
        }

        $chemin = __DIR__ . '/../images/menus/' . $nomFichier;

        if (is_file($chemin)) {
            if (!unlink($chemin)) {
                error_log(
                    'Impossible de supprimer le fichier : ' . $chemin
                );
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Le menu a été supprimé avec succès.'
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
        'message' => 'Impossible de supprimer le menu.'
    ]);
}
