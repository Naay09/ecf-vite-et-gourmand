/* CHARGER LES COMPTES EMPLOYÉS */

async function chargerEmployesAdmin() {

    // Récupérer le conteneur des employés
    const conteneur = document.getElementById('gestion-employes-liste');

    // Vérifier que nous sommes sur la bonne page
    if (!conteneur) {
        return;
    }

    const feedback = document.getElementById('gestion-employes-feedback');

    const champCsrf = document.getElementById('employe-csrf');

    // Réinitialiser l'affichage
    conteneur.innerHTML = '';

    feedback.textContent = 'Chargement des employés...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les employés depuis PHP
        const reponse = await fetch('/api/employes-admin.php',
            {
                cache: 'no-store'
            }
        );

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            throw new Error(
                resultat.message ||
                'Impossible de charger les employés.'
            );
        }

        // Conserver le jeton CSRF
        champCsrf.value = resultat.csrf_token;

        // Activer la création des comptes employés
        const boutonAjouter = document.getElementById('enregistrer-employe');

        boutonAjouter.disabled = false;

        // Vérifier si des employés existent
        if (resultat.employes.length === 0) {

            feedback.textContent =
                'Aucun compte employé pour le moment.';

            feedback.className = 'mb-4 text-secondary';

            return;
        }

        // Parcourir les employés
        for (const employe of resultat.employes) {

            const actif = Number(employe.actif) === 1;

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-md-6';

            // Carte
            const carte = document.createElement('div');
            carte.className = 'card h-100';

            const corps = document.createElement('div');
            corps.className = 'card-body d-flex flex-column';

            // Nom et prénom
            const titre = document.createElement('h3');
            titre.className = 'h5 card-title';

            titre.textContent =
                employe.prenom + ' ' + employe.nom;

            // Adresse e-mail
            const email = document.createElement('p');
            email.className = 'text-secondary mb-3';
            email.textContent = employe.email;

            // Statut du compte
            const statut = document.createElement('span');

            if (actif) {

                statut.className =
                    'badge text-bg-success align-self-start mb-3';

                statut.textContent = 'Actif';

            } else {

                statut.className =
                    'badge text-bg-secondary align-self-start mb-3';

                statut.textContent = 'Désactivé';
            }

            // Bouton de gestion du compte
            const bouton = document.createElement('button');

            bouton.type = 'button';
            bouton.dataset.employeId = employe.id;
            bouton.dataset.nouveauStatut = actif ? '0' : '1';

            if (actif) {

                bouton.className =
                    'btn btn-outline-danger btn-statut-employe mt-auto';

                bouton.textContent = 'Désactiver le compte';

            } else {

                bouton.className =
                    'btn btn-outline-success btn-statut-employe mt-auto';

                bouton.textContent = 'Réactiver le compte';
            }

            // Assembler la carte
            corps.appendChild(titre);
            corps.appendChild(email);
            corps.appendChild(statut);
            corps.appendChild(bouton);

            carte.appendChild(corps);
            colonne.appendChild(carte);

            conteneur.appendChild(colonne);
        }

        // Effacer le message de chargement
        feedback.textContent = '';
        feedback.className = 'mb-4';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = erreur.message;
        feedback.className = 'mb-4 text-danger';
    }
}

/* OUVRIR LA MODALE D'AJOUT D'UN EMPLOYÉ */

document.addEventListener('show.bs.modal', function(event) {

    // Vérifier la modale concernée
    if (event.target.id !== 'modal-ajouter-employe') {
        return;
    }

    const formulaire = document.getElementById(
        'form-ajouter-employe'
    );

    const feedback = document.getElementById(
        'ajouter-employe-feedback'
    );

    // Conserver le jeton CSRF
    const jeton = document.getElementById(
        'employe-csrf'
    ).value;

    // Réinitialiser les champs
    formulaire.reset();

    // Restaurer le jeton de sécurité
    document.getElementById('employe-csrf').value = jeton;

    // Effacer les anciens messages
    feedback.textContent = '';
    feedback.className = '';

});


/* CRÉER UN COMPTE EMPLOYÉ */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-ajouter-employe') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    const formulaire = event.target;

    const bouton = document.getElementById('enregistrer-employe');

    const feedback = document.getElementById('ajouter-employe-feedback');

    // Empêcher les doubles envois
    if (bouton.disabled) {
        return;
    }

    // Vérifier les champs obligatoires
    if (!formulaire.reportValidity()) {
        return;
    }

    // Récupérer les données du formulaire
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;

    feedback.textContent = 'Création du compte en cours...';
    feedback.className = 'text-secondary';

    try {

        // Envoyer les données à PHP
        const reponse = await fetch('/api/creer-employe.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        // Récupérer la réponse JSON
        const resultat = await reponse.json();

        // Vérifier si la création a réussi
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message ||
                'Impossible de créer le compte employé.';

            feedback.className = 'text-danger';

            bouton.disabled = false;

            return;
        }

        // Fermer la modale
        const elementModal = document.getElementById('modal-ajouter-employe');

        const modal = bootstrap.Modal.getInstance(elementModal);

        if (modal) {
            modal.hide();
        }

        // Actualiser la liste des employés
        await chargerEmployesAdmin();

        // Afficher le résultat sur la page
        const feedbackPage = document.getElementById(
            'gestion-employes-feedback'
        );

        feedbackPage.textContent = resultat.message;

        if (resultat.email_envoye) {

            feedbackPage.className = 'mb-4 text-success';

        } else {

            feedbackPage.className = 'mb-4 text-warning';
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer la création du compte. ' +
            'Vérifiez la liste des employés avant de réessayer.';

        feedback.className = 'text-danger';

    }
});

/* DÉSACTIVER OU RÉACTIVER UN COMPTE EMPLOYÉ */

document.addEventListener('click', async function(event) {

    // Vérifier le bouton concerné
    const bouton = event.target.closest('.btn-statut-employe');

    if (!bouton) {
        return;
    }

    // Empêcher les doubles clics
    if (bouton.disabled) {
        return;
    }

    // Récupérer les informations du bouton
    const employeId = bouton.dataset.employeId;
    const nouveauStatut = bouton.dataset.nouveauStatut;

    // Déterminer l'action demandée
    let action = 'désactiver';

    if (nouveauStatut === '1') {
        action = 'réactiver';
    }

    // Récupérer le nom de l'employé
    const carte = bouton.closest('.card');
    const titre = carte.querySelector('.card-title');
    const nomEmploye = titre.textContent;

    // Demander confirmation
    const confirmation = confirm('Voulez-vous vraiment ' + action + ' le compte de ' + nomEmploye + ' ?');

    if (!confirmation) {
        return;
    }

    const feedback = document.getElementById('gestion-employes-feedback');

    const champCsrf = document.getElementById('employe-csrf');

    // Préparer les données
    const donnees = new FormData();

    donnees.append('employe_id', employeId);
    donnees.append('actif', nouveauStatut);
    donnees.append('csrf_token', champCsrf.value);

    // Désactiver le bouton pendant la requête
    bouton.disabled = true;

    feedback.textContent = 'Modification du compte en cours...';

    feedback.className = 'mb-4 text-secondary';

    try {

        // Envoyer la modification à PHP
        const reponse = await fetch('/api/modifier-statut-employe.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        // Lire la réponse JSON
        const resultat = await reponse.json();

        // Vérifier le résultat
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message ||
                'Impossible de modifier le compte.';

            feedback.className = 'mb-4 text-danger';

            bouton.disabled = false;

            return;
        }

        // Actualiser la liste des employés
        await chargerEmployesAdmin();

        // Afficher la confirmation
        const feedbackPage = document.getElementById('gestion-employes-feedback');

        feedbackPage.textContent = resultat.message;
        feedbackPage.className = 'mb-4 text-success';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de confirmer la modification. ' + 'Actualisez la liste avant de réessayer.';
        feedback.className = 'mb-4 text-danger';
    }

});

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', function() {chargerEmployesAdmin();});
