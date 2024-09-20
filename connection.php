<?php

$dsn = 'mysql:host=127.0.0.1;port=3307; dbname=financemgmt';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo "Connection Error! " . $e->getMessage();
}
