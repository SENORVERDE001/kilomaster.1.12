<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: register_vehiculo.php");
    exit();
}

// Sanitizar y validar entradas
$marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);
$modelo = filter_input(INPUT_POST, 'modelo', FILTER_SANITIZE_STRING);
$anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
$consumo_km = filter_input(INPUT_POST, 'consumo_km', FILTER_VALIDATE_FLOAT);
$kilometraje_actual = filter_input(INPUT_POST, 'kilometraje_actual', FILTER_VALIDATE_INT);
$responsable_vehiculo = !empty($_POST['responsable_vehiculo']) ? (int)$_POST['responsable_vehiculo'] : null;

if (!$marca || !$modelo || $anio === false || $consumo_km === false || $kilometraje_actual === false) {
    $_SESSION['error'] = "Datos inválidos o incompletos.";
    header("Location: register_vehiculo.php");
    exit();
}

// Insertar en la base de datos
$sql = "INSERT INTO vehiculos (marca, modelo, anio, consumo_km, kilometraje_actual, responsable_vehiculo) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssidds", $marca, $modelo, $anio, $consumo_km, $kilometraje_actual, $responsable_vehiculo);

if ($stmt->execute()) {
    $_SESSION['success'] = "Vehículo registrado correctamente.";
    header("Location: admin_dashboard.php");
} else {
    $_SESSION['error'] = "Error al registrar el vehículo: " . $conn->error;
    header("Location: register_vehiculo.php");
}

$stmt->close();
$conn->close();
exit();
?>