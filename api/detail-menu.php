
<?php

require_once __DIR__ . '/../config/db-connection.php';

// Vérifier que l'identifiant existe dans l'URL
if (!isset($_GET['id'])) {
    exit('Aucun menu sélectionné.');
}

// Récupérer l'identifiant
$id = $_GET['id'];

$sql = " SELECT
        m.id,
        m.titre,
        m.description,
        m.nb_personnes_min,
        m.prix_personne,
        m.stock_disponible,
        m.conditions,
        m.delai_commande_jours,
        t.nom AS theme,
        r.nom AS regime
    FROM menu AS m
    JOIN theme AS t ON m.theme_id = t.id
    JOIN regime AS r ON m.regime_id = r.id
    WHERE m.id = :id
";

$stmt = $dbConnection->prepare($sql);

$stmt->execute(['id' => $id]);

$menu = $stmt->fetch();

if (!$menu) {
    http_response_code(404);
    exit('Menu introuvable.');
}


// Récupérer les plats du menu
$sqlPlats = "SELECT p.nom, p.categorie
    FROM plat AS p
    JOIN menu_plat AS mp ON p.id = mp.plat_id
    WHERE mp.menu_id = :id
    ORDER BY FIELD(p.categorie, 'entree', 'plat', 'dessert')
";

$stmt = $dbConnection->prepare($sqlPlats);
$stmt->execute(['id' => $id]);
$plats = $stmt->fetchAll();


// Récupérer les images
$sqlImages = "SELECT url, texte_alternatif
    FROM image_menu
    WHERE menu_id = :id
    ORDER BY ordre ASC
";

$stmt = $dbConnection->prepare($sqlImages);
$stmt->execute(['id' => $id]);
$images = $stmt->fetchAll();


// Récupérer les allergènes
$sqlAllergenes = "SELECT DISTINCT a.nom
    FROM allergene AS a
    JOIN plat_allergene AS pa
        ON a.id = pa.allergene_id
    JOIN menu_plat AS mp
        ON pa.plat_id = mp.plat_id
    WHERE mp.menu_id = :id
    ORDER BY a.nom
";

$stmt = $dbConnection->prepare($sqlAllergenes);
$stmt->execute(['id' => $id]);
$allergenes = $stmt->fetchAll();


$categories = [
    'entree' => 'Entrée',
    'plat' => 'Plat',
    'dessert' => 'Dessert'
];

$prix = number_format($menu['prix_personne'], 0, ',', ' ');
$delai = (int) $menu['delai_commande_jours'];
$stockDisponible = (int) $menu['stock_disponible'];
?>

<div class="modal-header align-items-start">
    <div>
        <p class="eyebrow mb-2" id="menu-modal-category"><?= htmlspecialchars($menu['theme'] . ' · ' . $menu['regime'], ENT_QUOTES, 'UTF-8') ?></p>

        <h2 class="modal-title" id="menu-modal-title"><?= htmlspecialchars($menu['titre'], ENT_QUOTES, 'UTF-8') ?></h2>
    </div>

    <button
        type="button"
        class="btn-close flex-shrink-0"
        data-bs-dismiss="modal"
        aria-label="Fermer les détails du menu"
    ></button>
</div>

<!-- Contenu défilant -->
<div class="modal-body">
    <p class="menu-modal-description" id="menu-modal-description"><?= htmlspecialchars($menu['description'], ENT_QUOTES, 'UTF-8') ?></p>

    <?php if ($images): ?>
    <!-- Galerie PDO sans défilement automatique -->
    <div
        id="menu-modal-gallery"
        class="carousel slide menu-gallery mb-4"
        role="region"
        aria-roledescription="carrousel"
        aria-label="Photos du menu"
        data-bs-interval="false"
    >
        <div class="carousel-inner" id="menu-modal-images">
            <?php foreach ($images as $index => $image): ?>
                <div class="carousel-item<?= $index === 0 ? ' active' : '' ?>">
                    <img src="<?= htmlspecialchars('/' . ltrim($image['url'], '/'), ENT_QUOTES, 'UTF-8') ?>"
                            class="menu-gallery-overview"
                            alt="<?= htmlspecialchars($image['texte_alternatif'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($images) > 1): ?>

        <button
            class="carousel-control-prev"
            type="button"
            data-bs-target="#menu-modal-gallery"
            data-bs-slide="prev"
        >
            <span
                class="carousel-control-prev-icon"
                aria-hidden="true"
            ></span>
            <span class="visually-hidden">
                Image précédente
            </span>
        </button>

        <button
            class="carousel-control-next"
            type="button"
            data-bs-target="#menu-modal-gallery"
            data-bs-slide="next"
        >
            <span
                class="carousel-control-next-icon"
                aria-hidden="true"
            ></span>
            <span class="visually-hidden">
                Image suivante
            </span>
        </button>

        <div class="carousel-indicators" id="menu-modal-indicators">
            <?php foreach ($images as $index => $image): ?>
                <button type="button" data-bs-target="#menu-modal-gallery"
                        data-bs-slide-to="<?= $index ?>"
                        <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>
                        aria-label="<?= htmlspecialchars($image['texte_alternatif'] ?: 'Image ' . ($index + 1), ENT_QUOTES, 'UTF-8') ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Composition du menu -->
    <h3>Au menu</h3>

    <ul class="menu-modal-dishes">
        <?php foreach ($plats as $plat): ?>
            <li><strong><?= htmlspecialchars($categories[$plat['categorie']] ?? $plat['categorie'], ENT_QUOTES, 'UTF-8') ?> :</strong>
                <?= htmlspecialchars($plat['nom'], ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>

    <p>
        <strong>Allergènes :</strong>
        <span id="menu-modal-allergens"><?= htmlspecialchars(implode(', ', array_column($allergenes, 'nom')) ?: 'Aucun allergène renseigné pour ce menu.', ENT_QUOTES, 'UTF-8') ?></span>
    </p>

    <p>
        <strong>Conditions du menu :</strong>
        <span id="menu-modal-conditions"><?= $delai > 0 ? 'Commande ' . $delai . ' jour' . ($delai > 1 ? 's' : '') . ' à l’avance. ' : 'Aucun délai minimum de commande. ' ?><?= htmlspecialchars($menu['conditions'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </p>

    <!-- Informations commerciales -->
    <div class="d-flex flex-wrap gap-3">
        <p class="mb-0">
            <strong>Minimum :</strong>
            <span id="menu-modal-minimum"><?= (int) $menu['nb_personnes_min'] ?></span>
            personnes
        </p>

        <p class="mb-0">
            <strong>Prix :</strong>
            <span id="menu-modal-price"><?= $prix ?></span>
            € / personne
        </p>

        <p class="mb-0">
            <strong>Disponibilité :</strong>

            <span
                id="menu-modal-stock"
                class="<?= $stockDisponible === 0 ? 'text-danger fw-semibold' : '' ?>"
            >
                <?php if ($stockDisponible > 0): ?>

                    <?= $stockDisponible ?> commandes disponibles

                <?php else: ?>

                    Indisponible

                <?php endif; ?>
            </span>
        </p>

    </div>
</div>


<!-- Action -->
<div class="modal-footer justify-content-start">

    <?php if ($stockDisponible > 0): ?>

        <!-- Menu disponible -->
        <button
            type="button"
            class="btn btn-primary button px-4 py-3"
            id="menu-modal-order"
            data-menu-id="<?= (int) $menu['id'] ?>"
        >
            Commander ce menu

            <i
                class="bi bi-arrow-right ms-3"
                aria-hidden="true"
            ></i>
        </button>

    <?php else: ?>

        <!-- Menu indisponible -->
        <button
            type="button"
            class="btn btn-secondary px-4 py-3"
            disabled
        >
            Menu indisponible
        </button>

    <?php endif; ?>

</div>

