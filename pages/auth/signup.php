<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            REJOIGNEZ L'AVENTURE GOURMANDE !
            </div>
        <h1>
            Et si tout commmençait
            <br />
            <em>ici ?</em>
        </h1>
        <p> Quelques instants suffisent pour créer votre espace personnel. <br />
            Vos prochains moments gourmands n'attendent plus que vous ! </p>

        <a class="btn btn-light button" href="#contact-form">Créer mon compte <i class="bi bi-arrow-down"></i></a>
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>

</section>


<section class="contact-form-section" id="contact-form">

    <div class="heading">

        <div class="eyebrow">INSCRIPTION</div>

        <h2>Créez votre <em>compte client.</em></h2>

        <div class="col-12 col-md-7">

            <form id="inscription-form" method="post">

                <p class="contact-form-note mb-4">
                    Tous les champs sont obligatoires.
                </p>

                <div class="row g-4">

                    <!-- Prénom -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-prenom" class="form-label">
                            Prénom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="inscription-prenom"
                            name="prenom"
                            autocomplete="given-name"
                            placeholder="Votre prénom"
                            maxlength="100"
                            required
                        >
                    </div>

                    <!-- Nom -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-nom" class="form-label">
                            Nom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="inscription-nom"
                            name="nom"
                            autocomplete="family-name"
                            placeholder="Votre nom"
                            maxlength="100"
                            required
                        >
                    </div>

                    <!-- E-mail -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-email" class="form-label">
                            E-mail *
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="inscription-email"
                            name="email"
                            autocomplete="email"
                            placeholder="vous@exemple.fr"
                            maxlength="254"
                            required
                        >
                    </div>

                    <!-- Téléphone -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-telephone" class="form-label">
                            Téléphone *
                        </label>
                        <input
                            type="tel"
                            class="form-control"
                            id="inscription-telephone"
                            name="telephone"
                            autocomplete="tel"
                            placeholder="Votre numéro de téléphone"
                            required
                        >
                    </div>

                    <!-- Adresse postale -->
                    <div class="col-12">
                        <label for="inscription-adresse" class="form-label">
                            Adresse postale *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="inscription-adresse"
                            name="adresse"
                            autocomplete="street-address"
                            placeholder="Numéro, rue, code postal et ville"
                            required
                        >
                    </div>

                    <!-- Mot de passe -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-password" class="form-label">
                            Mot de passe *
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            id="inscription-password"
                            name="mot_de_passe"
                            autocomplete="new-password"
                            minlength="10"
                            aria-describedby="password-help"
                            required
                        >
                    </div>

                    <!-- Confirmation du mot de passe -->
                    <div class="col-12 col-md-6">
                        <label for="inscription-password-confirm" class="form-label">
                            Confirmer le mot de passe *
                        </label>
                        <input
                            type="password"
                            class="form-control"
                            id="inscription-password-confirm"
                            name="confirmation_mot_de_passe"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <p class="form-text mb-2">Votre mot de passe doit respecter les critères suivants :</p>

                        <ul id="password-help" class="list-unstyled form-text">
                            <li id="critere-longueur" class="text-secondary">
                                ○ 10 caractères minimum
                            </li>
                            <li id="critere-majuscule" class="text-secondary">
                                ○ Une majuscule
                            </li>
                            <li id="critere-minuscule" class="text-secondary">
                                ○ Une minuscule
                            </li>
                            <li id="critere-chiffre" class="text-secondary">
                                ○ Un chiffre
                            </li>
                            <li id="critere-special" class="text-secondary">
                                ○ Un caractère spécial
                            </li>
                            <li id="critere-confirmation" class="text-secondary">
                                ○ Mots de passe identiques
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Bouton d'inscription -->
                <div class="col-12 mt-4">
                    <button
                        class="btn btn-primary button"
                        type="submit"
                    >
                        Créer mon compte
                    </button>
                </div>

                <!-- Messages de confirmation ou d'erreur -->
                <div
                    id="inscription-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>

            </form>

        </div>

    </div>

</section>
