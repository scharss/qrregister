<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Estructura de la tabla usuarios:\n";
    foreach ($columns as $column) {
        echo "{$column['Field']} - {$column['Type']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 