/* GESTION DES HORAIRES */

/* Charger les horaires depuis MySQL */

async function chargerHorairesEmploye() {

    const conteneur = document.getElementById(
        'gestion-horaires-liste'
    );

    // Ne rien faire si nous ne sommes pas sur la bonne page
    if (!conteneur) {
        return;
    }

    const feedback = document.getElementById(
        'gestion-horaires-feedback'
    );

    const bouton = document.getElementById(
        'gestion-horaires-enregistrer'
    );

    // Réinitialiser l'affichage
    conteneur.innerHTML = '';
    feedback.textContent = 'Chargement des horaires...';
    feedback.className = 'mb-4 text-secondary';

    bouton.disabled = true;

    try {

        // Récupérer les horaires depuis PHP
        const reponse = await fetch(
            '/api/horaires-employe.php',
            {
                cache: 'no-store'
            }
        );

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error(
                resultat.message ||
                'Impossible de charger les horaires.'
            );
        }

        // Conserver le jeton CSRF pour l'enregistrement
        const formulaire = document.getElementById('form-gestion-horaires');

        formulaire.dataset.csrfToken = resultat.csrf_token;

        // Parcourir les sept jours
        for (const horaire of resultat.horaires) {

            // Créer une colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12';

            // Créer une carte
            const carte = document.createElement('div');
            carte.className = 'card';

            const corps = document.createElement('div');
            corps.className = 'card-body';

            // En-tête : jour + fermeture
            const entete = document.createElement('div');
            entete.className =
                'd-flex justify-content-between align-items-center mb-3';

            const titre = document.createElement('h3');
            titre.className = 'h5 mb-0';

            // Mettre une majuscule au nom du jour
            titre.textContent =
                horaire.jour.charAt(0).toUpperCase() +
                horaire.jour.slice(1);

            // Case "Fermé"
            const groupeFermeture = document.createElement('div');
            groupeFermeture.className = 'form-check';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input';
            checkbox.id = 'horaire-ferme-' + horaire.id;
            checkbox.name = 'ferme[' + horaire.id + ']';
            checkbox.value = '1';
            checkbox.checked = Number(horaire.ferme) === 1;

            const labelFermeture = document.createElement('label');
            labelFermeture.className = 'form-check-label';
            labelFermeture.htmlFor = checkbox.id;
            labelFermeture.textContent = 'Fermé';

            groupeFermeture.appendChild(checkbox);
            groupeFermeture.appendChild(labelFermeture);

            entete.appendChild(titre);
            entete.appendChild(groupeFermeture);

            // Ligne contenant les deux horaires
            const ligne = document.createElement('div');
            ligne.className = 'row g-3';

            // Champ ouverture
            const colonneOuverture = document.createElement('div');
            colonneOuverture.className = 'col-12 col-md-6';

            const labelOuverture = document.createElement('label');
            labelOuverture.className = 'form-label';
            labelOuverture.textContent = 'Ouverture';
            labelOuverture.htmlFor = 'horaire-ouverture-' + horaire.id;

            const ouverture = document.createElement('input');
            ouverture.type = 'time';
            ouverture.className = 'form-control';
            ouverture.id = labelOuverture.htmlFor;
            ouverture.name = 'ouverture[' + horaire.id + ']';
            ouverture.value = horaire.ouverture ? horaire.ouverture.substring(0, 5): '';
            ouverture.required = true;

            // Champ fermeture
            const colonneFermeture = document.createElement('div');
            colonneFermeture.className = 'col-12 col-md-6';

            const labelHeureFermeture = document.createElement('label');
            labelHeureFermeture.className = 'form-label';
            labelHeureFermeture.textContent = 'Fermeture';
            labelHeureFermeture.htmlFor =
                'horaire-fermeture-' + horaire.id;

            const fermeture = document.createElement('input');
            fermeture.type = 'time';
            fermeture.className = 'form-control';
            fermeture.id = labelHeureFermeture.htmlFor;
            fermeture.name = 'fermeture[' + horaire.id + ']';
            fermeture.value = horaire.fermeture ? horaire.fermeture.substring(0, 5): '';
            fermeture.required = true;

            // Désactiver les horaires si le jour est fermé
            ouverture.disabled = checkbox.checked;
            fermeture.disabled = checkbox.checked;

            // Réagir à la case "Fermé"
            checkbox.addEventListener('change', function() {

                ouverture.disabled = checkbox.checked;
                fermeture.disabled = checkbox.checked;

            });

            // Assembler les éléments
            colonneOuverture.appendChild(labelOuverture);
            colonneOuverture.appendChild(ouverture);

            colonneFermeture.appendChild(labelHeureFermeture);
            colonneFermeture.appendChild(fermeture);

            ligne.appendChild(colonneOuverture);
            ligne.appendChild(colonneFermeture);

            corps.appendChild(entete);
            corps.appendChild(ligne);

            carte.appendChild(corps);
            colonne.appendChild(carte);

            conteneur.appendChild(colonne);
        }

        // Les horaires sont chargés
        feedback.textContent = '';

        // Activer le bouton d'enregistrement
        bouton.disabled = false;

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = erreur.message;
        feedback.className = 'mb-4 text-danger';
    }
}


/* ENREGISTRER LES HORAIRES */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-gestion-horaires') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    const formulaire = event.target;

    const bouton = document.getElementById(
        'gestion-horaires-enregistrer'
    );

    const feedback = document.getElementById(
        'gestion-horaires-feedback'
    );

    // Empêcher les doubles envois
    if (bouton.disabled) {
        return;
    }

    // Vérifier les champs du formulaire
    if (!formulaire.reportValidity()) {
        return;
    }

    // Récupérer les données du formulaire
    const donnees = new FormData(formulaire);

    // Ajouter le jeton CSRF
    donnees.append(
        'csrf_token',
        formulaire.dataset.csrfToken
    );

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement des horaires...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Envoyer les horaires à PHP
        const reponse = await fetch(
            '/api/modifier-horaires.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        // Lire la réponse JSON
        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message ||
                'Impossible d’enregistrer les horaires.';

            feedback.className = 'mb-4 text-danger';

            bouton.disabled = false;

            return;
        }

        // Afficher la confirmation
        feedback.textContent =
            'Les horaires ont été enregistrés avec succès.';

        feedback.className = 'mb-4 text-success';

        // Réactiver le bouton
        bouton.disabled = false;

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer l’enregistrement. ' +
            'Actualisez la page pour vérifier les horaires.';

        feedback.className = 'mb-4 text-danger';

        // Ne pas réactiver automatiquement le bouton :
        // le serveur a peut-être déjà enregistré les données.
    }

});

/* Charger les horaires à l'ouverture de la page */

document.addEventListener('DOMContentLoaded', function() {chargerHorairesEmploye();});

document.addEventListener('pageChargee', function() {chargerHorairesEmploye();});
