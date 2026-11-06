/* AFFICHER LES COMMANDES DANS L'ESPACE EMPLOYÉ */

async function chargerCommandesEmploye() {

    const liste = document.getElementById('employe-commandes-liste');

    // Vérifier que nous sommes sur la page Employé
    if (!liste) {
        return;
    }

    const feedback = document.getElementById('employe-commandes-feedback');

    // Vider la liste avant le chargement
    liste.innerHTML = '';

    feedback.textContent = 'Chargement des commandes...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Récupérer les commandes depuis PHP
        const reponse = await fetch('/api/commandes-employe.php', {
            cache: 'no-store'
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger les commandes.';

            feedback.className = 'mb-4 text-danger';
            return;
        }

        const commandes = resultat.commandes;

        // METTRE À JOUR LE TABLEAU DE BORD

        let nombreAttente = 0;
        let nombrePreparation = 0;

        for (const commande of commandes) {

            if (commande.statut_courant === 'en_attente') {
                nombreAttente++;
            }

            if (commande.statut_courant === 'en_preparation') {
                nombrePreparation++;
            }
        }

        document.getElementById('employe-commandes-attente')
            .textContent = nombreAttente;

        document.getElementById('employe-commandes-preparation')
            .textContent = nombrePreparation;

        // Aucune commande
        if (commandes.length === 0) {

            feedback.textContent = 'Aucune commande enregistrée.';
            return;
        }

        feedback.textContent = '';

        // CRÉER LES CARTES DE COMMANDES

        for (const commande of commandes) {

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-lg-6';

            // Identifier les cartes de commande
            colonne.classList.add('commande-employe');

            // Conserver les informations utiles aux filtres
            colonne.dataset.statut = normaliserTexteEmploye(
                commande.statut_courant
            );

            colonne.dataset.client = normaliserTexteEmploye(
                commande.prenom_client + ' ' +
                commande.nom_client + ' ' +
                commande.email_client
            );

            colonne.dataset.numero = commande.id;

            // Carte
            const carte = document.createElement('article');
            carte.className = 'card h-100';

            const contenu = document.createElement('div');
            contenu.className = 'card-body';

            // Numéro de commande
            const titre = document.createElement('h3');
            titre.className = 'h5 mb-3';
            titre.textContent = 'Commande n°' + commande.id;

            // Client
            const client = document.createElement('p');
            client.textContent =
                'Client : ' +
                commande.prenom_client + ' ' +
                commande.nom_client;

            // Menu
            const menu = document.createElement('p');
            menu.textContent =
                'Menu : ' + (commande.menu_titre || 'Menu indisponible');

            // Date de prestation
            const date = document.createElement('p');

            const dateFormatee = new Date(
                commande.date_prestation + 'T12:00:00'
            ).toLocaleDateString('fr-FR');

            date.textContent = 'Prestation : ' + dateFormatee;

            // Nombre de convives
            const convives = document.createElement('p');
            convives.textContent =
                'Convives : ' + commande.nb_personnes;

            
            // Statut
            const statut = document.createElement('p');

            statut.textContent =
                'Statut : ' +
                commande.statut_courant.replaceAll('_', ' ');

            // Définir la couleur selon le statut
            if (
                commande.statut_courant === 'en_attente' ||
                commande.statut_courant === 'en_attente_retour_materiel'
            ) {
                statut.classList.add('text-secondary');
            } else {
                statut.classList.add('text-primary');
            }

            // Prix total
            const total = document.createElement('p');
            total.className = 'fw-semibold';

            const prixFormate = Number(commande.total)
                .toLocaleString('fr-FR', {
                    style: 'currency',
                    currency: 'EUR'
                });

            total.textContent = 'Total : ' + prixFormate;

            // Bouton pour consulter la commande
            const boutonDetail = document.createElement('button');

            boutonDetail.type = 'button';
            boutonDetail.className = 'btn btn-primary mt-3';

            boutonDetail.textContent = 'Voir le détail';

            // Conserver le numéro de commande
            boutonDetail.dataset.commandeId = commande.id;

            boutonDetail.classList.add('btn-detail-employe');

            // Ajouter les éléments à la carte
            contenu.appendChild(titre);
            contenu.appendChild(client);
            contenu.appendChild(menu);
            contenu.appendChild(date);
            contenu.appendChild(convives);
            contenu.appendChild(statut);
            contenu.appendChild(total);
            contenu.appendChild(boutonDetail);

            carte.appendChild(contenu);
            colonne.appendChild(carte);
            liste.appendChild(colonne);
        }
        // Appliquer les filtres après le chargement des commandes
        filtrerCommandesEmploye();

    } catch (erreur) {

        feedback.textContent = 'Impossible de charger les commandes.';
        feedback.className = 'mb-4 text-danger';

        console.error(erreur);
    }
}


/* NORMALISER LE TEXTE POUR LA RECHERCHE */

function normaliserTexteEmploye(texte) {

    return String(texte)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}


/* FILTRER LES COMMANDES */

function filtrerCommandesEmploye() {

    const liste = document.getElementById('employe-commandes-liste');

    if (!liste) {
        return;
    }

    // Récupérer les filtres
    const filtreStatut = document.getElementById('employe-filtre-statut');

    const champRecherche = document.getElementById(
        'employe-recherche-commande'
    );

    const resultat = document.getElementById(
        'employe-filtres-resultat'
    );

    // Récupérer les valeurs choisies
    const statutRecherche = filtreStatut.value;

    const recherche = normaliserTexteEmploye(
        champRecherche.value.trim()
    );

    // Récupérer toutes les cartes
    const cartes = liste.querySelectorAll('.commande-employe');

    let nombreResultats = 0;

    for (const carte of cartes) {

        // Vérifier le statut
        const statutCorrespond =
            statutRecherche === 'tous' ||
            carte.dataset.statut === statutRecherche;

        // Vérifier le nom, l'e-mail ou le numéro
        const rechercheCorrespond =
            carte.dataset.client.includes(recherche) ||
            carte.dataset.numero.includes(recherche);

        // Afficher uniquement les cartes correspondantes
        if (statutCorrespond && rechercheCorrespond) {

            carte.classList.remove('d-none');

            nombreResultats++;

        } else {
            carte.classList.add('d-none');
        }
    }

    // Afficher le nombre de résultats
    resultat.textContent =
        nombreResultats + ' commande(s) trouvée(s).';

    if (nombreResultats === 0) {

        resultat.textContent =
            'Aucune commande ne correspond à votre recherche.';
    }
}


/* AFFICHER LE DÉTAIL D'UNE COMMANDE */

// Formater un prix
function formaterPrixEmploye(montant) {

    return Number(montant).toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    });
}

// Formater un statut
function formaterStatutEmploye(statut) {

    const noms = {
        'en_attente': 'En attente',
        'accepte': 'Acceptée',
        'en_preparation': 'En préparation',
        'en_cours_de_livraison': 'En cours de livraison',
        'livre': 'Livrée',
        'en_attente_retour_materiel': 'En attente du retour de matériel',
        'terminee': 'Terminée',
        'annulee': 'Annulée'
    };

    const valeur = normaliserTexteEmploye(statut);

    return noms[valeur] || String(statut).replaceAll('_', ' ');
}

// Détecter les clics sur Voir le détail
document.addEventListener('click', async function(event) {

    const bouton = event.target.closest('.btn-detail-employe');

    if (!bouton) {
        return;
    }

    const commandeId = bouton.dataset.commandeId;

    const modalElement = document.getElementById(
        'modal-commande-employe'
    );

    const contenu = document.getElementById(
        'employe-detail-contenu'
    );

    const feedback = document.getElementById(
        'employe-detail-feedback'
    );

    const historiqueListe = document.getElementById(
        'employe-detail-historique'
    );

    // Réinitialiser l'affichage
    contenu.classList.add('d-none');
    historiqueListe.innerHTML = '';

    // Masquer les anciennes actions de statut
    document.getElementById('employe-statut-section')
        .classList.add('d-none');

    // Masquer l'ancien formulaire de contact
    document.getElementById('form-contact-commande')
        .classList.add('d-none');

    // Réafficher l'emplacement des actions
    const zoneActions = document.getElementById('employe-detail-actions');

    zoneActions.classList.remove('d-none');
    zoneActions.innerHTML = '';

    feedback.textContent = 'Chargement de la commande...';
    feedback.className = 'text-secondary';

    // Ouvrir la modale
    const modale = bootstrap.Modal.getOrCreateInstance(modalElement);

    modale.show();

    try {

        // Récupérer le détail depuis PHP
        const reponse = await fetch(
            '/api/detail-commande-employe.php?id=' +
            encodeURIComponent(commandeId),
            { cache: 'no-store' }
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

        // Conserver les informations nécessaires à la modification
        const boutonStatut = document.getElementById('employe-enregistrer-statut');

        boutonStatut.dataset.commandeId = commande.id;
        boutonStatut.dataset.csrfToken = resultat.csrf_token;

        // Mémoriser le jeton CSRF pour le formulaire de contact
        const formulaireContact = document.getElementById('form-contact-commande');

        formulaireContact.dataset.csrfToken = resultat.csrf_token;

        // Afficher l'historique des contacts
        afficherHistoriqueContacts(resultat.contacts);

        /* ACTIONS SUR UNE COMMANDE */

        // Récupérer l'emplacement des boutons
        const actions = document.getElementById('employe-detail-actions');

        // Récupérer le statut actuel
        const statutCommande = normaliserTexteEmploye(
            commande.statut_courant
        );

        // Vérifier si la commande est encore modifiable
        if (
            statutCommande !== 'terminee' &&
            statutCommande !== 'annule' &&
            statutCommande !== 'annulee'
        ) {

            // BOUTON MODIFIER
            const boutonModifier = document.createElement('button');

            boutonModifier.type = 'button';
            boutonModifier.className = 'btn btn-primary';
            boutonModifier.textContent = 'Modifier';

            boutonModifier.addEventListener('click', function() {

                ouvrirContactCommande(commande.id, 'modification');

            });

            // BOUTON ANNULER
            const boutonAnnuler = document.createElement('button');

            boutonAnnuler.type = 'button';
            boutonAnnuler.className = 'btn btn-outline-danger';
            boutonAnnuler.textContent = 'Annuler';

            boutonAnnuler.addEventListener('click', function() {

                ouvrirContactCommande(commande.id, 'annulation');

            });

            // Ajouter les boutons dans la modale
            actions.appendChild(boutonModifier);
            actions.appendChild(boutonAnnuler);
        }

        // PRÉPARER LE CHANGEMENT DE STATUT

        const sectionStatut = document.getElementById(
            'employe-statut-section'
        );

        const selectStatut = document.getElementById(
            'employe-nouveau-statut'
        );

        const boutonEnregistrer = document.getElementById(
            'employe-enregistrer-statut'
        );

        // Réinitialiser les éléments
        sectionStatut.classList.add('d-none');

        selectStatut.innerHTML =
            '<option value="">Choisissez la prochaine étape</option>';

        boutonEnregistrer.disabled = true;

        document.getElementById('employe-statut-feedback')
            .textContent = '';

        // Récupérer le statut actuel
        const statutActuel = normaliserTexteEmploye(
            commande.statut_courant
        );

        // Définir les transitions possibles
        const transitions = {

            'en_attente': [
                { valeur: 'accepte', libelle: 'Acceptée' }
            ],

            'accepte': [
                { valeur: 'en_preparation', libelle: 'En préparation' }
            ],

            'en_preparation': [
                {
                    valeur: 'en_cours_de_livraison',
                    libelle: 'En cours de livraison'
                }
            ],

            'en_cours_de_livraison': [
                { valeur: 'livre', libelle: 'Livrée' }
            ],

            'livre': [
                {
                    valeur: 'terminee',
                    libelle: 'Terminée (aucun matériel prêté)'
                },
                {
                    valeur: 'en_attente_retour_materiel',
                    libelle: 'En attente du retour du matériel'
                }
            ]
        };

        // Récupérer les prochaines étapes
        const prochainesEtapes = transitions[statutActuel];

        // Afficher les choix autorisés
        if (prochainesEtapes) {

            document.getElementById('employe-statut-actuel')
                .textContent = formaterStatutEmploye(
                    commande.statut_courant
                );

            for (const etape of prochainesEtapes) {

                const option = document.createElement('option');

                option.value = etape.valeur;
                option.textContent = etape.libelle;

                selectStatut.appendChild(option);
            }

            sectionStatut.classList.remove('d-none');
        }

        // NUMÉRO
        document.getElementById('modal-commande-employe-titre')
            .textContent = 'Commande n°' + commande.id;

        // CLIENT
        document.getElementById('employe-detail-client')
            .textContent = commande.prenom_client + ' ' + commande.nom_client;

        document.getElementById('employe-detail-email')
            .textContent = commande.email_client;

        document.getElementById('employe-detail-telephone')
            .textContent = commande.telephone_client;

        // PRESTATION
        document.getElementById('employe-detail-menu')
            .textContent = commande.menu_titre || 'Menu indisponible';

        document.getElementById('employe-detail-date')
            .textContent = new Date(
                commande.date_prestation + 'T12:00:00'
            ).toLocaleDateString('fr-FR');

        document.getElementById('employe-detail-heure')
            .textContent = commande.heure_livraison;

        document.getElementById('employe-detail-convives')
            .textContent = commande.nb_personnes + ' personnes';

        document.getElementById('employe-detail-adresse')
            .textContent = commande.adresse_livraison +
                ', ' + commande.ville_livraison;

        document.getElementById('employe-detail-statut')
            .textContent = formaterStatutEmploye(commande.statut_courant);

        // PRIX
        document.getElementById('employe-detail-prix-menu')
            .textContent = formaterPrixEmploye(commande.prix_menu);

        document.getElementById('employe-detail-remise')
            .textContent = '- ' + formaterPrixEmploye(commande.remise);

        document.getElementById('employe-detail-livraison')
            .textContent = formaterPrixEmploye(commande.frais_livraison);

        document.getElementById('employe-detail-total')
            .textContent = formaterPrixEmploye(commande.total);

        // HISTORIQUE
        for (const etape of historique) {

            const ligne = document.createElement('li');
            ligne.className = 'list-group-item py-3';

            const statut = document.createElement('strong');

            statut.textContent = formaterStatutEmploye(etape.statut);

            const date = document.createElement('div');
            date.className = 'text-secondary small';

            date.textContent = new Date(
                etape.date_modification.replace(' ', 'T')
            ).toLocaleString('fr-FR');

            ligne.appendChild(statut);
            ligne.appendChild(date);

            // Afficher l'auteur lorsqu'il est renseigné
            if (etape.prenom && etape.nom) {

                const auteur = document.createElement('div');
                auteur.className = 'text-secondary small';

                auteur.textContent =
                    'Par ' + etape.prenom + ' ' + etape.nom;

                ligne.appendChild(auteur);
            }

            historiqueListe.appendChild(ligne);
        }

        if (historique.length === 0) {

            const message = document.createElement('li');
            message.className = 'list-group-item text-secondary';

            message.textContent = 'Aucun historique disponible.';

            historiqueListe.appendChild(message);
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

// Chargement initial
document.addEventListener('DOMContentLoaded', chargerCommandesEmploye);

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', chargerCommandesEmploye);

/* ÉCOUTEURS DES FILTRES */

// Filtrer lors d'un changement de statut
document.addEventListener('change', function(event) {

    if (event.target.id === 'employe-filtre-statut') {
        filtrerCommandesEmploye();
    }

});

// Filtrer pendant la recherche
document.addEventListener('input', function(event) {

    if (event.target.id === 'employe-recherche-commande') {
        filtrerCommandesEmploye();
    }

});

// Activer le bouton lorsqu'un nouveau statut est sélectionné
document.addEventListener('change', function(event) {

    if (event.target.id !== 'employe-nouveau-statut') {
        return;
    }

    const bouton = document.getElementById('employe-enregistrer-statut');

    bouton.disabled = event.target.value === '';

});


/* ENREGISTRER LE NOUVEAU STATUT */

document.addEventListener('click', async function(event) {

    if (event.target.id !== 'employe-enregistrer-statut') {
        return;
    }

    const bouton = event.target;

    const select = document.getElementById('employe-nouveau-statut');

    const feedback = document.getElementById('employe-statut-feedback');

    // Récupérer les informations
    const commandeId = bouton.dataset.commandeId;
    const nouveauStatut = select.value;
    const csrfToken = bouton.dataset.csrfToken;

    if (!commandeId || !nouveauStatut || !csrfToken) {
        return;
    }

    // Désactiver le bouton pendant l'enregistrement
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement en cours...';
    feedback.className = 'mt-3 text-secondary';

    // Préparer les données
    const donnees = new FormData();

    donnees.append('commande_id', commandeId);
    donnees.append('nouveau_statut', nouveauStatut);
    donnees.append('csrf_token', csrfToken);

    try {

        // Envoyer les informations à PHP
        const reponse = await fetch('/api/modifier-statut-commande.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de modifier le statut.';

            feedback.className = 'mt-3 text-danger';

            bouton.disabled = false;

            return;
        }

        // Confirmation
        feedback.textContent = resultat.message;
        feedback.className = 'mt-3 text-success';

        // Actualiser les cartes et les indicateurs
        await chargerCommandesEmploye();

        // Retrouver la nouvelle carte de cette commande
        const boutonsDetail = document.querySelectorAll(
            '.btn-detail-employe'
        );

        for (const boutonDetail of boutonsDetail) {

            if (boutonDetail.dataset.commandeId === commandeId) {

                // Recharger les informations dans la modale
                boutonDetail.click();

                break;
            }
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de modifier le statut.';
        feedback.className = 'mt-3 text-danger';

        bouton.disabled = false;
    }

});

/* OUVRIR LE FORMULAIRE DE CONTACT */

function ouvrirContactCommande(commandeId, action) {

    const formulaire = document.getElementById(
        'form-contact-commande'
    );

    // Réinitialiser le formulaire
    formulaire.reset();

    // Réinitialiser l'état du formulaire
    formulaire.dataset.envoiEnCours = 'non';
    formulaire.dataset.contactEnregistre = 'non';
    delete formulaire.dataset.contactId;

    // Ajouter le jeton CSRF
    formulaire.elements['csrf_token'].value =
        formulaire.dataset.csrfToken || '';

    // Désactiver le bouton Continuer
    document.getElementById('contact-commande-continuer').disabled = true;

    // Enregistrer les informations de la commande
    formulaire.elements['commande_id'].value = commandeId;
    formulaire.elements['action'].value = action;

    // Adapter le titre selon l'action
    const titre = document.getElementById('contact-commande-titre');

    if (action === 'annulation') {

        titre.textContent = 'Annuler la commande n°' + commandeId;

    } else {

        titre.textContent = 'Modifier la commande n°' + commandeId;
    }

    // Masquer le détail
    document.getElementById('employe-detail-contenu')
        .classList.add('d-none');

    // Masquer les actions
    document.getElementById('employe-detail-actions')
        .classList.add('d-none');

    // Afficher le formulaire
    formulaire.classList.remove('d-none');

    // Réinitialiser le message
    document.getElementById('contact-commande-feedback')
        .textContent = '';
}


/* RETOUR AU DÉTAIL DE LA COMMANDE */

document.addEventListener('click', function(event) {

    if (event.target.id !== 'contact-commande-retour') {
        return;
    }

    // Masquer le formulaire
    document.getElementById('form-contact-commande')
        .classList.add('d-none');

    // Réafficher le détail
    document.getElementById('employe-detail-contenu')
        .classList.remove('d-none');

    // Réafficher les actions
    document.getElementById('employe-detail-actions')
        .classList.remove('d-none');

});


/* VÉRIFIER LE FORMULAIRE DE CONTACT */

function verifierFormulaireContact() {

    const formulaire = document.getElementById('form-contact-commande');

    if (!formulaire) {
        return;
    }

    const bouton = document.getElementById('contact-commande-continuer');

    // Vérifier les champs obligatoires
    const champsValides = formulaire.checkValidity();

    // Vérifier le jeton CSRF
    const csrfPresent =
        formulaire.elements['csrf_token'].value !== '';

    // Vérifier si un envoi est en cours ou déjà terminé
    const envoiEnCours = formulaire.dataset.envoiEnCours === 'oui';
    const contactEnregistre = formulaire.dataset.contactEnregistre === 'oui';

    bouton.disabled =
        !champsValides ||
        !csrfPresent ||
        envoiEnCours ||
        contactEnregistre;
}

// Vérifier pendant la saisie
document.addEventListener('input', function(event) {

    if (event.target.closest('#form-contact-commande')) {
        verifierFormulaireContact();
    }

});

// Vérifier lorsque le mode de contact change
document.addEventListener('change', function(event) {

    if (event.target.closest('#form-contact-commande')) {
        verifierFormulaireContact();
    }

});

/* ENREGISTRER LE CONTACT AVEC LE CLIENT */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-contact-commande') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;
    const bouton = document.getElementById('contact-commande-continuer');
    const feedback = document.getElementById('contact-commande-feedback');

    // Éviter plusieurs envois
    if (
        formulaire.dataset.envoiEnCours === 'oui' ||
        formulaire.dataset.contactEnregistre === 'oui'
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

    feedback.textContent = 'Enregistrement du contact...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les données à PHP
        const reponse = await fetch('/api/contact-commande-employe.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible d’enregistrer le contact.';

            feedback.className = 'mt-3 text-danger';
            return;
        }

        // Mémoriser le contact enregistré
        formulaire.dataset.contactId = resultat.contact_id;
        formulaire.dataset.contactEnregistre = 'oui';

        // Actualiser l'historique des contacts
        try {

            const commandeId = formulaire.elements['commande_id'].value;

            const reponseDetail = await fetch(
                '/api/detail-commande-employe.php?id=' + encodeURIComponent(commandeId)
            );

            if (reponseDetail.ok) {

                const detailActualise = await reponseDetail.json();

                if (detailActualise.success) {

                    afficherHistoriqueContacts(detailActualise.contacts);

                }
            }

        } catch (erreur) {

            console.error(
                'Impossible d’actualiser l’historique des contacts :',
                erreur
            );
        }

        // Afficher la confirmation
        feedback.textContent = 'Le contact avec le client a bien été enregistré.';
        feedback.className = 'mt-3 text-success';

    } catch (erreur) {

        feedback.textContent =
            'Impossible de vérifier l’enregistrement du contact.';

        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        formulaire.dataset.envoiEnCours = 'non';
        verifierFormulaireContact();

    }

});

/* AFFICHER L'HISTORIQUE DES CONTACTS */

function afficherHistoriqueContacts(contacts) {

    const liste = document.getElementById('employe-contacts-liste');

    // Vider l'ancien historique
    liste.innerHTML = '';

    // Aucun contact enregistré
    if (contacts.length === 0) {

        const message = document.createElement('p');

        message.className = 'text-muted mb-0';
        message.textContent = 'Aucun contact enregistré pour cette commande.';

        liste.appendChild(message);

        return;
    }

    // Afficher chaque contact
    for (const contact of contacts) {

        // Créer une carte Bootstrap
        const carte = document.createElement('div');

        carte.className = 'border rounded p-3';

        // Action effectuée
        const action = document.createElement('h4');

        action.className = 'h6 mb-2';

        if (contact.action === 'annulation') {
            action.textContent = 'Annulation';
        } else {
            action.textContent = 'Modification';
        }

        // Mode de contact
        const canal = document.createElement('p');

        canal.className = 'mb-2';

        if (contact.canal === 'telephone') {
            canal.textContent = 'Contact par téléphone';
        } else {
            canal.textContent = 'Contact par e-mail';
        }

        // Motif
        const motif = document.createElement('p');

        motif.className = 'mb-2';
        motif.textContent = contact.motif;

        // Employé
        const employe = document.createElement('p');

        employe.className = 'small text-muted mb-1';

        employe.textContent = 'Par ' +
            (contact.employe_prenom || '') + ' ' +
            (contact.employe_nom || '');

        // Date et heure
        const date = document.createElement('p');

        date.className = 'small text-muted mb-0';

        const dateContact = new Date(
            contact.date_heure.replace(' ', 'T')
        );

        date.textContent = dateContact.toLocaleString('fr-FR');

        // Ajouter les éléments à la carte
        carte.appendChild(action);
        carte.appendChild(canal);
        carte.appendChild(motif);
        carte.appendChild(employe);
        carte.appendChild(date);

        // Ajouter la carte à la liste
        liste.appendChild(carte);
    }
}
