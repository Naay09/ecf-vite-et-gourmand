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

// Récupérer les informations
$menuId = $_POST['menu_id'] ?? '';
$jeton = $_POST['csrf_token'] ?? '';
$texteAlternatif = $_POST['image_principale_alt'] ?? '';
$titrePrincipal = $_POST['image_principale_titre'] ?? '';
$titresImages = $_POST['images_titres'] ?? [];

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

// Vérifier les informations
if (
    !is_string($menuId) ||
    !ctype_digit($menuId) ||
    (int) $menuId < 1 ||
    !is_string($texteAlternatif) ||
    mb_strlen($texteAlternatif) > 255
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Informations du menu invalides.'
    ]);

    exit;
}

$texteAlternatif = trim($texteAlternatif);

// Vérifier la présence de l'image principale
if (
    !isset($_FILES['image_principale']) ||
    $_FILES['image_principale']['error'] !== UPLOAD_ERR_OK
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Une image principale est obligatoire.'
    ]);

    exit;
}


// Vérifier les titres
if (
    !is_string($titrePrincipal) ||
    !is_array($titresImages)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Titres des images invalides.'
    ]);

    exit;
}

$titrePrincipal = trim($titrePrincipal);

if (
    $titrePrincipal === '' ||
    mb_strlen($titrePrincipal) > 150
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le titre de l’image principale est obligatoire (150 caractères maximum).'
    ]);

    exit;
}

// Préparer la liste des images à enregistrer
$fichiers = [];

$fichiers[] = [
    'fichier' => [
        'name' => $_FILES['image_principale']['name'],
        'tmp_name' => $_FILES['image_principale']['tmp_name'],
        'size' => $_FILES['image_principale']['size'],
        'error' => $_FILES['image_principale']['error']
    ],
    'titre' => $titrePrincipal,
    'ordre' => 0,
    'alt' => $texteAlternatif
];

// Ajouter les images complémentaires
if (isset($_FILES['images'])) {

    $images = $_FILES['images'];

    if (!is_array($images['name'])) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Format des images invalide.'
        ]);

        exit;
    }
    
    foreach ($images['name'] as $index => $nom) {

        if ($images['error'][$index] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Récupérer le titre de l'image
        $titreImage = $titresImages[$index] ?? '';

        // Vérifier le titre
        if (
            !is_string($titreImage) ||
            trim($titreImage) === '' ||
            mb_strlen(trim($titreImage)) > 150
        ) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Chaque image doit posséder un titre de 150 caractères maximum.'
            ]);

            exit;
        }

        // Ajouter l'image à la liste
        $fichiers[] = [
            'fichier' => [
                'name' => $nom,
                'tmp_name' => $images['tmp_name'][$index],
                'size' => $images['size'][$index],
                'error' => $images['error'][$index]
            ],
            'titre' => trim($titreImage),
            'ordre' => count($fichiers),
            'alt' => ''
        ];
    }
}

// Limiter le nombre de fichiers
if (count($fichiers) > 6) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Vous pouvez importer 6 images maximum.'
    ]);

    exit;
}

// Dossier de destination
$dossier = __DIR__ . '/../images/menus/';

// Préparer la validation des fichiers
$imagesValidees = [];

$finfo = new finfo(FILEINFO_MIME_TYPE);

$extensions = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

foreach ($fichiers as $element) {

    $fichier = $element['fichier'];

    // Vérifier l'envoi
    if (
        $fichier['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($fichier['tmp_name'])
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Un fichier n’a pas été transmis correctement.'
        ]);

        exit;
    }

    // Limiter chaque image à 5 Mo
    if (
        $fichier['size'] <= 0 ||
        $fichier['size'] > 5 * 1024 * 1024
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Chaque image doit faire 5 Mo maximum.'
        ]);

        exit;
    }

    // Vérifier le véritable type du fichier
    $mime = $finfo->file($fichier['tmp_name']);

    if (
        !isset($extensions[$mime]) ||
        getimagesize($fichier['tmp_name']) === false
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Format d’image invalide. Utilisez JPG, PNG ou WebP.'
        ]);

        exit;
    }

    // Générer un nom de fichier unique
    $nomFichier = bin2hex(random_bytes(16));
    $nomFichier .= '.' . $extensions[$mime];

    $imagesValidees[] = [
        'temporaire' => $fichier['tmp_name'],
        'titre' => $element['titre'],
        'nom' => $nomFichier,
        'ordre' => $element['ordre'],
        'alt' => $element['alt']
    ];
}

// Conserver les chemins des fichiers déplacés
$fichiersEnregistres = [];

try {

    // Démarrer la transaction
    $dbConnection->beginTransaction();

    // Vérifier que le menu existe
    $requeteMenu = $dbConnection->prepare(
        "SELECT id
         FROM menu
         WHERE id = :menu_id
         FOR UPDATE"
    );

    $requeteMenu->execute([
        'menu_id' => $menuId
    ]);

    if (!$requeteMenu->fetch()) {
        throw new InvalidArgumentException('Menu introuvable.');
    }

    // Vérifier que le menu ne possède pas déjà des images
    $requeteImages = $dbConnection->prepare(
        "SELECT COUNT(*)
         FROM image_menu
         WHERE menu_id = :menu_id"
    );

    $requeteImages->execute([
        'menu_id' => $menuId
    ]);

    if ((int) $requeteImages->fetchColumn() > 0) {

        throw new InvalidArgumentException(
            'Ce menu possède déjà des images.'
        );
    }

    // Vérifier le dossier de destination
    if (!is_dir($dossier)) {
        throw new RuntimeException('Dossier des images introuvable.');
    }

    if (!is_writable($dossier)) {
        throw new RuntimeException('Dossier des images non accessible en écriture.');
    }

    // Préparer l'insertion SQL
    $requeteInsertion = $dbConnection->prepare(
        "INSERT INTO image_menu (
            menu_id,
            titre,
            url,
            texte_alternatif,
            ordre
        )
        VALUES (
            :menu_id,
            :titre,
            :url,
            :texte_alternatif,
            :ordre
        )"
    );

    // Enregistrer les images
    foreach ($imagesValidees as $image) {

        $destination = $dossier . $image['nom'];

        // Déplacer le fichier téléchargé
        if (!move_uploaded_file($image['temporaire'], $destination)) {
            throw new RuntimeException('Impossible de déplacer une image.');
        }

        $fichiersEnregistres[] = $destination;

        // Chemin enregistré dans MySQL
        $url = 'images/menus/' . $image['nom'];

        $requeteInsertion->execute([
            'menu_id' => $menuId,
            'titre' => $image['titre'],
            'url' => $url,
            'texte_alternatif' => $image['alt'],
            'ordre' => $image['ordre']
        ]);
    }

    // Valider la transaction SQL
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Les images ont été enregistrées.',
        'nombre_images' => count($imagesValidees)
    ]);

} catch (Throwable $erreur) {

    // Annuler la transaction
    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    // Supprimer les fichiers éventuellement déplacés
    foreach ($fichiersEnregistres as $chemin) {

        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    error_log($erreur->getMessage());

    http_response_code(
        $erreur instanceof InvalidArgumentException ? 400 : 500
    );

    echo json_encode([
        'success' => false,
        'message' => $erreur instanceof InvalidArgumentException
            ? $erreur->getMessage()
            : 'Impossible d’enregistrer les images.'
    ]);
}
