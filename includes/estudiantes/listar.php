<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id 
        ORDER BY e.id DESC
    ");
    $stmt->execute();
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($estudiantes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener estudiantes']);
} 