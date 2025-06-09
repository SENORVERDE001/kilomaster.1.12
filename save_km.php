<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo "Método no permitido.";
    exit();
}

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'conductor') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit();
}

$id_vehiculo = filter_input(INPUT_POST, 'id_vehiculo', FILTER_VALIDATE_INT);
$km_inicial = filter_input(INPUT_POST, 'km_inicial', FILTER_VALIDATE_INT);
$km_final = filter_input(INPUT_POST, 'km_final', FILTER_VALIDATE_INT);
$foto_kilometraje_final = filter_input(INPUT_POST, 'foto_kilometraje_final', FILTER_SANITIZE_STRING);
$destino = filter_input(INPUT_POST, 'destino', FILTER_SANITIZE_STRING);
$otro_destino = filter_input(INPUT_POST, 'otro_destino', FILTER_SANITIZE_STRING);
$litros = filter_input(INPUT_POST, 'litros', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
$pesos = filter_input(INPUT_POST, 'pesos', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
$id_conductor = $_SESSION['usuario_id'];

$destino_final = ($destino === 'Otro' && !empty($otro_destino)) ? $otro_destino : $destino;

if (!$id_vehiculo || $km_inicial === false || $km_final === false || empty($foto_kilometraje_final) || empty($destino_final)) {
    http_response_code(400);
    echo "Datos inválidos o incompletos.";
    exit();
}

if ($km_final <= $km_inicial) {
    http_response_code(400);
    echo "El kilometraje final debe ser mayor que el inicial.";
    exit();
}

// Guardar la foto en el servidor (opcional)
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
$photo_path = $upload_dir . time() . '_km_final.jpg';
file_put_contents($photo_path, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_kilometraje_final)));

$conn->begin_transaction();
try {
    $sql = "INSERT INTO registros_conductor (id_vehiculo, id_conductor, km_inicial, km_final, foto_kilometraje_final_path, destino, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiss", $id_vehiculo, $id_conductor, $km_inicial, $km_final, $photo_path, $destino_final);
    $stmt->execute();
    $registro_conductor_id = $stmt->insert_id;
    $stmt->close();

    if ($litros !== null && $pesos !== null && $litros > 0 && $pesos > 0) {
        $sql_fuel = "INSERT INTO carga_combustible (registro_conductor_id, litros, pesos, fecha_carga) 
                     VALUES (?, ?, ?, NOW())";
        $stmt_fuel = $conn->prepare($sql_fuel);
        $stmt_fuel->bind_param("idd", $registro_conductor_id, $litros, $pesos);
        $stmt_fuel->execute();
        $stmt_fuel->close();
    }

    $sql_update = "UPDATE vehiculos SET kilometraje_actual = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $km_final, $id_vehiculo);
    $stmt_update->execute();
    $stmt_update->close();

    $conn->commit();
    http_response_code(200);
    echo "Kilometraje y combustible registrados correctamente.";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Error al registrar: " . $e->getMessage();
    if (file_exists($photo_path)) unlink($photo_path);
}

$conn->close();
exit();
?>