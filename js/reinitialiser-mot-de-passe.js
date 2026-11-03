
/* ENREGISTRER LE NOUVEAU MOT DE PASSE */

document.addEventListener('submit', async function(event) {

    // Vérifier le formulaire concerné
    if (event.target.id !== 'form-reinitialisation') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;
    const feedback = document.getElementById('reinitialisation-feedback');
    const bouton = document.getElementById('reset-password-submit');

    // Récupérer le jeton depuis l'URL
    const parametres = new URLSearchParams(window.location.search);
    const jeton = parametres.get('jeton');

    if (!jeton) {
        feedback.textContent = 'Lien de réinitialisation invalide.';
        feedback.className = 'mt-3 text-danger';
        return;
    }

    // Récupérer les mots de passe
    const motDePasse = formulaire.elements['mot_de_passe'].value;
    const confirmation = formulaire.elements['confirmation_mot_de_passe'].value;

    // Vérifier la confirmation
    if (motDePasse !== confirmation) {
        feedback.textContent = 'Les mots de passe ne correspondent pas.';
        feedback.className = 'mt-3 text-danger';
        return;
    }

    // Vérifier les critères de sécurité
    if (
        motDePasse.length < 10 ||
        /[A-Z]/.test(motDePasse) === false ||
        /[a-z]/.test(motDePasse) === false ||
        /[0-9]/.test(motDePasse) === false ||
        /[^A-Za-z0-9\s]/.test(motDePasse) === false
    ) {

        feedback.textContent = 'Le mot de passe ne respecte pas les critères de sécurité.';
        feedback.className = 'mt-3 text-danger';

        return;
    }

    // Placer le jeton dans le formulaire
    formulaire.elements['jeton'].value = jeton;

    // Préparer les données
    const donnees = new FormData(formulaire);

    bouton.disabled = true;
    feedback.textContent = 'Modification en cours...';
    feedback.className = 'mt-3 text-secondary';

    let envoiReussi = false;

    try {

        const reponse = await fetch('/api/reinitialiser-mot-de-passe.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-success';

            formulaire.reset();
            envoiReussi = true;

            // Retirer le jeton de l'URL après son utilisation
            window.history.replaceState(
                {},
                '',
                '/reinitialiser-mot-de-passe'
            );

        } else {

            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-danger';
        }

    } catch (erreur) {

        feedback.textContent = 'Impossible de modifier le mot de passe.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        // Garder le bouton désactivé après une réussite
        bouton.disabled = envoiReussi;

    }
});


/* VÉRIFICATION DU MOT DE PASSE EN TEMPS RÉEL */

document.addEventListener('input', function(event) {

    // Vérifier que l'utilisateur modifie un mot de passe
    if (
        event.target.id !== 'nouveau-password' &&
        event.target.id !== 'confirmation-password'
    ) {
        return;
    }

    // Récupérer les mots de passe
    const motDePasse = document.getElementById('nouveau-password').value;
    const confirmation = document.getElementById('confirmation-password').value;

    // Définir les critères de sécurité
    const criteres = [
        {
            id: 'reset-critere-longueur',
            texte: '10 caractères minimum',
            valide: motDePasse.length >= 10
        },
        {
            id: 'reset-critere-majuscule',
            texte: 'Une majuscule',
            valide: /[A-Z]/.test(motDePasse)
        },
        {
            id: 'reset-critere-minuscule',
            texte: 'Une minuscule',
            valide: /[a-z]/.test(motDePasse)
        },
        {
            id: 'reset-critere-chiffre',
            texte: 'Un chiffre',
            valide: /[0-9]/.test(motDePasse)
        },
        {
            id: 'reset-critere-special',
            texte: 'Un caractère spécial',
            valide: /[^A-Za-z0-9\s]/.test(motDePasse)
        },
        {
            id: 'reset-critere-confirmation',
            texte: 'Mots de passe identiques',
            valide: confirmation !== '' && motDePasse === confirmation
        }
    ];

    // Mettre à jour l'affichage
    criteres.forEach(function(critere) {

        const element = document.getElementById(critere.id);

        if (critere.valide) {

            element.textContent = '✓ ' + critere.texte;
            element.classList.add('text-success');
            element.classList.remove('text-secondary');

        } else {

            element.textContent = '○ ' + critere.texte;
            element.classList.add('text-secondary');
            element.classList.remove('text-success');

        }

    });

});
