<?php
session_start();
include 'db_connect.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador.";
    header("Location: index.php");
    exit();
}

// Obtener lista de conductores
$sql = "SELECT id, usuario, nombre, sucursal, no_licencia, activo FROM usuarios WHERE tipo_usuario = 'conductor'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Conductores</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_styles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Lista de Conductores</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Sucursal</th>
                    <th>No. Licencia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['sucursal']); ?></td>
                            <td><?php echo htmlspecialchars($row['no_licencia']); ?></td>
                            <td><?php echo $row['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-btn">Opciones</button>
                                    <div class="dropdown-menu">
                                        <a href="#" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['usuario']); ?>', '<?php echo htmlspecialchars($row['nombre']); ?>', '<?php echo htmlspecialchars($row['sucursal']); ?>', '<?php echo htmlspecialchars($row['no_licencia']); ?>', <?php echo $row['activo']; ?>)">Editar</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No hay conductores registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para editar conductor -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Conductor</h3>
            <form id="editForm" action="update_conductor.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" name="usuario" id="edit_usuario" required>
                </div>
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>
                <div class="form-group">
                    <label>Sucursal:</label>
                    <input type="text" name="sucursal" id="edit_sucursal" required>
                </div>
                <div class="form-group">
                    <label>Número de Licencia:</label>
                    <input type="text" name="no_licencia" id="edit_no_licencia" required>
                </div>
                <div class="form-group">
                    <label>Nueva Contraseña (déjalo en blanco si no deseas cambiarla):</label>
                    <input type="password" name="password" id="edit_password" placeholder="Ingresa nueva contraseña">
                </div>
                <div class="form-group">
                    <label>Estado:</label>
                    <select name="activo" id="edit_activo" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Guardar</button>
                <button type="button" onclick="closeEditModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // Mostrar/Ocultar menú desplegable
        document.querySelectorAll('.dropdown-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const menu = btn.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('dropdown-btn')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });

        // Abrir modal de edición
        function openEditModal(id, usuario, nombre, sucursal, no_licencia, activo) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_usuario').value = usuario;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_sucursal').value = sucursal;
            document.getElementById('edit_no_licencia').value = no_licencia;
            document.getElementById('edit_activo').value = activo;
            document.getElementById('edit_password').value = ''; // Limpiar el campo de contraseña
            document.getElementById('editModal').style.display = 'block';
        }

        // Cerrar modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>

<?php
$result->close();
$conn->close();
?>