<?php
session_start();
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    header("Location: index.php");
    exit(); // Aseguro que el script termine aquí si no es administrador
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Administrador</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Agrego viewport para responsividad -->
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background:rgb(244, 250, 244);
            min-height: 100vh;
        }
        .content {
            margin-left: 15;
            padding: 5vw;
            width: 100%;
            box-sizing: border-box;
        }
        .welcome {  
            background: white; /* Cambia el fondo a blanco */
            padding: 5vw;
            border-radius: 12px;
            border: 6px solid #ECA803; /* Agrega un borde amarillo */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 4vw;
            font-size: clamp(1.5rem, 5vw, 2rem);
        }
        p {
            color: #7f8c8d;
            line-height: 1.6;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }
        @media (min-width: 768px) {
            .content { 
                margin-left: 250px; 
                padding: 40px; 
                width: calc(100% - 250px); 
            }
            .welcome { padding: 30px; }
            h2 { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <div class="welcome">
            <h2>Bienvenido al Panel de Administrador</h2>
            <p>Selecciona una opción del menú lateral para gestionar conductores, vehículos y más.</p>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>