/* STATISTIQUES ADMINISTRATEUR */

// Numéro du dernier chargement demandé
let chargementStatistiques = 0;
// Conserver le graphique pour pouvoir l'actualiser
let graphiqueCommandesAdmin = null;

/* AFFICHER LE GRAPHIQUE DES COMMANDES */

function afficherGraphiqueCommandes(menus) {

    // Récupérer le canvas
    const canvas = document.getElementById(
        'graphique-commandes'
    );

    if (!canvas) {
        return;
    }

    // Supprimer l'ancien graphique s'il existe
    if (graphiqueCommandesAdmin) {

        graphiqueCommandesAdmin.destroy();

        graphiqueCommandesAdmin = null;
    }

    // Préparer les noms des menus
    const nomsMenus = menus.map(function(menu) {
        return menu.titre;
    });

    // Préparer le nombre de commandes
    const nombresCommandes = menus.map(function(menu) {
        return Number(menu.nombre_commandes);
    });

    // Créer le graphique
    graphiqueCommandesAdmin = new Chart(canvas, {

        type: 'bar',

        data: {

            labels: nomsMenus,

            datasets: [{
                label: 'Nombre de commandes',
                data: nombresCommandes,
                backgroundColor: '#dc744f',
                borderColor: '#d7b296',
                borderWidth: 1,
                borderRadius: 5
            }]

        },

        options: {

            responsive: true,
            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    callbacks: {

                        label: function(contexte) {

                            const nombre = contexte.parsed.y;

                            return nombre + (
                                nombre > 1
                                    ? ' commandes'
                                    : ' commande'
                            );
                        }

                    }

                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {
                        precision: 0,
                        stepSize: 1
                    }

                },

                x: {

                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }

                }

            }

        }

    });

}

/* CHARGER LES STATISTIQUES */

async function chargerStatistiquesAdmin() {

    // Vérifier que nous sommes sur la bonne page
    const formulaire = document.getElementById('form-filtres-statistiques');

    if (!formulaire) {
        return;
    }

    const feedback = document.getElementById('statistiques-feedback');

    const menuSelect = document.getElementById('stats-menu');

    const totalCommandes = document.getElementById('stats-total-commandes');

    const chiffreAffaires = document.getElementById('stats-chiffre-affaires');

    const tableau = document.getElementById('stats-tableau-menus');

    const bouton = formulaire.querySelector('button[type="submit"]');

    // Identifier ce chargement
    const chargementActuel = ++chargementStatistiques;

    // Récupérer les filtres
    const menuId = menuSelect.value;

    const dateDebut = document.getElementById( 'stats-date-debut').value;

    const dateFin = document.getElementById('stats-date-fin').value;

    // Vérifier les dates
    if (dateDebut && dateFin && dateDebut > dateFin) {

        feedback.textContent ='La date de début doit précéder la date de fin.';

        feedback.className = 'mb-4 text-danger';

        return;
    }

    // Préparer les paramètres de l'URL
    const parametres = new URLSearchParams();

    if (menuId) {
        parametres.append('menu_id', menuId);
    }

    if (dateDebut) {
        parametres.append('date_debut', dateDebut);
    }

    if (dateFin) {
        parametres.append('date_fin', dateFin);
    }

    // Préparer l'affichage
    bouton.disabled = true;

    feedback.textContent = 'Chargement des statistiques...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Interroger l'API PHP
        const reponse = await fetch('/api/statistiques-admin.php?' + parametres.toString(),
            {
                cache: 'no-store'
            }
        );

        const resultat = await reponse.json();

        // Ignorer une ancienne réponse
        if (chargementActuel !== chargementStatistiques) {
            return;
        }

        if (!reponse.ok || !resultat.success) {

            throw new Error(
                resultat.message ||
                'Impossible de charger les statistiques.'
            );
        }

        // Vérifier que la page est toujours affichée
        if (!formulaire.isConnected) {
            return;
        }

        // Afficher le nombre total de commandes
        totalCommandes.textContent = Number(resultat.total_commandes).toLocaleString('fr-FR');

        // Afficher le chiffre d'affaires
        chiffreAffaires.textContent = Number(resultat.chiffre_affaires_total).toLocaleString(
                'fr-FR',
                {
                    style: 'currency',
                    currency: 'EUR'
                }
            );

        // Conserver le menu actuellement sélectionné
        const menuSelectionne = menuSelect.value;

        // Réinitialiser la liste déroulante
        menuSelect.innerHTML = '';

        const optionTous = document.createElement('option');

        optionTous.value = '';
        optionTous.textContent = 'Tous les menus';

        menuSelect.appendChild(optionTous);

        // Ajouter les menus disponibles
        for (const menu of resultat.liste_menus) {

            const option = document.createElement('option');

            option.value = menu.id;
            option.textContent = menu.titre;

            menuSelect.appendChild(option);
        }

        // Restaurer la sélection
        menuSelect.value = menuSelectionne;

        // Préparer le tableau
        tableau.innerHTML = '';

        // Afficher les statistiques de chaque menu
        for (const menu of resultat.menus) {

            const ligne = document.createElement('tr');

            // Nom du menu
            const celluleMenu = document.createElement('td');

            celluleMenu.textContent = menu.titre;

            // Nombre de commandes
            const celluleCommandes = document.createElement('td');

            celluleCommandes.textContent =
                Number(menu.nombre_commandes).toLocaleString('fr-FR');

            // Chiffre d'affaires
            const celluleCA = document.createElement('td');

            celluleCA.className = 'text-end';

            celluleCA.textContent = Number(menu.chiffre_affaires).toLocaleString(
                    'fr-FR',
                    {
                        style: 'currency',
                        currency: 'EUR'
                    }
                );

            // Assembler la ligne
            ligne.appendChild(celluleMenu);
            ligne.appendChild(celluleCommandes);
            ligne.appendChild(celluleCA);

            tableau.appendChild(ligne);
        }

        // Aucun menu à afficher
        if (resultat.menus.length === 0) {

            const ligne = document.createElement('tr');
            const cellule = document.createElement('td');

            cellule.colSpan = 3;

            cellule.textContent =
                'Aucune donnée pour les filtres sélectionnés.';

            ligne.appendChild(cellule);
            tableau.appendChild(ligne);
        }

        // Afficher le graphique avec les données MongoDB
        afficherGraphiqueCommandes(resultat.menus);
        
        // Effacer le message de chargement
        feedback.textContent = '';
        feedback.className = 'mb-4';

    } catch (erreur) {

        if (chargementActuel !== chargementStatistiques) {
            return;
        }

        console.error(erreur);

        feedback.textContent = erreur.message;
        feedback.className = 'mb-4 text-danger';

    } finally {

        if (
            chargementActuel === chargementStatistiques &&
            formulaire.isConnected
        ) {
            bouton.disabled = false;
        }

    }

}

/* CHARGER LES STATISTIQUES AVEC LE ROUTEUR */

document.addEventListener('pageChargee', function() {chargerStatistiquesAdmin();});

/* APPLIQUER LES FILTRES */

document.addEventListener('submit', function(event) {

    if (event.target.id !== 'form-filtres-statistiques') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    // Actualiser les statistiques
    chargerStatistiquesAdmin();

});


/* RÉINITIALISER LES FILTRES DES STATISTIQUES */

document.addEventListener('click', function(event) {

    // Vérifier que le bouton a été cliqué
    const bouton = event.target.closest(
        '#reinitialiser-filtres-statistiques'
    );

    if (!bouton) {
        return;
    }

    // Récupérer le formulaire
    const formulaire = document.getElementById(
        'form-filtres-statistiques'
    );

    if (!formulaire) {
        return;
    }

    // Réinitialiser les champs du formulaire
    formulaire.reset();

    // Recharger toutes les statistiques
    chargerStatistiquesAdmin();

});

