<?php
session_start();

// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador para registrar conductores.";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Conductor</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_styles.css"> <!-- Referencia a admin_styles.css -->
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <div class="form-container register-form"> <!-- Añadí clase .register-form -->
            <h2>Registrar Conductor</h2>
            <form action="save_conductor.php" method="POST">
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" name="usuario" required placeholder="Ej. conductor1">
                </div>
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" required placeholder="Ej. Juan Pérez">
                </div>
                <div class="form-group">
                    <label>Sucursal:</label>
                    <input type="text" name="sucursal" required placeholder="Ej. Sucursal Norte">
                </div>
                <div class="form-group">
                    <label>Número de Licencia:</label>
                    <input type="text" name="no_licencia" required placeholder="Ej. A123456">
                </div>
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="password" required placeholder="Mínimo 6 caracteres">
                </div>
                <button type="submit" class="btn-save">Registrar</button>
            </form>
            
            <?php
            // Mostrar mensajes de éxito o error
            if (isset($_SESSION['success'])) {
                echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
        </div>
    </div>
</body>
</html>