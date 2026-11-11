
/* FORMULAIRE DE CONNEXION */

document.addEventListener('submit', async function(event) {

    // Vérifier qu'il s'agit du formulaire de connexion
    if (event.target.id !== 'form-connexion') {
        return;
    }

    // Empêcher l'envoi classique du formulaire
    event.preventDefault();

    // Récupérer les éléments du formulaire
    const formulaire = event.target;
    const feedback = document.getElementById('connexion-feedback');
    const bouton = formulaire.querySelector('button[type="submit"]');

    // Récupérer les données
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'envoi
    bouton.disabled = true;
    feedback.textContent = 'Connexion en cours...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les informations à PHP
        const reponse = await fetch('/api/signin.php', {
            method: 'POST',
            body: donnees
        });

        // Récupérer la réponse JSON
        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            feedback.textContent = 'Connexion réussie.';
            feedback.className = 'mt-3 text-success';

            formulaire.reset();

            const modale = document.getElementById('modal-connexion');

            // Vérifier si la connexion a été demandée pour commander
            const commandeEnAttente =
                sessionStorage.getItem('commandeApresConnexion') === 'true';

            // CAS 1 : Un client se connecte pour passer commande
            if (commandeEnAttente && resultat.role === 'utilisateur') {

                sessionStorage.removeItem('commandeApresConnexion');

                modale.addEventListener('hidden.bs.modal', function() {

                    window.history.pushState({}, '', '/commande');
                    window.dispatchEvent(new PopStateEvent('popstate'));

                }, { once: true });

            }

            // CAS 2 : Un administrateur se connecte
            else if (resultat.role === 'administrateur') {

                modale.addEventListener('hidden.bs.modal', function() {

                    window.history.pushState({}, '', '/espace-admin');
                    window.dispatchEvent(new PopStateEvent('popstate'));

                }, { once: true });

            }

            // CAS 3 : Un employé se connecte
            else if (resultat.role === 'employe') {

                modale.addEventListener('hidden.bs.modal', function() {

                    window.history.pushState({}, '', '/espace-employe');
                    window.dispatchEvent(new PopStateEvent('popstate'));

                }, { once: true });

            }

            // Fermer la modale
            bootstrap.Modal.getOrCreateInstance(modale).hide();

            // Actualiser le header
            await actualiserNavigation();

            // Signaler que l'utilisateur vient de se connecter
            document.dispatchEvent(new Event('connexionReussie'));

        } else {

            // Connexion refusée
            feedback.textContent = resultat.message || 'Identifiants incorrects.';
            feedback.className = 'mt-3 text-danger';

        }

    } catch (erreur) {

        // Erreur de communication avec le serveur
        feedback.textContent = 'Impossible de se connecter.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        // Réactiver le bouton
        bouton.disabled = false;

    }

});

// Actualiser la navigation selon la session PHP
async function actualiserNavigation() {

    const visiteur = document.getElementById('nav-visiteur');
    const connecte = document.getElementById('nav-connecte');
    const prenom = document.getElementById('nav-prenom');
    const connecteMobile = document.getElementById('nav-connecte-mobile');
    const prenomMobile = document.getElementById('nav-prenom-mobile');

    try {

        const reponse = await fetch('/api/session.php');

        if (!reponse.ok) {
            throw new Error('Erreur de vérification de session');
        }

        const session = await reponse.json();

        if (session.connecte) {

            visiteur.classList.add('d-none');
            connecte.classList.remove('d-none');
            prenomMobile.textContent = session.prenom;
            connecteMobile.classList.remove('d-none');

            prenom.textContent = session.prenom;

            // Récupérer le lien qui contient le prénom
            const lienEspace = prenom.closest('a');

            if (lienEspace) {

                // Administrateur
                if (session.role === 'administrateur') {
                    lienEspace.href = '/espace-admin';

                // Employé
                } else if (session.role === 'employe') {
                    lienEspace.href = '/espace-employe';

                // Client
                } else {
                    lienEspace.href = '/mon-compte';
                }
            }

            // Adapter également le lien mobile
            const lienMobile = prenomMobile.closest('a');

            if (lienMobile) {

                if (session.role === 'administrateur') {

                    lienMobile.href = '/espace-admin';

                } else if (session.role === 'employe') {

                    lienMobile.href = '/espace-employe';

                } else {

                    lienMobile.href = '/mon-compte';

                }
            }

        } else {

            visiteur.classList.remove('d-none');
            connecte.classList.add('d-none');
            connecteMobile.classList.add('d-none');

            prenom.textContent = '';
            prenomMobile.textContent = '';
        }

    } catch (erreur) {

        console.error(erreur);

        visiteur.classList.remove('d-none');
        connecte.classList.add('d-none');
        connecteMobile.classList.add('d-none');

        prenom.textContent = '';
        prenomMobile.textContent = '';
    }
}

// Vérifier la session au chargement du site
document.addEventListener('DOMContentLoaded', actualiserNavigation);

/* DÉCONNEXION */

document.addEventListener('click', async function(event) {

    // Vérifier si le bouton de déconnexion a été cliqué
    if (event.target.id !== 'nav-deconnexion') {
        return;
    }

    const bouton = event.target;

    // Désactiver le bouton pendant la déconnexion
    bouton.disabled = true;

    try {

        // Demander la déconnexion à PHP
        const reponse = await fetch('/api/signout.php', {
            method: 'POST'
        });

        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            // Mettre à jour la navigation
            await actualiserNavigation();

            // Rediriger vers la page d'accueil
            window.history.pushState({}, '', '/');
            window.dispatchEvent(new PopStateEvent('popstate'));

        } else {

            alert('La déconnexion a échoué.');
        }

    } catch (erreur) {

        console.error(erreur);
        alert('Impossible de se déconnecter.');

    } finally {

        bouton.disabled = false;

    }

});

document.getElementById('modal-connexion').addEventListener('hidden.bs.modal', function() {

    if (sessionStorage.getItem('commandeApresConnexion') === 'true') {
        sessionStorage.removeItem('commandeApresConnexion');
        sessionStorage.removeItem('menuIdEnAttente');
    }

});