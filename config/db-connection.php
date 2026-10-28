<?php
// Paramètres BDD
$host = '127.0.0.1';
$port = '8889';
$dbname = 'ecf_vite_et_gourmand';
$username = 'root';
$password = 'root';

try {
    // On se connecte à MySQL
    $dbConnection = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",$username,$password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Impossible de se connecter à la base de données.');
}