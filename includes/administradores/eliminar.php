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

// Validar ID
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

// No permitir eliminar el propio usuario
if ($id == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol_id = 1");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Administrador eliminado correctamente'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el administrador'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error al eliminar administrador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el administrador'
    ]);
} 