<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            TRAITEUR À BORDEAUX & ALENTOURS
            </div>
        <h1>
            À chaque occasion, sa
            <br />
            touche de 
            <em>gourmandise.</em>
        </h1>
        <p> Des menus généreux, imaginés au fil des saisons et préparés avec soin pour 
            réunir ceux que vous aimez autour d'une belle table. </p>

        <a class="btn btn-light button" href="#menu-section">Découvrir nos menus <i class="bi bi-arrow-down"></i> </a>
      
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>
</section>


<section class="menu-catalogue" id="menu-section">

    <div class="container-fluid p-0">

        <div class="d-flex flex-wrap justify-content-between
                    align-items-end gap-3 mb-4">
            <div>
                <div class="eyebrow">LA CARTE DU MOMENT</div>
                <h2 class="mb-0">À votre <em>table.</em></h2>
            </div>
            <p class="menu-count mb-0">6 menus à découvrir</p>
        </div>

        <!-- Filtres : présentation uniquement -->
<div class="menu-filters mb-4 mb-lg-5">
    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <label for="menu-search" class="form-label">
                Rechercher
            </label>
            <input
                type="search"
                class="form-control"
                id="menu-search"
                placeholder="Un menu, une envie…"
            >
        </div>

        <div class="col-12 col-md-6 col-lg-2">
            <label for="menu-theme" class="form-label">
                Thème
            </label>
            <select class="form-select" id="menu-theme">
                <option value="">Tous les thèmes</option>
                <option value="classique">Classique</option>
                <option value="noel">Noël</option>
                <option value="evenement">Événement</option>
                <option value="paques">Pâques</option>
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-2">
            <label for="menu-diet" class="form-label">
                Régime
            </label>
            <select class="form-select" id="menu-diet">
                <option value="">Tous les régimes</option>
                <option value="classique">Classique</option>
                <option value="vegetarien">Végétarien</option>
                <option value="vegan">Végan</option>
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-2">
            <label for="menu-guests" class="form-label">
                Nombre de convives
            </label>

            <input
                type="range"
                class="form-range"
                id="menu-guests"
                min="6"
                max="60"
                value="20"
                step="1"
                aria-describedby="menu-guests-help"
            >

            <small id="menu-guests-help">
                De 6 à 60 convives
            </small>
        </div>

        <div class="col-12 col-md-6 col-lg-2">
            <label for="menu-price" class="form-label">
                Prix maximum
            </label>
            <input
                type="range"
                class="form-range"
                id="menu-price"
                min="0"
                max="120"
                value="120"
                step="1"
                aria-describedby="menu-price-help"
            >
            <small id="menu-price-help">
                De 0 à 120 € / pers.
            </small>
        </div>
    </div>
</div>    
            
    </div>

        <!-- Cartes -->
         <?php
        $menus = [];
        $erreurChargement = false;

        try {
            // Connexion à la base de données
            require_once __DIR__ . '/../config/db-connection.php';

            // Récupération des menus
            $sql = " SELECT
                    m.id,
                    m.titre,
                    m.description,
                    m.nb_personnes_min,
                    m.prix_personne,
                    m.stock_disponible,
                    m.delai_commande_jours,
                    m.conditions,
                    t.nom AS theme,
                    r.nom AS regime,
                    i.url AS image_url,
                    i.texte_alternatif
                FROM menu AS m
                JOIN theme AS t ON m.theme_id = t.id
                JOIN regime AS r ON m.regime_id = r.id
                LEFT JOIN image_menu AS i ON i.menu_id = m.id AND i.ordre = 0
                ORDER BY m.id
            ";

            // Exécution de la requête
            $menuQuery = $dbConnection->query($sql);
            // Récupération des résultats
            $menus = $menuQuery->fetchAll();

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $erreurChargement = true;
        }

        ?>

        <?php if ($erreurChargement): ?>

            <p role="alert">
                Impossible de charger les menus pour le moment.
                Veuillez réessayer ultérieurement.
            </p>

        <?php elseif (empty($menus)): ?>

            <p>
                Aucun menu n'est disponible pour le moment.
                Revenez prochainement découvrir nos offres.
            </p>

        <?php else: ?>

        <div class="row g-4">

            <?php foreach ($menus as $menu): ?>

                <?php
                    // Préparation des données
                    $titre = htmlspecialchars($menu['titre'], ENT_QUOTES, 'UTF-8');

                    $description = htmlspecialchars($menu['description'], ENT_QUOTES, 'UTF-8');

                    $theme = htmlspecialchars($menu['theme'], ENT_QUOTES, 'UTF-8');

                    $regime = htmlspecialchars($menu['regime'], ENT_QUOTES, 'UTF-8');

                    $imageUrl = '/' . $menu['image_url'];

                    $imageAlt = htmlspecialchars($menu['texte_alternatif'],ENT_QUOTES,'UTF-8');

                    $prix = number_format($menu['prix_personne'], 0, ',', ' ');

                ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card menu-card h-100">

                        <div class="position-relative">

                                <img
                                    src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    class="card-img-top"
                                    alt="<?= $imageAlt ?>"
                                >

                            <span class="menu-tag">
                                <?= $theme ?>
                            </span>

                        </div>

                        <div class="card-body d-flex flex-column">

                            <h3 class="card-title">
                                <?= $titre ?>
                            </h3>

                            <p class="menu-description">
                                <?= $description ?>
                            </p>

                            <div class="menu-details mt-auto">

                                <div class="d-flex justify-content-between
                                            align-items-center gap-2">

                                    <small>
                                        Dès <?= (int) $menu['nb_personnes_min'] ?> personnes
                                    </small>

                                    <strong>
                                        <?= $prix ?> € / pers.
                                    </strong>

                                </div>

                                <div class="d-flex flex-wrap justify-content-between
                                            align-items-center gap-2 mt-4">

                                    <small>
                                        <?= $regime ?> ·
                                        <?= (int) $menu['stock_disponible'] ?> disponibles
                                    </small>

                                    <a
                                        href="#modal-menu"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-menu"
                                        data-menu-id="<?= (int) $menu['id'] ?>"
                                        aria-haspopup="dialog"
                                        aria-label="Voir le menu <?= $titre ?>"
                                    >
                                        Voir le menu
                                        <i
                                            class="bi bi-arrow-up-right"
                                            aria-hidden="true"
                                        ></i>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </div>
</section>

<!-- Modale détail menu -->

<div
    class="modal fade menu-modal"
    id="modal-menu"
    tabindex="-1"
    aria-labelledby="menu-modal-title"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered
                modal-dialog-scrollable modal-fullscreen-sm-down">

        <div class="modal-content" id="menu-modal-content">

            <div class="modal-body">
                Sélectionnez un menu pour afficher ses détails.
            </div>

        </div>
    </div>
</div>