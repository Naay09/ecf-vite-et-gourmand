/* CHARGER LES MENUS */

async function chargerMenusEmploye() {

    // Récupérer les éléments HTML
    const liste = document.getElementById('gestion-menus-liste');
    const feedback = document.getElementById('gestion-menus-feedback');

    // Vérifier que la page est affichée
    if (!liste || !feedback) {
        return;
    }

    // Réinitialiser l'affichage
    liste.innerHTML = '';

    feedback.textContent = 'Chargement des menus...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les menus depuis PHP
        const reponse = await fetch('/api/menus-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger les menus.';

            feedback.className = 'mb-4 text-danger';

            return;
        }

        // Récupérer les menus
        const menus = resultat.menus;

        // Vérifier si aucun menu n'existe
        if (menus.length === 0) {

            feedback.textContent = 'Aucun menu enregistré.';
            feedback.className = 'mb-4 text-secondary';

            return;
        }

        // Effacer le message de chargement
        feedback.textContent = '';

        // Créer une carte pour chaque menu
        for (const menu of menus) {

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-md-6 col-lg-4';

            // Carte Bootstrap
            const carte = document.createElement('article');
            carte.className = 'card h-100';

            // Contenu de la carte
            const contenu = document.createElement('div');
            contenu.className = 'card-body d-flex flex-column';

            // Thème
            const theme = document.createElement('span');
            theme.className = 'badge text-bg-primary align-self-start mb-3';
            theme.textContent = menu.theme_nom || 'Sans thème';

            // Titre
            const titre = document.createElement('h3');
            titre.className = 'h5 mb-3';
            titre.textContent = menu.titre;

            // Description
            const description = document.createElement('p');
            description.className = 'mb-3';
            description.textContent = menu.description || 'Aucune description.';

            // Régime
            const regime = document.createElement('p');
            regime.className = 'mb-2';
            regime.textContent = 'Régime : ' + (menu.regime_nom || 'Non renseigné');

            // Nombre minimum de personnes
            const personnes = document.createElement('p');
            personnes.className = 'mb-2';

            personnes.textContent =
                'Minimum : ' + menu.nb_personnes_min + ' personnes';

            // Prix par personne
            const prix = document.createElement('p');
            prix.className = 'fw-semibold mb-2';

            const prixFormate = Number(menu.prix_personne)
                .toLocaleString('fr-FR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });

            prix.textContent = 'Prix : ' + prixFormate + ' € / personne';

            // Stock disponible
            const stock = document.createElement('p');
            stock.className = 'mb-2';

            stock.textContent =
                'Stock disponible : ' + menu.stock_disponible;

            // Délai de commande
            const delai = document.createElement('p');
            delai.className = 'mb-3';

            delai.textContent =
                'Commande ' + menu.delai_commande_jours +
                ' jour(s) à l’avance';

            // Conditions
            const conditions = document.createElement('p');
            conditions.className = 'small text-muted mb-4';

            conditions.textContent =
                'Conditions : ' + (menu.conditions || 'Aucune condition particulière.');

            // Boutons de gestion
            const actions = document.createElement('div');
            actions.className = 'd-flex flex-wrap gap-2 mt-auto';

            // Bouton Modifier
            const boutonModifier = document.createElement('button');
            boutonModifier.type = 'button';
            boutonModifier.className = 'btn btn-outline-primary btn-modifier-menu';
            boutonModifier.textContent = 'Modifier';
            boutonModifier.dataset.menuId = menu.id;

            // Ouvrir la modale en mode modification
            boutonModifier.dataset.bsToggle = 'modal';
            boutonModifier.dataset.bsTarget = '#modal-gestion-menu';

            // Bouton Supprimer
            const boutonSupprimer = document.createElement('button');
            boutonSupprimer.type = 'button';
            boutonSupprimer.className = 'btn btn-outline-danger btn-supprimer-menu';
            boutonSupprimer.textContent = 'Supprimer';
            boutonSupprimer.dataset.menuId = menu.id;
            boutonSupprimer.dataset.menuTitre = menu.titre;

            // Préparer les actions
            actions.appendChild(boutonModifier);
            actions.appendChild(boutonSupprimer);

            // Construire la carte
            contenu.appendChild(theme);
            contenu.appendChild(titre);
            contenu.appendChild(description);
            contenu.appendChild(regime);
            contenu.appendChild(personnes);
            contenu.appendChild(prix);
            contenu.appendChild(stock);
            contenu.appendChild(delai);
            contenu.appendChild(conditions);
            contenu.appendChild(actions);

            carte.appendChild(contenu);
            colonne.appendChild(carte);

            // Ajouter la carte à la liste
            liste.appendChild(colonne);
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de charger les menus.';
        feedback.className = 'mb-4 text-danger';
    }
}

/* CHARGER LES THÈMES ET LES RÉGIMES */

async function chargerOptionsMenus() {

    const selectTheme = document.getElementById(
        'gestion-menu-theme'
    );

    const selectRegime = document.getElementById(
        'gestion-menu-regime'
    );

    // Vérifier que le formulaire existe
    if (!selectTheme || !selectRegime) {
        return false;
    }

    // Réinitialiser les listes
    selectTheme.innerHTML = '';
    selectRegime.innerHTML = '';

    // Ajouter les options par défaut
    const optionTheme = document.createElement('option');
    optionTheme.value = '';
    optionTheme.textContent = 'Choisir un thème';
    selectTheme.appendChild(optionTheme);

    const optionRegime = document.createElement('option');
    optionRegime.value = '';
    optionRegime.textContent = 'Choisir un régime';
    selectRegime.appendChild(optionRegime);

    try {

        // Récupérer les thèmes et régimes depuis PHP
        const reponse = await fetch('/api/options-menus-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error(
                resultat.message || 'Impossible de charger les options.'
            );
        }

        // Ajouter les thèmes
        for (const theme of resultat.themes) {

            const option = document.createElement('option');

            option.value = theme.id;
            option.textContent = theme.nom;

            selectTheme.appendChild(option);
        }

        // Ajouter les régimes
        for (const regime of resultat.regimes) {

            const option = document.createElement('option');

            option.value = regime.id;
            option.textContent = regime.nom;

            selectRegime.appendChild(option);
        }

        // Enregistrer le jeton CSRF
        document.getElementById('gestion-menu-csrf').value =
            resultat.csrf_token;

        return true;

    } catch (erreur) {

        console.error(erreur);

        return false;
    }
}

/* CHARGER LES PLATS DISPONIBLES POUR UN MENU */

async function chargerPlatsMenus() {

    // Récupérer les listes déroulantes
    const selectEntree = document.getElementById(
        'gestion-menu-entree'
    );

    const selectPlat = document.getElementById(
        'gestion-menu-plat'
    );

    const selectDessert = document.getElementById(
        'gestion-menu-dessert'
    );

    if (!selectEntree || !selectPlat || !selectDessert) {
        return false;
    }

    // Réinitialiser les listes
    selectEntree.innerHTML = '';
    selectPlat.innerHTML = '';
    selectDessert.innerHTML = '';

    // Ajouter les options par défaut
    const listes = [
        { select: selectEntree, texte: 'Choisir une entrée' },
        { select: selectPlat, texte: 'Choisir un plat principal' },
        { select: selectDessert, texte: 'Choisir un dessert' }
    ];

    for (const liste of listes) {

        const option = document.createElement('option');

        option.value = '';
        option.textContent = liste.texte;

        liste.select.appendChild(option);
    }

    try {

        // Récupérer les plats depuis PHP
        const reponse = await fetch('/api/plats-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error(
                resultat.message || 'Impossible de charger les plats.'
            );
        }

        // Parcourir les plats disponibles
        for (const plat of resultat.plats) {

            // Déterminer la liste correspondante
            let select = null;

            if (plat.categorie === 'entree') {
                select = selectEntree;
            } else if (plat.categorie === 'plat') {
                select = selectPlat;
            } else if (plat.categorie === 'dessert') {
                select = selectDessert;
            }

            if (!select) {
                continue;
            }

            // Créer l'option
            const option = document.createElement('option');

            option.value = plat.id;
            option.textContent = plat.nom;

            // Ajouter le plat à sa catégorie
            select.appendChild(option);
        }

        return true;

    } catch (erreur) {

        console.error(erreur);

        return false;
    }
}

/* CHARGER LES IMAGES EXISTANTES D'UN MENU */

async function chargerImagesMenu(menuId) {

    const conteneur = document.getElementById(
        'gestion-menu-images-existantes'
    );

    const apercuPrincipal = document.getElementById(
        'gestion-menu-image-principale-apercu'
    );

    const champTitrePrincipal = document.getElementById(
        'gestion-menu-image-titre'
    );

    const champAltPrincipal = document.getElementById(
        'gestion-menu-image-alt'
    );

    // Vider les anciennes images
    conteneur.innerHTML = '';
    apercuPrincipal.innerHTML = '';

    champTitrePrincipal.value = '';
    champAltPrincipal.value = '';

    try {

        // Récupérer les images depuis MySQL
        const reponse = await fetch(
            '/api/images-menu-employe.php?menu_id=' +
            encodeURIComponent(menuId),
            {
                cache: 'no-store'
            }
        );

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            return false;
        }

        // Parcourir les images du menu
        for (const imageMenu of resultat.images) {

            const cheminImage = '/' +
                imageMenu.url.replace(/^\/+/, '');

            // IMAGE PRINCIPALE
            if (Number(imageMenu.ordre) === 0) {

                const image = document.createElement('img');

                image.src = cheminImage;
                image.alt = imageMenu.texte_alternatif;
                image.className = 'img-fluid rounded mb-3';

                apercuPrincipal.appendChild(image);

                // Préremplir le titre
                champTitrePrincipal.value =
                    imageMenu.titre || '';

                // Préremplir le texte alternatif
                champAltPrincipal.value =
                    imageMenu.texte_alternatif || '';

                // Conserver l'identifiant de l'image
                champTitrePrincipal.dataset.imageId =
                    imageMenu.id;

                continue;
            }

            // IMAGES COMPLÉMENTAIRES

            const colonne = document.createElement('div');
            colonne.className = 'col-md-4';

            const carte = document.createElement('div');
            carte.className = 'card h-100';

            // Aperçu
            const image = document.createElement('img');

            image.src = cheminImage;
            image.alt = imageMenu.texte_alternatif;
            image.className = 'card-img-top';

            // Contenu de la carte
            const corps = document.createElement('div');
            corps.className = 'card-body';

            // Titre du champ
            const label = document.createElement('label');

            label.className = 'form-label';
            label.textContent = 'Titre de l’image';

            label.htmlFor = 'image-existante-titre-' +
                imageMenu.id;

            // Champ permettant de modifier le titre
            const inputTitre = document.createElement('input');

            inputTitre.type = 'text';
            inputTitre.id = label.htmlFor;
            inputTitre.className = 'form-control mb-3';
            inputTitre.name = 'images_existantes_titres[' +
                imageMenu.id + ']';

            inputTitre.value = imageMenu.titre || '';
            inputTitre.maxLength = 150;

            // Case à cocher pour supprimer l'image
            const groupeSuppression = document.createElement('div');

            groupeSuppression.className = 'form-check';

            const checkbox = document.createElement('input');

            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input';
            checkbox.id = 'supprimer-image-' + imageMenu.id;
            checkbox.name = 'images_a_supprimer[]';
            checkbox.value = imageMenu.id;

            const labelSuppression = document.createElement('label');

            labelSuppression.className = 'form-check-label';
            labelSuppression.htmlFor = checkbox.id;
            labelSuppression.textContent = 'Supprimer cette image';

            // Assembler les éléments
            groupeSuppression.appendChild(checkbox);
            groupeSuppression.appendChild(labelSuppression);

            corps.appendChild(label);
            corps.appendChild(inputTitre);
            corps.appendChild(groupeSuppression);

            carte.appendChild(image);
            carte.appendChild(corps);

            colonne.appendChild(carte);
            conteneur.appendChild(colonne);
        }

        return true;

    } catch (erreur) {

        console.error(erreur);

        return false;
    }
}

/* OUVRIR LA MODALE D'UN MENU */

document.addEventListener('show.bs.modal', async function(event) {

    // Vérifier la modale concernée
    if (event.target.id !== 'modal-gestion-menu') {
        return;
    }

    const formulaire = document.getElementById('form-gestion-menu');
    const titre = document.getElementById('modal-gestion-menu-titre');
    const feedback = document.getElementById('gestion-menu-form-feedback');
    const bouton = document.getElementById('gestion-menu-enregistrer');

    // Réinitialiser le formulaire
    formulaire.reset();

    // Réinitialiser un éventuel import d'images incomplet
    formulaire.dataset.menuEnAttenteImages = '';

    // Réinitialiser le suivi des modifications
    formulaire.dataset.modificationMenuEnregistree = '';

    document.getElementById('gestion-menu-id').value = '';
    document.getElementById('gestion-menu-csrf').value = '';

    document.getElementById('gestion-menu-titres-images').innerHTML = '';
    document.getElementById('gestion-menu-images-existantes').innerHTML = '';
    document.getElementById('gestion-menu-image-principale-apercu').innerHTML = '';


    // Nettoyer l'identifiant de l'image principale précédente
    document.getElementById('gestion-menu-image-titre').dataset.imageId = '';

    // L'image principale est obligatoire en création, mais facultative en modification.
    document.getElementById('gestion-menu-image-principale').required = false;

    titre.textContent = 'Ajouter un menu';
    bouton.textContent = 'Enregistrer';
    bouton.disabled = true;

    feedback.textContent = 'Chargement du formulaire...';
    feedback.className = 'text-secondary';

    // Identifier le bouton qui ouvre la modale
    const boutonOuverture = event.relatedTarget;

    const modification = boutonOuverture &&
        boutonOuverture.classList.contains('btn-modifier-menu');

    try {

        // Charger les thèmes et les régimes
        const optionsChargees = await chargerOptionsMenus();

        if (!optionsChargees) {
            throw new Error(
                'Impossible de charger les thèmes et les régimes.'
            );
        }

        // Charger les plats disponibles
        const platsCharges = await chargerPlatsMenus();

        if (!platsCharges) {
            throw new Error(
                'Impossible de charger les plats.'
            );
        }

        // MODE AJOUT
        if (!modification) {

            // Image principale obligatoire à la création
            document.getElementById('gestion-menu-image-principale').required = true;

            feedback.textContent = '';

            bouton.disabled = false;

            return;
        }

        // MODE MODIFICATION

        document.getElementById('gestion-menu-image-principale').required = false;
        titre.textContent = 'Modifier un menu';
        bouton.textContent = 'Enregistrer les modifications';

        const menuId = boutonOuverture.dataset.menuId;

        // Récupérer les informations générales
        const reponse = await fetch('/api/menus-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error('Impossible de récupérer les menus.');
        }

        // Rechercher le menu sélectionné
        const menu = resultat.menus.find(function(element) {

            return Number(element.id) === Number(menuId);

        });

        if (!menu) {
            throw new Error('Menu introuvable.');
        }

        // Préremplir les informations générales
        document.getElementById('gestion-menu-id').value =
            menu.id;

        document.getElementById('gestion-menu-titre').value =
            menu.titre;

        document.getElementById('gestion-menu-description').value =
            menu.description || '';

        document.getElementById('gestion-menu-theme').value =
            menu.theme_id;

        document.getElementById('gestion-menu-regime').value =
            menu.regime_id;

        document.getElementById('gestion-menu-personnes').value =
            menu.nb_personnes_min;

        document.getElementById('gestion-menu-prix').value =
            menu.prix_personne;

        document.getElementById('gestion-menu-delai').value =
            menu.delai_commande_jours;

        document.getElementById('gestion-menu-conditions').value =
            menu.conditions || '';

        // Récupérer les plats associés
        const reponsePlats = await fetch(
            '/api/plats-menu-employe.php?menu_id=' +
            encodeURIComponent(menuId),
            { cache: 'no-store' }
        );

        const resultatPlats = await reponsePlats.json();

        if (!reponsePlats.ok || !resultatPlats.success) {
            throw new Error(
                'Impossible de récupérer les plats associés.'
            );
        }

        // Préremplir les trois listes de plats
        for (const plat of resultatPlats.plats) {

            if (plat.categorie === 'entree') {

                document.getElementById('gestion-menu-entree').value =
                    plat.id;

            } else if (plat.categorie === 'plat') {

                document.getElementById('gestion-menu-plat').value =
                    plat.id;

            } else if (plat.categorie === 'dessert') {

                document.getElementById('gestion-menu-dessert').value =
                    plat.id;
            }
        }

        // Charger les images existantes
        const imagesChargees = await chargerImagesMenu(menuId);

        if (!imagesChargees) {
            throw new Error(
                'Impossible de charger les images du menu.'
            );
        }

        feedback.textContent = '';
        feedback.className = '';

        // Activer l'enregistrement des modifications
        bouton.disabled = false;

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = erreur.message;
        feedback.className = 'text-danger';

    }

});



/* ENREGISTRER UN MENU ET SES IMAGES */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-gestion-menu') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;

    const bouton = document.getElementById(
        'gestion-menu-enregistrer'
    );

    const feedback = document.getElementById(
        'gestion-menu-form-feedback'
    );

    // Empêcher les doubles envois
    if (bouton.disabled) {
        return;
    }

    // Vérifier les champs obligatoires
    if (!formulaire.reportValidity()) {
        return;
    }

    // Identifier le mode modification
    const menuExistantId = document.getElementById(
        'gestion-menu-id'
    ).value;

    if (menuExistantId !== '') {

        // Modifier le menu et sa galerie
        await enregistrerModificationsMenu(
            formulaire,
            bouton,
            feedback
        );

        return;
    }

    // Vérifier l'image principale
    const champImage = document.getElementById(
        'gestion-menu-image-principale'
    );

    if (champImage.files.length === 0) {

        feedback.textContent =
            'Veuillez sélectionner une image principale.';

        feedback.className = 'text-danger';

        return;
    }

    // Vérifier le titre de l'image principale
    const titrePrincipal = document.getElementById(
        'gestion-menu-image-titre'
    ).value.trim();

    if (titrePrincipal === '') {

        feedback.textContent =
            'Veuillez renseigner le titre de l’image principale.';

        feedback.className = 'text-danger';

        return;
    }

    // Récupérer les données du formulaire
    const donnees = new FormData(formulaire);

    // Récupérer un menu créé lors d'une précédente tentative
    let menuId = formulaire.dataset.menuEnAttenteImages;

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;

    try {

        // ÉTAPE 1 : CRÉER LE MENU SI NÉCESSAIRE
        if (!menuId) {

            feedback.textContent = 'Création du menu...';
            feedback.className = 'text-secondary';

            // Préparer les données générales, sans les images
            const donneesMenu = new FormData(formulaire);

            // Exclure les images et leurs informations
            donneesMenu.delete('image_principale');
            donneesMenu.delete('image_principale_alt');
            donneesMenu.delete('image_principale_titre');
            donneesMenu.delete('images[]');
            donneesMenu.delete('images_titres[]');

            // Envoyer les informations à PHP
            const reponseMenu = await fetch(
                '/api/enregistrer-menu.php',
                {
                    method: 'POST',
                    body: donneesMenu
                }
            );

            const resultatMenu = await reponseMenu.json();

            if (!reponseMenu.ok || !resultatMenu.success) {

                feedback.textContent =
                    resultatMenu.message ||
                    'Impossible de créer le menu.';

                feedback.className = 'text-danger';

                bouton.disabled = false;

                return;
            }

            // Récupérer l'identifiant du menu créé
            menuId = resultatMenu.menu_id;

            // Conserver cet identifiant en cas d'échec des images
            formulaire.dataset.menuEnAttenteImages = menuId;
        }

        // ÉTAPE 2 : ENREGISTRER LES IMAGES

        feedback.textContent = 'Importation des images...';
        feedback.className = 'text-secondary';

        // Ajouter l'identifiant du menu
        donnees.set('menu_id', menuId);

        // Envoyer les fichiers à PHP
        const reponseImages = await fetch(
            '/api/enregistrer-images-menu.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        const resultatImages = await reponseImages.json();

        if (!reponseImages.ok || !resultatImages.success) {

            feedback.textContent =
                'Le menu a été créé, mais les images n’ont pas ' +
                'pu être enregistrées. ' +
                (resultatImages.message || '') +
                ' Corrigez le problème puis réessayez.';

            feedback.className = 'text-danger';

            bouton.disabled = false;

            return;
        }

        // ÉTAPE 3 : TERMINER L'ENREGISTREMENT

        // Fermer la modale
        const elementModal = document.getElementById(
            'modal-gestion-menu'
        );

        const modal = bootstrap.Modal.getInstance(
            elementModal
        );

        if (modal) {
            modal.hide();
        }

        // Actualiser la liste des menus
        await chargerMenusEmploye();

        // Afficher la confirmation sur la page
        const feedbackPage = document.getElementById(
            'gestion-menus-feedback'
        );

        if (feedbackPage) {

            feedbackPage.textContent =
                'Le menu et ses images ont été enregistrés avec succès.';

            feedbackPage.className = 'mb-4 text-success';
        }

    } catch (erreur) {

        console.error(erreur);

        if (menuId) {

            feedback.textContent =
                'Impossible de confirmer l’importation des images. ' +
                'Vérifiez la galerie du menu avant de réessayer.';

        } else {

            feedback.textContent =
                'Impossible de confirmer la création du menu. ' +
                'Vérifiez la liste avant de réessayer.';
        }

        feedback.className = 'text-danger';

        // Ne pas relancer automatiquement :
        // le serveur a peut-être terminé l'opération.

    }

});

/* TITRES DES IMAGES COMPLÉMENTAIRES */

document.addEventListener('change', function(event) {

    if (event.target.id !== 'gestion-menu-images') {
        return;
    }

    const fichiers = event.target.files;

    const conteneur = document.getElementById(
        'gestion-menu-titres-images'
    );

    // Effacer les anciens champs
    conteneur.innerHTML = '';

    // Créer un champ de titre pour chaque image
    for (let index = 0; index < fichiers.length; index++) {

        const groupe = document.createElement('div');
        groupe.className = 'mb-3';

        const label = document.createElement('label');
        label.className = 'form-label';

        label.htmlFor = 'gestion-image-titre-' + index;

        label.textContent =
            'Titre de l’image : ' + fichiers[index].name;

        const input = document.createElement('input');

        input.type = 'text';
        input.id = 'gestion-image-titre-' + index;
        input.name = 'images_titres[]';
        input.className = 'form-control';
        input.maxLength = 150;
        input.required = true;

        groupe.appendChild(label);
        groupe.appendChild(input);

        conteneur.appendChild(groupe);
    }

});

/* MODIFIER UN MENU ET SES IMAGES */

async function enregistrerModificationsMenu(formulaire, bouton, feedback) {

    const menuId = document.getElementById(
        'gestion-menu-id'
    ).value;

    // Récupérer toutes les données du formulaire
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;

    // Vérifier si les informations du menu
    // ont déjà été enregistrées lors de cette tentative
    const menuDejaEnregistre =
        formulaire.dataset.modificationMenuEnregistree === '1';

    try {

        // ÉTAPE 1 : MODIFIER LES INFORMATIONS ET LES PLATS

        if (!menuDejaEnregistre) {

            feedback.textContent =
                'Modification des informations du menu...';

            feedback.className = 'text-secondary';

            // Préparer les informations sans les images
            const donneesMenu = new FormData(formulaire);

            donneesMenu.delete('image_principale');
            donneesMenu.delete('image_principale_titre');
            donneesMenu.delete('image_principale_alt');
            donneesMenu.delete('images[]');
            donneesMenu.delete('images_titres[]');

            // Envoyer les données à PHP
            const reponseMenu = await fetch(
                '/api/modifier-menu.php',
                {
                    method: 'POST',
                    body: donneesMenu
                }
            );

            const resultatMenu = await reponseMenu.json();

            if (!reponseMenu.ok || !resultatMenu.success) {

                feedback.textContent =
                    resultatMenu.message ||
                    'Impossible de modifier le menu.';

                feedback.className = 'text-danger';

                bouton.disabled = false;

                return;
            }

            // Les informations du menu ont été enregistrées
            formulaire.dataset.modificationMenuEnregistree = '1';
        }

        // ÉTAPE 2 : MODIFIER LA GALERIE

        feedback.textContent =
            'Enregistrement des modifications des images...';

        feedback.className = 'text-secondary';

        // Transmettre explicitement le menu concerné
        donnees.set('menu_id', menuId);

        const reponseImages = await fetch(
            '/api/modifier-images-menu.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        const resultatImages = await reponseImages.json();

        if (!reponseImages.ok || !resultatImages.success) {

            feedback.textContent =
                'Les informations du menu ont été enregistrées, ' +
                'mais les images n’ont pas pu être modifiées. ' +
                (resultatImages.message || '') +
                ' Vous pouvez corriger le problème et réessayer.';

            feedback.className = 'text-danger';

            // Autoriser une nouvelle tentative pour les images
            bouton.disabled = false;

            return;
        }

        // ÉTAPE 3 : CONFIRMER LES MODIFICATIONS

        // Fermer la modale
        const elementModal = document.getElementById(
            'modal-gestion-menu'
        );

        const modal = bootstrap.Modal.getInstance(
            elementModal
        );

        if (modal) {
            modal.hide();
        }

        // Actualiser les cartes
        await chargerMenusEmploye();

        // Afficher un message de confirmation
        const feedbackPage = document.getElementById(
            'gestion-menus-feedback'
        );

        if (feedbackPage) {

            feedbackPage.textContent =
                'Le menu et ses images ont été modifiés avec succès.';

            feedbackPage.className = 'mb-4 text-success';
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer l’enregistrement. ' +
            'Vérifiez les données du menu et sa galerie ' +
            'avant de réessayer.';

        feedback.className = 'text-danger';

        // Ne pas autoriser automatiquement une nouvelle tentative :
        // le serveur a peut-être terminé les opérations.

    }
}

/* SUPPRIMER UN MENU */

document.addEventListener('click', async function(event) {

    // Vérifier si le bouton Supprimer a été cliqué
    const bouton = event.target.closest('.btn-supprimer-menu');

    if (!bouton) {
        return;
    }

    // Éviter les doubles clics
    if (bouton.disabled) {
        return;
    }

    // Récupérer le menu concerné
    const menuId = bouton.dataset.menuId;
    const menuTitre = bouton.dataset.menuTitre;

    // Demander confirmation avant suppression
    const confirmation = confirm(
        'Voulez-vous vraiment supprimer le menu « ' +
        menuTitre +
        ' » ?\n\nCette action est irréversible.'
    );

    if (!confirmation) {
        return;
    }

    const feedback = document.getElementById(
        'gestion-menus-feedback'
    );

    // Désactiver le bouton pendant la requête
    bouton.disabled = true;

    feedback.textContent = 'Suppression du menu en cours...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer le jeton CSRF
        const reponseOptions = await fetch(
            '/api/options-menus-employe.php',
            {
                cache: 'no-store'
            }
        );

        const options = await reponseOptions.json();

        if (
            !reponseOptions.ok ||
            !options.success ||
            !options.csrf_token
        ) {
            throw new Error(
                'Impossible de récupérer le jeton de sécurité.'
            );
        }

        // Préparer les données à envoyer
        const donnees = new FormData();

        donnees.append('menu_id', menuId);
        donnees.append('csrf_token', options.csrf_token);

        // Envoyer la demande de suppression
        const reponse = await fetch(
            '/api/supprimer-menu.php',
            {
                method: 'POST',
                body: donnees
            }
        );

        const resultat = await reponse.json();

        // Vérifier la réponse du serveur
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message ||
                'Impossible de supprimer le menu.';

            feedback.className = 'mb-4 text-danger';

            bouton.disabled = false;

            return;
        }

        // Actualiser les menus
        await chargerMenusEmploye();

        // Afficher un message de confirmation
        feedback.textContent =
            'Le menu « ' + menuTitre +
            ' » a été supprimé avec succès.';

        feedback.className = 'mb-4 text-success';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer la suppression. ' +
            'Vérifiez si le menu apparaît toujours dans la liste ' +
            'avant de réessayer.';

        feedback.className = 'mb-4 text-danger';

        // Ne pas renvoyer automatiquement une suppression :
        // le serveur a peut-être déjà terminé l'opération.

    }

});

// Chargement initial
document.addEventListener('DOMContentLoaded', chargerMenusEmploye);

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', chargerMenusEmploye);