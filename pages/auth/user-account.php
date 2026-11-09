<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            VOTRE ESPACE, VOS ENVIES, VOS MOMENTS GOURMANDS.
            </div>
        <h1>
            Un espace rien que
            <br />
            pour <em>vous.</em>
        </h1>
        <p> Retrouvez vos informations et vos commandes en toute simplicité. </p>

        <a class="btn btn-light button" href="#my-account">Gérer mon compte <i class="bi bi-arrow-down"></i></a>
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>

</section>


<!-- INFORMATIONS PERSONNELLES -->
<section class="contact-form-section" id="my-account">

    <div class="heading">

        <div class="eyebrow">VOTRE ESPACE PERSONNEL</div>

        <h2>Mes informations <em>personnelles.</em></h2>

        <p class="contact-form-note mb-4">
            Consultez et modifiez vos coordonnées.
            Les champs marqués d'un * sont obligatoires.
        </p>

        <div class="col-12 col-md-7">

            <form id="form-compte" method="post">
                <input type="hidden" name="csrf_token" value="">

                <div class="row g-4">

                    <!-- Prénom -->
                    <div class="col-12 col-md-6">
                        <label for="compte-prenom" class="form-label">
                            Prénom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="compte-prenom"
                            name="prenom"
                            autocomplete="given-name"
                            maxlength="100"
                            required
                        >
                    </div>

                    <!-- Nom -->
                    <div class="col-12 col-md-6">
                        <label for="compte-nom" class="form-label">
                            Nom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="compte-nom"
                            name="nom"
                            autocomplete="family-name"
                            maxlength="100"
                            required
                        >
                    </div>

                    <!-- Adresse e-mail -->
                    <div class="col-12 col-md-6">
                        <label for="compte-email" class="form-label">
                            Adresse e-mail *
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="compte-email"
                            name="email"
                            autocomplete="email"
                            maxlength="254"
                            required
                        >
                    </div>

                    <!-- Téléphone -->
                    <div class="col-12 col-md-6">
                        <label for="compte-telephone" class="form-label">
                            Téléphone *
                        </label>
                        <input
                            type="tel"
                            class="form-control"
                            id="compte-telephone"
                            name="telephone"
                            autocomplete="tel"
                            maxlength="30"
                            required
                        >
                    </div>

                    <!-- Adresse postale -->
                    <div class="col-12">
                        <label for="compte-adresse" class="form-label">
                            Adresse postale *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="compte-adresse"
                            name="adresse"
                            autocomplete="street-address"
                            maxlength="255"
                            required
                        >
                    </div>

                </div>

                <!-- Enregistrer les modifications -->
                <div class="mt-4">
                    <button
                        type="submit"
                        class="btn btn-primary button"
                        id="compte-enregistrer"
                        disabled
                    >
                        Enregistrer mes modifications
                    </button>
                </div>

                <!-- Message de confirmation ou d'erreur -->
                <div
                    id="compte-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>

            </form>

        </div>

    </div>

</section>

<!-- MES COMMANDES -->
<section class="contact-form-section" id="section-mes-commandes">

    <div class="heading">

        <div class="eyebrow">VOTRE ESPACE CLIENT</div>

        <h2>Mes <em>commandes.</em></h2>

        <p class="contact-form-note mb-4">
            Retrouvez vos commandes et consultez
            les informations de vos prestations.
        </p>

        <!-- Message de chargement ou d'erreur -->
        <div
            id="mes-commandes-feedback"
            class="mb-4"
            role="status"
            aria-live="polite"
        ></div>

        <!-- Liste des commandes -->
        <div
            id="mes-commandes-liste"
            class="row g-4"
        ></div>

    </div>

</section>

<!-- MODALE DÉTAIL COMMANDE -->

<div
    class="modal fade"
    id="modal-detail-commande"
    tabindex="-1"
    aria-labelledby="modal-detail-commande-titre"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <!-- En-tête -->
            <div class="modal-header">

                <h2
                    class="modal-title fs-4"
                    id="modal-detail-commande-titre"
                >
                    Détail de votre commande
                </h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <!-- Contenu -->
            <div class="modal-body">

                <div
                    id="detail-commande-feedback"
                    role="status"
                    aria-live="polite"
                ></div>

                <div id="detail-commande-contenu" class="d-none">

                    <!-- PRESTATION -->
                    <h3 class="h5 mb-3">Votre prestation</h3>

                    <p><strong>Menu :</strong> <span id="detail-menu"></span></p>
                    <p><strong>Date :</strong> <span id="detail-date"></span></p>
                    <p><strong>Heure :</strong> <span id="detail-heure"></span></p>
                    <p><strong>Convives :</strong> <span id="detail-convives"></span></p>

                    <p>
                        <strong>Livraison :</strong>
                        <span id="detail-adresse"></span>
                    </p>

                    <p>
                        <strong>Statut :</strong>
                        <span id="detail-statut"></span>
                    </p>

                    <hr class="my-4">

                    <!-- PRIX -->
                    <h3 class="h5 mb-3">Détail du prix</h3>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Prix du menu</span>
                        <span id="detail-prix-menu"></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Remise</span>
                        <span id="detail-remise"></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span id="detail-livraison"></span>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <strong>Total</strong>
                        <strong id="detail-total"></strong>
                    </div>

                    <hr class="my-4">

                    <!-- SUIVI DE COMMANDE -->
                    <h3 class="h5 mb-3">Suivi de votre commande</h3>

                    <p
                        id="detail-suivi-message"
                        class="contact-form-note"
                    ></p>

                    <ul
                        id="detail-suivi-liste"
                        class="list-group list-group-flush"
                    ></ul>

                    <!-- FORMULAIRE D'AVIS CLIENT -->

                    <form id="form-avis-client" class="d-none mt-4">

                        <hr class="my-4">

                        <h3 class="h5 mb-3">
                            Donner votre avis
                        </h3>

                        <p class="text-muted mb-4">
                            Votre commande est terminée.
                            Partagez votre expérience avec Vite & Gourmand.
                        </p>

                        <!-- INFORMATIONS TECHNIQUES -->

                        <input type="hidden" id="avis-commande-id" name="commande_id">

                        <input type="hidden" id="avis-csrf-token" name="csrf_token">

                        <!-- NOTE -->

                        <fieldset class="mb-4">

                            <legend class="form-label">Votre note *</legend>

                            <div class="d-flex flex-wrap gap-3">

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="note"
                                        id="avis-note-1"
                                        value="1"
                                        required
                                    >
                                    <label class="form-check-label" for="avis-note-1">1</label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="note"
                                        id="avis-note-2"
                                        value="2"
                                    >
                                    <label class="form-check-label" for="avis-note-2">2</label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="note"
                                        id="avis-note-3"
                                        value="3"
                                    >
                                    <label class="form-check-label" for="avis-note-3">3</label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="note"
                                        id="avis-note-4"
                                        value="4"
                                    >
                                    <label class="form-check-label" for="avis-note-4">4</label>
                                </div>

                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="note"
                                        id="avis-note-5"
                                        value="5"
                                    >
                                    <label class="form-check-label" for="avis-note-5">5</label>
                                </div>

                            </div>

                        </fieldset>

                        <!-- COMMENTAIRE -->

                        <div class="mb-4">

                            <label for="avis-commentaire" class="form-label">Votre commentaire *</label>

                            <textarea
                                id="avis-commentaire"
                                name="commentaire"
                                class="form-control"
                                rows="4"
                                maxlength="1000"
                                required
                                placeholder="Comment s'est passée votre expérience ?"
                            ></textarea>

                            <p class="small text-muted mt-2">
                                1000 caractères maximum.
                            </p>

                        </div>

                        <p class="small text-muted">Votre avis sera publié après validation par notre équipe.</p>

                        <div
                            id="avis-client-feedback"
                            class="mt-3"
                            role="status"
                            aria-live="polite"
                        ></div>

                        <!-- ENREGISTREMENT -->

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="avis-client-envoyer"
                            disabled
                        >
                            Envoyer mon avis
                        </button>

                    </form>

                    <!-- AVIS DÉJÀ DÉPOSÉ -->

                    <section id="avis-client-existant" class="mt-4 d-none">

                        <hr class="my-4">

                        <h3 class="h5 mb-3">
                            Votre avis
                        </h3>

                        <!-- NOTE -->

                        <p class="mb-2">
                            <strong>Note :</strong>
                            <span id="avis-existant-note"></span>
                            / 5
                        </p>

                        <!-- COMMENTAIRE -->

                        <p class="mb-2">
                            <strong>Commentaire :</strong>
                        </p>

                        <p
                            id="avis-existant-commentaire"
                            class="mb-3"
                        ></p>

                        <!-- MODÉRATION -->

                        <p class="mb-0">
                            <strong>État :</strong>

                            <span
                                id="avis-existant-moderation"
                                class="badge"
                            ></span>
                        </p>

                    </section>

                </div>

                <!-- FORMULAIRE DE MODIFICATION D'UNE COMMANDE -->

                <form id="form-modifier-commande" class="d-none" method="post">

                    <!-- Identifiant de la commande -->
                    <input type="hidden" name="commande_id" id="modif-commande-id">

                    <!-- Jeton CSRF -->
                    <input type="hidden" name="csrf_token" id="modif-commande-csrf">

                    <h3 class="h5 mb-4">Modifier votre commande</h3>

                    <!-- MENU NON MODIFIABLE -->

                    <div class="mb-4">

                        <label class="form-label">Menu sélectionné</label>

                        <p id="modif-menu" class="fw-semibold mb-1"></p>

                        <small class="form-text">
                            Le menu ne peut pas être modifié.
                        </small>

                    </div>

                    <!-- COORDONNÉES DU CLIENT -->

                    <h4 class="h6 mt-4 mb-3">Vos coordonnées</h4>

                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label for="modif-prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control"
                                id="modif-prenom" name="prenom_client"
                                maxlength="100" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control"
                                id="modif-nom" name="nom_client"
                                maxlength="100" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control"
                                id="modif-email" name="email_client"
                                maxlength="255" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-telephone" class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control"
                                id="modif-telephone" name="telephone_client"
                                maxlength="20" required>
                        </div>

                    </div>

                    <!-- INFORMATIONS DE PRESTATION -->

                    <h4 class="h6 mt-4 mb-3">Votre prestation</h4>

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="modif-adresse" class="form-label">
                                Adresse de livraison *
                            </label>
                            <input type="text" class="form-control"
                                id="modif-adresse" name="adresse_livraison"
                                required>
                        </div>

                        <div class="col-12">
                            <label for="modif-ville" class="form-label">
                                Ville de livraison *
                            </label>
                            <input type="text" class="form-control"
                                id="modif-ville" name="ville_livraison"
                                maxlength="100" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-date" class="form-label">
                                Date de prestation *
                            </label>
                            <input type="date" class="form-control"
                                id="modif-date" name="date_prestation"
                                required>
                                <small id="modif-date-info" class="form-text"></small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-heure" class="form-label">
                                Heure de livraison *
                            </label>
                            <input type="time" class="form-control"
                                id="modif-heure" name="heure_livraison"
                                required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modif-convives" class="form-label">
                                Nombre de convives *
                            </label>
                            <input type="number" class="form-control"
                                id="modif-convives" name="nb_personnes"
                                min="1" step="1" required>
                                <small id="modif-convives-info" class="form-text"></small>
                        </div>

                        <div class="col-12 col-md-6" id="modif-distance-bloc">
                            <label for="modif-distance" class="form-label">
                                Distance de livraison (km)
                            </label>
                            <input type="number" class="form-control"
                                id="modif-distance" name="distance_livraison_km"
                                min="0" step="0.1">
                        </div>

                    </div>

                    <!-- RÉCAPITULATIF -->

                    <h4 class="h6 mt-4 mb-3">Nouveau récapitulatif</h4>

                    <div class="border rounded p-3">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Prix du menu</span>
                            <span id="modif-prix-menu">—</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Remise</span>
                            <span id="modif-remise">—</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Livraison</span>
                            <span id="modif-livraison">—</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <strong>Nouveau total</strong>
                            <strong id="modif-total">—</strong>
                        </div>

                    </div>

                    <!-- MESSAGES -->

                    <div
                        id="modif-commande-feedback"
                        class="mt-3"
                        role="status"
                        aria-live="polite"
                    ></div>

                    <!-- ACTIONS -->

                    <div class="d-flex flex-wrap gap-2 mt-4">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="modif-retour-detail"
                        >
                            Retour au détail
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="modif-enregistrer"
                            disabled
                        >
                            Enregistrer les modifications
                        </button>

                    </div>

                </form>


            </div>

            <!-- Pied de la modale -->
            <div class="modal-footer">

                <!-- Emplacement pour les actions du détail de la commande -->
                <div id="detail-commande-actions" class="d-flex gap-2"></div>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Fermer
                </button>

            </div>

        </div>

    </div>
</div>

