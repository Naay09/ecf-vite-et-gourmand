
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Autoriser uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Récupérer l'adresse e-mail
$email = $_POST['email'] ?? '';

// Vérifier l'adresse e-mail
if (
    !is_string($email) ||
    strlen($email) > 254 ||
    !filter_var(trim($email), FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Adresse e-mail invalide.'
    ]);

    exit;
}

$email = trim($email);

// Charger la connexion PDO et le service d'e-mails
require_once __DIR__ . '/../config/db-connection.php';
require_once __DIR__ . '/../includes/mail-service.php';

// Message identique pour toutes les adresses valides
$messageConfirmation = 'Si cette adresse est associée à un compte, vous recevrez un e-mail contenant un lien de réinitialisation.';

try {

    // Rechercher un compte client actif
    $requete = $dbConnection->prepare(
        "SELECT id, email
         FROM compte
         WHERE email = :email
         AND role = 'utilisateur'
         AND actif = 1"
    );

    $requete->execute([
        'email' => $email
    ]);

    $compte = $requete->fetch(PDO::FETCH_ASSOC);

    if ($compte) {

        // Éviter plusieurs demandes en moins d'une minute
        $requete = $dbConnection->prepare(
            'SELECT id
             FROM reinitialisation_mot_de_passe
             WHERE compte_id = :compte_id
             AND date_creation > DATE_SUB(NOW(), INTERVAL 60 SECOND)
             LIMIT 1'
        );

        $requete->execute([
            'compte_id' => $compte['id']
        ]);

        $demandeRecente = $requete->fetch();

        if (!$demandeRecente) {

            // Générer un jeton aléatoire
            $jeton = bin2hex(random_bytes(32));

            // Calculer son empreinte
            $jetonHash = hash('sha256', $jeton);

            // Regrouper les modifications SQL
            $dbConnection->beginTransaction();

            // Invalider les anciens liens
            $requete = $dbConnection->prepare(
                'UPDATE reinitialisation_mot_de_passe
                 SET date_utilisation = NOW()
                 WHERE compte_id = :compte_id
                 AND date_utilisation IS NULL'
            );

            $requete->execute([
                'compte_id' => $compte['id']
            ]);

            // Enregistrer le nouveau jeton et son expiration
            $requete = $dbConnection->prepare(
                'INSERT INTO reinitialisation_mot_de_passe
                 (compte_id, jeton_hash, date_expiration)
                 VALUES
                 (:compte_id, :jeton_hash,
                  DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
            );

            $requete->execute([
                'compte_id' => $compte['id'],
                'jeton_hash' => $jetonHash
            ]);

            $dbConnection->commit();

            // Préparer le lien de réinitialisation
            $config = require __DIR__ . '/../config/mail.php';

            $lien = $config['site_url']
                . '/reinitialiser-mot-de-passe?jeton='
                . $jeton;

            $sujet = 'Réinitialisation de votre mot de passe';

            $message = "Bonjour,\n\n"
                . "Vous avez demandé la réinitialisation de votre mot de passe.\n\n"
                . "Pour choisir un nouveau mot de passe, utilisez ce lien :\n"
                . $lien . "\n\n"
                . "Ce lien est valable pendant 30 minutes et ne peut être utilisé qu'une seule fois.\n\n"
                . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.\n\n"
                . "L'équipe Vite & Gourmand";

            // Envoyer le message avec PHPMailer
            $emailEnvoye = envoyerMail(
                $compte['email'],
                $sujet,
                $message
            );

            if (!$emailEnvoye) {
                error_log('Échec de l’envoi du lien de réinitialisation.');
            }
        }
    }

    // Même réponse que le compte existe ou non
    echo json_encode([
        'success' => true,
        'message' => $messageConfirmation
    ]);

} catch (Throwable $erreur) {

    // Annuler les modifications SQL non validées
    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de traiter votre demande pour le moment.'
    ]);
}
