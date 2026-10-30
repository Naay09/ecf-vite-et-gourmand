
/* FORMULAIRE DE CONTACT */

document.addEventListener('submit', async function(event) {

    // Vérifier qu'il s'agit du formulaire de contact
    if (event.target.id !== 'contact-form') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    // Récupérer le formulaire et les éléments utiles
    const formulaire = event.target;
    const message = document.getElementById('contact-feedback');
    const bouton = formulaire.querySelector('button[type="submit"]');

    // Récupérer les données du formulaire
    const donnees = new FormData(formulaire);

    // Afficher le chargement
    bouton.disabled = true;
    message.textContent = 'Envoi en cours...';
    message.className = 'mt-3 text-secondary';

    try {

        // Envoyer les données au fichier PHP
        const reponse = await fetch('/api/contact.php', {
            method: 'POST',
            body: donnees
        });

        // Récupérer la réponse du serveur
        const resultat = await reponse.json();

        // Vérifier si l'envoi a réussi
        if (reponse.ok && resultat.success) {

            message.textContent = 'Votre message a bien été envoyé.';
            message.className = 'mt-3 text-success';

            // Vider les champs du formulaire
            formulaire.reset();

        } else {

            message.textContent = resultat.message || 'Une erreur est survenue.';
            message.className = 'mt-3 text-danger';

        }

    } catch (erreur) {

        message.textContent = 'Impossible d’envoyer votre message.';
        message.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        // Réactiver le bouton
        bouton.disabled = false;

    }

});
