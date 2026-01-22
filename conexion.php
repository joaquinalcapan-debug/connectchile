<?php
$host = 'localhost';
$db = 'connectchile';
$user = 'root'; // cambia si usas otro
$pass = '';     // cambia si usas otro

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>