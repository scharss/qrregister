<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT u.id, u.nombre, u.apellidos 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        WHERE r.nombre = 'profesor' AND u.activo = 1 
        ORDER BY u.nombre, u.apellidos
    ");
    $stmt->execute();
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($profesores);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los profesores']);
} 