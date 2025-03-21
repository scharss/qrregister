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
    
    $stmt = $conn->query("SELECT id, nombre FROM grupos WHERE activo = 1 ORDER BY nombre");
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($grupos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los grupos']);
} 