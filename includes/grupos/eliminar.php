<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de grupo inválido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Verificar si hay estudiantes en el grupo
    $stmt = $conn->prepare("SELECT COUNT(*) FROM estudiantes WHERE grupo_id = ? AND activo = 1");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el grupo porque tiene estudiantes asignados'
        ]);
        exit;
    }

    // Soft delete del grupo
    $stmt = $conn->prepare("UPDATE grupos SET activo = 0 WHERE id = ? AND activo = 1");
    if ($stmt->execute([$id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Grupo eliminado correctamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Grupo no encontrado'
            ]);
        }
    } else {
        throw new Exception('Error al eliminar el grupo');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el grupo: ' . $e->getMessage()
    ]);
} 