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

$estudiante_id = filter_var($_POST['estudiante_id'], FILTER_VALIDATE_INT);
$grupo_id = filter_var($_POST['grupo_id'], FILTER_VALIDATE_INT);

if (!$estudiante_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
    exit;
}

// El grupo_id puede ser NULL si se quiere remover al estudiante del grupo
if ($grupo_id === '') {
    $grupo_id = null;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar que el estudiante existe
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE id = ?");
    $stmt->execute([$estudiante_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
        exit;
    }

    // Si se proporciona un grupo_id, verificar que existe
    if ($grupo_id !== null) {
        $stmt = $conn->prepare("SELECT id FROM grupos WHERE id = ?");
        $stmt->execute([$grupo_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Grupo no encontrado']);
            exit;
        }
    }

    // Actualizar el grupo del estudiante
    $stmt = $conn->prepare("UPDATE estudiantes SET grupo_id = ? WHERE id = ?");
    $stmt->execute([$grupo_id, $estudiante_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Grupo actualizado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el grupo'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
} 