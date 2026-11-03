
/* DEMANDE DE RÉINITIALISATION DU MOT DE PASSE */

document.addEventListener('submit', async function(event) {

    // Vérifier qu'il s'agit du bon formulaire
    if (event.target.id !== 'form-mot-de-passe-oublie') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    // Récupérer les éléments du formulaire
    const formulaire = event.target;
    const feedback = document.getElementById('reset-feedback');
    const bouton = formulaire.querySelector('button[type="submit"]');

    // Récupérer l'adresse e-mail
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'envoi
    bouton.disabled = true;
    feedback.textContent = 'Envoi de votre demande...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer la demande au serveur PHP
        const reponse = await fetch('/api/demande-reinitialisation.php', {
            method: 'POST',
            body: donnees
        });

        // Récupérer la réponse JSON
        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            // Afficher le message de confirmation
            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-success';

            formulaire.reset();

        } else {

            // Afficher une erreur
            feedback.textContent = resultat.message || 'Impossible de traiter votre demande.';
            feedback.className = 'mt-3 text-danger';

        }

    } catch (erreur) {

        feedback.textContent = 'Une erreur est survenue. Veuillez réessayer.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        // Réactiver le bouton
        bouton.disabled = false;

    }

});
