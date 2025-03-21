<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['administrador', 'profesor'])) {
    http_response_code(403);
    echo json_encode(['data' => []]);
    exit;
}

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['data' => []]);
    exit;
}

$estudiante_id = (int)$_GET['estudiante_id'];

$db = new Database();
$conn = $db->connect();

try {
    // Primero verificar que el estudiante existe y está activo
    $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE id = ? AND activo = 1");
    $stmt->execute([$estudiante_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['data' => []]);
        exit;
    }

    // Obtener las asistencias
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(a.fecha_hora, '%d/%m/%Y %H:%i:%s') as fecha_hora,
            CONCAT(u.nombre, ' ', u.apellidos) as profesor_nombre
        FROM asistencias a
        JOIN usuarios u ON a.profesor_id = u.id
        WHERE a.estudiante_id = ?
        ORDER BY a.fecha_hora DESC
    ");
    $stmt->execute([$estudiante_id]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $asistencias]);
} catch (PDOException $e) {
    error_log("Error al obtener asistencias: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['data' => []]);
} 