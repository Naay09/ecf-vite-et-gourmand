<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            UN BEAU MOMENT SE PREPARE DANS LES MOINDRES DETAILS.
            </div>
        <h1>
            Et si on passait
            <br />
            à <em>table.</em>
        </h1>
        <p> Vous avez trouvé le menu qui vous fait envie ? <br/>
            Il ne vous reste plus qu'à finaliser votre commande. </p>

        <a class="btn btn-light button" href="#form-commande">Finaliser ma commande<i class="bi bi-arrow-down"></i></a>
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>

</section>

<!-- FORMULAIRE DE COMMANDE -->
<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">VOTRE COMMANDE</div>

        <h2>Organisons votre <em>événement.</em></h2>

        <p class="contact-form-note mb-4">
            Complétez les informations de votre prestation.
            Les champs marqués d'un * sont obligatoires.
        </p>

        <div class="col-12 col-lg-8">

            <form id="form-commande" method="post">

                <!-- COORDONNÉES DU CLIENT -->

                <h3 class="mb-4">Vos coordonnées</h3>

                <div class="row g-4">

                    <div class="col-12 col-md-6">
                        <label for="commande-prenom" class="form-label">
                            Prénom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="commande-prenom"
                            name="prenom_client"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="commande-nom" class="form-label">
                            Nom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="commande-nom"
                            name="nom_client"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="commande-email" class="form-label">
                            Adresse e-mail *
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="commande-email"
                            name="email_client"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="commande-telephone" class="form-label">
                            Téléphone *
                        </label>
                        <input
                            type="tel"
                            class="form-control"
                            id="commande-telephone"
                            name="telephone_client"
                            maxlength="20"
                            required
                        >
                    </div>

                </div>

                <!-- INFORMATIONS DE LA PRESTATION -->

                <h3 class="mt-5 mb-4">Votre prestation</h3>

                <div class="row g-4">

                    <div class="col-12">
                        <label for="commande-adresse" class="form-label">
                            Adresse de livraison *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="commande-adresse"
                            name="adresse_livraison"
                            placeholder="Numéro et nom de rue"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <label for="commande-ville" class="form-label">
                            Ville de livraison *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="commande-ville"
                            name="ville_livraison"
                            maxlength="100"
                            placeholder="Ex. : Bordeaux"
                            required
                        >
                    </div>

                    <!-- Distance de livraison hors Bordeaux -->
                    <div
                        class="col-12 col-md-6 d-none"
                        id="commande-distance-bloc"
                    >
                        <label for="commande-distance" class="form-label">
                            Distance parcourue (km) *
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="commande-distance"
                            name="distance_livraison_km"
                            min="0.1"
                            step="0.1"
                            placeholder="Ex. : 20"
                        >

                        <small class="form-text">
                            Distance renseignée provisoirement pour les tests.
                        </small>
                    </div>


                    <div class="col-12 col-md-6">
                        <label for="commande-date" class="form-label">
                            Date de prestation *
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="commande-date"
                            name="date_prestation"
                            required
                        >
                        <small id="commande-date-info" class="form-text" aria-live="polite"></small>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="commande-heure" class="form-label">
                            Heure de livraison *
                        </label>
                        <input
                            type="time"
                            class="form-control"
                            id="commande-heure"
                            name="heure_livraison"
                            required
                        >
                    </div>

                </div>

                <!-- CHOIX DU MENU -->

                <h3 class="mt-5 mb-4">Votre menu</h3>

                <div class="row g-4">

                    <div class="col-12 col-md-8">
                        <label for="commande-menu" class="form-label">
                            Menu sélectionné *
                        </label>

                        <select
                            class="form-select"
                            id="commande-menu"
                            name="menu_id"
                            required
                        >
                            <option value="">
                                Choisissez un menu
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="commande-convives" class="form-label">
                            Nombre de convives *
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="commande-convives"
                            name="nb_personnes"
                            min="1"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <p
                            id="commande-conditions"
                            class="contact-form-note"
                        ></p>
                    </div>

                </div>

                <!-- RÉCAPITULATIF DU PRIX -->

                <h3 class="mt-5 mb-4">Récapitulatif du prix</h3>

                <div class="border p-4">

                    <div class="d-flex justify-content-between mb-3">
                        <span>Prix du menu</span>
                        <strong id="commande-prix-menu">—</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Remise</span>
                        <strong id="commande-remise">—</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Frais de livraison</span>
                        <strong id="commande-livraison">—</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <strong>Total de la commande</strong>
                        <strong id="commande-total">—</strong>
                    </div>

                </div>

                <!-- VALIDATION -->

                <div class="mt-4">

                    <button
                        type="submit"
                        class="btn btn-primary button"
                        id="commande-valider"
                        disabled
                    >
                        Confirmer ma commande
                    </button>

                </div>

                <div
                    id="commande-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>
                
                <input type="hidden" name="csrf_token" id="commande-csrf">
            </form>

        </div>

    </div>

</section>
