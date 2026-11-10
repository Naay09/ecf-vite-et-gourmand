// CHOISIR LE MODE DE FILTRAGE PAR PRIX

document.addEventListener('change', function(event) {

    // Vérifier que le changement concerne le mode de prix
    if (event.target.name !== 'menu-price-mode') {
        return;
    }

    // Récupérer le bloc du prix minimum
    const prixMinBlock = document.getElementById('menu-price-min-block');

    if (!prixMinBlock) {
        return;
    }

    // Afficher le prix minimum uniquement en mode fourchette
    if (event.target.value === 'range') {
        prixMinBlock.classList.remove('d-none');
    } else {
        prixMinBlock.classList.add('d-none');
    }

});

// ACTUALISER LES VALEURS DES CURSEURS

document.addEventListener('input', function(event) {

    // Vérifier que l'utilisateur déplace un curseur de prix
    if (event.target.id !== 'menu-price' &&
        event.target.id !== 'menu-price-min') {
        return;
    }

    const prixMin = document.getElementById('menu-price-min');
    const prixMax = document.getElementById('menu-price');

    const texteMin = document.getElementById('menu-price-min-help');
    const texteMax = document.getElementById('menu-price-help');

    if (!prixMin || !prixMax || !texteMin || !texteMax) {
        return;
    }

    // Empêcher le prix minimum de dépasser le maximum
    if (Number(prixMin.value) > Number(prixMax.value)) {

        if (event.target.id === 'menu-price-min') {
            prixMax.value = prixMin.value;
        } else {
            prixMin.value = prixMax.value;
        }

    }

    // Actualiser les textes sous les curseurs
    texteMin.textContent = 'À partir de ' + prixMin.value + ' € / pers.';

    texteMax.textContent = 'Jusqu’à ' + prixMax.value + ' € / pers.';

});


/* ACTUALISER LE NOMBRE DE CONVIVES */

document.addEventListener('input', function(event) {

    // Vérifier que le curseur des convives est utilisé
    if (event.target.id !== 'menu-guests') {
        return;
    }

    // Récupérer le texte sous le curseur
    const texteConvives = document.getElementById('menu-guests-help');

    if (!texteConvives) {
        return;
    }

    // Afficher la valeur sélectionnée
    texteConvives.textContent = 'Jusque ' + event.target.value + ' convives';

});

function actualiserCompteurMenus() {

    const compteur = document.getElementById('menu-count');
    const resultats = document.getElementById('menu-results');

    if (!compteur || !resultats) {
        return;
    }

    // Compter les cartes réellement affichées
    const nombre = resultats.querySelectorAll('.menu-card').length;

    if (nombre === 0) {
        compteur.textContent = 'Aucun menu trouvé.';
    } else {
        compteur.textContent =
            nombre +
            (nombre === 1 ? ' menu à découvrir' : ' menus à découvrir');
    }
}

// FILTRER LES MENUS

async function filtrerMenus() {

    // Récupérer le conteneur des cartes
    const resultats = document.getElementById('menu-results');

    if (!resultats) {
        return;
    }

    // Récupérer les valeurs des filtres
    const recherche = document.getElementById('menu-search').value;
    const theme = document.getElementById('menu-theme').value;
    const regime = document.getElementById('menu-diet').value;
    const convives = document.getElementById('menu-guests').value;

    const modePrix = document.querySelector(
        'input[name="menu-price-mode"]:checked'
    ).value;

    const prixMin = document.getElementById('menu-price-min').value;
    const prixMax = document.getElementById('menu-price').value;

    // Préparer les paramètres à transmettre à PHP
    const parametres = new URLSearchParams();

    parametres.append('recherche', recherche);
    parametres.append('theme', theme);
    parametres.append('regime', regime);
    parametres.append('convives', convives);
    parametres.append('mode_prix', modePrix);
    parametres.append('prix_max', prixMax);

    if (modePrix === 'range') {
        parametres.append('prix_min', prixMin);
    }

    try {

        // Envoyer les filtres à PHP
        const reponse = await fetch(
            '/api/filtrer-menus.php?' + parametres.toString()
        );

        if (!reponse.ok) {
            throw new Error('Erreur lors du filtrage des menus.');
        }

        // Récupérer les cartes HTML
        const html = await reponse.text();

        // Actualiser le catalogue
        resultats.innerHTML = html;
        actualiserCompteurMenus();

    } catch (erreur) {

        resultats.textContent = 'Impossible de charger les menus.';
        const compteur = document.getElementById('menu-count');
        if (compteur) {
            compteur.textContent = 'Nombre de menus indisponible.';
        }
        
        console.error(erreur);
    }
}


// Déclencher le filtrage quand un curseur ou la recherche change
document.addEventListener('input', function(event) {

    if (!event.target.closest('#menu-filters')) {
        return;
    }

    if (
        event.target.id === 'menu-search' ||
        event.target.id === 'menu-guests' ||
        event.target.id === 'menu-price' ||
        event.target.id === 'menu-price-min'
    ) {
        filtrerMenus();
    }

});


// Déclencher le filtrage quand un select ou le mode de prix change
document.addEventListener('change', function(event) {

    if (!event.target.closest('#menu-filters')) {
        return;
    }

    if (
        event.target.id === 'menu-theme' ||
        event.target.id === 'menu-diet' ||
        event.target.name === 'menu-price-mode'
    ) {
        filtrerMenus();
    }

});

/* RÉINITIALISER LES FILTRES */

document.addEventListener('click', function(event) {

    // Vérifier que le bouton Réinitialiser est cliqué
    const bouton = event.target.closest('#menu-reset');

    if (!bouton) {
        return;
    }

    // Réinitialiser les champs
    document.getElementById('menu-search').value = '';
    document.getElementById('menu-theme').value = '';
    document.getElementById('menu-diet').value = '';
    document.getElementById('menu-guests').value = 60;

    // Réinitialiser les prix
    document.getElementById('menu-price-min').value = 0;
    document.getElementById('menu-price').value = 120;

    // Revenir au mode Prix maximum
    document.getElementById('price-mode-max').checked = true;

    // Masquer le prix minimum
    document.getElementById('menu-price-min-block')
        .classList.add('d-none');

    // Actualiser les textes des curseurs
    document.getElementById('menu-guests-help')
        .textContent = 'Pour 60 convives';

    document.getElementById('menu-price-min-help')
        .textContent = 'À partir de 0 € / pers.';

    document.getElementById('menu-price-help')
        .textContent = 'Jusqu’à 120 € / pers.';

    // Afficher à nouveau tous les menus
    filtrerMenus();

});
