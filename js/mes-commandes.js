/* AFFICHER LES COMMANDES DU CLIENT */

async function chargerMesCommandes() {

    const liste = document.getElementById('mes-commandes-liste');

    // Vérifier que nous sommes sur la page Mon compte
    if (!liste) {
        return;
    }

    const feedback = document.getElementById('mes-commandes-feedback');

    // Vider l'ancienne liste
    liste.innerHTML = '';

    feedback.textContent = 'Chargement de vos commandes...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les commandes depuis PHP
        const reponse = await fetch('/api/mes-commandes.php');

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger vos commandes.';

            feedback.className = 'mb-4 text-danger';
            return;
        }

        const commandes = resultat.commandes;

        // Aucune commande enregistrée
        if (commandes.length === 0) {

            feedback.textContent =
                'Vous n’avez pas encore passé de commande.';

            return;
        }

        // Effacer le message de chargement
        feedback.textContent = '';

        // Créer une carte pour chaque commande
        for (const commande of commandes) {

            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-lg-6';

            const carte = document.createElement('article');
            carte.className = 'card h-100';

            const contenu = document.createElement('div');
            contenu.className = 'card-body';

            // Numéro de commande
            const titre = document.createElement('h3');
            titre.className = 'h5 mb-3';
            titre.textContent = 'Commande n°' + commande.id;

            // Menu
            const menu = document.createElement('p');
            menu.textContent =
                'Menu : ' + (commande.menu_titre || 'Menu indisponible');

            // Date de prestation
            const date = document.createElement('p');
            const dateFormatee = new Date(
                commande.date_prestation + 'T12:00:00'
            ).toLocaleDateString('fr-FR');

            date.textContent = 'Date : ' + dateFormatee;

            // Nombre de convives
            const convives = document.createElement('p');
            convives.textContent =
                'Convives : ' + commande.nb_personnes;

            // Prix total
            const total = document.createElement('p');

            const prixFormate = Number(commande.total)
                .toLocaleString('fr-FR', {
                    style: 'currency',
                    currency: 'EUR'
                });

            total.textContent = 'Total : ' + prixFormate;

            // Statut
            const statut = document.createElement('p');

            statut.textContent =
                'Statut : ' +
                commande.statut_courant.replaceAll('_', ' ');

            // Définir la couleur selon le statut
            if (
                commande.statut_courant === 'accepté' ||
                commande.statut_courant === 'livré' ||
                commande.statut_courant === 'terminée'
            ) {
                statut.className = 'text-primary';
            } else {
                statut.className = 'text-secondary';
            }

            // Bouton pour consulter le détail
            const boutonDetail = document.createElement('button');

            boutonDetail.type = 'button';
            boutonDetail.className = 'btn btn-primary mt-3';

            boutonDetail.textContent = 'Voir le détail et le suivi';

            // Mémoriser l'identifiant de la commande
            boutonDetail.dataset.commandeId = commande.id;

            boutonDetail.classList.add('btn-detail-commande');

            // Ajouter les informations à la carte
            contenu.appendChild(titre);
            contenu.appendChild(menu);
            contenu.appendChild(date);
            contenu.appendChild(convives);
            contenu.appendChild(total);
            contenu.appendChild(statut);
            contenu.appendChild(boutonDetail);
            carte.appendChild(contenu);
            colonne.appendChild(carte);
            liste.appendChild(colonne);
        }

    } catch (erreur) {

        feedback.textContent = 'Impossible de charger vos commandes.';
        feedback.className = 'mb-4 text-danger';

        console.error(erreur);
    }
}


/* AFFICHER LE DÉTAIL D'UNE COMMANDE */

// Formater un montant en euros
function formaterPrixCommande(montant) {

    return Number(montant).toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    });
}

// Formater une date de prestation
function formaterDatePrestation(date) {

    return new Date(date + 'T12:00:00')
        .toLocaleDateString('fr-FR');
}

// Formater une date et une heure de suivi
function formaterDateSuivi(date) {

    return new Date(date.replace(' ', 'T'))
        .toLocaleString('fr-FR');
}

// Normaliser les noms des statuts
function normaliserStatut(statut) {

    return String(statut)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replaceAll(' ', '_');
}

// Afficher un nom de statut lisible
function afficherNomStatut(statut) {

    const noms = {
        'en_attente': 'Commande enregistrée',
        'accepte': 'Commande acceptée',
        'en_preparation': 'En préparation',
        'en_cours_de_livraison': 'En cours de livraison',
        'livre': 'Livrée',
        'en_attente_retour_materiel': 'En attente du retour de matériel',
        'terminee': 'Terminée',
        'annulee': 'Annulée'
    };

    const statutNormalise = normaliserStatut(statut);

    return noms[statutNormalise] || String(statut).replaceAll('_', ' ');
}


// Écouter les clics sur les boutons de détail
document.addEventListener('click', async function(event) {

    const bouton = event.target.closest('.btn-detail-commande');

    if (!bouton) {
        return;
    }

    const commandeId = bouton.dataset.commandeId;

    const modalElement = document.getElementById('modal-detail-commande');

    const feedback = document.getElementById('detail-commande-feedback');

    const contenu = document.getElementById('detail-commande-contenu');

    const suiviListe = document.getElementById('detail-suivi-liste');

    const suiviMessage = document.getElementById('detail-suivi-message');

    // Réinitialiser l'affichage
    contenu.classList.add('d-none');
    // Revenir à l'affichage du détail à chaque ouverture
    document.getElementById('form-modifier-commande')
        .classList.add('d-none');

    document.getElementById('detail-commande-actions')
        .classList.remove('d-none');
    suiviListe.innerHTML = '';
    suiviMessage.textContent = '';

    feedback.textContent = 'Chargement de la commande...';
    feedback.className = 'text-secondary';

    // Ouvrir la modale Bootstrap
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    modal.show();

    try {

        // Récupérer le détail auprès de PHP
        const reponse = await fetch(
            '/api/detail-commande.php?id=' + encodeURIComponent(commandeId)
        );

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger la commande.';

            feedback.className = 'text-danger';

            return;
        }

        const commande = resultat.commande;
        const historique = resultat.historique;


        // GÉRER LES ACTIONS DE LA COMMANDE

        const actions = document.getElementById('detail-commande-actions');

        // Vider les anciens boutons
        actions.innerHTML = '';

        // Vérifier le statut actuel
        const statutCommande = normaliserStatut(commande.statut_courant);

        // Autoriser les modifications uniquement avant acceptation
        if (statutCommande === 'en_attente') {

            // Bouton Modifier
            const boutonModifier = document.createElement('button');

            boutonModifier.type = 'button';
            boutonModifier.className = 'btn btn-primary';
            boutonModifier.textContent = 'Modifier';

            boutonModifier.dataset.commandeId = commande.id;
            boutonModifier.classList.add('btn-modifier-commande');

            // Bouton Annuler
            const boutonAnnuler = document.createElement('button');

            boutonAnnuler.type = 'button';
            boutonAnnuler.className = 'btn btn-outline-danger';
            boutonAnnuler.textContent = 'Annuler';

            boutonAnnuler.dataset.commandeId = commande.id;
            boutonAnnuler.classList.add('btn-annuler-commande');

            // Désactiver temporairement les actions
            boutonModifier.disabled = false;
            boutonAnnuler.disabled = false;

            // OUVRIR LE FORMULAIRE DE MODIFICATION

            boutonModifier.addEventListener('click', async function() {

                const formulaire = document.getElementById('form-modifier-commande');

                // Autoriser une nouvelle modification
                formulaire.dataset.modificationTerminee = 'non';
                formulaire.dataset.envoiEnCours = 'non';

                // Masquer le détail de la commande
                contenu.classList.add('d-none');

                // Masquer les boutons Modifier et Annuler
                actions.classList.add('d-none');

                // Afficher le formulaire de modification
                formulaire.classList.remove('d-none');

                // Réinitialiser le message
                document.getElementById('modif-commande-feedback').textContent = '';

                // Préremplir l'identifiant
                formulaire.elements['commande_id'].value = commande.id;

                // Afficher le menu sans permettre sa modification
                document.getElementById('modif-menu').textContent =
                    commande.menu_titre;

                // Préremplir les coordonnées
                formulaire.elements['prenom_client'].value = commande.prenom_client;
                formulaire.elements['nom_client'].value = commande.nom_client;
                formulaire.elements['email_client'].value = commande.email_client;
                formulaire.elements['telephone_client'].value = commande.telephone_client;

                // Préremplir la prestation
                formulaire.elements['adresse_livraison'].value =
                    commande.adresse_livraison;

                formulaire.elements['ville_livraison'].value =
                    commande.ville_livraison;

                formulaire.elements['date_prestation'].value =
                    commande.date_prestation;

                formulaire.elements['heure_livraison'].value =
                    String(commande.heure_livraison).slice(0, 5);

                formulaire.elements['nb_personnes'].value =
                    commande.nb_personnes;

                formulaire.elements['distance_livraison_km'].value =
                    commande.distance_livraison_km || 0;

                // Conserver les informations de la commande
                commandeEnModification = commande;

                // Vérifier la date
                verifierDateModification();

                // Recalculer le prix
                calculerModificationCommande();


                // Récupérer le jeton CSRF
                const boutonEnregistrer = document.getElementById('modif-enregistrer');
                const feedbackModification = document.getElementById('modif-commande-feedback');

                boutonEnregistrer.disabled = true;
                formulaire.elements['csrf_token'].value = '';

                feedbackModification.textContent = 'Chargement du formulaire...';

                try {

                    const reponse = await fetch('/api/user-account.php');

                    const resultat = await reponse.json();

                    if (!reponse.ok || !resultat.success || !resultat.csrf_token) {

                        feedbackModification.textContent =
                            'Impossible de préparer la modification.';

                        feedbackModification.className = 'mt-3 text-danger';
                        return;
                    }

                    // Ajouter le jeton au formulaire
                    formulaire.elements['csrf_token'].value = resultat.csrf_token;

                    feedbackModification.textContent = '';

                    // Vérifier si le formulaire est prêt
                    verifierModificationCommande();

                } catch (erreur) {

                    feedbackModification.textContent =
                        'Impossible de préparer la modification.';

                    feedbackModification.className = 'mt-3 text-danger';

                    console.error(erreur);
                }

                // Afficher les montants actuellement enregistrés
                document.getElementById('modif-prix-menu').textContent =
                    formaterPrixCommande(commande.prix_menu);

                document.getElementById('modif-remise').textContent =
                    '- ' + formaterPrixCommande(commande.remise);

                document.getElementById('modif-livraison').textContent =
                    formaterPrixCommande(commande.frais_livraison);

                document.getElementById('modif-total').textContent =
                    formaterPrixCommande(commande.total);

            });


            // ANNULER UNE COMMANDE //

            boutonAnnuler.addEventListener('click', async function() {

                // Demander confirmation au client
                const confirmation = confirm(
                    'Voulez-vous vraiment annuler cette commande ?'
                );

                if (!confirmation) {
                    return;
                }

                const feedback = document.getElementById('detail-commande-feedback');

                // Désactiver les actions pendant le traitement
                boutonAnnuler.disabled = true;
                boutonModifier.disabled = true;

                feedback.textContent = 'Annulation en cours...';
                feedback.className = 'mb-3 text-secondary';

                let annulationReussie = false;

                try {

                    // Récupérer le jeton CSRF
                    const reponseCompte = await fetch('/api/user-account.php');

                    const resultatCompte = await reponseCompte.json();

                    if (!reponseCompte.ok || !resultatCompte.success) {
                        throw new Error('Impossible de vérifier la session.');
                    }

                    // Préparer les données
                    const donnees = new FormData();

                    donnees.append('commande_id', commande.id);
                    donnees.append('csrf_token', resultatCompte.csrf_token);

                    // Envoyer la demande d'annulation
                    const reponse = await fetch('/api/annuler-commande.php', {
                        method: 'POST',
                        body: donnees
                    });

                    const resultat = await reponse.json();

                    if (!reponse.ok || !resultat.success) {

                        feedback.textContent =
                            resultat.message || 'Impossible d’annuler la commande.';

                        feedback.className = 'mb-3 text-danger';

                        // Ne plus proposer les actions si le statut a changé
                        if (reponse.status !== 409 && reponse.status !== 404) {
                            boutonAnnuler.disabled = false;
                            boutonModifier.disabled = false;
                        }

                        return;
                    }

                    annulationReussie = true;

                    // Actualiser la liste des commandes
                    await chargerMesCommandes();

                    // Retrouver le bouton de détail de la commande actualisée
                    const boutonsDetail = document.querySelectorAll(
                        '.btn-detail-commande'
                    );

                    for (const bouton of boutonsDetail) {

                        if (bouton.dataset.commandeId === String(commande.id)) {

                            // Recharger le détail et le suivi depuis MySQL
                            bouton.click();

                            break;
                        }
                    }

                    // Afficher la confirmation dans l'espace client
                    const messageListe = document.getElementById(
                        'mes-commandes-feedback'
                    );

                    messageListe.textContent =
                        'La commande n°' + commande.id +
                        ' a bien été annulée.';

                    messageListe.className = 'mb-4 text-success';

                } catch (erreur) {

                    if (annulationReussie) {

                        feedback.textContent =
                            'La commande a été annulée, mais l’affichage ' +
                            'n’a pas pu être actualisé.';

                    } else {

                        feedback.textContent =
                            'Une erreur est survenue. Vérifiez le statut ' +
                            'de la commande avant de réessayer.';

                    }

                    feedback.className = 'mb-3 text-danger';

                    console.error(erreur);
                }

            });


            // Ajouter les boutons dans la modale
            actions.appendChild(boutonModifier);
            actions.appendChild(boutonAnnuler);
        }


        // Numéro de la commande
        document.getElementById('modal-detail-commande-titre')
            .textContent = 'Commande n°' + commande.id;

        // Informations de prestation
        document.getElementById('detail-menu')
            .textContent = commande.menu_titre || 'Menu indisponible';

        document.getElementById('detail-date')
            .textContent = formaterDatePrestation(commande.date_prestation);

        document.getElementById('detail-heure')
            .textContent = commande.heure_livraison;

        document.getElementById('detail-convives')
            .textContent = commande.nb_personnes + ' personnes';

        document.getElementById('detail-adresse')
            .textContent =
                commande.adresse_livraison + ', ' + commande.ville_livraison;

        document.getElementById('detail-statut')
            .textContent = afficherNomStatut(commande.statut_courant);

        // Détail du prix
        document.getElementById('detail-prix-menu')
            .textContent = formaterPrixCommande(commande.prix_menu);

        document.getElementById('detail-remise')
            .textContent = '- ' + formaterPrixCommande(commande.remise);

        document.getElementById('detail-livraison')
            .textContent = formaterPrixCommande(commande.frais_livraison);

        document.getElementById('detail-total')
            .textContent = formaterPrixCommande(commande.total);
   
        // SUIVI DE LA COMMANDE

        const statutActuel = normaliserStatut(commande.statut_courant);

        // Réinitialiser la liste du suivi
        suiviListe.innerHTML = '';

        // La commande est encore en attente
        if (statutActuel === 'en_attente') {

            suiviMessage.textContent =
                'Le suivi sera disponible après acceptation de votre commande.';

        } else {

            // Afficher toutes les étapes enregistrées dans MySQL
            for (const etape of historique) {

                const ligne = document.createElement('li');

                ligne.className = 'list-group-item py-3';

                // Nom du statut
                const titre = document.createElement('strong');

                titre.textContent = afficherNomStatut(etape.statut);

                // Date et heure de modification
                const date = document.createElement('div');

                date.className = 'text-secondary small';

                date.textContent = formaterDateSuivi(etape.date_modification);

                // Ajouter les informations
                ligne.appendChild(titre);
                ligne.appendChild(date);

                suiviListe.appendChild(ligne);
            }

            // Aucun historique enregistré
            if (historique.length === 0) {

                suiviMessage.textContent =
                    'Aucun historique disponible pour cette commande.';
            }
        }

        // Afficher le contenu
        feedback.textContent = '';
        contenu.classList.remove('d-none');

    } catch (erreur) {

        feedback.textContent = 'Impossible de charger la commande.';
        feedback.className = 'text-danger';

        console.error(erreur);

    }

});


/* RETOUR AU DÉTAIL DE LA COMMANDE */

document.addEventListener('click', function(event) {

    if (event.target.id !== 'modif-retour-detail') {
        return;
    }

    // Masquer le formulaire
    document.getElementById('form-modifier-commande')
        .classList.add('d-none');

    // Réafficher le détail
    document.getElementById('detail-commande-contenu')
        .classList.remove('d-none');

    // Réafficher les boutons de la modale
    document.getElementById('detail-commande-actions')
        .classList.remove('d-none');

});

let commandeEnModification = null;


/* RECALCULER LE PRIX D'UNE COMMANDE MODIFIÉE */

function calculerModificationCommande() {

    const commande = commandeEnModification;

    if (!commande) {
        return;
    }

    const formulaire = document.getElementById('form-modifier-commande');

    if (!formulaire || formulaire.classList.contains('d-none')) {
        return;
    }

    // Récupérer les champs
    const convives = document.getElementById('modif-convives');
    const ville = document.getElementById('modif-ville');
    const distance = document.getElementById('modif-distance');
    const distanceBloc = document.getElementById('modif-distance-bloc');

    // Récupérer les éléments du récapitulatif
    const prixMenu = document.getElementById('modif-prix-menu');
    const remise = document.getElementById('modif-remise');
    const livraison = document.getElementById('modif-livraison');
    const total = document.getElementById('modif-total');

    // Informations du menu
    const prixPersonne = Number(commande.prix_personne);
    const minimum = Number(commande.nb_personnes_min);
    const nombreConvives = Number(convives.value);

    // Afficher le minimum autorisé
    convives.min = minimum;

    document.getElementById('modif-convives-info').textContent =
        'Minimum : ' + minimum + ' personnes.';

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
    const montantMenu = prixPersonne * nombreConvives;

    // Calculer la remise
    let montantRemise = 0;

    if (nombreConvives >= minimum + 5) {
        montantRemise = montantMenu * 0.10;
    }

    // Déterminer les frais de livraison
    let fraisLivraison = 0;

    const villeLivraison = ville.value.trim().toLowerCase();

    const horsBordeaux =
        villeLivraison !== '' && villeLivraison !== 'bordeaux';

    // Afficher ou masquer le champ distance
    if (horsBordeaux) {
        distanceBloc.classList.remove('d-none');
        distance.required = true;
    } else {
        distanceBloc.classList.add('d-none');
        distance.required = false;
    }

    // Calculer la livraison hors Bordeaux
    if (horsBordeaux && distance.value !== '') {

        const kilometres = Number(distance.value);

        if (Number.isFinite(kilometres) && kilometres > 0) {
            fraisLivraison = 5 + (kilometres * 0.59);
        }
    }

    // Afficher le prix du menu et la remise
    prixMenu.textContent = formaterPrixCommande(montantMenu);

    remise.textContent =
        '- ' + formaterPrixCommande(montantRemise);

    // Attendre une ville et une distance valide
    if (
        villeLivraison === '' ||
        (horsBordeaux && (
            distance.value === '' ||
            !Number.isFinite(Number(distance.value)) ||
            Number(distance.value) <= 0
        ))
    ) {
        livraison.textContent = '—';
        total.textContent = '—';
        return;
    }

    // Calculer le nouveau total
    const nouveauTotal =
        montantMenu - montantRemise + fraisLivraison;

    livraison.textContent = formaterPrixCommande(fraisLivraison);
    total.textContent = formaterPrixCommande(nouveauTotal);
}


/* VÉRIFIER LE DÉLAI DE MODIFICATION */

function verifierDateModification() {

    if (!commandeEnModification) {
        return;
    }

    const dateChamp = document.getElementById('modif-date');
    const dateInfo = document.getElementById('modif-date-info');

    const delai = Number(
        commandeEnModification.delai_commande_jours
    );

    // Calculer la première date autorisée
    const dateMinimum = new Date();

    dateMinimum.setHours(0, 0, 0, 0);
    dateMinimum.setDate(dateMinimum.getDate() + delai);

    const annee = dateMinimum.getFullYear();

    const mois = String(
        dateMinimum.getMonth() + 1
    ).padStart(2, '0');

    const jour = String(
        dateMinimum.getDate()
    ).padStart(2, '0');

    const dateFormatee = annee + '-' + mois + '-' + jour;

    dateChamp.min = dateFormatee;

    // Afficher la première date possible
    dateInfo.textContent =
        'Première date possible : ' +
        dateMinimum.toLocaleDateString('fr-FR');

    // Signaler une date invalide
    if (dateChamp.value && dateChamp.value < dateFormatee) {
        dateInfo.className = 'form-text text-danger';
    } else {
        dateInfo.className = 'form-text';
    }
}

/* VÉRIFIER LE FORMULAIRE DE MODIFICATION */

function verifierModificationCommande() {

    const formulaire = document.getElementById('form-modifier-commande');

    if (!formulaire) {
        return;
    }

    const bouton = document.getElementById('modif-enregistrer');
    const total = document.getElementById('modif-total');

    // Vérifier les champs obligatoires
    const champsValides = formulaire.checkValidity();

    // Vérifier que le total a été calculé
    const prixCalcule = total.textContent.trim() !== '—';

    // Vérifier que le jeton CSRF est présent
    const csrfPresent = formulaire.elements['csrf_token'].value !== '';

    // Vérifier si un envoi est en cours
    const envoiEnCours =
        formulaire.dataset.envoiEnCours === 'oui';

    // Vérifier si les modifications ont déjà été enregistrées
    const modificationTerminee =
        formulaire.dataset.modificationTerminee === 'oui';

    bouton.disabled =
        !champsValides ||
        !prixCalcule ||
        !csrfPresent ||
        envoiEnCours ||
        modificationTerminee;
}

// Vérifier les modifications pendant la saisie

function actualiserFormulaireModification(event) {

    if (!event.target.closest('#form-modifier-commande')) {
        return;
    }

    calculerModificationCommande();
    verifierDateModification();
    verifierModificationCommande();
}

document.addEventListener('input', actualiserFormulaireModification);
document.addEventListener('change', actualiserFormulaireModification);


/* ENREGISTRER LES MODIFICATIONS */

document.addEventListener('submit', async function(event) {

    if (event.target.id !== 'form-modifier-commande') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;
    const bouton = document.getElementById('modif-enregistrer');
    const feedback = document.getElementById('modif-commande-feedback');

    // Empêcher plusieurs envois
    if (
        formulaire.dataset.envoiEnCours === 'oui' ||
        formulaire.dataset.modificationTerminee === 'oui'
    ) {
        return;
    }

    // Vérifier les champs
    if (!formulaire.reportValidity()) {
        return;
    }

    // Préparer les données
    const donnees = new FormData(formulaire);

    formulaire.dataset.envoiEnCours = 'oui';
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement des modifications...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les données à PHP
        const reponse = await fetch('/api/modifier-commande.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de modifier la commande.';

            feedback.className = 'mt-3 text-danger';
            return;
        }

        // L'enregistrement a réussi
        formulaire.dataset.modificationTerminee = 'oui';

        feedback.textContent =
            'Votre commande a bien été modifiée.';

        feedback.className = 'mt-3 text-success';

        // Actualiser les informations affichées
        await actualiserCommandeModifiee(resultat.commande_id);

    } catch (erreur) {

        if (formulaire.dataset.modificationTerminee === 'oui') {

            feedback.textContent =
                'La commande a été modifiée, mais l’affichage ' +
                'n’a pas pu être actualisé.';

        } else {

            feedback.textContent =
                'Une erreur est survenue. Vérifiez la commande ' +
                'avant de réessayer.';
        }

        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        formulaire.dataset.envoiEnCours = 'non';

        verifierModificationCommande();
    }

});


/* ACTUALISER UNE COMMANDE APRÈS MODIFICATION */

async function actualiserCommandeModifiee(commandeId) {

    // Récupérer les nouvelles informations depuis PHP
    const reponse = await fetch(
        '/api/detail-commande.php?id=' + encodeURIComponent(commandeId),
        { cache: 'no-store' }
    );

    const resultat = await reponse.json();

    if (!reponse.ok || !resultat.success) {
        throw new Error('Impossible de recharger la commande.');
    }

    const commande = resultat.commande;

    // Actualiser les informations de prestation
    document.getElementById('detail-date').textContent =
        formaterDatePrestation(commande.date_prestation);

    document.getElementById('detail-heure').textContent =
        commande.heure_livraison;

    document.getElementById('detail-convives').textContent =
        commande.nb_personnes + ' personnes';

    document.getElementById('detail-adresse').textContent =
        commande.adresse_livraison + ', ' + commande.ville_livraison;

    // Actualiser les prix
    document.getElementById('detail-prix-menu').textContent =
        formaterPrixCommande(commande.prix_menu);

    document.getElementById('detail-remise').textContent =
        '- ' + formaterPrixCommande(commande.remise);

    document.getElementById('detail-livraison').textContent =
        formaterPrixCommande(commande.frais_livraison);

    document.getElementById('detail-total').textContent =
        formaterPrixCommande(commande.total);

    // Actualiser les cartes de commandes
    await chargerMesCommandes();

    // Masquer le formulaire de modification
    document.getElementById('form-modifier-commande')
        .classList.add('d-none');

    // Réafficher le détail
    document.getElementById('detail-commande-contenu')
        .classList.remove('d-none');

    // Réafficher les actions
    document.getElementById('detail-commande-actions')
        .classList.remove('d-none');

    // Afficher la confirmation
    const feedback = document.getElementById('detail-commande-feedback');

    feedback.textContent = 'Votre commande a bien été modifiée.';
    feedback.className = 'mb-3 text-success';
}



// Chargement initial
document.addEventListener('DOMContentLoaded', chargerMesCommandes);

// Chargement avec notre routeur
document.addEventListener('pageChargee', chargerMesCommandes);
