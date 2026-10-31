
/* FORMULAIRE D'INSCRIPTION */

document.addEventListener('submit', async function(event) {

    // Vérifier qu'il s'agit du formulaire d'inscription
    if (event.target.id !== 'inscription-form') {
        return;
    }

    event.preventDefault();

    const formulaire = event.target;
    const feedback = document.getElementById('inscription-feedback');
    const bouton = formulaire.querySelector('button[type="submit"]');

    // Récupérer les mots de passe
    const motDePasse = formulaire.elements['mot_de_passe'].value;
    const confirmation = formulaire.elements['confirmation_mot_de_passe'].value;

    // Vérifier la complexité du mot de passe
    const majuscule = /[A-Z]/.test(motDePasse);
    const minuscule = /[a-z]/.test(motDePasse);
    const chiffre = /[0-9]/.test(motDePasse);
    const special = /[^A-Za-z0-9\s]/.test(motDePasse);

    if (
        motDePasse.length < 10 ||
        !majuscule ||
        !minuscule ||
        !chiffre ||
        !special
    ) {
        feedback.textContent = 'Le mot de passe ne respecte pas les critères demandés.';
        feedback.className = 'mt-3 text-danger';
        return;
    }

    // Vérifier la confirmation
    if (motDePasse !== confirmation) {
        feedback.textContent = 'Les mots de passe ne correspondent pas.';
        feedback.className = 'mt-3 text-danger';
        return;
    }

    // Préparer les données
    const donnees = new FormData(formulaire);

    bouton.disabled = true;
    feedback.textContent = 'Création du compte en cours...';
    feedback.className = 'mt-3 text-secondary';

    try {

        const reponse = await fetch('/api/signup.php', {
            method: 'POST',
            body: donnees
        });

        const resultat = await reponse.json();

        if (reponse.ok && resultat.success) {

            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-success';

            formulaire.reset();

        } else {

            feedback.textContent = resultat.message;
            feedback.className = 'mt-3 text-danger';
        }

    } catch (erreur) {

        feedback.textContent = 'Impossible de créer le compte.';
        feedback.className = 'mt-3 text-danger';

        console.error(erreur);

    } finally {

        bouton.disabled = false;
    }

});


/* VÉRIFICATION DU MOT DE PASSE EN TEMPS RÉEL */

document.addEventListener('input', function(event) {

    // Surveiller uniquement les champs de mot de passe
    if (
        event.target.id !== 'inscription-password' &&
        event.target.id !== 'inscription-password-confirm'
    ) {
        return;
    }

    // Récupérer les valeurs des deux champs
    const motDePasse = document.getElementById('inscription-password').value;
    const confirmation = document.getElementById('inscription-password-confirm').value;

    // Liste des critères à vérifier
    const criteres = [
        {
            id: 'critere-longueur',
            texte: '10 caractères minimum',
            valide: motDePasse.length >= 10
        },
        {
            id: 'critere-majuscule',
            texte: 'Une majuscule',
            valide: /[A-Z]/.test(motDePasse)
        },
        {
            id: 'critere-minuscule',
            texte: 'Une minuscule',
            valide: /[a-z]/.test(motDePasse)
        },
        {
            id: 'critere-chiffre',
            texte: 'Un chiffre',
            valide: /[0-9]/.test(motDePasse)
        },
        {
            id: 'critere-special',
            texte: 'Un caractère spécial',
            valide: /[^A-Za-z0-9\s]/.test(motDePasse)
        },
        {
            id: 'critere-confirmation',
            texte: 'Mots de passe identiques',
            valide: confirmation !== '' && motDePasse === confirmation
        }
    ];

    // Mettre à jour l'affichage des critères
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

