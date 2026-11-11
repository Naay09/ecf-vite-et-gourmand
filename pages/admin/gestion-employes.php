<section class="contact-form-section">

    <div class="heading">

        <!-- Retour à l'espace administrateur -->
        <a href="/espace-admin" class="d-inline-flex align-items-center gap-2 mb-4 text-decoration-none">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Retour à l'espace Administrateur
        </a>

        <div class="eyebrow">
            GESTION DES EMPLOYÉS
        </div>

        <h2>Les comptes <em>employés.</em></h2>

        <p class="contact-form-note mb-4">Créez de nouveaux comptes employés et gérezleurs accès à l'application.</p>

        <!-- Ajouter un employé -->
        <button
            type="button"
            id="ajouter-employe"
            class="btn btn-primary mb-4"
            data-bs-toggle="modal"
            data-bs-target="#modal-ajouter-employe"
        >
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Ajouter un employé
        </button>

        <!-- Messages -->
        <div
            id="gestion-employes-feedback"
            class="mb-4"
            role="status"
            aria-live="polite"
        ></div>

        <!-- Liste des employés -->
        <div
            id="gestion-employes-liste"
            class="row g-4"
        ></div>

    </div>

</section>


<!-- MODALE : AJOUTER UN EMPLOYÉ -->

<div
    class="modal fade"
    id="modal-ajouter-employe"
    tabindex="-1"
    aria-labelledby="modal-ajouter-employe-titre"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h2 class="modal-title h5" id="modal-ajouter-employe-titre">Ajouter un employé</h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <form id="form-ajouter-employe">

                <div class="modal-body">

                    <!-- Nom -->
                    <div class="mb-3">

                        <label
                            for="employe-nom"
                            class="form-label"
                        >
                            Nom
                        </label>

                        <input
                            type="text"
                            id="employe-nom"
                            name="nom"
                            class="form-control"
                            maxlength="100"
                            autocomplete="family-name"
                            required
                        >

                    </div>

                    <!-- Prénom -->
                    <div class="mb-3">

                        <label
                            for="employe-prenom"
                            class="form-label"
                        >
                            Prénom
                        </label>

                        <input
                            type="text"
                            id="employe-prenom"
                            name="prenom"
                            class="form-control"
                            maxlength="100"
                            autocomplete="given-name"
                            required
                        >

                    </div>

                    <!-- Email -->
                    <div class="mb-3">

                        <label
                            for="employe-email"
                            class="form-label"
                        >
                            Adresse e-mail
                        </label>

                        <input
                            type="email"
                            id="employe-email"
                            name="email"
                            class="form-control"
                            maxlength="255"
                            autocomplete="email"
                            required
                        >

                        <div class="form-text">
                            Cette adresse servira d'identifiant
                            de connexion.
                        </div>

                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-3">

                        <label
                            for="employe-mot-de-passe"
                            class="form-label"
                        >
                            Mot de passe
                        </label>

                        <input
                            type="password"
                            id="employe-mot-de-passe"
                            name="mot_de_passe"
                            class="form-control"
                            autocomplete="new-password"
                            minlength="12"
                            required
                        >

                        <div class="form-text">
                            Le mot de passe sera communiqué
                            directement à l'employé,
                            jamais par e-mail.
                        </div>

                    </div>

                    <input
                        type="hidden"
                        id="employe-csrf"
                        name="csrf_token"
                    >

                    <!-- Message de retour -->
                    <div
                        id="ajouter-employe-feedback"
                        role="status"
                        aria-live="polite"
                    ></div>

                </div>

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
                        id="enregistrer-employe"
                        class="btn btn-primary"
                        disabled
                    >
                        Créer le compte
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
