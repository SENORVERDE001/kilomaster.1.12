<?php
session_start();
include 'db_connect.php';

$sql = "SELECT id FROM usuarios WHERE usuario = ? AND id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $usuario, $id); // $id es opcional en registros nuevos
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "El usuario ya existe.";
    header("Location: formulario.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: register_conductor.php");
    exit();
}

// Capturar y sanitizar datos del formulario
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$sucursal = filter_input(INPUT_POST, 'sucursal', FILTER_SANITIZE_STRING);
$no_licencia = filter_input(INPUT_POST, 'no_licencia', FILTER_SANITIZE_STRING);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashear la contraseña
$tipo_usuario = 'conductor';

// Validar que todos los campos estén presentes
if (!$usuario || !$nombre || !$sucursal || !$no_licencia || !$password) {
    $_SESSION['error'] = "Datos inválidos o incompletos.";
    error_log("Error: Datos inválidos o incompletos - usuario: $usuario, nombre: $nombre, sucursal: $sucursal, no_licencia: $no_licencia");
    header("Location: register_conductor.php");
    exit();
}

// Preparar la consulta SQL
$sql = "INSERT INTO usuarios (usuario, nombre, sucursal, no_licencia, password, tipo_usuario) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conn->error;
    error_log("Error al preparar la consulta: " . $conn->error);
    header("Location: register_conductor.php");
    exit();
}

$stmt->bind_param("ssssss", $usuario, $nombre, $sucursal, $no_licencia, $password, $tipo_usuario);

// Ejecutar la consulta y verificar el resultado
if ($stmt->execute()) {
    $_SESSION['success'] = "Conductor registrado correctamente.";
    header("Location: admin_dashboard.php");
} else {
    $_SESSION['error'] = "Error al registrar el conductor: " . $stmt->error;
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    header("Location: register_conductor.php");
}

// Cerrar recursos
$stmt->close();
$conn->close();
exit();
?>