<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use chillerlan\QRCode\{QRCode, QROptions};

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

// Validar datos requeridos
$nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
$documento = htmlspecialchars(trim($_POST['documento'] ?? ''), ENT_QUOTES, 'UTF-8');
$grupo_id = !empty($_POST['grupo_id']) ? filter_var($_POST['grupo_id'], FILTER_VALIDATE_INT) : null;

if (empty($nombre) || empty($apellidos) || empty($documento)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Verificar si ya existe un estudiante con el mismo documento
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE documento = ?");
    $stmt->execute([$documento]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un estudiante con este documento']);
        exit;
    }

    // Si se especificó un grupo, verificar que existe
    if ($grupo_id !== null) {
        $stmt = $conn->prepare("SELECT id FROM grupos WHERE id = ?");
        $stmt->execute([$grupo_id]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El grupo seleccionado no existe']);
            exit;
        }
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Insertar el estudiante primero para obtener su ID
    $stmt = $conn->prepare("
        INSERT INTO estudiantes (nombre, apellidos, documento, grupo_id) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellidos, $documento, $grupo_id]);
    
    // Obtener el ID del estudiante recién creado
    $estudiante_id = $conn->lastInsertId();

    try {
        // Configurar opciones del QR
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 30,
            'imageBase64' => true,
            'bgColor' => [255, 255, 255],
            'fpColor' => [0, 0, 0],
            'quietzoneSize' => 1
        ]);

        // Generar el código QR con el ID del estudiante
        $qrcode = new QRCode($options);
        $qrImage = $qrcode->render((string)$estudiante_id);

        // Actualizar el estudiante con su código QR
        $stmt = $conn->prepare("UPDATE estudiantes SET qr_code = ? WHERE id = ?");
        $stmt->execute([$qrImage, $estudiante_id]);

        // Confirmar transacción
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Estudiante creado correctamente',
            'estudiante_id' => $estudiante_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error al generar QR: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al generar el código QR: ' . $e->getMessage()
        ]);
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al crear estudiante: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el estudiante: ' . $e->getMessage()
    ]);
} 