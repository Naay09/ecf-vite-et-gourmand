<!-- GESTION DES MENUS -->

<section class="contact-form-section">

    <div class="heading">

        <!-- RETOUR À L'ESPACE EMPLOYÉ -->
        <a href="/espace-employe" class="d-inline-flex align-items-center gap-2 mb-4 text-decoration-none">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Retour à l'espace Employé</a>

        <div class="eyebrow">GESTION DES MENUS</div>

        <h2>Les menus <em>proposés.</em></h2>

        <p class="contact-form-note mb-4">
            Ajoutez, modifiez et supprimez les menus proposés par Vite & Gourmand, ainsi que leurs plats, leurs images et leurs disponibilités.
        </p>

        <!-- ACTIONS -->

        <div class="mb-4">

            <button
                type="button"
                id="ajouter-menu"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modal-gestion-menu"
            >
                Ajouter un menu
            </button>

        </div>

        <!-- MESSAGES -->

        <div
            id="gestion-menus-feedback"
            class="mb-4"
            role="status"
            aria-live="polite"
        ></div>

        <!-- LISTE DES MENUS -->

        <div
            id="gestion-menus-liste"
            class="row g-4"
        ></div>

    </div>

</section>


<!-- MODALE AJOUT / MODIFICATION D'UN MENU -->

<div
    class="modal fade"
    id="modal-gestion-menu"
    tabindex="-1"
    aria-labelledby="modal-gestion-menu-titre"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <!-- EN-TÊTE -->

            <div class="modal-header">

                <h2
                    class="modal-title h5"
                    id="modal-gestion-menu-titre"
                >
                    Ajouter un menu
                </h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <!-- FORMULAIRE -->

            <form id="form-gestion-menu" enctype="multipart/form-data">

                <div class="modal-body">

                    <!-- IDENTIFIANT DU MENU -->

                    <input
                        type="hidden"
                        id="gestion-menu-id"
                        name="menu_id"
                    >

                    <!-- JETON CSRF -->

                    <input
                        type="hidden"
                        id="gestion-menu-csrf"
                        name="csrf_token"
                    >

                    <!-- TITRE -->

                    <div class="mb-3">

                        <label
                            for="gestion-menu-titre"
                            class="form-label"
                        >
                            Titre du menu
                        </label>

                        <input
                            type="text"
                            id="gestion-menu-titre"
                            name="titre"
                            class="form-control"
                            maxlength="150"
                            required
                        >

                    </div>

                    <!-- DESCRIPTION -->

                    <div class="mb-3">

                        <label
                            for="gestion-menu-description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            id="gestion-menu-description"
                            name="description"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            required
                        ></textarea>

                    </div>

                    <!-- THÈME ET RÉGIME -->

                    <div class="row g-3 mb-3">

                        <div class="col-12 col-md-6">

                            <label
                                for="gestion-menu-theme"
                                class="form-label"
                            >
                                Thème
                            </label>

                            <select
                                id="gestion-menu-theme"
                                name="theme_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Choisir un thème
                                </option>
                            </select>

                        </div>

                        <div class="col-12 col-md-6">

                            <label
                                for="gestion-menu-regime"
                                class="form-label"
                            >
                                Régime alimentaire
                            </label>

                            <select
                                id="gestion-menu-regime"
                                name="regime_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Choisir un régime
                                </option>
                            </select>

                        </div>

                    </div>

                    <!-- NOMBRE MINIMUM ET PRIX -->

                    <div class="row g-3 mb-3">

                        <div class="col-12 col-md-6">

                            <label
                                for="gestion-menu-personnes"
                                class="form-label"
                            >
                                Nombre minimum de personnes
                            </label>

                            <input
                                type="number"
                                id="gestion-menu-personnes"
                                name="nb_personnes_min"
                                class="form-control"
                                min="1"
                                step="1"
                                required
                            >

                        </div>

                        <div class="col-12 col-md-6">

                            <label
                                for="gestion-menu-prix"
                                class="form-label"
                            >
                                Prix par personne (€)
                            </label>

                            <input
                                type="number"
                                id="gestion-menu-prix"
                                name="prix_personne"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>

                    <!-- DÉLAI DE COMMANDE -->

                    <div class="mb-3">

                        <label
                            for="gestion-menu-delai"
                            class="form-label"
                        >
                            Délai minimum de commande (jours)
                        </label>

                        <input
                            type="number"
                            id="gestion-menu-delai"
                            name="delai_commande_jours"
                            class="form-control"
                            min="0"
                            step="1"
                            required
                        >

                        <div class="form-text">
                            Nombre de jours à respecter entre la
                            commande et la date de prestation.
                        </div>

                    </div>

                    <!-- COMPOSITION DU MENU -->

                    <fieldset class="mb-4">

                        <legend class="h5 mb-3">
                            Composition du menu
                        </legend>

                        <p class="form-text mb-3">
                            Sélectionnez une entrée, un plat principal et
                            un dessert parmi les plats enregistrés.
                        </p>

                        <!-- ENTRÉE -->

                        <div class="mb-3">

                            <label
                                for="gestion-menu-entree"
                                class="form-label"
                            >
                                Entrée
                            </label>

                            <select
                                id="gestion-menu-entree"
                                name="entree_id"
                                class="form-select"
                                required
                            >
                                <option value="">Choisir une entrée</option>
                            </select>

                        </div>

                        <!-- PLAT PRINCIPAL -->

                        <div class="mb-3">

                            <label
                                for="gestion-menu-plat"
                                class="form-label"
                            >
                                Plat principal
                            </label>

                            <select
                                id="gestion-menu-plat"
                                name="plat_id"
                                class="form-select"
                                required
                            >
                                <option value="">Choisir un plat principal</option>
                            </select>

                        </div>

                        <!-- DESSERT -->

                        <div class="mb-3">

                            <label
                                for="gestion-menu-dessert"
                                class="form-label"
                            >
                                Dessert
                            </label>

                            <select
                                id="gestion-menu-dessert"
                                name="dessert_id"
                                class="form-select"
                                required
                            >
                                <option value="">Choisir un dessert</option>
                            </select>

                        </div>

                    </fieldset>

                    <!-- CONDITIONS -->

                    <div class="mb-3">

                        <label
                            for="gestion-menu-conditions"
                            class="form-label"
                        >
                            Conditions du menu
                        </label>

                        <textarea
                            id="gestion-menu-conditions"
                            name="conditions"
                            class="form-control"
                            rows="3"
                            maxlength="1000"
                        ></textarea>

                        <div class="form-text">
                            Indiquez les précautions de conservation
                            ou les conditions particulières.
                        </div>

                    </div>

                    <!-- GALERIE D'IMAGES -->

                    <fieldset class="mb-4">

                        <legend class="h5 mb-3">
                            Galerie d'images
                        </legend>

                        <p class="form-text mb-3">
                            Ajoutez une image principale pour présenter
                            le menu, puis des images complémentaires
                            pour sa galerie.
                        </p>

                        <!-- IMAGE PRINCIPALE -->

                        <div class="mb-4">

                            <label
                                for="gestion-menu-image-principale"
                                class="form-label"
                            >
                                Image principale
                            </label>

                            <input
                                type="file"
                                id="gestion-menu-image-principale"
                                name="image_principale"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <div class="form-text">
                                Formats acceptés : JPG, PNG et WebP.
                            </div>

                            <div
                                id="gestion-menu-image-principale-apercu"
                                class="mt-3"
                            ></div>

                        </div>

                        <!-- TITRE DE L'IMAGE PRINCIPALE -->

                        <div class="mb-3">

                            <label
                                for="gestion-menu-image-titre"
                                class="form-label"
                            >
                                Titre de l'image principale
                            </label>

                            <input
                                type="text"
                                id="gestion-menu-image-titre"
                                name="image_principale_titre"
                                class="form-control"
                                maxlength="150"
                                placeholder="Ex. : Le Grand Classique"
                            >

                        </div>

                        <!-- TEXTE ALTERNATIF -->

                        <div class="mb-4">

                            <label
                                for="gestion-menu-image-alt"
                                class="form-label"
                            >
                                Description de l'image principale
                            </label>

                            <input
                                type="text"
                                id="gestion-menu-image-alt"
                                name="image_principale_alt"
                                class="form-control"
                                maxlength="255"
                            >

                            <div class="form-text">
                                Décrivez brièvement ce que représente
                                l'image pour les personnes utilisant
                                un lecteur d'écran.
                            </div>

                        </div>

                        <!-- IMAGES COMPLÉMENTAIRES -->

                        <div class="mb-3">

                            <label
                                for="gestion-menu-images"
                                class="form-label"
                            >
                                Images complémentaires
                            </label>

                            <input
                                type="file"
                                id="gestion-menu-images"
                                name="images[]"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                            >

                            <div class="form-text">
                                Vous pouvez sélectionner plusieurs images.
                            </div>

                        </div>

                        <!-- TITRES DES IMAGES COMPLÉMENTAIRES -->

                        <div id="gestion-menu-titres-images" class="mt-3"></div>

                        <!-- GALERIE EXISTANTE -->

                        <div
                            id="gestion-menu-images-existantes"
                            class="row g-3 mt-3"
                        ></div>

                    </fieldset>

                    <!-- MESSAGES -->

                    <div
                        id="gestion-menu-form-feedback"
                        role="status"
                        aria-live="polite"
                    ></div>

                </div>

                <!-- PIED DE MODALE -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        id="gestion-menu-enregistrer"
                        class="btn btn-primary"
                        disabled
                    >
                        Enregistrer
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

