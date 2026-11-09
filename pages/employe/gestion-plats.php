<!-- GESTION DES PLATS -->

<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">GESTION DES PLATS</div>

        <h2>Les plats <em>proposés.</em></h2>

        <p class="contact-form-note mb-4">
            Ajoutez, modifiez et supprimez les plats proposés par Vite & Gourmand, ainsi que leurs allergènes.
        </p>

        <!-- ACTIONS -->

        <div class="mb-4">

            <button
                type="button"
                id="ajouter-plat"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modal-gestion-plat"
            >
                Ajouter un plat
            </button>

        </div>

        <!-- MESSAGES -->

        <div
            id="gestion-plats-feedback"
            role="status"
            aria-live="polite"
        ></div>

        <!-- LISTE DES PLATS -->

        <div
            id="gestion-plats-liste"
            class="row g-4"
        ></div>

    </div>

</section>


<!-- MODALE AJOUT / MODIFICATION D'UN PLAT -->

<div
    class="modal fade"
    id="modal-gestion-plat"
    tabindex="-1"
    aria-labelledby="modal-gestion-plat-titre"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <!-- EN-TÊTE -->

            <div class="modal-header">

                <h2
                    class="modal-title h5"
                    id="modal-gestion-plat-titre"
                >
                    Ajouter un plat
                </h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <!-- FORMULAIRE -->

            <form id="form-gestion-plat">

                <div class="modal-body">

                    <!-- IDENTIFIANT DU PLAT -->

                    <input
                        type="hidden"
                        id="gestion-plat-id"
                        name="plat_id"
                    >

                    <!-- JETON CSRF -->

                    <input
                        type="hidden"
                        id="gestion-plat-csrf"
                        name="csrf_token"
                    >

                    <!-- NOM -->

                    <div class="mb-3">

                        <label
                            for="gestion-plat-nom"
                            class="form-label"
                        >
                            Nom du plat
                        </label>

                        <input
                            type="text"
                            id="gestion-plat-nom"
                            name="nom"
                            class="form-control"
                            maxlength="150"
                            required
                        >

                    </div>

                    <!-- DESCRIPTION -->

                    <div class="mb-3">

                        <label
                            for="gestion-plat-description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            id="gestion-plat-description"
                            name="description"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                        ></textarea>

                    </div>

                    <!-- CATÉGORIE -->

                    <div class="mb-3">

                        <label
                            for="gestion-plat-categorie"
                            class="form-label"
                        >
                            Catégorie
                        </label>

                        <select
                            id="gestion-plat-categorie"
                            name="categorie"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Choisir une catégorie
                            </option>

                            <option value="entree">
                                Entrée
                            </option>

                            <option value="plat">
                                Plat
                            </option>

                            <option value="dessert">
                                Dessert
                            </option>

                        </select>

                    </div>

                    <!-- ALLERGÈNES -->

                    <fieldset class="mb-3">

                        <legend class="form-label">
                            Allergènes
                        </legend>

                        <p class="form-text">
                            Sélectionnez les allergènes présents
                            dans ce plat.
                        </p>

                        <div
                            id="gestion-plat-allergenes"
                            class="row g-2"
                        ></div>

                    </fieldset>

                    <!-- MESSAGES -->

                    <div
                        id="gestion-plat-feedback"
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
                        id="gestion-plat-enregistrer"
                        class="btn btn-primary"
                    >
                        Enregistrer
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
