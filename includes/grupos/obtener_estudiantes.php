<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que se recibió el ID del grupo
if (!isset($_GET['grupo_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de grupo no proporcionado']);
    exit;
}

$grupo_id = intval($_GET['grupo_id']);

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener estudiantes del grupo
    $stmt = $conn->prepare("
        SELECT 
            e.id,
            e.nombre,
            e.apellidos,
            e.documento
        FROM estudiantes e
        WHERE e.grupo_id = ? AND e.activo = 1
        ORDER BY e.apellidos, e.nombre
    ");
    
    $stmt->execute([$grupo_id]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'estudiantes' => $estudiantes,
        'total' => count($estudiantes)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los estudiantes: ' . $e->getMessage()
    ]);
} 