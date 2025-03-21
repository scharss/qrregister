<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Habilitar reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $grupo_id = $_POST['grupo'] ?? '';
    $profesor_id = $_POST['profesor'] ?? '';
    $fechas = $_POST['fechas'] ?? '';

    // Validar que se haya seleccionado un grupo
    if (empty($grupo_id)) {
        throw new Exception('Debe seleccionar un grupo');
    }

    $where_conditions = ["e.grupo_id = ?"]; // Condición base
    $params = [$grupo_id];

    if (!empty($profesor_id)) {
        $where_conditions[] = "a.profesor_id = ?";
        $params[] = $profesor_id;
    }

    if (!empty($fechas)) {
        $fechas_array = explode(' - ', $fechas);
        if (count($fechas_array) == 2) {
            $where_conditions[] = "DATE(a.fecha_hora) BETWEEN ? AND ?";
            $params[] = $fechas_array[0];
            $params[] = $fechas_array[1];
        }
    }

    // Primero obtenemos todos los estudiantes del grupo
    $sql_estudiantes = "
        SELECT e.id, e.nombre, e.apellidos, e.documento
        FROM estudiantes e
        WHERE e.grupo_id = ? AND e.activo = 1
        ORDER BY e.apellidos, e.nombre";

    $stmt_estudiantes = $conn->prepare($sql_estudiantes);
    $stmt_estudiantes->execute([$grupo_id]);
    $estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);

    if (empty($estudiantes)) {
        throw new Exception('No hay estudiantes registrados en este grupo');
    }

    // Obtenemos las fechas con registros
    $where_clause = implode(" AND ", $where_conditions);
    $sql_fechas = "
        SELECT DISTINCT DATE(a.fecha_hora) as fecha
        FROM asistencias a
        JOIN estudiantes e ON a.estudiante_id = e.id
        WHERE $where_clause
        ORDER BY fecha";

    $stmt_fechas = $conn->prepare($sql_fechas);
    $stmt_fechas->execute($params);
    $fechas = $stmt_fechas->fetchAll(PDO::FETCH_COLUMN);

    // Si no hay fechas con registros, devolver mensaje apropiado
    if (empty($fechas)) {
        echo json_encode([
            'error' => true,
            'message' => 'No hay registros de asistencia para el período seleccionado'
        ]);
        exit;
    }

    // Obtenemos todas las asistencias
    $sql_asistencias = "
        SELECT 
            e.id as estudiante_id,
            DATE(a.fecha_hora) as fecha,
            TIME(a.fecha_hora) as hora
        FROM asistencias a
        JOIN estudiantes e ON a.estudiante_id = e.id
        WHERE $where_clause";

    $stmt_asistencias = $conn->prepare($sql_asistencias);
    $stmt_asistencias->execute($params);
    $asistencias = $stmt_asistencias->fetchAll(PDO::FETCH_ASSOC);

    // Organizamos los datos
    $asistencias_por_estudiante = [];
    foreach ($asistencias as $asistencia) {
        $asistencias_por_estudiante[$asistencia['estudiante_id']][$asistencia['fecha']] = 'Si asistió';
    }

    // Preparamos los datos para la respuesta
    $data = [];
    foreach ($estudiantes as $estudiante) {
        $row = [
            'estudiante' => $estudiante['apellidos'] . ', ' . $estudiante['nombre'],
            'documento' => $estudiante['documento']
        ];

        foreach ($fechas as $fecha) {
            $row[$fecha] = isset($asistencias_por_estudiante[$estudiante['id']][$fecha]) 
                ? 'Si asistió'
                : 'No asistió';
        }

        $data[] = $row;
    }

    // Preparamos las columnas
    $columns = [
        ['title' => 'Estudiante', 'data' => 'estudiante'],
        ['title' => 'Documento', 'data' => 'documento']
    ];

    foreach ($fechas as $fecha) {
        $fecha_formateada = date('d/m/Y', strtotime($fecha));
        $columns[] = [
            'title' => $fecha_formateada,
            'data' => $fecha,
            'defaultContent' => 'No asistió'
        ];
    }

    echo json_encode([
        'data' => $data,
        'columns' => $columns
    ]);

} catch (Exception $e) {
    error_log("Error en get_asistencias.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 