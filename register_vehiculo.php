<?php
session_start();
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

$sql_conductores = "SELECT id, nombre FROM usuarios WHERE tipo_usuario = 'conductor' AND activo = 1";
$conductores_result = $conn->query($sql_conductores);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Vehículo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_styles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="form-container">
            <h2>Registrar Vehículo</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="save_vehiculo.php" method="POST">
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" required>
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" required>
                </div>
                <div class="form-group">
                    <label for="anio">Año:</label>
                    <input type="number" id="anio" name="anio" required>
                </div>
                <div class="form-group">
                    <label for="consumo_km">Consumo (km/l):</label>
                    <input type="number" step="0.01" id="consumo_km" name="consumo_km" required>
                </div>
                <div class="form-group">
                    <label for="kilometraje_actual">Kilometraje Actual:</label>
                    <input type="number" id="kilometraje_actual" name="kilometraje_actual" required>
                </div>
                <div class="form-group">
                    <label for="responsable_vehiculo">Responsable:</label>
                    <select id="responsable_vehiculo" name="responsable_vehiculo">
                        <option value="">Sin asignar</option>
                        <?php while ($conductor = $conductores_result->fetch_assoc()): ?>
                            <option value="<?php echo $conductor['id']; ?>"><?php echo htmlspecialchars($conductor['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn-save">Guardar</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
$conductores_result->close();
?>