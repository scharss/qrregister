-- Configuración inicial
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Usar la base de datos existente
USE bytezenc_attendant;

-- Eliminar tablas existentes si existen
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS roles;

-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios (administradores y profesores)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    rol_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    INDEX idx_correo (correo),
    INDEX idx_documento (documento),
    INDEX idx_rol (rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de grupos
CREATE TABLE grupos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de estudiantes
CREATE TABLE estudiantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    grupo_id INT,
    qr_code TEXT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_id) REFERENCES grupos(id),
    INDEX idx_documento (documento),
    INDEX idx_grupo (grupo_id),
    INDEX idx_nombre_apellidos (nombre, apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asistencias
CREATE TABLE asistencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estudiante_id INT NOT NULL,
    profesor_id INT NOT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id),
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_profesor (profesor_id),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles básicos
INSERT INTO roles (nombre) VALUES 
('administrador'),
('profesor');

-- Insertar administrador por defecto
-- Contraseña: xxxxxxx
INSERT INTO usuarios (nombre, apellidos, correo, password, documento, rol_id) VALUES 
('admin', 'Sistema', 'scharss@gmail.com', '$2y$10$2y8PGMbTVoQdKLDXzHHcPuXb.YEpEZiHcMxY9hqMqt7FvGz0Qs4Hy', 'ADMIN001', 1);

-- Procedimiento almacenado para obtener asistencias por grupo y fecha
DELIMITER //
CREATE PROCEDURE sp_asistencias_por_grupo(
    IN grupo_id INT,
    IN fecha_inicio DATE,
    IN fecha_fin DATE
)
BEGIN
    SELECT 
        e.nombre,
        e.apellidos,
        e.documento,
        g.nombre as grupo,
        COUNT(a.id) as total_asistencias,
        GROUP_CONCAT(DATE_FORMAT(a.fecha_hora, '%Y-%m-%d %H:%i') ORDER BY a.fecha_hora) as fechas_asistencia
    FROM estudiantes e
    LEFT JOIN grupos g ON e.grupo_id = g.id
    LEFT JOIN asistencias a ON e.id = a.estudiante_id
    WHERE e.grupo_id = grupo_id
    AND (a.fecha_hora BETWEEN fecha_inicio AND fecha_fin OR a.fecha_hora IS NULL)
    GROUP BY e.id, e.nombre, e.apellidos, e.documento, g.nombre;
END //
DELIMITER ;

-- Procedimiento almacenado para obtener asistencias por profesor
DELIMITER //
CREATE PROCEDURE sp_asistencias_por_profesor(
    IN profesor_id INT,
    IN fecha_inicio DATE,
    IN fecha_fin DATE
)
BEGIN
    SELECT 
        e.nombre as estudiante_nombre,
        e.apellidos as estudiante_apellidos,
        e.documento,
        g.nombre as grupo,
        DATE_FORMAT(a.fecha_hora, '%Y-%m-%d %H:%i') as fecha_asistencia
    FROM asistencias a
    JOIN estudiantes e ON a.estudiante_id = e.id
    LEFT JOIN grupos g ON e.grupo_id = g.id
    WHERE a.profesor_id = profesor_id
    AND a.fecha_hora BETWEEN fecha_inicio AND fecha_fin
    ORDER BY a.fecha_hora DESC;
END //
DELIMITER ;

-- Trigger para validar el rol del usuario antes de insertar
DELIMITER //
CREATE TRIGGER before_usuario_insert
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.rol_id NOT IN (SELECT id FROM roles) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Rol no válido';
    END IF;
END //
DELIMITER ;

-- Trigger para validar el grupo antes de insertar un estudiante
DELIMITER //
CREATE TRIGGER before_estudiante_insert
BEFORE INSERT ON estudiantes
FOR EACH ROW
BEGIN
    IF NEW.grupo_id IS NOT NULL AND NEW.grupo_id NOT IN (SELECT id FROM grupos) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Grupo no válido';
    END IF;
END //
DELIMITER ;

-- Vista para resumen de asistencias
CREATE VIEW v_resumen_asistencias AS
SELECT 
    g.nombre as grupo,
    COUNT(DISTINCT e.id) as total_estudiantes,
    COUNT(DISTINCT a.id) as total_asistencias,
    DATE(a.fecha_hora) as fecha
FROM grupos g
LEFT JOIN estudiantes e ON g.id = e.grupo_id
LEFT JOIN asistencias a ON e.id = a.estudiante_id
GROUP BY g.id, g.nombre, DATE(a.fecha_hora);

COMMIT; 