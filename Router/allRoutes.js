import Route from "./route.js";

//Définir ici vos routes
export const allRoutes = [
    new Route("/", "Accueil", "/pages/public/home.html"),
    new Route("/menus", "Menus", "/pages/public/menus.php"),
    new Route("/contact", "Contact", "/pages/public/contact.php"),
    new Route("/mentions-legales", "Mentions légales", "/pages/public/mentions-legales.html"),
    new Route("/politique-de-confidentialite", "Politique de confidentialité", "/pages/public/politique-confidentialite.html"),
    new Route("/cgv", "Conditions générales de vente", "/pages/public/cgv.html"),
    new Route("/404", "Page non trouvée", "/pages/public/404.html"),
    new Route("/inscription","Inscrivez-vous","/pages/auth/signup.php"),
    new Route("/mon-compte","Mon espace","/pages/auth/user-account.php"),
    new Route("/mot-de-passe-oublie","Mot de passe oublié","/pages/auth/forgot-password.php"),
    new Route("/reinitialiser-mot-de-passe","Réinitialiser mon mot de passe","/pages/auth/reinitialiser-mot-de-passe.php"),
    new Route("/commande","Page de commande","/pages/commandes/commande.php"),
    new Route("/espace-employe","Espace Employé","/pages/employe/espace-employe.php"),
    new Route("/gestion-plats","Gestion des plats | Vite & Gourmand","/pages/employe/gestion-plats.php"),
    new Route("/gestion-menus","Gestion des menus | Vite & Gourmand","/pages/employe/gestion-menus.php"),
    new Route("/gestion-horaires","Gestion des horaires | Vite & Gourmand","/pages/employe/gestion-horaires.php"),
    new Route("/espace-admin","Espace Administrateur","/pages/admin/espace-admin.php"),
    new Route("/gestion-employes","Gestion des employés | Vite & Gourmand","/pages/admin/gestion-employes.php"),
    
];

//Le titre s'affiche comme ceci : Route.titre - websitename
export const websiteName = "Vite & Gourmand";