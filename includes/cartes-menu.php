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

        $stockDisponible = (int) $menu['stock_disponible'];

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

                            <?php if ($stockDisponible > 0): ?>

                                <?= $stockDisponible ?> disponibles

                            <?php else: ?>

                                <span class="text-danger fw-semibold">
                                    Indisponible
                                </span>

                            <?php endif; ?>
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