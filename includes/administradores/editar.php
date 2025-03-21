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

// Validar datos
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
$nombre = trim(filter_var($_POST['nombre'], FILTER_SANITIZE_STRING));
$email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';

if (!$id || empty($nombre) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar si ya existe otro usuario con ese email
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con este email']);
        exit;
    }

    // Actualizar el administrador
    if (!empty($password)) {
        // Si se proporcionó una nueva contraseña, actualizarla también
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET nombre = ?, correo = ?, password = ? 
            WHERE id = ? AND rol_id = 1
        ");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$nombre, $email, $hashed_password, $id]);
    } else {
        // Si no se proporcionó contraseña, actualizar solo nombre y email
        $stmt = $conn->prepare("
            UPDATE usuarios 
            SET nombre = ?, correo = ? 
            WHERE id = ? AND rol_id = 1
        ");
        $stmt->execute([$nombre, $email, $id]);
    }

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Administrador actualizado correctamente'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el administrador o no se realizaron cambios'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error al actualizar administrador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el administrador'
    ]);
} 