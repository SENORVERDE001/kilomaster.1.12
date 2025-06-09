<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            background: #f0f0f0;
        }
        .register-container { 
            width: 350px; 
            padding: 30px; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-register {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registro de Usuario</h2>
        <form action="process_register.php" method="POST">
            <div class="form-group">
                <label>Usuario:</label>
                <input type="text" name="usuario" required>
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Tipo de usuario:</label>
                <select name="tipo_usuario" required>
                    <option value="conductor">Conductor</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn-register">Registrarse</button>
        </form>
    </div>
</body>
</html>