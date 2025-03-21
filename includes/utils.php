<?php

function sendJsonResponse($data, $statusCode = 200) {
    // Limpiar cualquier salida anterior
    if (ob_get_length()) ob_clean();
    
    // Establecer headers
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    // Asegurarse de que no haya errores PHP en la salida
    error_reporting(0);
    
    // Enviar respuesta
    echo json_encode($data);
    exit;
}

function handleError($message, $statusCode = 500) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

// Función para verificar si es una petición AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Función para verificar el método de la petición
function checkRequestMethod($method) {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        handleError('Método no permitido', 405);
    }
} 