<?php
session_start();

// Simulación de login desde modal vía JSON
$datos = json_decode(file_get_contents("php://input"), true);

$usuario = $datos['usuario'] ?? '';
$clave = $datos['clave'] ?? '';

if ($usuario === 'admin' && $clave === '1234') {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['ultima_actividad'] = time();

    echo json_encode(['exito' => true]);
} else {
    echo json_encode(['exito' => false, 'error' => 'Usuario o contraseña incorrectos.']);
}


