<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Habilitar el reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $conn;

    public function __construct() {
        // Verificar que las variables de entorno existen
        if (!isset($_ENV['DB_SERVER']) || !isset($_ENV['DB_USERNAME']) || !isset($_ENV['DB_NAME'])) {
            throw new Exception('Faltan variables de entorno necesarias para la conexión a la base de datos');
        }

        $this->host = $_ENV['DB_SERVER'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->database = $_ENV['DB_NAME'];
    }

    public function connect() {
        try {
            if (!$this->conn) {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->database}",
                    $this->username,
                    $this->password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->database}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            return null;
        }
    }
} 