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

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['estudiante_id']) || !isset($_POST['grupo_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$estudiante_id = intval($_POST['estudiante_id']);
$grupo_id = intval($_POST['grupo_id']);

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Verificar que el estudiante pertenece al grupo
    $stmt = $conn->prepare("
        SELECT id 
        FROM estudiantes 
        WHERE id = ? AND grupo_id = ? AND activo = 1
    ");
    $stmt->execute([$estudiante_id, $grupo_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El estudiante no pertenece a este grupo']);
        exit;
    }
    
    // Remover al estudiante del grupo (establecer grupo_id a NULL)
    $stmt = $conn->prepare("
        UPDATE estudiantes 
        SET grupo_id = NULL 
        WHERE id = ? AND grupo_id = ? AND activo = 1
    ");
    
    if ($stmt->execute([$estudiante_id, $grupo_id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Estudiante removido del grupo exitosamente'
        ]);
    } else {
        throw new Exception('Error al remover al estudiante del grupo');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
} 