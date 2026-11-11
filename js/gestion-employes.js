/* CHARGER LES COMPTES EMPLOYÉS */

async function chargerEmployesAdmin() {

    // Récupérer le conteneur des employés
    const conteneur = document.getElementById(
        'gestion-employes-liste'
    );

    // Vérifier que nous sommes sur la bonne page
    if (!conteneur) {
        return;
    }

    const feedback = document.getElementById(
        'gestion-employes-feedback'
    );

    const champCsrf = document.getElementById(
        'employe-csrf'
    );

    // Réinitialiser l'affichage
    conteneur.innerHTML = '';

    feedback.textContent = 'Chargement des employés...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les employés depuis PHP
        const reponse = await fetch(
            '/api/employes-admin.php',
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

            if (actif) {

                bouton.className =
                    'btn btn-outline-danger btn-statut-employe mt-auto';

                bouton.textContent = 'Désactiver le compte';

            } else {

                bouton.className =
                    'btn btn-outline-success btn-statut-employe mt-auto';

                bouton.textContent = 'Réactiver le compte';
            }

            // Action disponible à une prochaine étape
            bouton.disabled = true;

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


/* CHARGER LA PAGE */

/* Premier chargement */

document.addEventListener('DOMContentLoaded', function() {

    chargerEmployesAdmin();

});


/* Navigation SPA */

document.addEventListener('pageChargee', function() {

    chargerEmployesAdmin();

});
