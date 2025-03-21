<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        echo "Conexión exitosa a la base de datos";
    } else {
        echo "Error al conectar a la base de datos";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log($e->getMessage());
} 