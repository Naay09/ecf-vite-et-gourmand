# Vite et Gourmand

## Présentation du projet

Vite et Gourmand est une application web développée dans le cadre de mon Évaluation en Cours de Formation (ECF) pour le titre professionnel Développeur Web et Web Mobile.

Le projet répond aux besoins d'une entreprise fictive de traiteur située à Bordeaux, créée et gérée par Julie et José.

L'objectif est de permettre à l'entreprise de présenter ses menus en ligne, de faciliter la prise de commandes et de centraliser la gestion de son activité.

L'application dispose de trois espaces adaptés aux différents profils d'utilisateurs : client, employé et administrateur.

## Fonctionnalités principales

### Visiteur
- Consulter les menus proposés par le traiteur.
- Rechercher et filtrer les menus selon différents critères.
- Consulter les détails des menus, les plats et leurs allergènes.
- Consulter les avis clients validés.
- Contacter l'entreprise.
- Créer un compte ou se connecter.

### Client
- Gérer ses informations personnelles.
- Commander un menu pour une prestation.
- Consulter le détail et le suivi de ses commandes.
- Modifier ou annuler une commande avant son acceptation.
- Déposer un avis après la réalisation d'une commande.
- Réinitialiser son mot de passe en cas d'oubli.

### Employé
- Consulter et gérer les commandes des clients.
- Modifier les statuts des commandes.
- Gérer les annulations et les retours de matériel.
- Ajouter, modifier et supprimer des menus et des plats.
- Gérer les horaires de l'entreprise.
- Modérer les avis clients.

### Administrateur
- Accéder aux fonctionnalités de l'espace employé.
- Créer et gérer les comptes employés.
- Activer ou désactiver un compte employé.
- Consulter les statistiques des commandes et du chiffre d'affaires.
- Filtrer les statistiques par menu et par période.

## Technologies utilisées

### Front-end

- HTML5 : structure des pages.
- CSS3 / SASS : mise en forme et personnalisation de l'interface.
- Bootstrap 5 : composants visuels et mise en page responsive.
- Bootstrap Icons : icônes de l'interface.
- JavaScript : interactions, formulaires, filtres et chargement dynamique des pages.
- Chart.js : affichage des graphiques dans l'espace administrateur.

### Back-end

- PHP : traitement des requêtes, logique métier et gestion des sessions.
- PDO : communication sécurisée avec la base de données MySQL.
- API PHP : échanges de données entre l'interface et le serveur.
- Mailtrap : envoi des e-mails via SMTP.

### Bases de données

- MySQL : stockage des utilisateurs, menus, plats, commandes, avis et autres données métier.
- MongoDB : stockage des données utilisées pour les statistiques des commandes.

### Outils de développement

- Visual Studio Code : éditeur de code.
- MAMP : environnement local PHP et MySQL.
- phpMyAdmin : administration de la base de données MySQL.
- MongoDB Shell : consultation et administration de MongoDB.

## Architecture de l'application

Vite et Gourmand repose sur une architecture web composée de trois parties :

1. Interface utilisateur : les pages HTML, les styles CSS et les scripts JavaScript assurent l'affichage et les interactions.
2. Serveur PHP : les fichiers PHP traitent les formulaires, vérifient les autorisations et exécutent les opérations métier.
3. Bases de données : MySQL conserve les données principales de l'application, tandis que MongoDB est utilisé pour les statistiques.

La navigation utilise un routeur JavaScript permettant de charger dynamiquement les différentes pages de l'application.

### Gestion des accès

L'application distingue trois rôles :

- `utilisateur` : accès à l'espace client.
- `employe` : accès aux fonctionnalités de gestion opérationnelle.
- `administrateur` : accès aux fonctionnalités employé et aux outils d'administration.

Les autorisations sont vérifiées côté serveur grâce aux sessions PHP et aux contrôles d'accès présents dans les API.

## Installation et lancement en local

### 1. Prérequis

Pour faire fonctionner l'application en local, les outils suivants sont nécessaires :

- PHP 8.3 ou version compatible avec le projet.
- MySQL 8.
- MongoDB 7.
- Composer pour les dépendances PHP.
- Node.js et npm pour les dépendances front-end.
- Un serveur web local capable d'exécuter PHP, par exemple MAMP.

Les extensions PHP nécessaires aux connexions PDO MySQL et MongoDB doivent être activées.

### 2. Récupération du projet

Placer le dossier `ecf-vite-et-gourmand` dans le répertoire de travail souhaité, puis l'ouvrir dans Visual Studio Code.

### 3. Installation des dépendances

Installer les dépendances PHP définies dans `composer.json` :

composer install


Installer les dépendances JavaScript définies dans `package.json` :

npm install


Ces opérations nécessitent que Composer, PHP, Node.js et npm soient correctement configurés sur la machine.

### 4. Configuration de MySQL

Démarrer le serveur MySQL depuis l'environnement local.

Créer une base de données pour l'application, puis importer les scripts SQL présents dans le dossier `database/` :

- `bdd.sql` : structure de la base de données.
- `donnees.sql` : données de démonstration.

L'importation peut être réalisée depuis phpMyAdmin, en important d'abord la structure puis les données.

Configurer ensuite la connexion MySQL dans le fichier :

`config/db-connection.php`

Les paramètres de connexion doivent correspondre à l'environnement local.


### 5. Configuration de MongoDB

Démarrer le serveur MongoDB local.

L'application utilise MongoDB pour enregistrer les informations nécessaires aux statistiques des commandes.

La connexion est configurée dans :

`config/mongodb.php`

La collection utilisée est :

`commandes_statistiques`

Le projet comporte également un script permettant d'importer ou de synchroniser les commandes existantes depuis MySQL :

`scripts/importer-commandes-mongodb.php`

Ce script doit être exécuté depuis un terminal avec une version de PHP disposant de l'extension MongoDB.

### 6. Configuration des e-mails

Les envois d'e-mails sont assurés par Mailtrap avec un serveur SMTP.

Les paramètres de connexion sont définis dans :

`config/mail.php`

La configuration doit notamment préciser :

- Le serveur SMTP.
- Le port de connexion.
- Le mode de chiffrement.
- L'identifiant et le mot de passe SMTP.
- L'adresse d'expédition.

Pour les tests locaux, un service SMTP de test peut être utilisé.

### 7. Démarrage de l'application

Démarrer les services MySQL et MongoDB ainsi que le serveur PHP local.

Dans l'environnement utilisé pendant le développement, l'application est accessible à l'adresse :

http://localhost:3000/


Cette adresse est propre à la configuration locale et peut être différente sur une autre machine.

### 8. Vérification du fonctionnement

Après le démarrage, vérifier les fonctionnalités principales :

- Affichage de la page d'accueil et des menus.
- Chargement des images et des détails des menus.
- Inscription et connexion des utilisateurs.
- Accès aux espaces client, employé et administrateur selon les rôles.
- Création et suivi d'une commande.
- Envoi des e-mails.
- Affichage des statistiques dans l'espace administrateur.

## Structure du projet

Le projet Vite et Gourmand est organisé en plusieurs dossiers afin de séparer les différentes responsabilités de l'application.


| `api/` | Contient les fichiers PHP qui traitent les requêtes de l'application : authentification, commandes, menus, avis, employés et statistiques. |
| `config/` | Regroupe les fichiers de configuration des connexions MySQL, MongoDB et du service SMTP. |
| `database/` | Contient les scripts SQL permettant de créer et d'alimenter la base de données MySQL. |
| `images/` | Contient les images utilisées dans l'application, notamment celles des menus et des plats. |
| `includes/` | Regroupe les composants PHP réutilisables, les contrôles d'accès et certaines fonctions métier. |
| `js/` | Contient les scripts JavaScript responsables des interactions, formulaires et fonctionnalités dynamiques. |
| `node_modules/` | Contient les dépendances installées avec npm. |
| `pages/` | Contient les pages et fragments HTML/PHP de l'application, organisés selon leurs fonctionnalités. |
| `Router/` | Contient les fichiers JavaScript permettant de gérer les routes et le chargement des pages. |
| `scripts/` | Regroupe les scripts utilitaires et de maintenance, notamment l'importation des commandes dans MongoDB. |
| `vendor/` | Contient les dépendances PHP installées avec Composer. |
| `index.html` | Point d'entrée principal de l'interface web. |
| `composer.json` | Déclare les dépendances PHP du projet. |
| `package.json` | Déclare les dépendances JavaScript du projet. |
| `README.md` | Documentation technique et fonctionnelle de l'application. |

### Organisation du code

L'application sépare le traitement des données et leur affichage.

Les pages sont chargées par le routeur JavaScript. Les interactions utilisateur déclenchent des requêtes vers les fichiers PHP présents dans `api/`.

Ces fichiers exécutent les vérifications nécessaires, communiquent avec les bases de données et transmettent les résultats à l'interface.

Les composants communs sont regroupés dans `includes/` afin d'éviter de répéter certaines fonctionnalités dans plusieurs fichiers.

Cette organisation facilite la maintenance et l'évolution de l'application.

## Sécurité de l'application

La sécurité de Vite et Gourmand repose sur plusieurs mécanismes destinés à protéger les comptes utilisateurs, les données et les fonctionnalités de gestion.

### Authentification et mots de passe

- Les mots de passe sont hachés avec la fonction PHP `password_hash()`.
- Les mots de passe sont vérifiés avec `password_verify()`.
- Des critères de complexité sont imposés lors de la création d'un compte.
- Les sessions PHP permettent de conserver l'authentification des utilisateurs.
- L'identifiant de session est renouvelé après une connexion réussie.
- Les comptes désactivés ne peuvent plus se connecter ni accéder aux fonctionnalités protégées.
- Les utilisateurs peuvent demander la réinitialisation de leur mot de passe par e-mail.

La réinitialisation du mot de passe est actuellement réservée aux comptes ayant le rôle `utilisateur`.

### Gestion des rôles et des autorisations

L'application distingue trois rôles : `utilisateur`, `employe` et `administrateur`.

Chaque espace dispose de contrôles d'accès adaptés aux permissions de son rôle.

Les autorisations sont vérifiées côté serveur dans les API PHP. La protection des routes JavaScript vient compléter ces contrôles pour la navigation.

### Protection des formulaires

Les formulaires sensibles utilisent des jetons CSRF afin de limiter les risques de requêtes non autorisées provenant d'un autre site.

Les données transmises sont également contrôlées côté serveur, notamment pour vérifier les champs obligatoires, les formats et les valeurs autorisées.

### Sécurité des bases de données

Les échanges avec MySQL utilisent PDO et des requêtes préparées afin de limiter les risques d'injection SQL.

Les opérations métier qui nécessitent plusieurs modifications liées utilisent des transactions pour préserver la cohérence des données.

Les commandes sont également synchronisées avec MongoDB pour alimenter les statistiques administrateur.

### Protection contre les contenus malveillants

Les contenus affichés dans les pages sont échappés lorsque cela est nécessaire afin de limiter les risques de scripts injectés (XSS).

Les images envoyées depuis les espaces de gestion font l'objet de contrôles côté serveur, notamment sur leur type et leur format.

### Sécurisation du déploiement

Avant la mise en production, les mesures suivantes devront être finalisées :

- Protéger les fichiers SQL, les configurations et les scripts internes contre les accès publics.
- Utiliser des variables d'environnement ou une configuration privée pour les identifiants MySQL, MongoDB et SMTP.
- Désactiver l'affichage des erreurs PHP détaillées aux visiteurs.
- Configurer HTTPS et les attributs de sécurité des cookies.
- Mettre en place les en-têtes HTTP de sécurité adaptés à l'application.
- Vérifier les droits d'accès aux bases de données et aux fichiers du serveur.

Un audit fonctionnel et de sécurité a été effectué en environnement local avant le déploiement. Les contrôles ont notamment porté sur l'authentification, les autorisations, les injections SQL, les XSS, les jetons CSRF et l'accès aux données des commandes.

## Gestion des bases de données

L'application Vite et Gourmand utilise deux systèmes de bases de données : MySQL et MongoDB.

### Base de données relationnelle MySQL

MySQL constitue la base de données principale de l'application.

Elle permet de stocker les informations nécessaires au fonctionnement du site, notamment :

- Les comptes utilisateurs et leurs rôles.
- Les menus proposés par le traiteur.
- Les plats, leurs catégories et leurs allergènes.
- Les thèmes et les régimes alimentaires.
- Les commandes des clients.
- L'historique des changements de statut.
- Les prêts et les retours de matériel.
- Les avis clients.
- Les horaires de l'entreprise.

Les relations entre les tables sont assurées par des clés étrangères.

L'application utilise PHP et PDO pour effectuer les opérations de lecture, de création, de modification et de suppression des données.

Les scripts SQL permettant de créer et d'alimenter la base se trouvent dans le dossier `database/`.

### Base de données NoSQL MongoDB

MongoDB est utilisée pour stocker les informations nécessaires aux statistiques de commandes accessibles depuis l'espace administrateur.

La base contient une collection appelée :

`commandes_statistiques`

Chaque document représente une commande et contient notamment :

- L'identifiant de la commande.
- L'identifiant du menu.
- Le titre du menu.
- La date de création.
- Le nombre de personnes.
- Le montant total.
- Le statut de la commande.

Un index unique sur `commande_id` permet d'éviter les doublons.

### Synchronisation MySQL et MongoDB

MySQL reste la source principale des données métier.

Lorsqu'une commande est créée ou que son statut est modifié, l'application enregistre d'abord les changements dans MySQL.

Après la validation de la transaction MySQL, une fonction PHP synchronise les informations de la commande dans MongoDB.

Cette synchronisation permet d'actualiser les statistiques utilisées par l'administrateur.

En cas d'indisponibilité de MongoDB, l'erreur est journalisée afin de ne pas annuler une opération métier déjà validée dans MySQL.

### Script de synchronisation manuelle

Un script PHP permet de réimporter les commandes existantes depuis MySQL vers MongoDB :

`scripts/importer-commandes-mongodb.php`

Il peut être utilisé pour initialiser la collection ou rattraper une désynchronisation entre les deux bases.

Il doit être exécuté depuis un terminal disposant d'un accès aux bases de données et de l'extension PHP MongoDB.

### Exploitation des statistiques

Dans l'espace administrateur, les statistiques combinent les deux sources de données :

- MongoDB : calcul du nombre de commandes par menu.
- MySQL : calcul du chiffre d'affaires associé aux commandes terminées.

L'administrateur peut filtrer les résultats par menu et par période.

Les résultats sont présentés sous forme d'indicateurs, de tableau et de graphique avec Chart.js.

## Déploiement en production

L'application Vite et Gourmand est déployée sur la plateforme Railway et accessible à l'adresse suivante :

**https://vite-et-gourmand-prod.up.railway.app/**

### Hébergement et architecture

L'environnement de production repose sur plusieurs services distincts :

- Railway : hébergement et déploiement de l'application.
- Railpack : installation des dépendances et construction de l'application.
- FrankenPHP et Caddy : exécution du code PHP et gestion des requêtes HTTP.
- MySQL : stockage des données relationnelles (utilisateurs, menus, plats, commandes, avis, etc.).
- MongoDB : stockage des données utilisées pour les statistiques des commandes.
- Mailtrap : gestion des e-mails transactionnels.

Les services MySQL et MongoDB sont indépendants du service qui héberge l'application PHP.

### Préparation de l'application

Une copie du projet a été préparée spécifiquement pour son déploiement sur Railway.

Plusieurs adaptations ont été nécessaires :

- Configuration du serveur avec un fichier `Caddyfile`.
- Création d'un script `start-container.sh` pour le démarrage de l'application.
- Utilisation d'un dossier `public/` comme racine web.
- Construction des fichiers publics avec `npm run build`.
- Configuration des extensions PHP nécessaires, notamment `pdo_mysql` et `mongodb`.
- Utilisation de variables d'environnement pour les informations de connexion et les paramètres sensibles.
- Mise en place d'un volume persistant monté sur `/data` pour conserver les images téléversées.

La configuration du déploiement est gérée avec Railway dans le fichier `.railway/railway.ts`.

### Déploiement avec Railway CLI

Le déploiement est effectué depuis une copie locale du projet, à l'aide de Railway CLI.

Après avoir configuré le projet et le service cible, la commande suivante permet d'envoyer l'application :

railway up "$(pwd)" --path-as-root --service vite-et-gourmand --environment production --verbose


Railway prend ensuite en charge les différentes étapes de construction :

1. Envoi et extraction des fichiers du projet.
2. Installation des dépendances PHP avec Composer.
3. Installation des dépendances JavaScript avec npm.
4. Construction des fichiers publics avec `npm run build`.
5. Préparation du serveur FrankenPHP et Caddy.
6. Démarrage du service avec `/start-container.sh`.

Un contrôle de santé est configuré sur la route `/healthz.php` afin de vérifier que l'application PHP répond.

### Configuration des bases de données

Les bases MySQL et MongoDB sont hébergées sur Railway.

Les paramètres de connexion sont transmis à l'application à l'aide de variables d'environnement, sans enregistrer les identifiants directement dans les fichiers publics.

MySQL conserve les données principales nécessaires au fonctionnement de l'application.

MongoDB est utilisé pour les statistiques des commandes, notamment leur exploitation dans l'espace administrateur.

### Adaptation de l'envoi des e-mails

L'application utilise Mailtrap pour l'envoi des e-mails transactionnels.

Lors du développement local, les e-mails étaient gérés à l'aide de Mailtrap avec le protocole SMTP**.

Cependant, une difficulté est apparue lors du déploiement sur Railway : la configuration d'hébergement choisie ne permettait pas d'établir les connexions SMTP nécessaires.

Pour résoudre ce problème, le système d'envoi a été adapté afin d'utiliser l'API HTTP de Mailtrap dans l'environnement de production.

Cette solution permet de transmettre les demandes d'envoi via des requêtes HTTPS, sans dépendre d'une connexion SMTP.

L'application dispose ainsi de deux modes d'envoi selon son environnement :

| Développement local | Mailtrap / SMTP |
| Production Railway | API HTTP Mailtrap |

Les paramètres nécessaires à l'envoi sont configurés au moyen de variables d'environnement.
