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
