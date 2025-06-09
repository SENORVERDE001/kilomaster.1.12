<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'conductor') {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit();
}

$id_vehiculo = filter_input(INPUT_POST, 'id_vehiculo', FILTER_VALIDATE_INT);
$litros = filter_input(INPUT_POST, 'litros', FILTER_VALIDATE_FLOAT);
$pesos = filter_input(INPUT_POST, 'pesos', FILTER_VALIDATE_FLOAT);

if (!$id_vehiculo || $litros === false || $pesos === false || $litros <= 0 || $pesos <= 0) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o incompletos']);
    exit();
}

// Guardar temporalmente en la sesión hasta que se complete el recorrido
$_SESSION['fuel_data'] = [
    'id_vehiculo' => $id_vehiculo,
    'litros' => $litros,
    'pesos' => $pesos
];

echo json_encode(['success' => true]);
$conn->close();
?>