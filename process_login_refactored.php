<?php
session_start();
include 'db_connect.php';
include 'utils/csrf.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido.";
    header("Location: index.php");
    exit();
}

// Validate CSRF token
$csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
if (!validateCsrfToken($csrf_token)) {
    $_SESSION['error'] = "Token CSRF inválido.";
    header("Location: index.php");
    exit();
}

// Capture and sanitize form data
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? '';

if (!$usuario || !$password) {
    $_SESSION['error'] = "Por favor, ingresa usuario y contraseña.";
    header("Location: index.php");
    exit();
}

// Search for user in database
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
            // Successful login, set session variables
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
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

// Close resources
$stmt->close();
$conn->close();
exit();
?>
