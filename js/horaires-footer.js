/* AFFICHER LES HORAIRES */

async function chargerHorairesFooter() {

    const conteneur = document.getElementById(
        'horaires-footer'
    );

    if (!conteneur) {
        return;
    }

    conteneur.textContent = 'Chargement des horaires...';

    try {

        const reponse = await fetch(
            '/api/horaires-publics.php',
            {
                cache: 'no-store'
            }
        );

        const resultat = await reponse.json();

        if (!reponse.ok || !resultat.success) {
            throw new Error('Impossible de récupérer les horaires.');
        }

        // Vider le conteneur
        conteneur.innerHTML = '';

        const horaires = resultat.horaires;

        if (horaires.length === 0) {

            conteneur.textContent =
                'Horaires indisponibles pour le moment.';

            return;
        }

        // Regrouper les journées consécutives
        // ayant les mêmes horaires
        const groupes = [];

        for (const horaire of horaires) {

            const ferme = Number(horaire.ferme) === 1;

            const ouverture = ferme
                ? ''
                : horaire.ouverture.substring(0, 5);

            const fermeture = ferme
                ? ''
                : horaire.fermeture.substring(0, 5);

            const dernierGroupe = groupes[groupes.length - 1];

            if (
                dernierGroupe &&
                dernierGroupe.ferme === ferme &&
                dernierGroupe.ouverture === ouverture &&
                dernierGroupe.fermeture === fermeture
            ) {

                dernierGroupe.jours.push(horaire.jour);

            } else {

                groupes.push({
                    jours: [horaire.jour],
                    ferme: ferme,
                    ouverture: ouverture,
                    fermeture: fermeture
                });
            }
        }

        // Afficher les groupes
        for (const groupe of groupes) {

            const paragraphe = document.createElement('p');

            const premierJour = groupe.jours[0];
            const dernierJour = groupe.jours[groupe.jours.length - 1];

            const nomPremier =
                premierJour.charAt(0).toUpperCase() +
                premierJour.slice(1);

            const nomDernier =
                dernierJour.charAt(0).toUpperCase() +
                dernierJour.slice(1);

            // Afficher le ou les jours
            if (groupe.jours.length === 1) {

                paragraphe.textContent = nomPremier;

            } else if (groupe.jours.length === 2) {

                paragraphe.textContent =
                    nomPremier + ' — ' + nomDernier;

            } else {

                paragraphe.textContent =
                    nomPremier + ' — ' + nomDernier;
            }

            // Afficher les heures ou la fermeture
            const texteHoraire = document.createElement('b');

            if (groupe.ferme) {

                texteHoraire.textContent = 'Fermé';

            } else {

                texteHoraire.textContent =
                    groupe.ouverture + ' — ' +
                    groupe.fermeture;
            }

            paragraphe.appendChild(texteHoraire);
            conteneur.appendChild(paragraphe);
        }

    } catch (erreur) {

        console.error(erreur);

        conteneur.textContent =
            'Horaires indisponibles pour le moment.';
    }

}


/* CHARGER LES HORAIRES */

document.addEventListener('DOMContentLoaded', function() {
    chargerHorairesFooter();
});

document.addEventListener('pageChargee', function() {
    chargerHorairesFooter();
});
