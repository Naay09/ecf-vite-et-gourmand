
document.addEventListener('show.bs.modal', async function (event) {

    // Vérifier qu'il s'agit de notre modale
    if (event.target.id !== 'modal-menu') {
        return;
    }

    // Récupérer le contenu et le bouton cliqué
    const contenu = event.target.querySelector('#menu-modal-content');
    const bouton = event.relatedTarget;

    if (!bouton || !contenu) {
        return;
    }

    // Récupérer l'identifiant du menu
    const id = bouton.getAttribute('data-menu-id');

    // Afficher le message de chargement
    contenu.textContent = 'Chargement du menu...';

    try {
        // Récupérer les informations du menu depuis PHP
        const reponse = await fetch('/api/detail-menu.php?id=' + id);

        if (!reponse.ok) {
            throw new Error('Impossible de charger le menu.');
        }

        // Récupérer le HTML retourné par PHP
        const html = await reponse.text();

        // Afficher les informations dans la modale
        contenu.innerHTML = html;

    } catch (erreur) {
        contenu.textContent = 'Une erreur est survenue.';
        console.error(erreur);
    }

});


/* BOUTON COMMANDER CE MENU */

document.addEventListener('click', async function(event) {

    // Récupérer le bouton Commander
    const bouton = event.target.closest('#menu-modal-order');

    if (!bouton) {
        return;
    }

    // Récupérer l'identifiant du menu
    const menuId = bouton.getAttribute('data-menu-id');

    if (!menuId) {
        return;
    }

    try {

        // Vérifier si le client est connecté
        const reponse = await fetch('/api/session.php');

        if (!reponse.ok) {
            throw new Error('Erreur lors de la vérification de la session.');
        }

        const session = await reponse.json();

        // Mémoriser le menu choisi
        sessionStorage.setItem('menuIdEnAttente', menuId);

        // Récupérer les deux modales
        const modalMenu = document.getElementById('modal-menu');
        const modalConnexion = document.getElementById('modal-connexion');

        if (!modalMenu || !modalConnexion) {
            return;
        }

        // Attendre la fermeture de la modale du menu
        modalMenu.addEventListener('hidden.bs.modal', function() {

            if (!session.connecte) {

                // Mémoriser que le client souhaite commander
                sessionStorage.setItem('commandeApresConnexion', 'true');

                // Visiteur non connecté : afficher la connexion
                const connexion = bootstrap.Modal.getOrCreateInstance(modalConnexion);
                connexion.show();

            } else if (session.role === 'utilisateur') {

                // Client connecté : accéder à la commande
                window.history.pushState({}, '', '/commande');
                window.dispatchEvent(new PopStateEvent('popstate'));

            } else {

                // Employé ou administrateur
                alert('Cette fonctionnalité est réservée aux clients.');

            }

        }, { once: true });

        // Fermer la modale du menu
        const menu = bootstrap.Modal.getInstance(modalMenu);

        if (menu) {
            menu.hide();
        }

    } catch (erreur) {

        console.error(erreur);
        alert('Impossible de vérifier votre connexion.');

    }

});