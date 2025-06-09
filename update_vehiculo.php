<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: list_vehiculos.php");
    exit();
}

// Capturar y sanitizar datos del formulario
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);
$modelo = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_STRING);
$anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
$consumo_km = filter_input(INPUT_POST, 'consumo_km', FILTER_VALIDATE_FLOAT);
$kilometraje_actual = filter_input(INPUT_POST, 'kilometraje_actual', FILTER_VALIDATE_INT);
$responsable_vehiculo = !empty($_POST['responsable_vehiculo']) ? (int)$_POST['responsable_vehiculo'] : null;

// Validar que todos los campos obligatorios estén presentes
if (!$id || !$marca || !$modelo || $anio === false || $consumo_km === false || $kilometraje_actual === false) {
    $_SESSION['error'] = "Datos inválidos o incompletos.";
    error_log("Error: Datos inválidos o incompletos - id: $id, marca: $marca, modelo: $modelo, anio: $anio, consumo_km: $consumo_km, kilometraje_actual: $kilometraje_actual");
    header("Location: list_vehiculos.php");
    exit();
}

// Preparar la consulta SQL
$sql = "UPDATE vehiculos SET marca = ?, modelo = ?, anio = ?, consumo_km = ?, kilometraje_actual = ?, responsable_vehiculo = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conn->error;
    error_log("Error al preparar la consulta: " . $conn->error);
    header("Location: list_vehiculos.php");
    exit();
}

// Vincular parámetros
$stmt->bind_param("ssidddi", $marca, $modelo, $anio, $consumo_km, $kilometraje_actual, $responsable_vehiculo, $id);

// Ejecutar la consulta y verificar el resultado
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Vehículo actualizado correctamente.";
    } else {
        $_SESSION['error'] = "No se encontró el vehículo o no se realizaron cambios.";
        error_log("No se actualizó ningún registro - id: $id");
    }
} else {
    $_SESSION['error'] = "Error al actualizar el vehículo: " . $stmt->error;
    error_log("Error al ejecutar la consulta: " . $stmt->error);
}

// Cerrar recursos
$stmt->close();
$conn->close();
header("Location: list_vehiculos.php");
exit();
?>