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
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
$correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
$documento = trim($_POST['documento'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validaciones básicas
if (!$id || empty($nombre) || empty($apellidos) || empty($correo) || empty($documento)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo no válido']);
    exit;
}

if (!preg_match('/^\d+$/', $documento)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El documento debe contener solo números']);
    exit;
}

// Validar contraseña si se proporciona una nueva
if (!empty($password)) {
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
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar que el profesor existe y es realmente un profesor
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND rol_id = 2");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Profesor no encontrado']);
        exit;
    }

    // Verificar si ya existe otro usuario con ese correo o documento
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE (correo = ? OR documento = ?) AND id != ?");
    $stmt->execute([$correo, $documento, $id]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con este correo o documento']);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // Actualizar el profesor
        if (!empty($password)) {
            // Si se proporcionó una nueva contraseña, actualizarla también
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET nombre = ?, apellidos = ?, correo = ?, documento = ?, password = ? 
                WHERE id = ? AND rol_id = 2
            ");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$nombre, $apellidos, $correo, $documento, $hashed_password, $id]);
        } else {
            // Si no se proporcionó contraseña, actualizar solo los demás campos
            $stmt = $conn->prepare("
                UPDATE usuarios 
                SET nombre = ?, apellidos = ?, correo = ?, documento = ? 
                WHERE id = ? AND rol_id = 2
            ");
            $stmt->execute([$nombre, $apellidos, $correo, $documento, $id]);
        }

        if ($stmt->rowCount() > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Profesor actualizado correctamente'
            ]);
        } else {
            throw new Exception('No se realizaron cambios');
        }

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al actualizar profesor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el profesor'
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al actualizar profesor: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 