-- MySQL schema for Planilla de Horas Extras y Toneladas
CREATE DATABASE IF NOT EXISTS planillas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE planillas;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identificacion VARCHAR(50) NOT NULL UNIQUE,
  nombres VARCHAR(150) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  clave VARCHAR(255) NOT NULL,
  celular VARCHAR(50),
  cargo ENUM('auxiliar','jefe','admin') DEFAULT 'auxiliar',
  unidad_operativa ENUM('Cali','Pereira') DEFAULT 'Cali',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Registros diarios
CREATE TABLE IF NOT EXISTS registros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  fecha DATE NOT NULL,
  hora_entrada TIME NOT NULL,
  hora_salida TIME NOT NULL,
  placa VARCHAR(30),
  numero_folio VARCHAR(100),
  toneladas DECIMAL(10,2) DEFAULT 0,
  ton_coteadas DECIMAL(10,2) DEFAULT 0,
  firma TEXT,
  bono_transporte DECIMAL(10,2) DEFAULT 0,
  bono_alimentacion DECIMAL(10,2) DEFAULT 0,
  recargo_nocturno DECIMAL(10,2) DEFAULT 0,
  he_diurnas DECIMAL(10,2) DEFAULT 0,
  he_nocturnas DECIMAL(10,2) DEFAULT 0,
  he_dom_fest DECIMAL(10,2) DEFAULT 0,
  he_diurnas_dom DECIMAL(10,2) DEFAULT 0,
  he_nocturnas_dom DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, fecha),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Adicionales por unidad
CREATE TABLE IF NOT EXISTS adicionales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  unidad_operativa ENUM('Cali','Pereira') DEFAULT 'Cali',
  servicio VARCHAR(150) NOT NULL,
  cantidad DECIMAL(10,2) DEFAULT 0,
  valor_unitario DECIMAL(12,2) DEFAULT 0,
  valor_total DECIMAL(12,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample admin user (password: admin123)
INSERT INTO users (identificacion, nombres, apellidos, correo, clave, celular, cargo, unidad_operativa)
VALUES ('0001','Admin','Sistema','admin@example.com', CONCAT('$2y$10$', SUBSTRING(SHA2(RAND(),256),1,44)), '3000000000', 'admin', 'Cali')
ON DUPLICATE KEY UPDATE id=id;
