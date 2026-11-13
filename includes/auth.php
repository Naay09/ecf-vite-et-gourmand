<?php

// Vérifier la connexion et les autorisations
function verifierAcces($rolesAutorises = []) {

    // Démarrer une session sécurisée
    require_once __DIR__ . '/session.php';

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['compte_id'])) {

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Vous devez être connecté.'
        ]);

        exit;
    }

    // Connexion à la base de données
    require __DIR__ . '/../config/db-connection.php';

    // Vérifier le rôle actuel et l'état du compte
    $requete = $dbConnection->prepare(
        'SELECT role, actif, mot_de_passe_hash FROM compte WHERE id = :id'
    );

    $requete->execute([
        'id' => $_SESSION['compte_id']
    ]);

    $compte = $requete->fetch(PDO::FETCH_ASSOC);

    // Refuser un compte inexistant ou désactivé
    if (!$compte || (int) $compte['actif'] !== 1) {

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Compte non autorisé.'
        ]);

        exit;
    }

    // Vérifier si le mot de passe a changé depuis la connexion
    $empreinteActuelle = hash('sha256', $compte['mot_de_passe_hash']);

    if (
        !isset($_SESSION['empreinte_mot_de_passe']) ||
        !is_string($_SESSION['empreinte_mot_de_passe']) ||
        !hash_equals(
            $empreinteActuelle,
            $_SESSION['empreinte_mot_de_passe']
        )
    ) {
        // Invalider l'ancienne session
        $_SESSION = [];
        session_destroy();

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Votre session a expiré. Veuillez vous reconnecter.'
        ]);

        exit;
    }

    // Vérifier les rôles autorisés
    if (
        !empty($rolesAutorises) &&
        !in_array($compte['role'], $rolesAutorises, true)
    ) {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Accès interdit.'
        ]);

        exit;
    }

    return $compte;
}
