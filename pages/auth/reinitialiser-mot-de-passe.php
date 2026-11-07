<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">SÉCURITÉ DE VOTRE COMPTE</div>

        <h2>Choisissez votre <em>nouveau mot de passe.</em></h2>

        <p class="contact-form-note mb-4">
            Définissez un nouveau mot de passe pour accéder
            à votre espace personnel.
        </p>

        <div class="col-12 col-md-7">

            <form id="form-reinitialisation" method="post">

                <!-- Jeton temporaire -->
                <input
                    type="hidden"
                    id="reset-jeton"
                    name="jeton"
                >

                <div class="row g-4">

                    <!-- Nouveau mot de passe -->
                    <div class="col-12">
                        <label for="nouveau-password" class="form-label">
                            Nouveau mot de passe *
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="nouveau-password"
                            name="mot_de_passe"
                            autocomplete="new-password"
                            minlength="10"
                            maxlength="72"
                            required
                        >
                    </div>

                    <!-- Confirmation -->
                    <div class="col-12">
                        <label for="confirmation-password" class="form-label">
                            Confirmer le mot de passe *
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="confirmation-password"
                            name="confirmation_mot_de_passe"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                   <!-- Critères de sécurité -->
                        <div class="col-12">

                            <p class="form-text mb-2">
                                Votre mot de passe doit respecter les critères suivants :
                            </p>

                            <ul class="list-unstyled form-text">

                                <li id="reset-critere-longueur" class="text-secondary">
                                    ○ 10 caractères minimum
                                </li>

                                <li id="reset-critere-majuscule" class="text-secondary">
                                    ○ Une majuscule
                                </li>

                                <li id="reset-critere-minuscule" class="text-secondary">
                                    ○ Une minuscule
                                </li>

                                <li id="reset-critere-chiffre" class="text-secondary">
                                    ○ Un chiffre
                                </li>

                                <li id="reset-critere-special" class="text-secondary">
                                    ○ Un caractère spécial
                                </li>

                                <li id="reset-critere-confirmation" class="text-secondary">
                                    ○ Mots de passe identiques
                                </li>

                            </ul>

                        </div>

                </div>

                <div class="mt-4">
                    <button
                        type="submit"
                        id="reset-password-submit"
                        class="btn btn-primary button"
                    >
                        Enregistrer mon nouveau mot de passe
                    </button>
                </div>

                <!-- Messages de confirmation ou d'erreur -->
                <div
                    id="reinitialisation-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                >
                </div>

            </form>

        </div>

    </div>

</section>
