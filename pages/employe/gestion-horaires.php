<section class="contact-form-section">

    <div class="heading">

        <!-- Retour à l'espace employé -->
        <a
            href="/espace-employe"
            class="d-inline-flex align-items-center gap-2 mb-4 text-decoration-none"
        >
            <i
                class="bi bi-arrow-left"
                aria-hidden="true"
            ></i>
            Retour à l'espace Employé
        </a>

        <div class="eyebrow">
            GESTION DES HORAIRES
        </div>

        <h2>
            Les horaires <em>d'ouverture.</em>
        </h2>

        <p class="contact-form-note mb-4">
            Modifiez les horaires d'ouverture du lundi au dimanche
            et indiquez les jours de fermeture.
        </p>

        <!-- Messages de confirmation ou d'erreur -->
        <div
            id="gestion-horaires-feedback"
            class="mb-4"
            role="status"
            aria-live="polite"
        ></div>

        <!-- Formulaire des horaires -->
        <form id="form-gestion-horaires">

            <div
                id="gestion-horaires-liste"
                class="row g-3"
            ></div>

            <div class="mt-4">

                <button
                    type="submit"
                    id="gestion-horaires-enregistrer"
                    class="btn btn-primary"
                    disabled
                >
                    Enregistrer les horaires
                </button>

            </div>

        </form>

    </div>

</section>
