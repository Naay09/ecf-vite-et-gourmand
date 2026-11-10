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

// Vérifier les droits
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les champs
$menuId = $_POST['menu_id'] ?? '';
$jeton = $_POST['csrf_token'] ?? '';

$titrePrincipal = $_POST['image_principale_titre'] ?? '';
$altPrincipal = $_POST['image_principale_alt'] ?? '';

$titresExistants = $_POST['images_existantes_titres'] ?? [];
$imagesASupprimer = $_POST['images_a_supprimer'] ?? [];
$titresNouveaux = $_POST['images_titres'] ?? [];

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

// Vérifier les données reçues
if (
    !is_string($menuId) ||
    !ctype_digit($menuId) ||
    (int) $menuId < 1 ||
    !is_string($titrePrincipal) ||
    !is_string($altPrincipal) ||
    !is_array($titresExistants) ||
    !is_array($imagesASupprimer) ||
    !is_array($titresNouveaux)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données invalides.'
    ]);

    exit;
}

$titrePrincipal = trim($titrePrincipal);
$altPrincipal = trim($altPrincipal);

// Vérifier le titre et le texte alternatif principal
if (
    $titrePrincipal === '' ||
    mb_strlen($titrePrincipal) > 150 ||
    $altPrincipal === '' ||
    mb_strlen($altPrincipal) > 255
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Le titre et le texte alternatif de l’image principale sont obligatoires.'
    ]);

    exit;
}

// Préparer les fichiers à importer
$fichiers = [];

// Nouvelle image principale, si sélectionnée
if (isset($_FILES['image_principale'])) {

    $image = $_FILES['image_principale'];

    if ($image['error'] !== UPLOAD_ERR_NO_FILE) {

        $fichiers[] = [
            'fichier' => $image,
            'principal' => true,
            'titre' => $titrePrincipal,
            'alt' => $altPrincipal
        ];
    }
}

// Images complémentaires
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

        $titre = $titresNouveaux[$index] ?? '';

        if (
            !is_string($titre) ||
            trim($titre) === '' ||
            mb_strlen(trim($titre)) > 150
        ) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Chaque nouvelle image doit posséder un titre.'
            ]);

            exit;
        }

        $fichiers[] = [
            'fichier' => [
                'name' => $nom,
                'tmp_name' => $images['tmp_name'][$index],
                'size' => $images['size'][$index],
                'error' => $images['error'][$index]
            ],
            'principal' => false,
            'titre' => trim($titre),
            'alt' => trim($titre)
        ];
    }
}

// Vérifier les formats et les tailles
$extensions = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

$finfo = new finfo(FILEINFO_MIME_TYPE);

$imagesValidees = [];

foreach ($fichiers as $element) {

    $fichier = $element['fichier'];

    if (
        $fichier['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($fichier['tmp_name']) ||
        $fichier['size'] <= 0 ||
        $fichier['size'] > 5 * 1024 * 1024
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Chaque image doit être valide et ne pas dépasser 5 Mo.'
        ]);

        exit;
    }

    $mime = $finfo->file($fichier['tmp_name']);

    if (
        !isset($extensions[$mime]) ||
        getimagesize($fichier['tmp_name']) === false
    ) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Formats autorisés : JPG, PNG et WebP.'
        ]);

        exit;
    }

    // Générer un nom unique
    $nom = bin2hex(random_bytes(16));
    $nom .= '.' . $extensions[$mime];

    $imagesValidees[] = [
        'temporaire' => $fichier['tmp_name'],
        'nom' => $nom,
        'principal' => $element['principal'],
        'titre' => $element['titre'],
        'alt' => $element['alt']
    ];
}

// Dossier des images importées
$dossier = __DIR__ . '/../images/menus/';

// Fichiers créés pendant cette requête
$fichiersCrees = [];

// Anciens fichiers à nettoyer après validation
$anciensFichiers = [];

// Indiquer si la transaction SQL a été validée
$transactionValidee = false;

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

        throw new InvalidArgumentException(
            'Menu introuvable.'
        );
    }

    // Récupérer les images existantes
    $requeteImages = $dbConnection->prepare(
        "SELECT id, url, ordre
         FROM image_menu
         WHERE menu_id = :menu_id
         ORDER BY ordre, id"
    );

    $requeteImages->execute([
        'menu_id' => $menuId
    ]);

    $imagesExistantes = $requeteImages->fetchAll(
        PDO::FETCH_ASSOC
    );

    $imagePrincipale = null;
    $imagesComplementaires = [];

    // Séparer l'image principale des autres images
    foreach ($imagesExistantes as $image) {

        if ((int) $image['ordre'] === 0) {

            $imagePrincipale = $image;

        } else {

            $imagesComplementaires[$image['id']] = $image;
        }
    }

    // Vérifier les images à supprimer
    $idsSuppression = [];

    foreach ($imagesASupprimer as $id) {

        if (
            !is_string($id) ||
            !ctype_digit($id) ||
            !isset($imagesComplementaires[$id])
        ) {

            throw new InvalidArgumentException(
                'Une image à supprimer est invalide.'
            );
        }

        $idsSuppression[$id] = true;
    }

    // Vérifier les titres des images existantes
    foreach ($titresExistants as $id => $titre) {

        // Vérifier que l'image appartient bien au menu
        if (
            !ctype_digit((string) $id) ||
            !isset($imagesComplementaires[$id])
        ) {

            throw new InvalidArgumentException(
                'Image introuvable.'
            );
        }

        // Ne pas vérifier le titre d'une image à supprimer
        if (isset($idsSuppression[$id])) {
            continue;
        }

        // Vérifier le titre des images conservées
        if (
            !is_string($titre) ||
            trim($titre) === '' ||
            mb_strlen(trim($titre)) > 150
        ) {

            throw new InvalidArgumentException(
                'Un titre d’image est invalide.'
            );
        }
    }

    // Compter les images conservées
    $nombreImages = 1;

    foreach ($imagesComplementaires as $id => $image) {

        if (!isset($idsSuppression[$id])) {
            $nombreImages++;
        }
    }

    // Ajouter les nouvelles images complémentaires au total
    foreach ($imagesValidees as $image) {

        if (!$image['principal']) {
            $nombreImages++;
        }
    }

    // Limiter à 6 images
    if ($nombreImages > 6) {

        throw new InvalidArgumentException(
            'Un menu ne peut pas contenir plus de 6 images.'
        );
    }

    // Vérifier si une nouvelle image principale est fournie
    $nouvellePrincipale = false;

    foreach ($imagesValidees as $image) {

        if ($image['principal']) {
            $nouvellePrincipale = true;
        }
    }

    // Une image principale est obligatoire
    if (!$imagePrincipale && !$nouvellePrincipale) {

        throw new InvalidArgumentException(
            'Une image principale est obligatoire.'
        );
    }

    // Vérifier le dossier de destination uniquement si des fichiers sont importés
    if (
        count($imagesValidees) > 0 &&
        (!is_dir($dossier) || !is_writable($dossier))
    ) {
        throw new RuntimeException(
            'Le dossier des images est inaccessible.'
        );
    }

    // Modifier le titre et le texte alternatif de l'image principale
    if ($imagePrincipale) {

        $requete = $dbConnection->prepare(
            "UPDATE image_menu
             SET titre = :titre,
                 texte_alternatif = :alt
             WHERE id = :id
             AND menu_id = :menu_id"
        );

        $requete->execute([
            'titre' => $titrePrincipal,
            'alt' => $altPrincipal,
            'id' => $imagePrincipale['id'],
            'menu_id' => $menuId
        ]);
    }

    // Modifier les titres des images complémentaires
    $requeteTitre = $dbConnection->prepare(
        "UPDATE image_menu
         SET titre = :titre
         WHERE id = :id
         AND menu_id = :menu_id"
    );

    foreach ($titresExistants as $id => $titre) {

        // Ne pas modifier une image destinée à être supprimée
        if (isset($idsSuppression[$id])) {
            continue;
        }

        $requeteTitre->execute([
            'titre' => trim($titre),
            'id' => $id,
            'menu_id' => $menuId
        ]);
    }

    // Préparer la suppression SQL
    $requeteSuppression = $dbConnection->prepare(
        "DELETE FROM image_menu
         WHERE id = :id
         AND menu_id = :menu_id"
    );

    // Supprimer les associations des images cochées
    foreach ($idsSuppression as $id => $valeur) {

        // Mémoriser l'ancien fichier
        $anciensFichiers[] =
            $imagesComplementaires[$id]['url'];

        $requeteSuppression->execute([
            'id' => $id,
            'menu_id' => $menuId
        ]);

        unset($imagesComplementaires[$id]);
    }

    // Préparer l'insertion des nouvelles images
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
            :alt,
            :ordre
        )"
    );

    // Préparer le remplacement d'une image
    $requeteRemplacement = $dbConnection->prepare(
        "UPDATE image_menu
         SET titre = :titre,
             url = :url,
             texte_alternatif = :alt
         WHERE id = :id
         AND menu_id = :menu_id"
    );

    // Déterminer le prochain ordre disponible
    $prochainOrdre = 1;

    foreach ($imagesComplementaires as $image) {

        $ordre = (int) $image['ordre'];

        if ($ordre >= $prochainOrdre) {

            $prochainOrdre = $ordre + 1;
        }
    }

    // Importer les nouveaux fichiers
    foreach ($imagesValidees as $image) {

        $destination = $dossier . $image['nom'];

        // Déplacer le fichier téléchargé
        if (!move_uploaded_file(
            $image['temporaire'],
            $destination
        )) {

            throw new RuntimeException(
                'Impossible de déplacer une image.'
            );
        }

        // Mémoriser le nouveau fichier
        $fichiersCrees[] = $destination;

        // Construire le chemin enregistré dans MySQL
        $url = 'images/menus/' . $image['nom'];

        if ($image['principal'] && $imagePrincipale) {

            // Mémoriser l'ancienne image principale
            $anciensFichiers[] = $imagePrincipale['url'];

            // Remplacer l'image principale
            $requeteRemplacement->execute([
                'titre' => $image['titre'],
                'url' => $url,
                'alt' => $image['alt'],
                'id' => $imagePrincipale['id'],
                'menu_id' => $menuId
            ]);

        } else {

            // Déterminer l'ordre de la nouvelle image
            if ($image['principal']) {

                $ordre = 0;

            } else {

                $ordre = $prochainOrdre;
                $prochainOrdre++;
            }

            // Ajouter la nouvelle image
            $requeteInsertion->execute([
                'menu_id' => $menuId,
                'titre' => $image['titre'],
                'url' => $url,
                'alt' => $image['alt'],
                'ordre' => $ordre
            ]);
        }
    }

    // Préparer la réorganisation des images
    $requeteOrdre = $dbConnection->prepare(
        "UPDATE image_menu
         SET ordre = :ordre
         WHERE id = :id
         AND menu_id = :menu_id"
    );

    // Récupérer les images complémentaires restantes
    $requeteListe = $dbConnection->prepare(
        "SELECT id
         FROM image_menu
         WHERE menu_id = :menu_id
         AND ordre > 0
         ORDER BY ordre, id"
    );

    $requeteListe->execute([
        'menu_id' => $menuId
    ]);

    $ordre = 1;

    // Réattribuer les ordres à partir de 1
    while ($image = $requeteListe->fetch(PDO::FETCH_ASSOC)) {

        $requeteOrdre->execute([
            'ordre' => $ordre,
            'id' => $image['id'],
            'menu_id' => $menuId
        ]);

        $ordre++;
    }

    // Valider les modifications SQL
    $dbConnection->commit();

    // Indiquer que la transaction est validée
    $transactionValidee = true;

    // Nettoyer les anciens fichiers après validation
    foreach ($anciensFichiers as $url) {

        // Ne nettoyer que nos fichiers importés
        if (strpos($url, 'images/menus/') !== 0) {
            continue;
        }

        $nom = basename($url);

        // Vérifier que le chemin ne contient pas de sous-dossier
        if ($url !== 'images/menus/' . $nom) {
            continue;
        }

        try {

            // Vérifier que le fichier n'est plus utilisé
            $requeteReference = $dbConnection->prepare(
                "SELECT COUNT(*)
                 FROM image_menu
                 WHERE url = :url"
            );

            $requeteReference->execute([
                'url' => $url
            ]);

            if (
                (int) $requeteReference->fetchColumn() > 0
            ) {
                continue;
            }

            $chemin = $dossier . $nom;

            if (is_file($chemin)) {

                if (!unlink($chemin)) {

                    error_log(
                        'Impossible de supprimer : ' . $chemin
                    );
                }
            }

        } catch (Throwable $erreurNettoyage) {

            // Une erreur de nettoyage ne doit pas annuler
            // les modifications déjà enregistrées dans MySQL
            error_log($erreurNettoyage->getMessage());
        }
    }

    // Réponse en cas de succès
    echo json_encode([
        'success' => true,
        'message' => 'Les images du menu ont été modifiées.'
    ]);

} catch (Throwable $erreur) {

    // Annuler la transaction si elle est encore active
    if ($dbConnection->inTransaction()) {

        $dbConnection->rollBack();
    }

    // Supprimer les nouveaux fichiers uniquement
    // si la transaction SQL n'a pas été validée
    if (!$transactionValidee) {

        foreach ($fichiersCrees as $chemin) {

            if (is_file($chemin)) {

                unlink($chemin);
            }
        }
    }

    // Enregistrer l'erreur dans les logs PHP
    error_log($erreur->getMessage());

    // Déterminer le code HTTP
    if ($erreur instanceof InvalidArgumentException) {

        http_response_code(400);

    } else {

        http_response_code(500);
    }

    // Retourner un message JSON
    echo json_encode([
        'success' => false,
        'message' => $erreur instanceof InvalidArgumentException
            ? $erreur->getMessage()
            : 'Impossible de modifier les images.'
    ]);
}
