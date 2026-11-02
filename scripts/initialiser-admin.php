
<?php

// Autoriser uniquement une exécution depuis le terminal
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit.');
}

// Connexion à la base de données
require_once __DIR__ . '/../config/db-connection.php';

// Informations de José
$nom = 'Martin';
$prenom = 'José';
$email = 'jose@example.com';

// Générer un nouveau mot de passe sécurisé
$motDePasse = 'Aa!' . bin2hex(random_bytes(16));

// Protéger le mot de passe
$hash = password_hash($motDePasse, PASSWORD_DEFAULT);

// Vérifier si le compte existe déjà
$requete = $dbConnection->prepare(
    'SELECT id FROM compte WHERE email = :email'
);

$requete->execute([
    'email' => $email
]);

$compte = $requete->fetch(PDO::FETCH_ASSOC);

if ($compte) {

    // Mettre à jour le compte existant
    $requete = $dbConnection->prepare(
        "UPDATE compte
         SET mot_de_passe_hash = :hash,
             role = 'administrateur',
             actif = 1
         WHERE id = :id"
    );

    $requete->execute([
        'hash' => $hash,
        'id' => $compte['id']
    ]);

    echo "Compte de José mis à jour.\n";

} else {

    // Créer le compte administrateur
    $requete = $dbConnection->prepare(
        "INSERT INTO compte
         (nom, prenom, email, mot_de_passe_hash, role, actif)
         VALUES
         (:nom, :prenom, :email, :hash, 'administrateur', 1)"
    );

    $requete->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'hash' => $hash
    ]);

    echo "Compte administrateur de José créé.\n";
}

// Afficher le mot de passe une seule fois
echo "Identifiant : " . $email . "\n";
echo "Nouveau mot de passe : " . $motDePasse . "\n";
