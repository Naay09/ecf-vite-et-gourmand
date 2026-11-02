/* CHARGEMENT DES INFORMATIONS DU CLIENT */

async function chargerCompte() {

    // Récupérer le formulaire
    const formulaire = document.getElementById('form-compte');

    // Ne rien faire si nous ne sommes pas sur la page Mon compte
    if (!formulaire) {
        return;
    }

    const feedback = document.getElementById('compte-feedback');
    const bouton = document.getElementById('compte-enregistrer');

    // Désactiver le bouton pendant le chargement
    bouton.disabled = true;
    feedback.textContent = 'Chargement de vos informations...';

    try {

        // Récupérer les données du client depuis PHP
        const reponse = await fetch('/api/user-account.php');

        const resultat = await reponse.json();

        // Vérifier que la récupération a réussi
        if (!reponse.ok || !resultat.success) {
            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-danger';
            return;
        }

        // Remplir les champs du formulaire
        formulaire.elements['prenom'].value = resultat.compte.prenom;
        formulaire.elements['nom'].value = resultat.compte.nom;
        formulaire.elements['email'].value = resultat.compte.email;
        formulaire.elements['telephone'].value = resultat.compte.telephone || '';
        formulaire.elements['adresse'].value = resultat.compte.adresse || '';
        formulaire.elements['csrf_token'].value = resultat.csrf_token;

        // Activer le bouton
        bouton.disabled = false;

        // Effacer le message de chargement
        feedback.textContent = '';

    } catch (erreur) {

        feedback.textContent = 'Impossible de charger vos informations.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);
    }
}

// Chargement initial du site
document.addEventListener('DOMContentLoaded', chargerCompte);

// Chargement d'une page par notre routeur
document.addEventListener('pageChargee', chargerCompte);

/* MODIFICATION DES INFORMATIONS PERSONNELLES */

document.addEventListener('submit', async function(event) {

    // Vérifier qu'il s'agit du formulaire Mon compte
    if (event.target.id !== 'form-compte') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    // Récupérer les éléments du formulaire
    const formulaire = event.target;
    const feedback = document.getElementById('compte-feedback');
    const bouton = document.getElementById('compte-enregistrer');

    // Récupérer les données du formulaire
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;
    feedback.textContent = 'Enregistrement en cours...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les modifications à PHP
        const reponse = await fetch('/api/update-account.php', {
            method: 'POST',
            body: donnees
        });

        // Récupérer la réponse du serveur
        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            // Afficher la confirmation
            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-success';

            // Actualiser le prénom dans le header
            actualiserNavigation();

        } else {

            // Afficher le message d'erreur
            feedback.textContent = resultat.message || 'Modification impossible.';
            feedback.className = 'mt-3 text-danger';

        }

    } catch (erreur) {

        feedback.textContent = 'Impossible d’enregistrer vos informations.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        // Réactiver le bouton
        bouton.disabled = false;

    }

});