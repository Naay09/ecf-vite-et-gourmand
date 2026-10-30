
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Fonction commune pour envoyer un e-mail
function envoyerMail($destinataire, $sujet, $message) {

    // Récupérer les paramètres SMTP
    $config = require __DIR__ . '/../config/mail.php';

    // Créer un objet PHPMailer
    $mail = new PHPMailer(true);

    try {

        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // Expéditeur et destinataire
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($destinataire);

        // Contenu du message
        $mail->isHTML(false);
        $mail->Subject = $sujet;
        $mail->Body = $message;

        // Envoyer l'e-mail
        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log('Erreur envoi mail : ' . $e->getMessage());

        return false;
    }
}
