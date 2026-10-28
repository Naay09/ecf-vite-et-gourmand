-- ==========================================
-- DONNÉES INITIALES : VITE & GOURMAND
-- ==========================================

USE vite_et_gourmand;

-- ==========================================
-- TABLE : THEME
-- ==========================================

INSERT INTO theme (nom)
VALUES
    ('Noël'),
    ('Pâques'),
    ('Classique'),
    ('Événement');


-- ==========================================
-- TABLE : REGIME
-- ==========================================

INSERT INTO regime (nom)
VALUES
    ('Classique'),
    ('Végétarien'),
    ('Vegan');


-- ==========================================
-- TABLE : ALLERGENE
-- ==========================================

INSERT INTO allergene (nom)
VALUES
    ('Céréales contenant du gluten'),
    ('Crustacés'),
    ('Œufs'),
    ('Poissons'),
    ('Arachides'),
    ('Soja'),
    ('Lait'),
    ('Fruits à coque'),
    ('Céleri'),
    ('Moutarde'),
    ('Graines de sésame'),
    ('Mollusques');


-- ==========================================
-- TABLE : PLAT
-- ==========================================

INSERT INTO plat (nom, description, categorie)
VALUES
    -- Le Grand Classique
    ('Velouté de saison, éclats de noisette', NULL, 'entree'),
    ('Suprême de volaille, jus aux herbes', NULL, 'plat'),
    ('Tarte fine aux pommes', NULL, 'dessert'),

    -- La Table de Noël
    ('Foie gras maison, chutney de figues', NULL, 'entree'),
    ('Magret de canard, sauce aux épices douces', NULL, 'plat'),
    ('Bûche chocolat et praliné', NULL, 'dessert'),

    -- Le Jardin Gourmand
    ('Légumes rôtis, crème citronnée', NULL, 'entree'),
    ('Risotto aux champignons et sauge', NULL, 'plat'),
    ('Pavlova aux fruits frais', NULL, 'dessert'),

    -- Pâques en famille
    ('Asperges, vinaigrette aux agrumes', NULL, 'entree'),
    ('Agneau confit, pommes grenailles', NULL, 'plat'),
    ('Nid chocolat et fruits rouges', NULL, 'dessert'),

    -- La Pause Végétale
    ('Houmous de petits pois, pain aux graines', NULL, 'entree'),
    ('Curry doux de légumes et riz parfumé', NULL, 'plat'),
    ('Crème coco, mangue et citron vert', NULL, 'dessert'),

    -- Le Grand Jour
    ('Bouchées apéritives du chef', NULL, 'entree'),
    ('Paleron confit, légumes de saison', NULL, 'plat'),
    ('Entremets signature au choix', NULL, 'dessert');



-- ==========================================
-- TABLE : MENU
-- ==========================================

INSERT INTO menu (
    titre,
    description,
    theme_id,
    regime_id,
    nb_personnes_min,
    prix_personne,
    stock_disponible,
    conditions,
    delai_commande_jours
)
VALUES
(
    'Le Grand Classique',
    'L’élégance d’un repas généreux, cuisiné avec les produits du marché.',
    (SELECT id FROM theme WHERE nom = 'Classique'),
    (SELECT id FROM regime WHERE nom = 'Classique'),
    8,
    42.00,
    20,
    'À conserver au frais et à consommer sous 24 h.',
    3
),
(
    'La Table de Noël',
    'Un menu de fête aux saveurs réconfortantes, prêt à partager.',
    (SELECT id FROM theme WHERE nom = 'Noël'),
    (SELECT id FROM regime WHERE nom = 'Classique'),
    6,
    58.00,
    12,
    'Retrait ou livraison le jour convenu.',
    7
),
(
    'Le Jardin Gourmand',
    'Une cuisine végétale colorée, de saison et pleine de caractère.',
    (SELECT id FROM theme WHERE nom = 'Événement'),
    (SELECT id FROM regime WHERE nom = 'Végétarien'),
    10,
    36.00,
    15,
    'Les plats sont à maintenir au frais.',
    4
),
(
    'Pâques en famille',
    'Le menu printanier qui donne le ton d’un déjeuner en famille.',
    (SELECT id FROM theme WHERE nom = 'Pâques'),
    (SELECT id FROM regime WHERE nom = 'Classique'),
    8,
    46.00,
    10,
    'À conserver au frais et à consommer sous 24 h.',
    5
),
(
    'La Pause Végétale',
    'Une proposition 100 % végétale, fraîche et naturellement gourmande.',
    (SELECT id FROM theme WHERE nom = 'Classique'),
    (SELECT id FROM regime WHERE nom = 'Vegan'),
    6,
    34.00,
    18,
    'À conserver au frais et à consommer sous 24 h.',
    3
),
(
    'Le Grand Jour',
    'Une expérience complète pour célébrer les grands moments.',
    (SELECT id FROM theme WHERE nom = 'Événement'),
    (SELECT id FROM regime WHERE nom = 'Classique'),
    12,
    72.00,
    8,
    'Échange préalable avec notre équipe.',
    14
);


-- ==========================================
-- TABLE : MENU_PLAT
-- ==========================================

INSERT INTO menu_plat (menu_id, plat_id)
SELECT m.id, p.id
FROM menu AS m
CROSS JOIN plat AS p
WHERE
    (m.titre = 'Le Grand Classique' AND p.nom IN (
        'Velouté de saison, éclats de noisette',
        'Suprême de volaille, jus aux herbes',
        'Tarte fine aux pommes'
    ))
    OR
    (m.titre = 'La Table de Noël' AND p.nom IN (
        'Foie gras maison, chutney de figues',
        'Magret de canard, sauce aux épices douces',
        'Bûche chocolat et praliné'
    ))
    OR
    (m.titre = 'Le Jardin Gourmand' AND p.nom IN (
        'Légumes rôtis, crème citronnée',
        'Risotto aux champignons et sauge',
        'Pavlova aux fruits frais'
    ))
    OR
    (m.titre = 'Pâques en famille' AND p.nom IN (
        'Asperges, vinaigrette aux agrumes',
        'Agneau confit, pommes grenailles',
        'Nid chocolat et fruits rouges'
    ))
    OR
    (m.titre = 'La Pause Végétale' AND p.nom IN (
        'Houmous de petits pois, pain aux graines',
        'Curry doux de légumes et riz parfumé',
        'Crème coco, mangue et citron vert'
    ))
    OR
    (m.titre = 'Le Grand Jour' AND p.nom IN (
        'Bouchées apéritives du chef',
        'Paleron confit, légumes de saison',
        'Entremets signature au choix'
    ));


-- ==========================================
-- TABLE : PLAT_ALLERGENE
-- ==========================================

INSERT INTO plat_allergene (plat_id, allergene_id)
SELECT p.id, a.id
FROM plat AS p
CROSS JOIN allergene AS a
WHERE
    -- Le Grand Classique
    (p.nom = 'Velouté de saison, éclats de noisette'
     AND a.nom IN ('Fruits à coque', 'Lait'))

    OR (p.nom = 'Suprême de volaille, jus aux herbes'
     AND a.nom = 'Lait')

    OR (p.nom = 'Tarte fine aux pommes'
     AND a.nom IN ('Céréales contenant du gluten', 'Lait'))

    -- La Table de Noël
    OR (p.nom = 'Magret de canard, sauce aux épices douces'
     AND a.nom = 'Lait')

    OR (p.nom = 'Bûche chocolat et praliné'
     AND a.nom IN (
        'Céréales contenant du gluten',
        'Œufs', 'Lait', 'Fruits à coque'
     ))

    -- Le Jardin Gourmand
    OR (p.nom = 'Légumes rôtis, crème citronnée'
     AND a.nom = 'Lait')

    OR (p.nom = 'Risotto aux champignons et sauge'
     AND a.nom = 'Lait')

    OR (p.nom = 'Pavlova aux fruits frais'
     AND a.nom IN ('Œufs', 'Lait'))

    -- Pâques en famille
    OR (p.nom = 'Nid chocolat et fruits rouges'
     AND a.nom IN (
        'Céréales contenant du gluten', 'Œufs', 'Lait'
     ))

    -- La Pause Végétale
    OR (p.nom = 'Houmous de petits pois, pain aux graines'
     AND a.nom IN (
        'Graines de sésame',
        'Céréales contenant du gluten'
     ))

    -- Le Grand Jour
    OR (p.nom = 'Bouchées apéritives du chef'
     AND a.nom IN (
        'Céréales contenant du gluten', 'Œufs', 'Lait'
     ))

    OR (p.nom = 'Entremets signature au choix'
     AND a.nom IN (
        'Céréales contenant du gluten', 'Œufs', 'Lait'
     ));


-- ==========================================
-- TABLE : IMAGE_MENU
-- ==========================================

INSERT INTO image_menu (
    menu_id,
    titre,
    url,
    texte_alternatif,
    ordre
)
VALUES
-- Le Grand Classique
(
    (SELECT id FROM menu WHERE titre = 'Le Grand Classique'),
    'Le Grand Classique',
    'images/le-grand-classique.png',
    'Présentation du menu Le Grand Classique',
    0
),
(
    (SELECT id FROM menu WHERE titre = 'Le Grand Classique'),
    'Velouté de saison',
    'images/gc-1.png',
    'Velouté de saison aux éclats de noisette',
    1
),
(
    (SELECT id FROM menu WHERE titre = 'Le Grand Classique'),
    'Suprême de volaille',
    'images/gc-2.png',
    'Suprême de volaille accompagné de son jus aux herbes',
    2
),
(
    (SELECT id FROM menu WHERE titre = 'Le Grand Classique'),
    'Tarte fine aux pommes',
    'images/gc-3.png',
    'Tarte fine aux pommes',
    3
),

-- La Table de Noël
(
    (SELECT id FROM menu WHERE titre = 'La Table de Noël'),
    'La Table de Noël',
    'images/la-table-de-noel.png',
    'Présentation du menu de Noël',
    0
),

-- Le Jardin Gourmand
(
    (SELECT id FROM menu WHERE titre = 'Le Jardin Gourmand'),
    'Le Jardin Gourmand',
    'images/le-jardin-gourmand.png',
    'Présentation du menu végétarien Le Jardin Gourmand',
    0
),

-- Pâques en famille
(
    (SELECT id FROM menu WHERE titre = 'Pâques en famille'),
    'Pâques en famille',
    'images/paques-en-famille.png',
    'Présentation du menu de Pâques',
    0
),

-- La Pause Végétale
(
    (SELECT id FROM menu WHERE titre = 'La Pause Végétale'),
    'La Pause Végétale',
    'images/la-pause-vegan.png',
    'Présentation du menu vegan La Pause Végétale',
    0
),

-- Le Grand Jour
(
    (SELECT id FROM menu WHERE titre = 'Le Grand Jour'),
    'Le Grand Jour',
    'images/le-grand-jour.png',
    'Présentation du menu événementiel Le Grand Jour',
    0
);


-- ==========================================
-- TABLE : COMPTE
-- ==========================================

INSERT INTO compte (
    nom,
    prenom,
    email,
    telephone,
    adresse,
    mot_de_passe_hash,
    role,
    actif
)
VALUES
(
    'Test',
    'Client',
    'client@example.com',
    '0600000000',
    '10 rue Exemple, 33000 Bordeaux',
    '$2y$10$AopzS8pKTXJn.KIyuG1Tg.OLSA7gmQXJxFfCvHahDTAHe1.q3T5Y2',
    'utilisateur',
    TRUE
),
(
    'Martin',
    'Julie',
    'julie@example.com',
    NULL,
    NULL,
    '$2y$10$PCjUfK5X22f/JKbEFSXhp.miZ2/DKxrChiR0oeHlXH2/JhREKbAZ2',
    'employe',
    TRUE
),
(
    'Martin',
    'José',
    'jose@example.com',
    NULL,
    NULL,
    '$2y$10$ckF00LuHwZ1MxbdQaVpWvutiQ.Ix8YZoDEyEMP0NC3EqftUJk1yEG',
    'administrateur',
    TRUE
);


-- ==========================================
-- TABLE : COMMANDE
-- ==========================================

INSERT INTO commande (
    compte_id,
    menu_id,
    date_creation,
    date_prestation,
    heure_livraison,
    adresse_livraison,
    ville_livraison,
    distance_livraison_km,
    nom_client,
    prenom_client,
    email_client,
    telephone_client,
    nb_personnes,
    prix_menu,
    remise,
    frais_livraison,
    total,
    statut_courant
)
VALUES
-- Commande 1 : en attente
(
    (SELECT id FROM compte WHERE email = 'client@example.com'),
    (SELECT id FROM menu WHERE titre = 'Le Grand Classique'),
    '2026-10-28 10:00:00',
    '2026-11-15',
    '12:00:00',
    '10 rue Exemple, 33000 Bordeaux',
    'Bordeaux',
    0,
    'Test',
    'Client',
    'client@example.com',
    '0600000000',
    8,
    336.00,
    0.00,
    0.00,
    336.00,
    'en_attente'
),

-- Commande 2 : acceptée
(
    (SELECT id FROM compte WHERE email = 'client@example.com'),
    (SELECT id FROM menu WHERE titre = 'La Table de Noël'),
    '2026-10-27 09:00:00',
    '2026-12-24',
    '19:00:00',
    '10 rue Exemple, 33000 Bordeaux',
    'Bordeaux',
    0,
    'Test',
    'Client',
    'client@example.com',
    '0600000000',
    12,
    696.00,
    69.60,
    0.00,
    626.40,
    'accepté'
),

-- Commande 3 : terminée
(
    (SELECT id FROM compte WHERE email = 'client@example.com'),
    (SELECT id FROM menu WHERE titre = 'Le Jardin Gourmand'),
    '2026-10-08 14:00:00',
    '2026-10-15',
    '12:30:00',
    '10 rue Exemple, 33000 Bordeaux',
    'Bordeaux',
    0,
    'Test',
    'Client',
    'client@example.com',
    '0600000000',
    10,
    360.00,
    0.00,
    0.00,
    360.00,
    'terminEe'
);


-- ==========================================
-- TABLE : HISTORIQUE_STATUT
-- ==========================================

INSERT INTO historique_statut (
    commande_id,
    compte_id,
    statut,
    date_modification
)
VALUES

-- Le Grand Classique
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Grand Classique')
     AND email_client = 'client@example.com'),
    NULL,
    'en_attente',
    '2026-10-28 10:00:00'
),

-- La Table de Noël
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'La Table de Noël')
     AND email_client = 'client@example.com'),
    NULL,
    'en_attente',
    '2026-10-27 09:00:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'La Table de Noël')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'accepte',
    '2026-10-27 11:30:00'
),

-- Le Jardin Gourmand
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    NULL,
    'en_attente',
    '2026-10-08 14:00:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'accepte',
    '2026-10-09 09:00:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'en_preparation',
    '2026-10-15 08:00:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'en_cours_de_livraison',
    '2026-10-15 11:30:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'livre',
    '2026-10-15 12:30:00'
),
(
    (SELECT c.id
     FROM commande AS c
     JOIN menu AS m ON c.menu_id = m.id
     WHERE m.titre = 'Le Jardin Gourmand'
     AND c.email_client = 'client@example.com'),
    (SELECT id FROM compte
     WHERE email = 'julie@example.com'),
    'en_attente_retour_materiel',
    '2026-10-15 12:30:00'
),
(
    (SELECT id FROM commande
     WHERE menu_id = (SELECT id FROM menu
                      WHERE titre = 'Le Jardin Gourmand')
     AND email_client = 'client@example.com'),
    (SELECT id FROM compte WHERE email = 'julie@example.com'),
    'terminee',
    '2026-10-15 13:00:00'
);



-- ==========================================
-- TABLE : CONTACT_COMMANDE
-- ==========================================

INSERT INTO contact_commande (
    commande_id,
    compte_id,
    date_heure,
    canal,
    motif,
    action
)
VALUES
(
    (
        SELECT c.id
        FROM commande AS c
        JOIN menu AS m ON c.menu_id = m.id
        WHERE m.titre = 'La Table de Noël'
        AND c.email_client = 'client@example.com'
    ),
    (SELECT id FROM compte
     WHERE email = 'julie@example.com'),
    '2026-10-27 10:30:00',
    'telephone',
    'Confirmation de la modification de l’heure de livraison.',
    'modification'
),
(
    (
        SELECT c.id
        FROM commande AS c
        JOIN menu AS m ON c.menu_id = m.id
        WHERE m.titre = 'Le Jardin Gourmand'
        AND c.email_client = 'client@example.com'
    ),
    (SELECT id FROM compte
     WHERE email = 'julie@example.com'),
    '2026-10-09 10:00:00',
    'email',
    'Échange concernant une modification de l’adresse de livraison.',
    'modification'
);


-- ==========================================
-- TABLE : PRET_MATERIEL
-- ==========================================

INSERT INTO pret_materiel (
    commande_id,
    description,
    date_pret,
    date_limite_retour,
    date_retour
)
VALUES (
    (
        SELECT c.id
        FROM commande AS c
        JOIN menu AS m ON c.menu_id = m.id
        WHERE m.titre = 'Le Jardin Gourmand'
        AND c.email_client = 'client@example.com'
    ),
    '2 bacs de maintien au chaud',
    '2026-10-15 12:30:00',
    '2026-10-29',
    '2026-10-15 12:50:00'
);


-- ==========================================
-- TABLE : AVIS
-- ==========================================

INSERT INTO avis (
    commande_id,
    note,
    commentaire,
    date_creation,
    moderation
)
VALUES (
    (
        SELECT c.id
        FROM commande AS c
        JOIN menu AS m ON c.menu_id = m.id
        WHERE m.titre = 'Le Jardin Gourmand'
        AND c.email_client = 'client@example.com'
    ),
    5,
    'Une prestation de qualité, des plats délicieux et une livraison parfaitement organisée.',
    '2026-10-16 10:30:00',
    'valide'
);


-- ==========================================
-- TABLE : HORAIRES
-- ==========================================

INSERT INTO horaires (
    jour,
    ouverture,
    fermeture,
    ferme
)
VALUES
    ('lundi', '09:00:00', '18:00:00', FALSE),
    ('mardi', '09:00:00', '18:00:00', FALSE),
    ('mercredi', '09:00:00', '18:00:00', FALSE),
    ('jeudi', '09:00:00', '18:00:00', FALSE),
    ('vendredi', '09:00:00', '18:00:00', FALSE),
    ('samedi', '09:00:00', '13:00:00', FALSE),
    ('dimanche', '09:00:00', '13:00:00', FALSE);
