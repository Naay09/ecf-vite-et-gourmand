/* AFFICHER LES COMMANDES DANS L'ESPACE EMPLOYÉ */

// Contacts enregistrés pour des modifications/ une annulation en attente
const contactsModification = {};
const contactsAnnulation = {};

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

    // Masquer l'ancien formulaire de modification
    document.getElementById('form-modification-commande-employe')
        .classList.add('d-none');

    // Masquer l'ancienne confirmation d'annulation
    document.getElementById('employe-confirmation-annulation')
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

         /* AFFICHER LE MATÉRIEL PRÊTÉ */

        // Récupérer les éléments
        const sectionPret = document.getElementById(
            'employe-pret-section'
        );

        const boutonRestitution = document.getElementById(
            'employe-confirmer-restitution'
        );

        const etatPret = document.getElementById(
            'employe-pret-etat'
        );

        const ligneRetour = document.getElementById(
            'employe-pret-retour-ligne'
        );

        // Réinitialiser l'affichage
        sectionPret.classList.add('d-none');
        ligneRetour.classList.add('d-none');

        boutonRestitution.classList.add('d-none');
        boutonRestitution.disabled = true;

        delete boutonRestitution.dataset.commandeId;
        delete boutonRestitution.dataset.csrfToken;

        document.getElementById('employe-restitution-feedback')
            .textContent = '';

        // Récupérer le prêt de matériel
        const pret = resultat.pret_materiel;

        // Afficher la section uniquement si un prêt existe
        if (pret) {

            // Description du matériel
            document.getElementById('employe-pret-description')
                .textContent = pret.description;

            // Date du prêt
            const datePret = pret.date_pret
                .substring(0, 10)
                .split('-')
                .reverse()
                .join('/');

            document.getElementById('employe-pret-date')
                .textContent = datePret;

            // Date limite de restitution
            const dateLimite = pret.date_limite_retour
                .split('-')
                .reverse()
                .join('/');

            document.getElementById('employe-pret-date-limite')
                .textContent = dateLimite;

            // Vérifier si le matériel a été restitué
            if (pret.date_retour !== null) {

                // Afficher l'état "Matériel restitué"
                etatPret.textContent = 'Matériel restitué';
                etatPret.className = 'badge text-bg-success';

                // Formater la date de restitution
                const dateRetour = pret.date_retour
                    .substring(0, 10)
                    .split('-')
                    .reverse()
                    .join('/');

                const heureRetour = pret.date_retour.substring(11, 16);

                document.getElementById('employe-pret-date-retour')
                    .textContent = dateRetour + ' à ' + heureRetour;

                // Afficher la date de restitution
                ligneRetour.classList.remove('d-none');

            } else {

                // Le matériel n'a pas encore été restitué
                etatPret.textContent = 'En attente de restitution';
                etatPret.className = 'badge text-bg-warning';

                // Autoriser la restitution uniquement au bon statut
                if (commande.statut_courant === 'en_attente_retour_materiel') {

                    boutonRestitution.dataset.commandeId = commande.id;

                    boutonRestitution.dataset.csrfToken =
                        resultat.csrf_token;

                    boutonRestitution.disabled = false;
                    boutonRestitution.classList.remove('d-none');
                }
            }

            // Afficher la section
            sectionPret.classList.remove('d-none');
        }

        /* RESTAURER LES CONTACTS ENREGISTRÉS */

        // Récupérer les contacts non utilisés depuis MySQL
        const contactsUtilisables = resultat.contacts_utilisables;

        // Contact pour une modification
        if (contactsUtilisables.modification) {

            contactsModification[commande.id] =
                contactsUtilisables.modification;

        } else {

            delete contactsModification[commande.id];
        }

        // Contact pour une annulation
        if (contactsUtilisables.annulation) {

            contactsAnnulation[commande.id] =
                contactsUtilisables.annulation;

        } else {

            delete contactsAnnulation[commande.id];
        }

        // Afficher l'historique des contacts
        afficherHistoriqueContacts(resultat.contacts);

        // Préparer le formulaire de modification
        remplirFormulaireModificationEmploye(
            commande,
            resultat.csrf_token
        );

        // Masquer le formulaire de modification
        document.getElementById('form-modification-commande-employe')
            .classList.add('d-none');

        /* ACTIONS SUR UNE COMMANDE */

        const actions = document.getElementById('employe-detail-actions');

        const statutCommande = commande.statut_courant;

        // Statuts autorisant une modification ou une annulation
        const statutsAutorises = [
            'en_attente',
            'accepte',
            'en_preparation',
            'en_cours_de_livraison'
        ];

        // Afficher les boutons uniquement si le statut le permet
        if (statutsAutorises.includes(statutCommande)) {

            // Bouton Modifier
            const boutonModifier = document.createElement('button');

            boutonModifier.type = 'button';
            boutonModifier.className = 'btn btn-primary';
            boutonModifier.textContent = 'Modifier';

            boutonModifier.addEventListener('click', function() {

                ouvrirContactCommande(commande.id, 'modification');

            });

            // Bouton Annuler
            const boutonAnnuler = document.createElement('button');

            boutonAnnuler.type = 'button';
            boutonAnnuler.className = 'btn btn-outline-danger';
            boutonAnnuler.textContent = 'Annuler';

            boutonAnnuler.addEventListener('click', function() {

                ouvrirContactCommande(commande.id, 'annulation');

            });

            // Ajouter les boutons
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

        // Masquer les champs du matériel
        document.getElementById('employe-materiel-champs')
            .classList.add('d-none');

        // Vider l'ancienne description
        document.getElementById('employe-description-materiel')
            .value = '';

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

/* VÉRIFIER LE FORMULAIRE DE CHANGEMENT DE STATUT */

function verifierChangementStatutEmploye() {

    const selectStatut = document.getElementById(
        'employe-nouveau-statut'
    );

    const sectionMateriel = document.getElementById(
        'employe-materiel-champs'
    );

    const descriptionMateriel = document.getElementById(
        'employe-description-materiel'
    );

    const bouton = document.getElementById(
        'employe-enregistrer-statut'
    );

    const nouveauStatut = selectStatut.value;

    // Vérifier si le nouveau statut nécessite un prêt
    if (nouveauStatut === 'en_attente_retour_materiel') {

        sectionMateriel.classList.remove('d-none');

        // La description est obligatoire
        bouton.disabled = descriptionMateriel.value.trim() === '';

    } else {

        sectionMateriel.classList.add('d-none');

        descriptionMateriel.value = '';

        // Un nouveau statut doit être sélectionné
        bouton.disabled = nouveauStatut === '';
    }
}

// Vérifier lorsque le statut change
document.addEventListener('change', function(event) {

    if (event.target.id === 'employe-nouveau-statut') {
        verifierChangementStatutEmploye();
    }

});

// Vérifier pendant la saisie de la description
document.addEventListener('input', function(event) {

    if (event.target.id === 'employe-description-materiel') {
        verifierChangementStatutEmploye();
    }

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

    // Récupérer la description du matériel
    const descriptionMateriel = document.getElementById(
        'employe-description-materiel'
    ).value.trim();

    // Vérifier la description si un prêt est nécessaire
    if (
        nouveauStatut === 'en_attente_retour_materiel' &&
        descriptionMateriel === ''
    ) {

        feedback.textContent = 'Veuillez renseigner le matériel prêté.';
        feedback.className = 'mt-3 text-danger';

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

    // Ajouter la description uniquement en cas de prêt
    if (nouveauStatut === 'en_attente_retour_materiel') {

        donnees.append('description_materiel', descriptionMateriel);
    }

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

// Vérifier si un contact a déjà été enregistré
if (
    action === 'modification' &&
    contactsModification[commandeId]
) {

    const formulaireModification = document.getElementById(
        'form-modification-commande-employe'
    );

    // Réutiliser le contact existant
    formulaireModification.elements['contact_id'].value =
        contactsModification[commandeId];

    // Masquer le détail et le formulaire de contact
    document.getElementById('employe-detail-contenu')
        .classList.add('d-none');

    document.getElementById('employe-detail-actions')
        .classList.add('d-none');

    document.getElementById('form-contact-commande')
        .classList.add('d-none');

    // Afficher directement le formulaire de modification
    formulaireModification.classList.remove('d-none');

    verifierFormulaireModificationEmploye();

    return;
}


// Vérifier si un contact d'annulation existe déjà
if (
    action === 'annulation' &&
    contactsAnnulation[commandeId]
) {

    ouvrirConfirmationAnnulation(
        commandeId,
        contactsAnnulation[commandeId],
        formulaire.dataset.csrfToken
    );

    return;
}

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

        // Conserver le contact pour une modification ultérieure
        if (formulaire.elements['action'].value === 'modification') {

            const commandeId = formulaire.elements['commande_id'].value;

            contactsModification[commandeId] = resultat.contact_id;
        }

        // Récupérer l'action choisie
        const action = formulaire.elements['action'].value;

        // Si l'employé souhaite modifier la commande
        if (action === 'modification') {

            const formulaireModification = document.getElementById(
                'form-modification-commande-employe'
            );

            // Transmettre l'identifiant du contact enregistré
            formulaireModification.elements['contact_id'].value =
                resultat.contact_id;

            // Masquer le formulaire de contact
            formulaire.classList.add('d-none');

            // Afficher le formulaire de modification
            formulaireModification.classList.remove('d-none');
        }

        // Si l'employé souhaite annuler la commande
        if (action === 'annulation') {

            const commandeId = formulaire.elements['commande_id'].value;

            const csrfToken = formulaire.elements['csrf_token'].value;

            // Conserver le contact enregistré
            contactsAnnulation[commandeId] = resultat.contact_id;

            // Afficher la confirmation
            ouvrirConfirmationAnnulation(
                commandeId,
                resultat.contact_id,
                csrfToken
            );
        }

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

        // État du contact préalable
        const etatContact = document.createElement('span');

        etatContact.className = 'badge mt-2';

        if (contact.utilise_le) {

            etatContact.classList.add('text-bg-success');
            etatContact.textContent = 'Action effectuée';

        } else {

            etatContact.classList.add('text-bg-secondary');
            etatContact.textContent = 'Action en attente';
        }

        // Ajouter les éléments à la carte
        carte.appendChild(action);
        carte.appendChild(canal);
        carte.appendChild(motif);
        carte.appendChild(employe);
        carte.appendChild(date);
        carte.appendChild(etatContact);
        
        // Ajouter la carte à la liste
        liste.appendChild(carte);
    }
}

/* PRÉREMPLIR LE FORMULAIRE DE MODIFICATION */

function remplirFormulaireModificationEmploye(commande, csrfToken) {

    const formulaire = document.getElementById(
        'form-modification-commande-employe'
    );

    // Informations techniques
    formulaire.elements['commande_id'].value = commande.id;
    formulaire.elements['csrf_token'].value = csrfToken;

    formulaire.elements['contact_id'].value = '';

    // Informations du client
    formulaire.elements['prenom_client'].value = commande.prenom_client;
    formulaire.elements['nom_client'].value = commande.nom_client;
    formulaire.elements['email_client'].value = commande.email_client;
    formulaire.elements['telephone_client'].value = commande.telephone_client;

    // Informations de la prestation
    formulaire.elements['date_prestation'].value =
        commande.date_prestation.substring(0, 10);

    formulaire.elements['heure_livraison'].value =
        commande.heure_livraison.substring(0, 5);

    formulaire.elements['nb_personnes'].value = commande.nb_personnes;

    // Informations de livraison
    formulaire.elements['adresse_livraison'].value =
        commande.adresse_livraison;

    formulaire.elements['ville_livraison'].value =
        commande.ville_livraison;

    formulaire.elements['distance_livraison_km'].value =
        commande.distance_livraison_km;

}

/* RETOUR AU DÉTAIL DEPUIS LA MODIFICATION */

document.addEventListener('click', function(event) {

    if (event.target.id !== 'modif-commande-retour') {
        return;
    }

    // Masquer le formulaire de modification
    document.getElementById('form-modification-commande-employe')
        .classList.add('d-none');

    // Réafficher le détail
    document.getElementById('employe-detail-contenu')
        .classList.remove('d-none');

    // Réafficher les actions
    document.getElementById('employe-detail-actions')
        .classList.remove('d-none');

});


/* VÉRIFIER LE FORMULAIRE DE MODIFICATION */

function verifierFormulaireModificationEmploye() {

    const formulaire = document.getElementById(
        'form-modification-commande-employe'
    );

    if (!formulaire) {
        return;
    }

    const bouton = document.getElementById(
        'modif-commande-enregistrer'
    );

    // Vérifier les champs obligatoires
    const champsValides = formulaire.checkValidity();

    // Vérifier le contact préalable
    const contactPresent =
        formulaire.elements['contact_id'].value !== '';

    // Vérifier le jeton de sécurité
    const csrfPresent =
        formulaire.elements['csrf_token'].value !== '';

    // Vérifier qu'aucun envoi n'est en cours
    const envoiEnCours =
        formulaire.dataset.envoiEnCours === 'oui';

    // Activer ou désactiver le bouton
    bouton.disabled =
        !champsValides ||
        !contactPresent ||
        !csrfPresent ||
        envoiEnCours;
}

/* SURVEILLER LES CHAMPS DU FORMULAIRE */

document.addEventListener('input', function(event) {

    if (event.target.closest('#form-modification-commande-employe')) {
        verifierFormulaireModificationEmploye();
    }

});

document.addEventListener('change', function(event) {

    if (event.target.closest('#form-modification-commande-employe')) {
        verifierFormulaireModificationEmploye();
    }

});


/* ENREGISTRER LES MODIFICATIONS D'UNE COMMANDE */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-modification-commande-employe') {
        return;
    }

    // Empêcher le rechargement de la page
    event.preventDefault();

    // Récupérer les éléments
    const formulaire = event.target;

    const bouton = document.getElementById(
        'modif-commande-enregistrer'
    );

    const feedback = document.getElementById(
        'modif-commande-feedback'
    );

    // Éviter les doubles envois
    if (formulaire.dataset.envoiEnCours === 'oui') {
        return;
    }

    // Vérifier les champs
    if (!formulaire.reportValidity()) {
        return;
    }

    const commandeId = formulaire.elements['commande_id'].value;
    const contactId = formulaire.elements['contact_id'].value;
    const csrfToken = formulaire.elements['csrf_token'].value;

    if (!commandeId || !contactId || !csrfToken) {

        feedback.textContent =
            'Le contact préalable ou les informations de sécurité sont manquants.';

        feedback.className = 'mt-3 text-danger';

        return;
    }

    // Récupérer les informations du formulaire
    const donnees = new FormData(formulaire);

    // Désactiver le bouton pendant l'enregistrement
    formulaire.dataset.envoiEnCours = 'oui';
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement des modifications...';
    feedback.className = 'mt-3 text-secondary';

    try {

        // Envoyer les données à PHP
        const reponse = await fetch('/api/modifier-commande-employe.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de modifier la commande.';

            feedback.className = 'mt-3 text-danger';

            return;
        }

        // La modification est enregistrée
        feedback.textContent = resultat.message;
        feedback.className = 'mt-3 text-success';

        // Le contact ne peut plus être réutilisé
        delete contactsModification[commandeId];

        formulaire.elements['contact_id'].value = '';

        // Actualiser les commandes
        await chargerCommandesEmploye();

        // Retrouver la commande modifiée
        const boutonsDetail = document.querySelectorAll(
            '.btn-detail-employe'
        );

        let boutonCommande = null;

        for (const boutonDetail of boutonsDetail) {

            if (boutonDetail.dataset.commandeId === commandeId) {

                boutonCommande = boutonDetail;
                break;
            }
        }

        if (boutonCommande) {

            // Masquer le formulaire
            formulaire.classList.add('d-none');

            // Recharger le détail de la commande
            boutonCommande.click();
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer l’enregistrement. Actualisez le détail de la commande avant de réessayer.';

        feedback.className = 'mt-3 text-danger';

    } finally {

        formulaire.dataset.envoiEnCours = 'non';

        verifierFormulaireModificationEmploye();

    }

});


/* AFFICHER LA CONFIRMATION D'ANNULATION */

function ouvrirConfirmationAnnulation(commandeId, contactId, csrfToken) {

    const confirmation = document.getElementById(
        'employe-confirmation-annulation'
    );

    // Renseigner le numéro de commande
    document.getElementById('annulation-commande-numero')
        .textContent = 'n°' + commandeId;

    // Conserver les informations nécessaires
    document.getElementById('annulation-commande-id')
        .value = commandeId;

    document.getElementById('annulation-contact-id')
        .value = contactId;

    document.getElementById('annulation-csrf-token')
        .value = csrfToken;

    // Masquer les autres contenus
    document.getElementById('employe-detail-contenu')
        .classList.add('d-none');

    document.getElementById('employe-detail-actions')
        .classList.add('d-none');

    document.getElementById('form-contact-commande')
        .classList.add('d-none');

    document.getElementById('form-modification-commande-employe')
        .classList.add('d-none');

    // Réinitialiser le message
    document.getElementById('annulation-commande-feedback')
        .textContent = '';

    // Afficher la confirmation
    confirmation.classList.remove('d-none');

    // Réinitialiser l'état de l'envoi
    confirmation.dataset.envoiEnCours = 'non';

    // Activer le bouton si les informations sont présentes
    const boutonConfirmer = document.getElementById(
        'annulation-commande-confirmer'
    );

    boutonConfirmer.disabled = !commandeId || !contactId || !csrfToken;
}


/* RETOUR AU DÉTAIL DEPUIS L'ANNULATION */

document.addEventListener('click', function(event) {

    if (event.target.id !== 'annulation-commande-retour') {
        return;
    }

    // Masquer la confirmation
    document.getElementById('employe-confirmation-annulation')
        .classList.add('d-none');

    // Réafficher le détail
    document.getElementById('employe-detail-contenu')
        .classList.remove('d-none');

    // Réafficher les actions
    document.getElementById('employe-detail-actions')
        .classList.remove('d-none');

});


/* CONFIRMER L'ANNULATION D'UNE COMMANDE */

document.addEventListener('click', async function(event) {

    // Vérifier qu'il s'agit du bouton d'annulation
    if (event.target.id !== 'annulation-commande-confirmer') {
        return;
    }

    // Récupérer les éléments
    const bouton = event.target;

    const confirmation = document.getElementById(
        'employe-confirmation-annulation'
    );

    const feedback = document.getElementById(
        'annulation-commande-feedback'
    );

    // Éviter les doubles envois
    if (confirmation.dataset.envoiEnCours === 'oui') {
        return;
    }

    // Récupérer les informations
    const commandeId = document.getElementById(
        'annulation-commande-id'
    ).value;

    const contactId = document.getElementById(
        'annulation-contact-id'
    ).value;

    const csrfToken = document.getElementById(
        'annulation-csrf-token'
    ).value;

    // Vérifier les informations
    if (!commandeId || !contactId || !csrfToken) {

        feedback.textContent =
            'Les informations nécessaires à l’annulation sont manquantes.';

        feedback.className = 'mt-3 text-danger';

        return;
    }

    // Préparer les données
    const donnees = new FormData();

    donnees.append('commande_id', commandeId);
    donnees.append('contact_id', contactId);
    donnees.append('csrf_token', csrfToken);

    // Désactiver le bouton pendant l'enregistrement
    confirmation.dataset.envoiEnCours = 'oui';
    bouton.disabled = true;

    feedback.textContent = 'Annulation en cours...';
    feedback.className = 'mt-3 text-secondary';

    let annulationEnregistree = false;

    try {

        // Envoyer la demande à PHP
        const reponse = await fetch('/api/annuler-commande-employe.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible d’annuler la commande.';

            feedback.className = 'mt-3 text-danger';

            return;
        }

        // L'annulation a été confirmée par PHP
        annulationEnregistree = true;

        // Supprimer le contact mémorisé
        delete contactsAnnulation[commandeId];

        // Effacer l'identifiant du contact utilisé
        document.getElementById('annulation-contact-id').value = '';

        feedback.textContent = resultat.message;
        feedback.className = 'mt-3 text-success';

        // Actualiser les cartes et les indicateurs
        await chargerCommandesEmploye();

        // Retrouver la commande annulée
        const boutonsDetail = document.querySelectorAll(
            '.btn-detail-employe'
        );

        for (const boutonDetail of boutonsDetail) {

            if (boutonDetail.dataset.commandeId === commandeId) {

                // Masquer la confirmation
                confirmation.classList.add('d-none');

                // Recharger le détail de la commande
                boutonDetail.click();

                break;
            }
        }

    } catch (erreur) {

        console.error(erreur);

        if (annulationEnregistree) {

            feedback.textContent =
                'La commande est annulée, mais l’affichage n’a pas pu être actualisé.';

        } else {

            feedback.textContent =
                'Impossible de confirmer le résultat. Rechargez la commande avant de réessayer.';

        }

        feedback.className = 'mt-3 text-danger';

    } finally {

        confirmation.dataset.envoiEnCours = 'non';

        // Ne pas réactiver après une annulation réussie
        if (annulationEnregistree) {
            bouton.disabled = true;
        }
    }
});


/* CONFIRMER LA RESTITUTION DU MATÉRIEL */

document.addEventListener('click', async function(event) {

    // Vérifier le bouton concerné
    if (event.target.id !== 'employe-confirmer-restitution') {
        return;
    }

    const bouton = event.target;

    // Empêcher les clics multiples
    if (bouton.disabled) {
        return;
    }

    const feedback = document.getElementById(
        'employe-restitution-feedback'
    );

    // Récupérer les informations
    const commandeId = bouton.dataset.commandeId;
    const csrfToken = bouton.dataset.csrfToken;

    if (!commandeId || !csrfToken) {

        feedback.textContent = 'Informations de restitution manquantes.';
        feedback.className = 'mt-3 text-danger';

        return;
    }

    // Demander une confirmation à l'employé
    const confirmation = window.confirm(
        'Confirmez-vous que le matériel de la commande n°'
        + commandeId + ' a bien été restitué ?'
    );

    if (!confirmation) {
        return;
    }

    // Préparer les données
    const donnees = new FormData();

    donnees.append('commande_id', commandeId);
    donnees.append('csrf_token', csrfToken);

    // Désactiver le bouton
    bouton.disabled = true;

    feedback.textContent = 'Enregistrement de la restitution...';
    feedback.className = 'mt-3 text-secondary';

    let restitutionEnregistree = false;

    try {

        // Envoyer les informations à PHP
        const reponse = await fetch('/api/confirmer-restitution.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse de PHP
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible d’enregistrer la restitution.';

            feedback.className = 'mt-3 text-danger';

            // La demande a été refusée par PHP
            bouton.disabled = false;

            return;
        }

        // La restitution est enregistrée dans MySQL
        restitutionEnregistree = true;

        feedback.textContent = resultat.message;
        feedback.className = 'mt-3 text-success';

        // Actualiser les commandes et les indicateurs
        await chargerCommandesEmploye();

        // Retrouver le bouton de détail de la commande
        const boutonsDetail = document.querySelectorAll(
            '.btn-detail-employe'
        );

        for (const boutonDetail of boutonsDetail) {

            if (boutonDetail.dataset.commandeId === commandeId) {

                // Recharger le détail de la commande
                boutonDetail.click();

                break;
            }
        }

    } catch (erreur) {

        console.error(erreur);

        if (restitutionEnregistree) {

            feedback.textContent =
                'La restitution est enregistrée, mais l’affichage n’a pas pu être actualisé.';

        } else {

            feedback.textContent =
                'Impossible de confirmer le résultat. Rechargez la commande avant de réessayer.';
        }

        feedback.className = 'mt-3 text-danger';

    }

});

/* CHARGER LES AVIS À MODÉRER */

async function chargerAvisEmploye() {

    // Récupérer les éléments de la page
    const liste = document.getElementById('employe-avis-liste');
    const feedback = document.getElementById('employe-avis-feedback');
    const compteur = document.getElementById('employe-avis-attente');

    // Vérifier que les éléments existent
    if (!liste || !feedback) {
        return;
    }

    // Réinitialiser l'affichage
    liste.innerHTML = '';

    feedback.textContent = 'Chargement des avis...';
    feedback.className = 'mb-4 text-secondary';

    if (compteur) {
        compteur.textContent = '—';
    }

    try {

        // Récupérer les avis depuis PHP
        const reponse = await fetch('/api/avis-employe.php', {
            cache: 'no-store'
        });

        // Lire la réponse JSON
        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de charger les avis.';

            feedback.className = 'mb-4 text-danger';

            return;
        }

        // Récupérer les avis
        const avis = resultat.avis;

        // Mettre à jour le compteur
        if (compteur) {
            compteur.textContent = avis.length;
        }

        // Vérifier si aucun avis n'est en attente
        if (avis.length === 0) {

            feedback.textContent = 'Aucun avis en attente de modération.';
            feedback.className = 'mb-4 text-secondary';

            return;
        }

        // Effacer le message de chargement
        feedback.textContent = '';

        // Créer une carte pour chaque avis
        for (const avisClient of avis) {

            // Colonne Bootstrap
            const colonne = document.createElement('div');
            colonne.className = 'col-12 col-md-6';

            // Carte Bootstrap
            const carte = document.createElement('article');
            carte.className = 'card h-100';

            // Contenu de la carte
            const contenu = document.createElement('div');
            contenu.className = 'card-body d-flex flex-column';

            // Numéro de commande
            const titre = document.createElement('h3');
            titre.className = 'h5 mb-3';
            titre.textContent = 'Commande n°' + avisClient.commande_id;

            // Nom du client
            const client = document.createElement('p');
            client.className = 'mb-2';
            client.textContent =
                'Client : ' + avisClient.prenom_client + ' ' +
                avisClient.nom_client;

            // Menu commandé
            const menu = document.createElement('p');
            menu.className = 'mb-2';
            menu.textContent = 'Menu : ' + avisClient.menu_titre;

            // Note du client
            const note = document.createElement('p');
            note.className = 'fw-semibold mb-3';
            note.textContent = 'Note : ' + avisClient.note + ' / 5';

            // Commentaire
            const commentaire = document.createElement('p');
            commentaire.className = 'mb-3';
            commentaire.textContent = avisClient.commentaire;

            // Date de dépôt
            const date = document.createElement('p');
            date.className = 'small text-muted mb-3';

            const dateAvis = new Date(
                avisClient.date_creation.replace(' ', 'T')
            );

            date.textContent =
                'Déposé le ' + dateAvis.toLocaleString('fr-FR');

            // Statut de modération
            const statut = document.createElement('span');
            statut.className = 'badge text-bg-warning align-self-start mt-auto';
            statut.textContent = 'En attente de validation';

            // Boutons de modération
            const actions = document.createElement('div');
            actions.className = 'd-flex flex-wrap gap-2 mt-3';

            // Bouton Valider
            const boutonValider = document.createElement('button');

            boutonValider.type = 'button';
            boutonValider.className = 'btn btn-primary';
            boutonValider.textContent = 'Valider';

            boutonValider.dataset.avisId = avisClient.id;
            boutonValider.dataset.moderation = 'valide';
            boutonValider.dataset.csrfToken = resultat.csrf_token;

            boutonValider.classList.add('btn-moderer-avis');

            // Bouton Refuser
            const boutonRefuser = document.createElement('button');

            boutonRefuser.type = 'button';
            boutonRefuser.className = 'btn btn-outline-danger';
            boutonRefuser.textContent = 'Refuser';

            boutonRefuser.dataset.avisId = avisClient.id;
            boutonRefuser.dataset.moderation = 'refusee';
            boutonRefuser.dataset.csrfToken = resultat.csrf_token;

            boutonRefuser.classList.add('btn-moderer-avis');

            // Ajouter les boutons
            actions.appendChild(boutonValider);
            actions.appendChild(boutonRefuser);

            // Ajouter les informations au contenu
            contenu.appendChild(titre);
            contenu.appendChild(client);
            contenu.appendChild(menu);
            contenu.appendChild(note);
            contenu.appendChild(commentaire);
            contenu.appendChild(date);
            contenu.appendChild(statut);
            contenu.appendChild(actions);

            // Ajouter la carte à la colonne
            carte.appendChild(contenu);
            colonne.appendChild(carte);

            // Ajouter la colonne à la liste
            liste.appendChild(colonne);
        }

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent = 'Impossible de charger les avis.';
        feedback.className = 'mb-4 text-danger';
    }
}

/* MODÉRER UN AVIS CLIENT */

document.addEventListener('click', async function(event) {

    // Vérifier le bouton cliqué
    const bouton = event.target.closest('.btn-moderer-avis');

    if (!bouton || bouton.disabled) {
        return;
    }

    // Récupérer les informations
    const avisId = bouton.dataset.avisId;
    const moderation = bouton.dataset.moderation;
    const csrfToken = bouton.dataset.csrfToken;

    if (!avisId || !moderation || !csrfToken) {
        return;
    }

    // Demander une confirmation
    const action = moderation === 'valide' ? 'valider' : 'refuser';

    const confirmation = window.confirm(
        'Voulez-vous vraiment ' + action + ' cet avis ?'
    );

    if (!confirmation) {
        return;
    }

    // Récupérer les boutons de la carte
    const carte = bouton.closest('.card');

    const boutons = carte.querySelectorAll('.btn-moderer-avis');

    // Désactiver les deux boutons
    boutons.forEach(function(element) {
        element.disabled = true;
    });

    // Préparer les données
    const donnees = new FormData();

    donnees.append('avis_id', avisId);
    donnees.append('moderation', moderation);
    donnees.append('csrf_token', csrfToken);

    const feedback = document.getElementById(
        'employe-avis-feedback'
    );

    feedback.textContent = 'Modération en cours...';
    feedback.className = 'mb-4 text-secondary';

    try {

        // Envoyer la demande à PHP
        const reponse = await fetch('/api/moderer-avis.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        // Vérifier la réponse
        if (!reponse.ok || !resultat.success) {

            feedback.textContent =
                resultat.message || 'Impossible de modérer cet avis.';

            feedback.className = 'mb-4 text-danger';

            // Réactiver les boutons
            boutons.forEach(function(element) {
                element.disabled = false;
            });

            return;
        }

        // Afficher la confirmation
        feedback.textContent = resultat.message;
        feedback.className = 'mb-4 text-success';

        // Actualiser la liste et le compteur
        await chargerAvisEmploye();

        // Réafficher la confirmation après le chargement
        feedback.textContent = resultat.message;
        feedback.className = 'mb-4 text-success';

    } catch (erreur) {

        console.error(erreur);

        feedback.textContent =
            'Impossible de confirmer la modération. Actualisez la liste avant de réessayer.';

        feedback.className = 'mb-4 text-danger';

    }

});


// Chargement initial
document.addEventListener('DOMContentLoaded', chargerCommandesEmploye);
document.addEventListener('DOMContentLoaded', chargerAvisEmploye);

// Chargement des pages avec notre routeur
document.addEventListener('pageChargee', chargerCommandesEmploye);
document.addEventListener('pageChargee', chargerAvisEmploye);