let csrfGestionPlats = '';

/* CHARGER LES PLATS */

async function chargerPlats() {

    // Récupérer les éléments HTML
    const liste = document.getElementById('gestion-plats-liste');
    const feedback = document.getElementById('gestion-plats-feedback');

    // Vérifier que la page est affichée
    if (!liste || !feedback) {
        return;
    }

    // Réinitialiser l'affichage
    liste.innerHTML = '';

    feedback.textContent = 'Chargement des plats...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les plats depuis PHP
        const reponse = await fetch('/api/plats-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger les plats.';

            feedback.className = 'mb-4 text-danger';

            return;
        }

        // Récupérer les plats
        const plats = resultat.plats;

        // Vérifier si aucun plat n'existe
        if (plats.length === 0) {

            feedback.textContent = 'Aucun plat enregistré.';
            feedback.className = 'mb-4 text-secondary';

            return;
        }

        // Effacer le message de chargement
        feedback.textContent = '';

        // Créer une carte pour chaque plat
        for (const plat of plats) {

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-md-6 col-lg-4';

            // Carte Bootstrap
            const carte = document.createElement('article');
            carte.className = 'card h-100';

            // Contenu de la carte
            const contenu = document.createElement('div');
            contenu.className = 'card-body d-flex flex-column';

            // Catégorie du plat
            const categorie = document.createElement('span');
            categorie.className = 'badge text-bg-primary align-self-start mb-3';

            if (plat.categorie === 'entree') {
                categorie.textContent = 'Entrée';
            } else if (plat.categorie === 'plat') {
                categorie.textContent = 'Plat';
            } else if (plat.categorie === 'dessert') {
                categorie.textContent = 'Dessert';
            }

            // Nom du plat
            const nom = document.createElement('h3');
            nom.className = 'h5 mb-3';
            nom.textContent = plat.nom;

            // Description
            const description = document.createElement('p');
            description.className = 'mb-3';
            description.textContent = plat.description || 'Aucune description.';

            // Allergènes
            const allergenes = document.createElement('p');
            allergenes.className = 'small text-muted mb-4';

            if (plat.allergenes.length > 0) {

                const nomsAllergenes = plat.allergenes.map(function(allergene) {
                    return allergene.nom;
                });

                allergenes.textContent =
                    'Allergènes : ' + nomsAllergenes.join(', ');

            } else {

                allergenes.textContent = 'Aucun allergène renseigné.';

            }

            // Boutons de gestion
            const actions = document.createElement('div');
            actions.className = 'd-flex flex-wrap gap-2 mt-auto';

            // Bouton Modifier
            const boutonModifier = document.createElement('button');
            boutonModifier.type = 'button';
            boutonModifier.className = 'btn btn-outline-primary';
            boutonModifier.textContent = 'Modifier';
            boutonModifier.dataset.platId = plat.id;
            boutonModifier.classList.add('btn-modifier-plat');

            // Ouvrir la modale Bootstrap
            boutonModifier.dataset.bsToggle = 'modal';
            boutonModifier.dataset.bsTarget = '#modal-gestion-plat';

            // Bouton Supprimer
            const boutonSupprimer = document.createElement('button');
            boutonSupprimer.type = 'button';
            boutonSupprimer.className = 'btn btn-outline-danger';
            boutonSupprimer.textContent = 'Supprimer';
            boutonSupprimer.dataset.platId = plat.id;
            boutonSupprimer.classList.add('btn-supprimer-plat');

            // Ajouter les boutons
            actions.appendChild(boutonModifier);
            actions.appendChild(boutonSupprimer);

            // Construire la carte
            contenu.appendChild(categorie);
            contenu.appendChild(nom);
            contenu.appendChild(description);
            contenu.appendChild(allergenes);
            contenu.appendChild(actions);

            carte.appendChild(contenu);
            colonne.appendChild(carte);

            // Ajouter la carte à la liste
            liste.appendChild(colonne);
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de charger les plats.';
        feedback.className = 'mb-4 text-danger';
    }
}

/* CHARGER LES ALLERGÈNES DU FORMULAIRE */

async function chargerAllergenesPlat() {

    const conteneur = document.getElementById(
        'gestion-plat-allergenes'
    );

    if (!conteneur) {
        return false;
    }

    // Vider les anciennes cases à cocher
    conteneur.innerHTML = '';

    try {

        // Récupérer les allergènes
        const reponse = await fetch('/api/allergenes-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error(
                resultat.message || 'Impossible de charger les allergènes.'
            );
        }

        document.getElementById('gestion-plat-csrf').value =
        resultat.csrf_token;

        // Conserver le jeton pour la suppression
        csrfGestionPlats = resultat.csrf_token;

        // Créer une case à cocher pour chaque allergène
        for (const allergene of resultat.allergenes) {

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-sm-6';

            // Conteneur de la case à cocher
            const groupe = document.createElement('div');
            groupe.className = 'form-check';

            // Case à cocher
            const caseACocher = document.createElement('input');

            caseACocher.type = 'checkbox';
            caseACocher.className = 'form-check-input';

            caseACocher.id = 'gestion-allergene-' + allergene.id;
            caseACocher.name = 'allergenes[]';
            caseACocher.value = allergene.id;

            // Libellé
            const label = document.createElement('label');

            label.className = 'form-check-label';
            label.htmlFor = caseACocher.id;
            label.textContent = allergene.nom;

            // Construire l'élément
            groupe.appendChild(caseACocher);
            groupe.appendChild(label);

            colonne.appendChild(groupe);
            conteneur.appendChild(colonne);
        }

        return true;

    } catch (erreur) {

        console.error(erreur);

        conteneur.textContent =
            'Impossible de charger les allergènes.';

        return false;
    }
}


/* OUVERTURE DE LA MODALE D'UN PLAT */

document.addEventListener('show.bs.modal', async function(event) {

    // Vérifier la modale concernée
    if (event.target.id !== 'modal-gestion-plat') {
        return;
    }

    const formulaire = document.getElementById('form-gestion-plat');
    const titre = document.getElementById('modal-gestion-plat-titre');
    const feedback = document.getElementById('gestion-plat-feedback');
    const bouton = document.getElementById('gestion-plat-enregistrer');

    // Réinitialiser le formulaire
    formulaire.reset();

    document.getElementById('gestion-plat-id').value = '';
    document.getElementById('gestion-plat-csrf').value = '';

    titre.textContent = 'Ajouter un plat';

    feedback.textContent = '';
    feedback.className = '';

    bouton.textContent = 'Enregistrer';
    bouton.disabled = true;

    // Récupérer le bouton qui a ouvert la modale
    const boutonOuverture = event.relatedTarget;

    // Vérifier si nous sommes en mode modification
    const modification = boutonOuverture &&
        boutonOuverture.classList.contains('btn-modifier-plat');

    // Charger les allergènes
    const chargementReussi = await chargerAllergenesPlat();

    if (!chargementReussi) {

        feedback.textContent = 'Impossible de charger les allergènes.';
        feedback.className = 'text-danger';

        return;
    }

    // MODE AJOUT
    if (!modification) {

        bouton.disabled = false;

        return;
    }

    // MODE MODIFICATION
    titre.textContent = 'Modifier un plat';
    bouton.textContent = 'Enregistrer les modifications';

    const platId = boutonOuverture.dataset.platId;

    try {

        // Récupérer les plats depuis PHP
        const reponse = await fetch('/api/plats-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error('Impossible de récupérer le plat.');
        }

        // Rechercher le plat sélectionné
        const plat = resultat.plats.find(function(element) {

            return Number(element.id) === Number(platId);

        });

        if (!plat) {
            throw new Error('Plat introuvable.');
        }

        // Préremplir les champs
        document.getElementById('gestion-plat-id').value = plat.id;

        document.getElementById('gestion-plat-nom').value = plat.nom;

        document.getElementById('gestion-plat-description').value =
            plat.description || '';

        document.getElementById('gestion-plat-categorie').value =
            plat.categorie;

        // Précocher les allergènes associés
        const cases = document.querySelectorAll(
            '#gestion-plat-allergenes input[type="checkbox"]'
        );

        cases.forEach(function(caseACocher) {

            caseACocher.checked = plat.allergenes.some(function(allergene) {

                return Number(allergene.id) === Number(caseACocher.value);

            });

        });

        // Activer l'enregistrement des modifications
        bouton.disabled = false;

        feedback.textContent = '';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = erreur.message;
        feedback.className = 'text-danger';

    }

});


/* AJOUTER OU MODIFIER UN PLAT */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-gestion-plat') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;

    const bouton = document.getElementById(
        'gestion-plat-enregistrer'
    );

    const feedback = document.getElementById(
        'gestion-plat-feedback'
    );

    // Éviter les doubles envois
    if (bouton.disabled) {
        return;
    }

    // Vérifier les champs
    if (!formulaire.reportValidity()) {
        return;
    }

    // Récupérer l'identifiant du plat
    const platId = document.getElementById('gestion-plat-id').value;

    // Déterminer l'opération
    let url = '/api/enregistrer-plat.php';

    if (platId !== '') {
        url = '/api/modifier-plat.php';
    }

    // Préparer les données
    const donnees = new FormData(formulaire);

    // Désactiver le bouton
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement en cours...';
    feedback.className = 'text-secondary';

    try {

        // Envoyer les informations à PHP
        const reponse = await fetch(url, {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible d’enregistrer le plat.';

            feedback.className = 'text-danger';

            bouton.disabled = false;

            return;
        }

        // Fermer la modale
        const elementModal = document.getElementById(
            'modal-gestion-plat'
        );

        const modal = bootstrap.Modal.getInstance(elementModal);

        if (modal) {
            modal.hide();
        }

        // Actualiser la liste des plats
        await chargerPlats();

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer l’enregistrement. Vérifiez la liste avant de réessayer.';

        feedback.className = 'text-danger';

    }

});


/* SUPPRIMER UN PLAT */

document.addEventListener('click', async function(event) {

    // Vérifier le bouton cliqué
    const bouton = event.target.closest('.btn-supprimer-plat');

    if (!bouton || bouton.disabled) {
        return;
    }

    // Récupérer l'identifiant du plat
    const platId = bouton.dataset.platId;

    // Demander une confirmation
    const confirmation = window.confirm(
        'Voulez-vous vraiment supprimer ce plat ?'
    );

    if (!confirmation) {
        return;
    }

    const feedback = document.getElementById(
        'gestion-plats-feedback'
    );

    if (!feedback) {
        return;
    }

    // Désactiver le bouton pendant la suppression
    bouton.disabled = true;

    feedback.textContent = 'Suppression du plat...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer le jeton CSRF si nécessaire
        if (!csrfGestionPlats) {

            const reponseJeton = await fetch(
                '/api/allergenes-employe.php',
                { cache: 'no-store' }
            );

            const resultatJeton = await reponseJeton.json();

            if (!reponseJeton.ok || !resultatJeton.success) {
                throw new Error('Impossible de vérifier la sécurité de la demande.');
            }

            csrfGestionPlats = resultatJeton.csrf_token;
        }

        // Préparer les données
        const donnees = new FormData();

        donnees.append('plat_id', platId);
        donnees.append('csrf_token', csrfGestionPlats);

        // Envoyer la demande à PHP
        const reponse = await fetch('/api/supprimer-plat.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de supprimer le plat.';

            feedback.className = 'mb-4 text-danger';

            bouton.disabled = false;

            return;
        }

        // Actualiser la liste
        await chargerPlats();

        // Afficher la confirmation
        feedback.textContent = resultat.message;
        feedback.className = 'mb-4 text-success';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer la suppression. Actualisez la liste avant de réessayer.';

        feedback.className = 'mb-4 text-danger';

    }

});


// Chargement initial
document.addEventListener('DOMContentLoaded', chargerPlats);

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', chargerPlats);
