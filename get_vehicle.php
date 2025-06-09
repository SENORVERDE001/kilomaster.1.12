<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$vehicle_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$vehicle_id) {
    echo json_encode(['error' => 'ID inválido']);
    exit();
}

$sql = "SELECT id, marca, modelo, anio, consumo_km, kilometraje_actual, responsable_vehiculo 
        FROM vehiculos 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();

if ($vehicle) {
    echo json_encode($vehicle);
} else {
    echo json_encode(['error' => 'Vehículo no encontrado']);
}

$stmt->close();
$conn->close();
?>