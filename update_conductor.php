<?php
session_start();
include 'db_connect.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador.";
    header("Location: index.php");
    exit();
}

// Verificar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "No fue posible guardar lo solicitado. Método no permitido.";
    header("Location: lista_conductores.php");
    exit();
}

// Capturar y sanitizar datos del formulario
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$sucursal = filter_input(INPUT_POST, 'sucursal', FILTER_SANITIZE_STRING);
$no_licencia = filter_input(INPUT_POST, 'no_licencia', FILTER_SANITIZE_STRING);
$activo = filter_input(INPUT_POST, 'activo', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Validar que todos los campos obligatorios estén presentes
if (!$id || !$usuario || !$nombre || !$sucursal || !$no_licencia || $activo === false) {
    $_SESSION['error'] = "No fue posible guardar lo solicitado. Datos inválidos o incompletos.";
    error_log("Error: Datos inválidos o incompletos - id: $id, usuario: $usuario, nombre: $nombre, sucursal: $sucursal, no_licencia: $no_licencia, activo: $activo");
    header("Location: list_conductores.php");
    exit();
}

// Preparar la consulta SQL según si hay nueva contraseña
if ($password !== null) {
    $sql = "UPDATE usuarios SET usuario = ?, nombre = ?, sucursal = ?, no_licencia = ?, activo = ?, password = ? WHERE id = ? AND tipo_usuario = 'conductor'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "No fue posible guardar lo solicitado. Error al preparar la consulta: " . $conn->error;
        error_log("Error al preparar la consulta: " . $conn->error);
        header("Location: list_conductores.php");
        exit();
    }
    $stmt->bind_param("ssssisi", $usuario, $nombre, $sucursal, $no_licencia, $activo, $password, $id);
} else {
    $sql = "UPDATE usuarios SET usuario = ?, nombre = ?, sucursal = ?, no_licencia = ?, activo = ? WHERE id = ? AND tipo_usuario = 'conductor'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "No fue posible guardar lo solicitado. Error al preparar la consulta: " . $conn->error;
        error_log("Error al preparar la consulta: " . $conn->error);
        header("Location: list_conductores.php");
        exit();
    }
    $stmt->bind_param("ssssii", $usuario, $nombre, $sucursal, $no_licencia, $activo, $id);
}

// Ejecutar la consulta y verificar el resultado
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Conductor actualizado con éxito.";
    } else {
        $_SESSION['error'] = "No se realizaron cambios o no se encontró el conductor.";
        error_log("No se actualizó ningún registro - id: $id");
    }
} else {
    $_SESSION['error'] = "Error al actualizar el conductor: " . $stmt->error;
    error_log("Error al ejecutar la consulta: " . $stmt->error);
}

// Cerrar recursos
$stmt->close();
$conn->close();
header("Location: list_conductores.php");
exit();
?>