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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar datos
$nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$documento = trim($_POST['documento'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validaciones
if (empty($nombre) || empty($email) || empty($documento) || empty($password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

if (!preg_match('/^\d+$/', $documento)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El documento debe contener solo números']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar si ya existe un usuario con ese email o documento
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? OR documento = ?");
    $stmt->execute([$email, $documento]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con este email o documento']);
        exit;
    }

    // Crear el administrador
    $stmt = $conn->prepare("
        INSERT INTO usuarios (nombre, correo, documento, password, rol_id) 
        VALUES (?, ?, ?, ?, 1)
    ");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    if ($stmt->execute([$nombre, $email, $documento, $hashed_password])) {
        echo json_encode([
            'success' => true,
            'message' => 'Administrador creado correctamente'
        ]);
    } else {
        throw new Exception('Error al crear el administrador');
    }

} catch (PDOException $e) {
    error_log("Error al crear administrador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el administrador'
    ]);
} catch (Exception $e) {
    error_log("Error al crear administrador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el administrador'
    ]);
} 