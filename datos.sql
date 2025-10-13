create table usuario(
    id int auto_increment primary key,
    nombre varchar(255),
    correo varchar(255) 
)

SELECT * FROM usuario WHERE correo = 'admin@example.com' LIMIT 1



CREATE DATABASE PlataformaDB;

USE PlataformaDB;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    name_complete VARCHAR(250) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Contraseñas cifradas
    rol ENUM('admin', 'invitado') NOT NULL DEFAULT 'invitado'
);



CREATE TABLE instrumento_pl2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    plataforma INT NOT NULL,
    FOREIGN KEY (plataforma) REFERENCES plataformas(id)
);

CREATE TABLE plataformas (
    id INT PRIMARY KEY,
    ubicacion VARCHAR(255) NOT NULL
);
CREATE TABLE instrumento_t155 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag VARCHAR(255) NOT NULL UNIQUE,
    plataforma INT NOT NULL,
    FOREIGN KEY (plataforma) REFERENCES plataformas(id)
);

-- Registra plataformas iniciales
INSERT INTO plataformas (id,ubicacion) VALUES 
(1,'Plataforma 1'), 
(2,'Plataforma 2'), 
(3,'Plataforma 3'), 
(4,'Plataforma 4'), 
(5,'Plataforma 5'),
(6,'Plataforma 6'),
(7,'Plataforma 7'),
(8,'Base');

INSERT INTO usuario (username, name_complete, password, rol)
VALUES ('samuel', 'samuel joel', '$2y$10$9N305Q5Y0D75.geqrVCx.Oy/Y6xLLtB.13sq4iCbx.zD0BkSS6Gii', 'admin');



mysql> SELECT COUNT(*) AS total FROM (SELECT i.id, i.tag, p.nombre as escalon FROM instrumento_t155 i 
JOIN plataformas p ON i.plataforma = p.id WHERE i.id LIKE 'A5F-989' OR i.tag LIKE 'A5F-989' OR escalon LIKE 'A5F-989') 
AS subconsulta_filtrada;
ERROR 1054 (42S22): Unknown column 'escalon' in 'where clause';
