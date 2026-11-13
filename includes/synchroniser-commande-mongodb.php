<?php

/* SYNCHRONISATION D'UNE COMMANDE VERS MONGODB */

function synchroniserCommandeMongoDB($commandeId, $dbConnection) {

    // Charger la connexion MongoDB
    require __DIR__ . '/../config/mongodb.php';

    // Récupérer la commande depuis MySQL
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
         LEFT JOIN menu m ON m.id = c.menu_id
         WHERE c.id = :id"
    );

    $requete->execute([
        'id' => $commandeId
    ]);

    $commande = $requete->fetch(PDO::FETCH_ASSOC);

    // Vérifier que la commande existe
    if (!$commande) {
        return false;
    }

    // Convertir la date MySQL
    $date = new DateTimeImmutable(
        $commande['date_creation'],
        new DateTimeZone('Europe/Paris')
    );

    $dateMongo = new MongoDB\BSON\UTCDateTime(
        $date->getTimestamp() * 1000
    );

    // Préparer les données MongoDB
    $document = [
        'commande_id' => (int) $commande['id'],
        'menu_id' => (int) $commande['menu_id'],
        'menu_titre' => $commande['menu_titre'],
        'date_creation' => $dateMongo,
        'nb_personnes' => (int) $commande['nb_personnes'],
        'total' => (float) $commande['total'],
        'statut' => $commande['statut_courant']
    ];

    // Préparer la mise à jour MongoDB
    $operations = new MongoDB\Driver\BulkWrite();

    $operations->update(
        [
            'commande_id' => (int) $commandeId
        ],
        [
            '$set' => $document
        ],
        [
            'upsert' => true
        ]
    );

    // Enregistrer dans MongoDB
    $mongoConnection->executeBulkWrite(
        $mongoDatabase . '.' . $mongoCollection,
        $operations
    );

    return true;
}
