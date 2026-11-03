<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            UN PETIT OUBLI ? PAS DE PANIQUE !
            </div>
        <h1>
            Et si on retrouvait
            <br />
            le <em>chemin.</em>
        </h1>
        <p> Un mot de passe oublié, ça arrive même aux plus gourmands ! </p>

        <a class="btn btn-light button" href="#form-mot-de-passe-oublie">Réinitialiser mon mot de passe<i class="bi bi-arrow-down"></i></a>
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>

</section>


<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">VOTRE ESPACE PERSONNEL</div>

        <h2>Mot de passe <em>oublié ?</em></h2>

        <p class="contact-form-note mb-4">
            Renseignez votre adresse e-mail pour recevoir
            un lien de réinitialisation de votre mot de passe.
        </p>

        <div class="col-12 col-md-7">

            <form id="form-mot-de-passe-oublie" method="post">

                <div class="mb-4">
                    <label for="reset-email" class="form-label">
                        Adresse e-mail *
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="reset-email"
                        name="email"
                        autocomplete="email"
                        placeholder="vous@exemple.fr"
                        maxlength="254"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary button"
                >
                    Recevoir un lien de réinitialisation
                </button>

                <div
                    id="reset-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>

            </form>

        </div>

    </div>

</section>
