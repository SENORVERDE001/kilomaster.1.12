<?php 
// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_styles.css">
</head>
<body>
    <!-- Botón hamburguesa para pantallas pequeñas -->
    <button class="hamburger" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <img src="images/logo2.png" alt="Menú" class="sidebar-logo">
        <a href="register_conductor.php" <?php echo basename($_SERVER['PHP_SELF']) == 'register_conductor.php' ? 'class="active"' : ''; ?>>Registrar Conductor</a>
        <a href="register_vehiculo.php" <?php echo basename($_SERVER['PHP_SELF']) == 'register_vehiculo.php' ? 'class="active"' : ''; ?>>Registrar Vehículo</a>
        <a href="list_conductores.php" <?php echo basename($_SERVER['PHP_SELF']) == 'list_conductores.php' ? 'class="active"' : ''; ?>>Lista de Conductores</a>
        <a href="list_vehiculos.php" <?php echo basename($_SERVER['PHP_SELF']) == 'list_vehiculos.php' ? 'class="active"' : ''; ?>>Lista de Vehículos</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Cerrar el sidebar al hacer clic fuera en pantallas pequeñas
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.querySelector('.hamburger');
            if (window.innerWidth <= 767 && !sidebar.contains(event.target) && !hamburger.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html>