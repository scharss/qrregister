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

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nombre = trim($_POST['nombre'] ?? '');

if (!$id || empty($nombre)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Verificar si existe otro grupo con el mismo nombre
    $stmt = $conn->prepare("SELECT id FROM grupos WHERE nombre = ? AND id != ? AND activo = 1");
    $stmt->execute([$nombre, $id]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un grupo con ese nombre']);
        exit;
    }

    // Actualizar el grupo
    $stmt = $conn->prepare("UPDATE grupos SET nombre = ? WHERE id = ? AND activo = 1");
    if ($stmt->execute([$nombre, $id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Grupo actualizado correctamente',
                'grupo' => [
                    'id' => $id,
                    'nombre' => $nombre
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Grupo no encontrado']);
        }
    } else {
        throw new Exception('Error al actualizar el grupo');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el grupo: ' . $e->getMessage()
    ]);
} 