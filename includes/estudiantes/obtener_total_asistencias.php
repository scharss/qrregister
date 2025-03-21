<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['administrador', 'profesor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
    exit;
}

$estudiante_id = (int)$_GET['estudiante_id'];

$db = new Database();
$conn = $db->connect();

try {
    // Obtener total de asistencias
    $stmt = $conn->prepare("SELECT COUNT(*) FROM asistencias WHERE estudiante_id = ?");
    $stmt->execute([$estudiante_id]);
    $total = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'total' => $total]);
} catch (PDOException $e) {
    error_log("Error al obtener total de asistencias: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener total de asistencias']);
} 