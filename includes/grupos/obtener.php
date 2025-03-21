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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de grupo inválido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id, nombre FROM grupos WHERE id = ? AND activo = 1");
    $stmt->execute([$id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($grupo) {
        echo json_encode([
            'success' => true,
            'grupo' => $grupo
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Grupo no encontrado'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener el grupo'
    ]);
} 