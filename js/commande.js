
/* PRÉREMPLIR LES COORDONNÉES DU CLIENT */

async function chargerCoordonneesCommande() {

    // Récupérer le formulaire de commande
    const formulaire = document.getElementById('form-commande');

    // Vérifier que nous sommes sur la page Commande
    if (!formulaire) {
        return;
    }

    // Récupérer les éléments utiles
    const feedback = document.getElementById('commande-feedback');
    const bouton = document.getElementById('commande-valider');

    // Désactiver la validation pendant le chargement
    bouton.disabled = true;

    feedback.textContent = 'Chargement de vos coordonnées...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Récupérer les coordonnées depuis PHP
        const reponse = await fetch('/api/user-account.php');

        const resultat = await reponse.json();

        // Vérifier que les informations ont été récupérées
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger vos coordonnées.';

            feedback.className = 'mt-3 text-danger';
            return;
        }

        // Préremplir les champs du formulaire
        formulaire.elements['prenom_client'].value = resultat.compte.prenom;
        formulaire.elements['nom_client'].value = resultat.compte.nom;
        formulaire.elements['email_client'].value = resultat.compte.email;
        formulaire.elements['telephone_client'].value = resultat.compte.telephone || '';
        formulaire.elements['adresse_livraison'].value = resultat.compte.adresse || '';
        formulaire.elements['csrf_token'].value = resultat.csrf_token;

        // Effacer le message de chargement
        feedback.textContent = '';

    } catch (erreur) {

        feedback.textContent = 'Impossible de charger vos coordonnées.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);
    }

}

/* CHARGEMENT DES MENUS POUR LA COMMANDE */

async function chargerMenusCommande() {

    const select = document.getElementById('commande-menu');

    // Vérifier que nous sommes sur la page Commande
    if (!select) {
        return;
    }

    const conditions = document.getElementById('commande-conditions');
    const convives = document.getElementById('commande-convives');

    try {

        // Récupérer les menus depuis PHP
        const reponse = await fetch('/api/menus-commande.php');

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            conditions.textContent = 'Impossible de charger les menus.';
            return;
        }

        const menus = resultat.menus;

        // Remplir la liste déroulante
        select.innerHTML = '<option value="">Choisissez un menu</option>';

        for (const menu of menus) {

            const option = document.createElement('option');

            option.value = menu.id;
            option.textContent = menu.titre;

            select.appendChild(option);
        }

        // Récupérer le menu sélectionné précédemment
        const menuEnAttente = sessionStorage.getItem('menuIdEnAttente');

        if (menuEnAttente) {
            select.value = menuEnAttente;
        }

        // Afficher les conditions du menu sélectionné
        function afficherConditionsMenu() {

            let menuChoisi = null;

            for (const menu of menus) {

                if (String(menu.id) === select.value) {
                    menuChoisi = menu;
                    break;
                }
            }

            if (!menuChoisi) {

                conditions.textContent = '';
                convives.min = 1;
                convives.value = '';

                sessionStorage.removeItem('menuIdEnAttente');

                return;
            }

            // Définir le nombre minimum de convives
            const minimum = Number(menuChoisi.nb_personnes_min);

            convives.min = minimum;
            convives.value = minimum;

            // Afficher les conditions du menu
            conditions.textContent =
                'Minimum : ' + minimum + ' personnes. ' +
                'Commande ' + menuChoisi.delai_commande_jours +
                ' jours à l’avance. ' +
                menuChoisi.stock_disponible +
                ' commandes disponibles.';
        }


        /* VÉRIFIER LE DÉLAI DE COMMANDE */

        function verifierDelaiCommande() {

            const dateChamp = document.getElementById('commande-date');
            const dateInfo = document.getElementById('commande-date-info');

            // Rechercher le menu sélectionné
            let menuChoisi = null;

            for (const menu of menus) {

                if (String(menu.id) === select.value) {
                    menuChoisi = menu;
                    break;
                }
            }

            // Aucun menu sélectionné
            if (!menuChoisi) {
                dateChamp.min = '';
                dateChamp.value = '';
                dateInfo.textContent = '';
                return;
            }

            // Récupérer le délai du menu
            const delai = Number(menuChoisi.delai_commande_jours);

            // Récupérer la date d'aujourd'hui
            const dateMinimum = new Date();

            dateMinimum.setHours(0, 0, 0, 0);

            // Ajouter le délai de commande
            dateMinimum.setDate(dateMinimum.getDate() + delai);

            // Formater la date pour le champ HTML
            const annee = dateMinimum.getFullYear();

            const mois = String(dateMinimum.getMonth() + 1).padStart(2, '0');

            const jour = String(dateMinimum.getDate()).padStart(2, '0');

            const dateFormatee = annee + '-' + mois + '-' + jour;

            // Définir la première date autorisée
            dateChamp.min = dateFormatee;

            // Effacer une date devenue invalide
            if (dateChamp.value && dateChamp.value < dateFormatee) {
                dateChamp.value = '';
            }

            // Informer le client
            dateInfo.textContent =
                'Première date de prestation possible : ' +
                dateMinimum.toLocaleDateString('fr-FR') + '.';
        }


        /* CALCUL DU PRIX DE LA COMMANDE */

        function calculerPrixCommande() {

            // Éléments du récapitulatif
            const prixMenu = document.getElementById('commande-prix-menu');
            const remise = document.getElementById('commande-remise');
            const livraison = document.getElementById('commande-livraison');
            const total = document.getElementById('commande-total');

            // Éléments de livraison
            const ville = document.getElementById('commande-ville');
            const distanceBloc = document.getElementById('commande-distance-bloc');
            const distanceChamp = document.getElementById('commande-distance');

            const villeLivraison = ville.value.trim().toLowerCase();

            // Vérifier si la livraison est hors Bordeaux
            const horsBordeaux =
                villeLivraison !== '' && villeLivraison !== 'bordeaux';

            // Afficher ou masquer le champ distance
            if (horsBordeaux) {

                distanceBloc.classList.remove('d-none');
                distanceChamp.required = true;

            } else {

                distanceBloc.classList.add('d-none');
                distanceChamp.required = false;
                distanceChamp.value = '';
            }

            // Rechercher le menu sélectionné
            let menuChoisi = null;

            for (const menu of menus) {

                if (String(menu.id) === select.value) {
                    menuChoisi = menu;
                    break;
                }
            }

            // Aucun menu sélectionné
            if (!menuChoisi) {

                prixMenu.textContent = '—';
                remise.textContent = '—';
                livraison.textContent = '—';
                total.textContent = '—';

                return;
            }

            // Informations du menu
            const prixPersonne = Number(menuChoisi.prix_personne);
            const minimum = Number(menuChoisi.nb_personnes_min);
            const nombreConvives = Number(convives.value);

            // Vérifier le nombre de convives
            if (
                !convives.value ||
                !Number.isInteger(nombreConvives) ||
                nombreConvives < minimum
            ) {

                prixMenu.textContent = '—';
                remise.textContent = '—';
                livraison.textContent = '—';
                total.textContent = '—';

                return;
            }

            // Calculer le prix du menu
            const prixTotalMenu = prixPersonne * nombreConvives;

            // Calculer la remise
            let montantRemise = 0;

            if (nombreConvives >= minimum + 5) {
                montantRemise = prixTotalMenu * 0.10;
            }

            // Afficher le prix et la remise
            prixMenu.textContent = prixTotalMenu.toFixed(2) + ' €';
            remise.textContent = '- ' + montantRemise.toFixed(2) + ' €';

            // Attendre que la ville soit renseignée
            if (villeLivraison === '') {

                livraison.textContent = '—';
                total.textContent = '—';

                return;
            }

            // Calculer les frais de livraison
            let fraisLivraison = 0;

            if (horsBordeaux) {

                // Vérifier la distance renseignée
                if (distanceChamp.value === '') {

                    livraison.textContent = '—';
                    total.textContent = '—';

                    return;
                }

                const distance = Number(distanceChamp.value);

                if (!Number.isFinite(distance) || distance <= 0) {

                    livraison.textContent = '—';
                    total.textContent = '—';

                    return;
                }

                // 5 € + 0,59 € par kilomètre
                fraisLivraison = 5 + (distance * 0.59);
            }

            // Calculer le total
            const prixFinal = prixTotalMenu - montantRemise + fraisLivraison;

            // Afficher les montants
            livraison.textContent = fraisLivraison.toFixed(2) + ' €';
            total.textContent = prixFinal.toFixed(2) + ' €';
        }


        // Mettre à jour le menu lorsque le client change de choix
        select.addEventListener('change', function() {

            if (select.value) {
                sessionStorage.setItem('menuIdEnAttente', select.value);
            } else {
                sessionStorage.removeItem('menuIdEnAttente');
            }

            afficherConditionsMenu();
            verifierDelaiCommande();
            calculerPrixCommande();
        });

        // Recalculer lorsque le nombre de convives change
        convives.addEventListener('input', calculerPrixCommande);


        // Recalculer lorsque la ville change
        const villeCommande = document.getElementById('commande-ville');
        villeCommande.addEventListener('input', function() {

            // Effacer l'ancienne distance
            document.getElementById('commande-distance').value = '';

            calculerPrixCommande();
        });

        // Recalculer lorsque la distance change
        const distanceCommande = document.getElementById('commande-distance');
        distanceCommande.addEventListener('input', calculerPrixCommande);


        // Afficher les informations dès le chargement
        afficherConditionsMenu();
        verifierDelaiCommande();
        calculerPrixCommande();
        

    } catch (erreur) {

        conditions.textContent = 'Impossible de charger les menus.';

        console.error(erreur);
    }
}

/* VÉRIFIER LE FORMULAIRE DE COMMANDE */

function verifierFormulaireCommande() {

    const formulaire = document.getElementById('form-commande');

    if (!formulaire) {
        return;
    }

    const bouton = document.getElementById('commande-valider');
    const total = document.getElementById('commande-total');

    // Vérifier les champs obligatoires
    const champsValides = formulaire.checkValidity();

    // Vérifier que le prix a été calculé
    const prixCalcule = total.textContent.trim() !== '—';

    // Vérifier que le jeton CSRF est présent
    const csrfPresent =
        formulaire.elements['csrf_token'].value !== '';

    // Vérifier que la commande n'est pas déjà enregistrée
    const commandeTerminee =
        formulaire.dataset.commandeEnregistree === 'oui';

    // Vérifier qu'aucun envoi n'est en cours
    const envoiEnCours =
        formulaire.dataset.envoiEnCours === 'oui';

    // Activer ou désactiver le bouton
    bouton.disabled =
        !champsValides ||
        !prixCalcule ||
        !csrfPresent ||
        commandeTerminee ||
        envoiEnCours;
}

// Vérifier lorsque le client saisit une information
document.addEventListener('input', function(event) {

    if (event.target.closest('#form-commande')) {
        verifierFormulaireCommande();
    }

});

// Vérifier lorsque le client change une sélection
document.addEventListener('change', function(event) {

    if (event.target.closest('#form-commande')) {
        verifierFormulaireCommande();
    }

});


// Chargement initial du site
document.addEventListener('DOMContentLoaded', chargerCoordonneesCommande);
document.addEventListener('DOMContentLoaded', chargerMenusCommande);

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', chargerCoordonneesCommande);
document.addEventListener('pageChargee', chargerMenusCommande);

// Chargement des coordonnées après une connexion
document.addEventListener('connexionReussie', chargerCoordonneesCommande);


/* ENREGISTRER LA COMMANDE */

document.addEventListener('submit', async function(event) {

    // Vérifier que le formulaire est celui de la commande
    if (event.target.id !== 'form-commande') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;
    const bouton = document.getElementById('commande-valider');
    const feedback = document.getElementById('commande-feedback');

    // Éviter un deuxième envoi
    if (
        formulaire.dataset.envoiEnCours === 'oui' ||
        formulaire.dataset.commandeEnregistree === 'oui'
    ) {
        return;
    }

    // Vérifier les champs avant l'envoi
    if (!formulaire.reportValidity()) {
        return;
    }

    // Préparer les données du formulaire
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'envoi
    formulaire.dataset.envoiEnCours = 'oui';
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement de votre commande...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les données à PHP
        const reponse = await fetch('/api/enregistrer-commande.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            if (resultat.email_envoye) {

                feedback.textContent =
                    'Votre commande n°' + resultat.commande_id +
                    ' a bien été enregistrée. ' +
                    'Un e-mail de confirmation a été envoyé.';

            } else {

                feedback.textContent =
                    'Votre commande n°' + resultat.commande_id +
                    ' a bien été enregistrée, mais l’e-mail ' +
                    'de confirmation n’a pas pu être envoyé.';
            }

            feedback.className = 'mt-3 text-success';

            // Mémoriser que la commande est terminée
            formulaire.dataset.commandeEnregistree = 'oui';

            // Supprimer le menu en attente
            sessionStorage.removeItem('menuIdEnAttente');

        } else {

            // Afficher le message d'erreur de PHP
            feedback.textContent =
                resultat.message || 'Impossible d’enregistrer la commande.';

            feedback.className = 'mt-3 text-danger';

        }

    } catch (erreur) {

        feedback.textContent =
            'Une erreur est survenue. Vérifiez si la commande ' +
            'a été enregistrée avant de réessayer.';

        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        formulaire.dataset.envoiEnCours = 'non';

        verifierFormulaireCommande();

    }

});
