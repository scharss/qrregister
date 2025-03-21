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

// Obtener y validar el ID del estudiante
$estudiante_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$estudiante_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT e.*, g.nombre as grupo_nombre 
        FROM estudiantes e 
        LEFT JOIN grupos g ON e.grupo_id = g.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$estudiante_id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'estudiante' => $estudiante
    ]);

} catch (PDOException $e) {
    error_log("Error al obtener estudiante: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los datos del estudiante'
    ]);
} 