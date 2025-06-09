<?php  
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Kilomaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="login-container">
        <div class="login-header">
            <img src="images/logo2.png" alt="Logo de Kilomaster">
            <h1>Inicia sesión</h1>
            <p>Accede con tu cuenta de usuario</p>
        </div>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <input type="text" id="usuario" name="usuario" required placeholder="Usuario">
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" required placeholder="Contraseña">
            </div>
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="footer">
            <p>¿Olvidaste tu contraseña? <a href="#">Contacta a tu administrador</a></p>
        </div>
    </div>
</body>
</html>
