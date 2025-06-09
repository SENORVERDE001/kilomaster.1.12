<?php
include 'db_connect.php';

$id_vehiculo = filter_input(INPUT_GET, 'id_vehiculo', FILTER_VALIDATE_INT);

if ($id_vehiculo === false || $id_vehiculo === null) {
    echo '0';
    exit();
}

$sql = "SELECT km_final FROM registros_conductor WHERE id_vehiculo = ? ORDER BY fecha_registro DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_vehiculo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['km_final'];
} else {
    echo '0';
}

$stmt->close();
$conn->close();
?>