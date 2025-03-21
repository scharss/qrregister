<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id, nombre, created_at FROM grupos WHERE activo = 1 ORDER BY id DESC");
    $stmt->execute();
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($grupos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
} 