<?php
session_start();
include 'db_connect.php';

$sql = "SELECT id FROM usuarios WHERE usuario = ? AND id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $usuario, $id); // $id es opcional en registros nuevos   agregados por mi
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "El usuario ya existe.";
    header("Location: formulario.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tipo_usuario = $_POST['tipo_usuario'];
    
    $sql = "INSERT INTO usuarios (usuario, password, tipo_usuario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $usuario, $password, $tipo_usuario);
    
    if ($stmt->execute()) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo_usuario'] = $tipo_usuario;
        
        if ($tipo_usuario == 'administrador') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: conductor_dashboard.php");
        }
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
}
$conn->close();
?>