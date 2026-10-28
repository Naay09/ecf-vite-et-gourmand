CREATE DATABASE IF NOT EXISTS ecf_vite_et_gourmand
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Sélection de la base de données
USE ecf_vite_et_gourmand;

-- ==========================================
-- TABLE : COMPTE
-- ==========================================

CREATE TABLE compte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    telephone VARCHAR(20) NULL,
    adresse TEXT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'employe', 'administrateur')
        NOT NULL DEFAULT 'utilisateur',
    actif BOOLEAN NOT NULL DEFAULT TRUE
);


-- ==========================================
-- TABLE : THEME
-- ==========================================

CREATE TABLE theme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);


-- ==========================================
-- TABLE : REGIME
-- ==========================================

CREATE TABLE regime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);


-- ==========================================
-- TABLE : MENU
-- ==========================================

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    theme_id INT NOT NULL,
    regime_id INT NOT NULL,
    nb_personnes_min INT NOT NULL,
    prix_personne DECIMAL(10,2) NOT NULL,
    stock_disponible INT NOT NULL DEFAULT 0,
    conditions TEXT NOT NULL,
    delai_commande_jours INT NOT NULL DEFAULT 0,

    CHECK (nb_personnes_min > 0),
    CHECK (prix_personne >= 0),
    CHECK (stock_disponible >= 0),
    CHECK (delai_commande_jours >= 0),

    FOREIGN KEY (theme_id) REFERENCES theme(id),
    FOREIGN KEY (regime_id) REFERENCES regime(id)
);


-- ==========================================
-- TABLE : PLAT
-- ==========================================

CREATE TABLE plat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT NULL,
    categorie ENUM('entree', 'plat', 'dessert') NOT NULL
);


-- ==========================================
-- TABLE : MENU_PLAT
-- ==========================================

CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,

    PRIMARY KEY (menu_id, plat_id),

    FOREIGN KEY (menu_id) REFERENCES menu(id),
    FOREIGN KEY (plat_id) REFERENCES plat(id)
);


-- ==========================================
-- TABLE : IMAGE_MENU
-- ==========================================

CREATE TABLE image_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    titre VARCHAR(150) NULL,
    url VARCHAR(500) NOT NULL,
    texte_alternatif VARCHAR(255) NOT NULL,
    ordre INT NOT NULL DEFAULT 0,

    CHECK (ordre >= 0),

    FOREIGN KEY (menu_id) REFERENCES menu(id)
);


-- ==========================================
-- TABLE : ALLERGENES
-- ==========================================

CREATE TABLE allergene (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);


-- ==========================================
-- TABLE : PLAT_ALLERGENES
-- ==========================================

CREATE TABLE plat_allergene (
    plat_id INT NOT NULL,
    allergene_id INT NOT NULL,

    PRIMARY KEY (plat_id, allergene_id),

    FOREIGN KEY (plat_id) REFERENCES plat(id),
    FOREIGN KEY (allergene_id) REFERENCES allergene(id)
);


-- ==========================================
-- TABLE : COMMANDE
-- ==========================================

CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compte_id INT NOT NULL,
    menu_id INT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    adresse_livraison TEXT NOT NULL,
    ville_livraison VARCHAR(100) NOT NULL,
    distance_livraison_km DECIMAL(8,2) NULL,
    nom_client VARCHAR(100) NOT NULL,
    prenom_client VARCHAR(100) NOT NULL,
    email_client VARCHAR(255) NOT NULL,
    telephone_client VARCHAR(20) NOT NULL,
    nb_personnes INT NOT NULL,
    prix_menu DECIMAL(10,2) NOT NULL,
    remise DECIMAL(10,2) NOT NULL DEFAULT 0,
    frais_livraison DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    statut_courant ENUM(
        'en_attente',
        'accepté',
        'en_preparation',
        'en_cours_de_livraison',
        'livré',
        'en_attente_retour_materiel',
        'terminée',
        'annulée'
    ) NOT NULL DEFAULT 'en_attente',

    CHECK (nb_personnes > 0),
    CHECK (prix_menu >= 0),
    CHECK (remise >= 0),
    CHECK (frais_livraison >= 0),
    CHECK (total >= 0),
    CHECK (remise <= prix_menu),
    CHECK (distance_livraison_km IS NULL
    OR distance_livraison_km >= 0),

    FOREIGN KEY (compte_id) REFERENCES compte(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);


-- ==========================================
-- TABLE : HISTORIQUE_STATUT
-- ==========================================

CREATE TABLE historique_statut (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    compte_id INT NULL,
    statut ENUM(
        'en_attente',
        'accepte',
        'en_preparation',
        'en_cours_de_livraison',
        'livre',
        'en_attente_retour_materiel',
        'terminee',
        'annulee'
    ) NOT NULL,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (commande_id) REFERENCES commande(id),
    FOREIGN KEY (compte_id) REFERENCES compte(id)
);


-- ==========================================
-- TABLE : CONTACT_COMMANDE
-- ==========================================

CREATE TABLE contact_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    compte_id INT NOT NULL,
    date_heure DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    canal ENUM('telephone', 'email') NOT NULL,
    motif TEXT NOT NULL,
    action ENUM('modification', 'annulation') NOT NULL,

    FOREIGN KEY (commande_id) REFERENCES commande(id),
    FOREIGN KEY (compte_id) REFERENCES compte(id)
);


-- ==========================================
-- TABLE : PRET_MATERIEL
-- ==========================================

CREATE TABLE pret_materiel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    date_pret DATETIME NOT NULL,
    date_limite_retour DATE NOT NULL,
    date_retour DATETIME NULL,

    CHECK (date_limite_retour >= DATE(date_pret)),
    CHECK (date_retour IS NULL OR date_retour >= date_pret),

    FOREIGN KEY (commande_id) REFERENCES commande(id)
);


-- ==========================================
-- TABLE : AVIS
-- ==========================================

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL UNIQUE,
    note TINYINT NOT NULL,
    commentaire TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    moderation ENUM('en_attente', 'valide', 'refusee')
        NOT NULL DEFAULT 'en_attente',

    CHECK (note BETWEEN 1 AND 5),

    FOREIGN KEY (commande_id) REFERENCES commande(id)
);


-- ==========================================
-- TABLE : HORAIRES
-- ==========================================

CREATE TABLE horaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jour ENUM(
        'lundi',
        'mardi',
        'mercredi',
        'jeudi',
        'vendredi',
        'samedi',
        'dimanche'
    ) NOT NULL UNIQUE,
    ouverture TIME NULL,
    fermeture TIME NULL,
    ferme BOOLEAN NOT NULL DEFAULT FALSE,

    CHECK (
        (ferme = TRUE AND ouverture IS NULL AND fermeture IS NULL)
        OR
        (ferme = FALSE
         AND ouverture IS NOT NULL
         AND fermeture IS NOT NULL
         AND ouverture < fermeture)
    )
);
