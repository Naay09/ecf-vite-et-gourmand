
/* AFFICHER LES AVIS VALIDÉS SUR L'ACCUEIL */

async function chargerAvisPublics() {

    // Récupérer les éléments HTML
    const liste = document.getElementById('avis-publics-liste');
    const feedback = document.getElementById('avis-publics-feedback');

    // Vérifier que nous sommes sur la page d'accueil
    if (!liste || !feedback) {
        return;
    }

    // Réinitialiser l'affichage
    liste.innerHTML = '';

    feedback.textContent = 'Chargement des avis...';

    try {

        // Récupérer les avis validés
        const reponse = await fetch('/api/avis-public.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger les avis.';

            return;
        }

        // Récupérer les avis
        const avis = resultat.avis;

        // Si aucun avis n'a encore été validé
        if (avis.length === 0) {

            feedback.textContent =
                'Les premiers avis clients seront bientôt disponibles.';

            return;
        }

        // Effacer le message de chargement
        feedback.textContent = '';

        // Créer une carte pour chaque avis
        for (const avisClient of avis) {

            // Carte
            const article = document.createElement('article');

            // Note
            const etoiles = document.createElement('div');
            etoiles.className = 'stars';

            etoiles.textContent =
                '★'.repeat(Number(avisClient.note)) +
                '☆'.repeat(5 - Number(avisClient.note));

            // Commentaire
            const commentaire = document.createElement('p');
            commentaire.textContent = avisClient.commentaire;

            // Prénom du client
            const client = document.createElement('b');
            client.textContent = avisClient.prenom_client;

            // Menu concerné
            const menu = document.createElement('small');
            menu.textContent = avisClient.menu_titre;

            // Construire la carte
            article.appendChild(etoiles);
            article.appendChild(commentaire);
            article.appendChild(client);
            article.appendChild(menu);

            // Ajouter la carte à la liste
            liste.appendChild(article);
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de charger les avis clients.';
    }
}

// Chargement initial
document.addEventListener('DOMContentLoaded', chargerAvisPublics);

// Navigation SPA
document.addEventListener('pageChargee', chargerAvisPublics);
