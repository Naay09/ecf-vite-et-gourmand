import Route from "./route.js";

//Définir ici vos routes
export const allRoutes = [
    new Route("/", "Accueil", "/public/home.html"),
    new Route("/menus", "Menus", "/public/menus.html"),
    new Route("/contact", "Contact", "/public/contact.html"),
    new Route("/mentions-legales", "Mentions légales", "/public/mentions-legales.html"),
    new Route("/politique-de-confidentialite", "Politique de confidentialité", "/public/politique-confidentialite.html"),
    new Route("/cgv", "Conditions générales de vente", "/public/cgv.html"),
    new Route("/404", "Page non trouvée", "/public/404.html"),
];

//Le titre s'affiche comme ceci : Route.titre - websitename
export const websiteName = "Vite & Gourmand";