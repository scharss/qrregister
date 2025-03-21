<?php
session_start();
require_once '../config/database.php';
require_once 'utils.php';

// Función para obtener la ruta base
function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim(str_replace('/includes', '', $scriptDir), '/');
}

// Habilitar todos los errores para debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de acceso
error_log("Intento de login - " . date('Y-m-d H:i:s'));

// Verificar método y tipo de petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Método no permitido', 405);
}

// Obtener y validar datos
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

error_log("Intento de login para email: " . $email);
error_log("Contraseña recibida (longitud): " . strlen($password));

if (!$email || !$password) {
    handleError('Por favor complete todos los campos', 400);
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!$conn) {
        error_log("Error de conexión a la base de datos");
        handleError('Error de conexión a la base de datos', 500);
    }

    // Debug de la conexión
    error_log("Conexión establecida correctamente");

    $stmt = $conn->prepare("SELECT u.*, r.nombre as rol_nombre 
                           FROM usuarios u 
                           JOIN roles r ON u.rol_id = r.id 
                           WHERE u.correo = ?");
    
    if (!$stmt->execute([$email])) {
        error_log("Error al ejecutar la consulta: " . implode(" ", $stmt->errorInfo()));
        handleError('Error al verificar credenciales', 500);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug de la consulta
    error_log("Resultado de la consulta: " . ($user ? "Usuario encontrado" : "Usuario no encontrado"));
    
    if ($user) {
        error_log("Hash almacenado: " . $user['password']);
        error_log("Verificando contraseña para usuario: " . $user['nombre']);
        $passwordValid = password_verify($password, $user['password']);
        error_log("Resultado de verificación de contraseña: " . ($passwordValid ? "Válida" : "Inválida"));
        
        // Generar un nuevo hash para comparar
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        error_log("Nuevo hash generado: " . $newHash);
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol_nombre'];
        
        $basePath = getBasePath();
        $redirect = $user['rol_nombre'] === 'administrador' ? 
            'pages/admin/dashboard.php' : 
            'pages/profesor/scanner.php';
        
        error_log("Login exitoso para usuario: " . $user['nombre']);
        error_log("Redirigiendo a: " . $redirect);
        
        sendJsonResponse([
            'success' => true,
            'redirect' => $redirect
        ]);
    } else {
        error_log("Credenciales inválidas para email: " . $email);
        handleError('Credenciales inválidas', 401);
    }
} catch (PDOException $e) {
    error_log("Error en login.php: " . $e->getMessage());
    handleError('Error en el servidor: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    error_log("Error inesperado: " . $e->getMessage());
    handleError('Error inesperado: ' . $e->getMessage(), 500);
} 