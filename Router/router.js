import Route from "./route.js";
import { allRoutes, websiteName } from "./allRoutes.js";

// Création d'une route pour la page 404 (page introuvable)
const route404 = new Route("404", "Page introuvable", "/pages/public/404.html");

// Fonction pour récupérer la route correspondant à une URL donnée
const getRouteByUrl = (url) => {
  let currentRoute = null;
  // Parcours de toutes les routes pour trouver la correspondance
  allRoutes.forEach((element) => {
    if (element.url == url) {
      currentRoute = element;
    }
  });
  // Si aucune correspondance n'est trouvée, on retourne la route 404
  if (currentRoute != null) {
    return currentRoute;
  } else {
    return route404;
  }
};

// Fonction pour charger le contenu de la page
const LoadContentPage = async () => {
  let ouvrirModaleConnexion = false;
  // Vérifier l'accès à la page Mon compte
    if (window.location.pathname === '/mon-compte') {

        try {
            const reponse = await fetch('/api/session.php');

            if (!reponse.ok) {
                throw new Error('Impossible de vérifier la session.');
            }

            const session = await reponse.json();

            // Visiteur non connecté : redirection vers l'inscription
            if (!session.connecte) {
                window.history.replaceState({}, '', '/inscription');

            // Employé ou administrateur : redirection vers l'accueil
            } else if (session.role !== 'utilisateur') {
                window.history.replaceState({}, '', '/');
            }

        } catch (erreur) {
            console.error(erreur);
            window.history.replaceState({}, '', '/inscription');
        }
    }

    // Vérifier l'accès à la page Commande
    if (window.location.pathname === '/commande') {

        try {

            const reponse = await fetch('/api/session.php', {
                cache: 'no-store'
            });

            if (!reponse.ok) {
                throw new Error('Impossible de vérifier la session.');
            }

            const session = await reponse.json();

            // Visiteur non connecté
            if (!session.connecte) {

                // Revenir à la commande après connexion
                sessionStorage.setItem('commandeApresConnexion', 'true');

                // Redirection vers l'accueil
                window.history.replaceState({}, '', '/');

                // Demander l'ouverture de la modale
                ouvrirModaleConnexion = true;

            // Employé ou administrateur
            } else if (session.role !== 'utilisateur') {

                window.history.replaceState({}, '', '/');

            }

        } catch (erreur) {

            console.error(erreur);

            // Refuser l'accès si la session ne peut pas être vérifiée
            window.history.replaceState({}, '', '/');

        }
    }

  const path = window.location.pathname;
  // Récupération de l'URL actuelle
  const actualRoute = getRouteByUrl(path);
  // Récupération du contenu HTML de la route
  const html = await fetch(actualRoute.pathHtml).then((data) => data.text());
  // Ajout du contenu HTML à l'élément avec l'ID "main-page"
  document.getElementById("main-page").innerHTML = html;
  // Signaler que le contenu de la page a été chargé
  document.dispatchEvent(new Event('pageChargee'));
    // Masquer la section contact commune sur la page du formulaire
  const contactSection = document.getElementById("contact");
    // Ouvrir la connexion si le visiteur voulait accéder à /commande
  if (ouvrirModaleConnexion) {

      const modalConnexion = document.getElementById('modal-connexion');

      if (modalConnexion) {

          const modal = bootstrap.Modal.getOrCreateInstance(modalConnexion);

          modal.show();
      }
  }

if (contactSection) {
    contactSection.classList.toggle(
        "d-none",
        actualRoute.url === "/contact"
    );
}

  // Ajout du contenu JavaScript
  if (actualRoute.pathJS != "") {
    // Création d'une balise script
    var scriptTag = document.createElement("script");
    scriptTag.setAttribute("type", "text/javascript");
    scriptTag.setAttribute("src", actualRoute.pathJS);

    // Ajout de la balise script au corps du document
    document.querySelector("body").appendChild(scriptTag);
  }

  // Changement du titre de la page
  document.title = actualRoute.title + " - " + websiteName;
};

// Fonction pour gérer les événements de routage (clic sur les liens)
const routeEvent = (event) => {
  event = event || window.event;
  event.preventDefault();
  // Mise à jour de l'URL dans l'historique du navigateur
  window.history.pushState({}, "", event.target.href);
  // Chargement du contenu de la nouvelle page
  LoadContentPage();
};

// Gestion de l'événement de retour en arrière dans l'historique du navigateur
window.onpopstate = LoadContentPage;
// Assignation de la fonction routeEvent à la propriété route de la fenêtre
window.route = routeEvent;
// Chargement du contenu de la page au chargement initial
LoadContentPage();