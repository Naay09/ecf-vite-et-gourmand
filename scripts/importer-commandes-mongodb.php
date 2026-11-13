<?php

// IMPORTER LES COMMANDES MYSQL DANS MONGODB

// Ce script doit être exécuté uniquement dans le Terminal
if (PHP_SAPI !== 'cli') {
    exit('Exécution interdite.');
}

// Connexion MySQL
require __DIR__ . '/../config/db-connection.php';

// Connexion MongoDB
require __DIR__ . '/../config/mongodb.php';

try {

    // Récupérer les commandes et leurs menus
    $requete = $dbConnection->prepare(
        "SELECT
            c.id,
            c.menu_id,
            m.titre AS menu_titre,
            c.date_creation,
            c.nb_personnes,
            c.total,
            c.statut_courant
         FROM commande c
         INNER JOIN menu m ON m.id = c.menu_id
         ORDER BY c.id ASC"
    );

    $requete->execute();

    $commandes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Préparer les opérations MongoDB
    $operations = new MongoDB\Driver\BulkWrite();

    $nombreCommandes = 0;

    foreach ($commandes as $commande) {

        // Convertir la date MySQL en date MongoDB
        $date = new DateTimeImmutable(
            $commande['date_creation'],
            new DateTimeZone('Europe/Paris')
        );

        $dateMongo = new MongoDB\BSON\UTCDateTime(
            $date->getTimestamp() * 1000
        );

        // Préparer le document
        $document = [
            'commande_id' => (int) $commande['id'],
            'menu_id' => (int) $commande['menu_id'],
            'menu_titre' => $commande['menu_titre'],
            'date_creation' => $dateMongo,
            'nb_personnes' => (int) $commande['nb_personnes'],
            'total' => (float) $commande['total'],
            'statut' => $commande['statut_courant']
        ];

        // Ajouter ou actualiser la commande
        $operations->update(
            [
                'commande_id' => (int) $commande['id']
            ],
            [
                '$set' => $document
            ],
            [
                'upsert' => true
            ]
        );

        $nombreCommandes++;
    }

    // Exécuter les opérations s'il existe des commandes
    if ($nombreCommandes > 0) {

        $mongoConnection->executeBulkWrite(
            $mongoDatabase . '.' . $mongoCollection,
            $operations
        );
    }

    echo "Importation terminée.\n";
    echo "Commandes traitées : " . $nombreCommandes . "\n";

} catch (Throwable $erreur) {

    error_log($erreur->getMessage());

    echo "Erreur pendant l'importation.\n";
    exit(1);
}
