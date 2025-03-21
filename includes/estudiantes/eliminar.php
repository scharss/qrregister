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

// Obtener y validar el ID del estudiante
$estudiante_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if (!$estudiante_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Eliminar primero las asistencias del estudiante
    $stmt = $conn->prepare("DELETE FROM asistencias WHERE estudiante_id = ?");
    $stmt->execute([$estudiante_id]);

    // Luego eliminar el estudiante
    $stmt = $conn->prepare("DELETE FROM estudiantes WHERE id = ?");
    $stmt->execute([$estudiante_id]);

    // Si no se eliminó ningún estudiante, significa que no existía
    if ($stmt->rowCount() === 0) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
        exit;
    }

    // Confirmar transacción
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Estudiante eliminado correctamente'
    ]);

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    error_log("Error al eliminar estudiante: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el estudiante'
    ]);
} 