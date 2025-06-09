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
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        @property --rotate { 
            syntax: "<angle>";
            initial-value: 132deg;
            inherits: false;
        }

        body {
            font-family: 'Raleway', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: auto;
            position: relative;
            background: black;
        }
        
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('images/fondo.jpg') no-repeat center center/cover;
            z-index: -1;
        }
        
        .login-container {
            background: rgba(0, 0, 0, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.8s ease-in-out;
            z-index: 1;
            color: white;
            text-align: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-70px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #fff;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
            box-sizing: border-box;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: black;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-sizing: border-box;
        }
        
        .btn-login::before {
            content: "";
            position: absolute;
            width: 104%;
            height: 102%;
            border-radius: 8px;
            background-image: linear-gradient(
                var(--rotate), #FED27A, #742D31 43%
            );
            top: -1%;
            left: -2%;
            z-index: -1;
            animation: spin 2.5s linear infinite;
        }
        
        .btn-login::after {
            position: absolute;
            content: "";
            top: 10px;
            left: 0;
            right: 0;
            z-index: -1;
            height: 100%;
            width: 100%;
            margin: 0 auto;
            transform: scale(0.8);
            filter: blur(10px);
            background-image: linear-gradient(
                var(--rotate), #FED27A, #742D31 43%
            );
            opacity: 1;
            transition: opacity .5s;
            animation: spin 2.5s linear infinite;
        }

        @keyframes spin {
            0% {
                --rotate: 0deg;
            }
            100% {
                --rotate: 360deg;
            }
        }
        
        .footer a {
            color: #00ffff;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
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