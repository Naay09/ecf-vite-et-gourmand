<section class="hero text-white">

    <div class="hero-content">
        <div class="eyebrow">
            <i class="bi bi-circle-fill"></i>
            UNE QUESTION, UNE ENVIE, UN BEAU MOMENT A PRÉPARER ?
            </div>
        <h1>
            Et si on préparait ça
            <br />
            <em>ensemble ?</em>
        </h1>
        <p> Un évènement à organiser ou une petite envie de réunir ?
            Racontez-nous votre idée, on s'occupe de la suite ! </p>

        <a class="btn btn-light button" href="#contact-form">Parlons de votre projet <i class="bi bi-arrow-down"></i></a>
    </div>
    <div class="hero-art">
        <img src="/images/cuisine-avec-le-coeur.svg" alt="Assiette décorative : cuisiné avec le cœur"/>
    </div>

</section>

<section class="contact-form-section" id="contact-form">

    <div class=" heading">
        <div class="eyebrow">VOTRE PROJET COMMENCE ICI</div>
        <h2>Racontez-nous <em>votre envie.</em></h2>
        <div class="col-12 col-md-5">
            <form id="contact-form" method="post">
                <p class="contact-form-note mb-4">Les champs marqués d’un * sont obligatoires.</p>
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label for="prenom" class="form-label">
                            Prénom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="prenom"
                            name="prenom"
                            autocomplete="given-name"
                            placeholder="Votre prénom"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="nom" class="form-label">
                            Nom *
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="nom"
                            name="nom"
                            autocomplete="family-name"
                            placeholder="Votre nom"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">
                            E-mail *
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            autocomplete="email"
                            placeholder="vous@exemple.fr"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="telephone" class="form-label">
                            Téléphone
                        </label>
                        <input
                            type="tel"
                            class="form-control"
                            id="telephone"
                            name="telephone"
                            autocomplete="tel"
                            placeholder="Votre numéro de téléphone"
                        >
                    </div>

                    <div class="col-12">
                        <label for="titre" class="form-label">
                            Objet de votre demande *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="titre"
                            name="titre"
                            maxlength="150"
                            placeholder="Ex. : Demande de devis pour un mariage"
                            required
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="date-evenement" class="form-label">
                            Date souhaitée
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="date-evenement"
                            name="date_evenement"
                        >
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="convives" class="form-label">
                            Nombre de convives
                        </label>
                        <input
                            type="number"
                            class="form-control"
                            id="convives"
                            name="convives"
                            min="1"
                            step="1"
                            placeholder="Ex. : 20"
                        >
                    </div>

                    <div class="col-12">
                        <label for="message" class="form-label">
                            Votre message *
                        </label>
                        <textarea
                            class="form-control"
                            id="message"
                            name="message"
                            rows="6"
                            placeholder="Le lieu, vos envies de menu, vos questions… On veut tout savoir !"
                            required
                        ></textarea>
                    </div>
                    
                      <!-- Champ anti-spam -->
                    <div class="visually-hidden" aria-hidden="true">
                        <label for="site-web">Ne pas remplir ce champ</label>
                        <input
                            type="text"
                            id="site-web"
                            name="site_web"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    </div>
                    
                </div>

                <div class="col-12 mt-4">
                <button class="btn btn-primary button" type="submit">Envoyer mon message</button>
                </div>

                <!-- Message de confirmation ou d'erreur -->
                <div
                    id="contact-feedback"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>

            </form>

    </div>
        
</section>

<section class="quote-light">
    <i class="bi bi-asterisk"></i>
    <p>
        Les jolis tables commencent
        <br />
        par une <em>rencontre</em>.
    </p>
    <a href="/menus">En attendant, découvrez nos menus <i class="bi bi-arrow-up-right"></i></a>
</section>