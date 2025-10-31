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



CREATE TABLE instrumento_pl3 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag VARCHAR(255) NOT NULL UNIQUE,
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



















-- ===========================================================
--  BASE DE DATOS DE VOCABULARIO INGLÉS–ESPAÑOL
--  Samuel Luján — versión con clave única (sin IGNORE)
-- ===========================================================

CREATE DATABASE IF NOT EXISTS english_notes
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_0900_ai_ci;
USE english_notes;

-- -----------------------------------------------------------
-- TABLA PRINCIPAL: en_vocab
-- -----------------------------------------------------------
CREATE TABLE en_vocab (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  english       VARCHAR(120)  NOT NULL,              -- palabra o frase en inglés
  pronunciation VARCHAR(120)  NULL,                  -- pronunciación fonética
  spanish       VARCHAR(180)  NOT NULL,              -- traducción o significado
  pos           ENUM('verb','phrasal_verb','noun','adjective','adverb','expression')
                 NOT NULL DEFAULT 'expression',      -- tipo gramatical
  level         ENUM('A1','A2','B1','B2','C1','C2')  NULL, -- nivel de dificultad
  example_en    VARCHAR(240)  NULL,                  -- ejemplo en inglés
  example_es    VARCHAR(240)  NULL,                  -- ejemplo en español
  notes         VARCHAR(240)  NULL,                  -- observaciones o categoría
  opposite_id   BIGINT UNSIGNED NULL,                -- referencia al opuesto
  source        VARCHAR(120)  NULL,                  -- origen del registro (cuaderno, app, etc.)
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uq_en_vocab_english UNIQUE (english),   -- 🔒 clave única
  CONSTRAINT fk_en_vocab_opposite FOREIGN KEY (opposite_id)
    REFERENCES en_vocab(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- ÍNDICES ÚTILES
-- -----------------------------------------------------------
CREATE INDEX idx_en_vocab_spanish ON en_vocab (spanish);

-- (Opcional) para búsquedas tipo "Google"
-- CREATE FULLTEXT INDEX ft_en_vocab_text
--   ON en_vocab (english, spanish, notes, example_en, example_es);

-- -----------------------------------------------------------
-- CARGA INICIAL DE TUS PALABRAS
-- -----------------------------------------------------------
INSERT INTO en_vocab (english, pronunciation, spanish, pos, notes)
VALUES
('Go up',      'gou ap',     'Subir',                      'phrasal_verb', 'directions'),
('Go down',    'gou daun',   'Bajar',                      'phrasal_verb', 'directions'),
('Take in',    'tei kin',    'Aceptar',                    'phrasal_verb', NULL),
('Chase out',  'cheis aut',  'Expulsar',                   'phrasal_verb', NULL),
('Move in',    'mu vin',     'Moverse adentro',            'phrasal_verb', 'directions'),
('Move out',   'mu vaut',    'Moverse afuera',             'phrasal_verb', 'directions'),
('Step in',    'ste pin',    'Pase adentro',               'phrasal_verb', 'directions'),
('Step out',   'ste paut',   'Pase afuera',                'phrasal_verb', 'directions'),
('Look here',  'luk jier',   'Mire aquí',                  'expression',    NULL),
('Look there', 'luk dear',   'Mira allí',                  'expression',    NULL),
('See him',    'si jim',     'Verlo a él',                 'expression',    NULL),
('See her',    'si jer',     'Verla a ella',               'expression',    NULL),
('Listen now',   'lí-sen nau',    'Escuche ahora',         'expression',    'time'),
('Listen later', 'lí-sen léi-re', 'Escuche más tarde',     'expression',    'time'),
('Come in',    'ka mín',     'Entre adentro',              'phrasal_verb', 'directions'),
('Come out',   'ka maut',    'Salga afuera',               'phrasal_verb', 'directions');

-- -----------------------------------------------------------
-- ENLACES DE OPUESTOS
-- -----------------------------------------------------------
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Go down'
  SET v.opposite_id = o.id WHERE v.english = 'Go up';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Go up'
  SET v.opposite_id = o.id WHERE v.english = 'Go down';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Move out'
  SET v.opposite_id = o.id WHERE v.english = 'Move in';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Move in'
  SET v.opposite_id = o.id WHERE v.english = 'Move out';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Step out'
  SET v.opposite_id = o.id WHERE v.english = 'Step in';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Step in'
  SET v.opposite_id = o.id WHERE v.english = 'Step out';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Come out'
  SET v.opposite_id = o.id WHERE v.english = 'Come in';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Come in'
  SET v.opposite_id = o.id WHERE v.english = 'Come out';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Look there'
  SET v.opposite_id = o.id WHERE v.english = 'Look here';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Look here'
  SET v.opposite_id = o.id WHERE v.english = 'Look there';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Listen later'
  SET v.opposite_id = o.id WHERE v.english = 'Listen now';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'Listen now'
  SET v.opposite_id = o.id WHERE v.english = 'Listen later';

UPDATE en_vocab v JOIN en_vocab o ON o.english = 'See her'
  SET v.opposite_id = o.id WHERE v.english = 'See him';
UPDATE en_vocab v JOIN en_vocab o ON o.english = 'See him'
  SET v.opposite_id = o.id WHERE v.english = 'See her';
