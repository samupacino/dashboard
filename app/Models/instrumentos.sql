CREATE TABLE instrumentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    tag VARCHAR(50) NOT NULL,
    tag_normalizado VARCHAR(50) NOT NULL,

    descripcion VARCHAR(150) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    planta_id INT UNSIGNED NOT NULL,
    area VARCHAR(100) NOT NULL,

    ubicacion_exacta TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    observacion TEXT DEFAULT NULL,

    estado ENUM('activo','inactivo') DEFAULT 'activo',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (planta_id, tag_normalizado),

    FOREIGN KEY (planta_id)
        REFERENCES plantas(id)
);

CREATE TABLE plantas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150) DEFAULT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo'
    
    
);

INSERT INTO plantas (nombre) VALUES ('T155');
INSERT INTO plantas (nombre) VALUES ('PL3XL');
INSERT INTO plantas (nombre) VALUES ('VPSA120');
