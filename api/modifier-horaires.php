<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);

    exit;
}

// Vérifier les droits
require __DIR__ . '/../includes/auth.php';

verifierAcces(['employe', 'administrateur']);

// Connexion PDO
require __DIR__ . '/../config/db-connection.php';

// Récupérer les données du formulaire
$ouvertures = $_POST['ouverture'] ?? [];
$fermetures = $_POST['fermeture'] ?? [];
$joursFermes = $_POST['ferme'] ?? [];
$jeton = $_POST['csrf_token'] ?? '';

// Vérifier le jeton CSRF
if (
    !is_string($jeton) ||
    empty($_SESSION['csrf_employe']) ||
    !hash_equals($_SESSION['csrf_employe'], $jeton)
) {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Jeton de sécurité invalide.'
    ]);

    exit;
}

// Vérifier le format des tableaux
if (
    !is_array($ouvertures) ||
    !is_array($fermetures) ||
    !is_array($joursFermes)
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Données des horaires invalides.'
    ]);

    exit;
}

try {

    // Démarrer une transaction
    $dbConnection->beginTransaction();

    // Récupérer les sept jours enregistrés
    $requeteJours = $dbConnection->prepare(
        "SELECT
            id,
            jour,
            ouverture,
            fermeture
        FROM horaires
        ORDER BY id
        FOR UPDATE"
    );

    $requeteJours->execute();

    $jours = $requeteJours->fetchAll(PDO::FETCH_ASSOC);

    if (count($jours) !== 7) {

        throw new InvalidArgumentException(
            'La table des horaires doit contenir sept jours.'
        );
    }

    // Préparer la modification SQL
    $requeteModification = $dbConnection->prepare(
        "UPDATE horaires
         SET
            ouverture = :ouverture,
            fermeture = :fermeture,
            ferme = :ferme
         WHERE id = :id"
    );

    // Vérifier et enregistrer chaque jour
    foreach ($jours as $jour) {

        $id = $jour['id'];

        // Déterminer si le jour est fermé
        $ferme = isset($joursFermes[$id]) ? 1 : 0;

        if ($ferme === 1) {

            $ouverture = null;
            $fermeture = null;

        } else {

            // Récupérer les nouvelles heures
            $ouverture = $ouvertures[$id] ?? '';
            $fermeture = $fermetures[$id] ?? '';

            // Vérifier leur format HH:MM
            if (
                !is_string($ouverture) ||
                !is_string($fermeture) ||
                !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $ouverture) ||
                !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $fermeture)
            ) {

                throw new InvalidArgumentException(
                    'Format horaire invalide pour ' . $jour['jour'] . '.'
                );
            }

            // Vérifier que la fermeture est après l'ouverture
            if ($fermeture <= $ouverture) {

                throw new InvalidArgumentException(
                    'L’heure de fermeture doit être après ' .
                    'l’ouverture pour ' . $jour['jour'] . '.'
                );
            }
        }

        // Enregistrer les informations du jour
        $requeteModification->execute([
            'ouverture' => $ouverture,
            'fermeture' => $fermeture,
            'ferme' => $ferme,
            'id' => $id
        ]);
    }

    // Valider les modifications
    $dbConnection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Les horaires ont été enregistrés avec succès.'
    ]);

} catch (InvalidArgumentException $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $erreur->getMessage()
    ]);

} catch (Throwable $erreur) {

    if ($dbConnection->inTransaction()) {
        $dbConnection->rollBack();
    }

    error_log($erreur->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Impossible de modifier les horaires.'
    ]);
}
