<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$vehicle_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$conductor_id = filter_input(INPUT_GET, 'conductor_id', FILTER_VALIDATE_INT);
$destino = filter_input(INPUT_GET, 'destino', FILTER_SANITIZE_STRING);
$date_start = filter_input(INPUT_GET, 'date_start', FILTER_SANITIZE_STRING);
$date_end = filter_input(INPUT_GET, 'date_end', FILTER_SANITIZE_STRING);
$km_min = filter_input(INPUT_GET, 'km_min', FILTER_VALIDATE_INT);
$sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_STRING) ?: 'fecha_registro DESC';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10000000000;

if (!$vehicle_id) {
    echo json_encode(['error' => 'ID de vehículo inválido']);
    exit();
}

// Validar fechas
if ($date_start && $date_end) {
    if (!DateTime::createFromFormat('Y-m-d', $date_start) || !DateTime::createFromFormat('Y-m-d', $date_end)) {
        echo json_encode(['error' => 'Formato de fecha inválido']);
        exit();
    }
    if (strtotime($date_start) > strtotime($date_end)) {
        echo json_encode(['error' => 'La fecha de inicio no puede ser mayor que la fecha de fin']);
        exit();
    }
}

// Validar sort_by
$allowed_sorts = ['fecha_registro DESC', 'fecha_registro ASC', 'km_difference DESC', 'km_difference ASC'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'fecha_registro DESC';
}

$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Contar total de registros
$count_sql = "SELECT COUNT(*) AS total FROM registros_conductor rc WHERE rc.id_vehiculo = ?";
$count_params = [$vehicle_id];
$count_types = "i";

if ($conductor_id) {
    $count_sql .= " AND rc.id_conductor = ?";
    $count_params[] = $conductor_id;
    $count_types .= "i";
}
if ($destino) {
    $count_sql .= " AND rc.destino LIKE ?";
    $count_params[] = "%$destino%";
    $count_types .= "s";
}
if ($date_start && $date_end) {
    $count_sql .= " AND rc.fecha_registro BETWEEN ? AND ?";
    $count_params[] = $date_start;
    $count_params[] = $date_end;
    $count_types .= "ss";
}
if ($km_min !== null) {
    $count_sql .= " AND (rc.km_final - rc.km_inicial) >= ?";
    $count_params[] = $km_min;
    $count_types .= "i";
}

try {
    $stmt_count = $conn->prepare($count_sql);
    if (!$stmt_count) throw new Exception('Error al preparar la consulta de conteo');
    $stmt_count->bind_param($count_types, ...$count_params);
    $stmt_count->execute();
    $total = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
    exit();
}

// Consulta principal
$sql = "SELECT rc.id, rc.id_conductor, rc.id_vehiculo, rc.km_inicial, rc.km_final, 
               (rc.km_final - rc.km_inicial) AS km_difference, 
               rc.destino, rc.fecha_registro, rc.foto_kilometraje_final_path, 
               u.nombre AS conductor,
               SUM(cc.pesos) AS total_pesos, SUM(cc.litros) AS total_litros,
               (SELECT MIN(km_inicial) FROM registros_conductor WHERE id_vehiculo = rc.id_vehiculo) AS initial_vehicle_km
        FROM registros_conductor rc
        JOIN usuarios u ON rc.id_conductor = u.id
        LEFT JOIN carga_combustible cc ON rc.id = cc.registro_conductor_id
        WHERE rc.id_vehiculo = ?";
$params = [$vehicle_id];
$types = "i";

if ($conductor_id) {
    $sql .= " AND rc.id_conductor = ?";
    $params[] = $conductor_id;
    $types .= "i";
}
if ($destino) {
    $sql .= " AND rc.destino LIKE ?";
    $params[] = "%$destino%";
    $types .= "s";
}
if ($date_start && $date_end) {
    $sql .= " AND rc.fecha_registro BETWEEN ? AND ?";
    $params[] = $date_start;
    $params[] = $date_end;
    $types .= "ss";
}
if ($km_min !== null) {
    $sql .= " AND (rc.km_final - rc.km_inicial) >= ?";
    $params[] = $km_min;
    $types .= "i";
}

$sql .= " GROUP BY rc.id ORDER BY $sort_by LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Error al preparar la consulta principal');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $trips = [];
    $trip_counter = ($page - 1) * $limit + 1;
    while ($row = $result->fetch_assoc()) {
        $row['trip_id'] = $trip_counter++;
        $trips[] = $row;
    }

    echo json_encode(['data' => $trips, 'total' => $total]);
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error interno del servidor']);
    exit();
}

$conn->close();
?>