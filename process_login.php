<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: index.php");
    exit();
}

// Capturar y sanitizar datos del formulario
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$password = $_POST['password'];

if (!$usuario || !$password) {
    $_SESSION['error'] = "Por favor, ingresa usuario y contraseña.";
    header("Location: index.php");
    exit();
}

// Buscar al usuario en la base de datos
$sql = "SELECT id, usuario, password, tipo_usuario, activo FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conn->error;
    error_log("Error al preparar la consulta: " . $conn->error);
    header("Location: index.php");
    exit();
}

$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        if ($row['activo'] == 1) {
            // Login exitoso, establecer variables de sesión
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario']; // Guardamos el nombre de usuario
            $_SESSION['tipo_usuario'] = $row['tipo_usuario'];
            if ($row['tipo_usuario'] === 'administrador') {
                header("Location: admin_dashboard.php");
            } else if ($row['tipo_usuario'] === 'conductor') {
                header("Location: conductor_dashboard.php");
            } else {
                $_SESSION['error'] = "Tipo de usuario no reconocido.";
                header("Location: index.php");
            }
        } else {
            $_SESSION['error'] = "Tu cuenta está inactiva. Contacta al administrador para activarla.";
            header("Location: index.php");
        }
    } else {
        $_SESSION['error'] = "Usuario o contraseña incorrectos.";
        header("Location: index.php");
    }
} else {
    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
}

// Cerrar recursos
$stmt->close();
$conn->close();
exit();
?>