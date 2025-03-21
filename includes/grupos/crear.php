<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener y validar los datos
$nombre = trim($_POST['nombre'] ?? '');

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre del grupo es requerido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Verificar si ya existe un grupo con ese nombre
    $stmt = $conn->prepare("SELECT id FROM grupos WHERE nombre = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un grupo con ese nombre']);
        exit;
    }

    // Insertar el nuevo grupo
    $stmt = $conn->prepare("INSERT INTO grupos (nombre, activo) VALUES (?, 1)");
    if ($stmt->execute([$nombre])) {
        $id = $conn->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Grupo creado correctamente',
            'grupo' => [
                'id' => $id,
                'nombre' => $nombre
            ]
        ]);
    } else {
        throw new Exception('Error al crear el grupo');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el grupo: ' . $e->getMessage()
    ]);
} 