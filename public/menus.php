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

        <!-- Filtres -->
    <div class="menu-filters mb-4 mb-lg-5" id="menu-filters">
        <div class="row g-3">

            <!-- Recherche -->
            <div class="col-12 col-lg-4">
                <label for="menu-search" class="form-label">Rechercher</label>
                <input
                    type="search"
                    class="form-control"
                    id="menu-search"
                    placeholder="Un menu, une envie…"
                >
            </div>

            <!-- Thème -->
            <div class="col-12 col-md-6 col-lg-2">
                <label for="menu-theme" class="form-label">Thème</label>
                <select class="form-select" id="menu-theme">
                    <option value="">Tous les thèmes</option>
                    <option value="Classique">Classique</option>
                    <option value="Noël">Noël</option>
                    <option value="Événement">Événement</option>
                    <option value="Pâques">Pâques</option>
                </select>
            </div>

            <!-- Régime -->
            <div class="col-12 col-md-6 col-lg-2">
                <label for="menu-diet" class="form-label">Régime</label>
                <select class="form-select" id="menu-diet">
                    <option value="">Tous les régimes</option>
                    <option value="Classique">Classique</option>
                    <option value="Végétarien">Végétarien</option>
                    <option value="Vegan">Végan</option>
                </select>
            </div>

            <!-- Nombre de convives -->
            <div class="col-12 col-md-6 col-lg-2">
                <label for="menu-guests" class="form-label">Nombre de convives</label>

                <input
                    type="range"
                    class="form-range"
                    id="menu-guests"
                    min="6"
                    max="60"
                    value="60"
                    step="1"
                    aria-describedby="menu-guests-help"
                >

                <small id="menu-guests-help">
                    Jusque 60 convives
                </small>
            </div>
            
            <!-- Prix -->
            <div class="col-12">

                    <!-- Choix du mode de filtrage -->
                    <fieldset class="mb-3">

                        <legend class="form-label">
                            Filtrer par prix
                        </legend>

                        <div class="d-flex flex-wrap gap-3">

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="menu-price-mode"
                                    id="price-mode-max"
                                    value="max"
                                    checked
                                >
                                <label class="form-check-label" for="price-mode-max">
                                    Prix maximum
                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="menu-price-mode"
                                    id="price-mode-range"
                                    value="range"
                                >
                                <label class="form-check-label" for="price-mode-range">
                                    Fourchette de prix
                                </label>
                            </div>

                        </div>
                    </fieldset>

                    <!-- Curseurs -->
                    <div class="row g-3 menu-price-controls">

                        <!-- Prix minimum : masqué par défaut -->
                        <div class="col-12 col-md-6 d-none" id="menu-price-min-block">

                            <label for="menu-price-min" class="form-label">
                                Prix minimum
                            </label>

                            <input
                                type="range"
                                class="form-range"
                                id="menu-price-min"
                                min="0"
                                max="120"
                                value="0"
                                step="1"
                                aria-describedby="menu-price-min-help"
                            >

                            <small id="menu-price-min-help">
                                À partir de 0 € / pers.
                            </small>
                        </div>

                        <!-- Prix maximum -->
                        <div class="col-12 col-md-6">

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
                                Jusqu'à 120 € / pers.
                            </small>
                        </div>

                    </div>
                </div>
        </div>
            <!-- Réinitialisation des filtres -->
            <div class="d-flex mt-3">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    id="menu-reset"
                >
                    <i class="bi bi-arrow-counterclockwise me-2"
                    aria-hidden="true"></i>
                    Réinitialiser les filtres
                </button>
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

        <div class="row g-4" id="menu-results">
            <?php require __DIR__ . '/../includes/cartes-menu.php'; ?>
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