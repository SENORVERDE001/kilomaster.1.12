<?php
session_start();
include 'db_connect.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador.";
    header("Location: index.php");
    exit();
}

// Obtener lista de vehículos con el nombre del responsable (si existe)
$sql = "SELECT v.id, v.marca, v.modelo, v.anio, v.consumo_km, v.kilometraje_actual, v.responsable_vehiculo, u.nombre AS responsable_nombre 
        FROM vehiculos v 
        LEFT JOIN usuarios u ON v.responsable_vehiculo = u.id";
$result = $conn->query($sql);

// Obtener lista de vehículos para el select en el modal
$sql_vehiculos = "SELECT id, marca, modelo, kilometraje_actual FROM vehiculos";
$vehiculos_result = $conn->query($sql_vehiculos);
if (!$vehiculos_result) {
    $_SESSION['error'] = "Error al obtener los vehículos.";
    header("Location: index.php");
    exit();
}
$vehiculos_array = [];
while ($vehiculo = $vehiculos_result->fetch_assoc()) {
    $vehiculos_array[] = $vehiculo;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Vehículos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_styles.css">
    <style>
        .dropdown-menu {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Lista de Vehículos</h1>
        
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
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Consumo (km/l)</th>
                    <th>Kilometraje Actual</th>
                    <th>Responsable</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['marca']); ?></td>
                            <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($row['anio']); ?></td>
                            <td><?php echo number_format($row['consumo_km'], 2); ?></td>
                            <td><?php echo number_format($row['kilometraje_actual']); ?></td>
                            <td><?php echo $row['responsable_nombre'] ? htmlspecialchars($row['responsable_nombre']) : 'Sin asignar'; ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-btn">Opciones</button>
                                    <div class="dropdown-menu">
                                        <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['marca']); ?>', '<?php echo htmlspecialchars($row['modelo']); ?>', <?php echo $row['anio']; ?>, <?php echo $row['consumo_km']; ?>, <?php echo $row['kilometraje_actual']; ?>, <?php echo $row['responsable_vehiculo'] ?: 'null'; ?>)">Editar</button>
                                        <button onclick="openReportModal(<?php echo $row['id']; ?>)">Ver Reporte</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No hay vehículos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para editar vehículo -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Vehículo</h3>
            <form id="editForm" action="update_vehiculo.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Marca:</label>
                    <input type="text" name="marca" id="edit_marca" required>
                </div>
                <div class="form-group">
                    <label>Modelo:</label>
                    <input type="text" name="modelo" id="edit_modelo" required>
                </div>
                <div class="form-group">
                    <label>Año:</label>
                    <select name="anio" id="edit_anio" required aria-required="true">
                        <?php
                        $current_year = date('Y');
                        for ($year = $current_year; $year >= 1999; $year--) {
                            echo "<option value=\"$year\">$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Consumo (km/l):</label>
                    <input type="number" step="0.01" name="consumo_km" id="edit_consumo_km" required>
                </div>
                <div class="form-group">
                    <label>Kilometraje Actual:</label>
                    <input type="number" name="kilometraje_actual" id="edit_kilometraje_actual" required>
                </div>
                <div class="form-group">
                    <label>Responsable:</label>
                    <select name="responsable_vehiculo" id="edit_responsable_vehiculo">
                        <option value="">Sin asignar</option>
                        <?php
                        $conductores_sql = "SELECT id, nombre FROM usuarios WHERE tipo_usuario = 'conductor' AND activo = 1";
                        $conductores_result = $conn->query($conductores_sql);
                        while ($conductor = $conductores_result->fetch_assoc()) {
                            echo "<option value='{$conductor['id']}'>" . htmlspecialchars($conductor['nombre']) . "</option>";
                        }
                        $conductores_result->close();
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn-save">Guardar</button>
                <button type="button" onclick="closeEditModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para reporte de viajes -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <h3>Reporte de Viajes</h3>
            <!-- Formulario de filtros completo -->
            <div class="filter-form">
                <div class="form-group">
                    <label for="modal_vehicle_id">Vehículo:</label>
                    <select id="modal_vehicle_id" name="modal_vehicle_id" required aria-required="true">
                        <option value="">Selecciona un vehículo</option>
                        <?php 
                        foreach ($vehiculos_array as $vehiculo): ?>
                            <option value="<?php echo $vehiculo['id']; ?>" data-kilometraje-actual="<?php echo $vehiculo['kilometraje_actual']; ?>">
                                <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modal_conductor_id">Conductor:</label>
                    <input type="text" id="modal_conductor_id" name="modal_conductor_id" placeholder="ID del conductor">
                </div>
                <div class="form-group">
                    <label for="modal_date_start">Fecha Inicial:</label>
                    <input type="date" id="modal_date_start" name="modal_date_start">
                </div>
                <div class="form-group">
                    <label for="modal_date_end">Fecha Final:</label>
                    <input type="date" id="modal_date_end" name="modal_date_end">
                </div>
                <div class="form-group">
                    <label for="modal_destino">Destino:</label>
                    <input type="text" id="modal_destino" name="modal_destino" placeholder="Destino del viaje">
                </div>
                
                <div class="form-group">
                    <label for="modal_km_min">Kilometraje Mínimo:</label>
                    <input type="number" id="modal_km_min" name="modal_km_min" min="0" placeholder="Kilometraje mínimo">
                </div>
                <div class="form-group">
                    <label for="modal_sort_by">Ordenar por:</label>
                    <select id="modal_sort_by" name="modal_sort_by">
                        <option value="fecha_registro DESC">Fecha (Más reciente)</option>
                        <option value="fecha_registro ASC">Fecha (Más antiguo)</option>
                        <option value="km_difference DESC">Kilometraje (Mayor)</option>
                        <option value="km_difference ASC">Kilometraje (Menor)</option>
                    </select>
                </div>
                <div class="btn-container">
                    <button type="button" onclick="validateAndFetchModal()">Aplicar Filtros</button>
                    <button type="button" onclick="resetFiltersModal()">Restablecer</button>
                </div>
            </div>

            <!-- Controles de tabla -->
            <div class="table-controls">
                <div id="modalColumnToggles" style="display: none; margin: 10px 0;">
                    <label><input type="checkbox" data-column="1" checked> ID Vehículo</label>
                    <label><input type="checkbox" data-column="2" checked> ID Conductor</label>
                    <label><input type="checkbox" data-column="6" checked> KM Inicial</label>
                    <label><input type="checkbox" data-column="7" checked> KM Final</label>
                    <label><input type="checkbox" data-column="8" checked> Diferencia</label>
                    <label><input type="checkbox" data-column="9" checked> Destino</label>
                    <label><input type="checkbox" data-column="10" checked> Fecha</label>
                    <label><input type="checkbox" data-column="11" checked> Litros</label>
                    <label><input type="checkbox" data-column="12" checked> Pesos</label>
                    <label><input type="checkbox" data-column="13" checked> Foto</label>
                </div>
            </div>

            <!-- Resumen de kilometraje recorrido -->
            <div id="modalKilometrajeSummary" class="summary" style="display: none;">
                <h3>Resumen del Vehículo</h3>
                <p>Kilometraje recorrido desde el registro: <span id="modalTotalKilometraje"></span> km</p>
                <p id="modalDateRangeKilometraje" style="display:none;">Kilometraje recorrido en el rango de fechas: <span></span> km</p>
            </div>

            <!-- Tabla de reporte -->
            <div class="table-container" id="modalTableContainer">
                <table id="modalReportTable">
                    <thead>
                        <tr>
                            <th>ID Viaje</th>
                            <th>ID Vehículo</th>
                            <th>ID Conductor</th>
                            <th>Conductor</th>
                            <th>KM Inicial</th>
                            <th>KM Final</th>
                            <th>Diferencia</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Litros</th>
                            <th>Pesos</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody id="modalReportBody">
                        <tr><td colspan="13">Selecciona un vehículo y aplica los filtros para ver el reporte.</td></tr>
                    </tbody>
                </table>
                <div id="modalLoading" style="display: none; text-align: center; padding: 10px;">Cargando más datos...</div>
            </div>
            <button type="button" onclick="closeReportModal()">Cerrar</button>
        </div>
    </div>

    <!-- Modal para ver foto en tamaño completo -->
    <div id="photoModal" class="modal">
        <div class="modal-content">
            <h3>Foto del Kilometraje</h3>
            <img id="fullSizePhoto" src="" alt="Foto del Kilometraje" style="width: 100%; border-radius: 8px;">
            <div style="margin-top: 10px;">
                <button type="button" onclick="downloadPhoto()" class="btn-save">Descargar</button>
                <button type="button" onclick="closePhotoModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // Asegurar que el modal esté oculto al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const reportModal = document.getElementById('reportModal');
            if (reportModal) {
                reportModal.style.display = 'none';
                console.log('Modal inicializado como oculto en list_vehiculos.php');
            }
        });

        // Función para escapar caracteres HTML y prevenir XSS
        function escapeHTML(str) {
            if (str === null || str === undefined) return '';
            str = String(str);
            return str.replace(/[&<>"'`=\/]/g, function (s) {
                return {
                    '&': '&amp;',
                    '<': '<',
                    '>': '>',
                    '"': '"',
                    "'": '&#39;',
                    '`': '&#x60;',
                    '=': '&#x3D;',
                    '/': '&#x2F;'
                }[s];
            });
        }

        let modalIsLoading = false;
        let modalHasMoreData = true;
        let modalCurrentPage = 1;

        // Mostrar/Ocultar menú desplegable
        document.querySelectorAll('.dropdown-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const menu = btn.nextElementSibling;
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('dropdown-btn')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => menu.style.display = 'none');
            }
        });

        // Abrir modal de edición
        function openEditModal(id, marca, modelo, anio, consumo_km, kilometraje_actual, responsable_vehiculo) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_marca').value = marca;
            document.getElementById('edit_modelo').value = modelo;
            document.getElementById('edit_anio').value = anio;
            document.getElementById('edit_consumo_km').value = consumo_km;
            document.getElementById('edit_kilometraje_actual').value = kilometraje_actual;
            document.getElementById('edit_responsable_vehiculo').value = responsable_vehiculo || '';
            document.getElementById('editModal').style.display = 'block';
        }

        // Cerrar modal de edición
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Abrir modal de reporte
        let currentVehicleId;
        function openReportModal(vehicleId) {
            console.log('openReportModal llamado en list_vehiculos.php con vehicleId:', vehicleId, new Error().stack);
            currentVehicleId = vehicleId;
            document.getElementById('modal_vehicle_id').value = vehicleId;
            document.getElementById('reportModal').style.display = 'block';
            modalCurrentPage = 1;
            modalHasMoreData = true;
            fetchTrips();
        }

        // Cerrar modal de reporte
        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }

        // Abrir modal de foto
        let currentPhotoPath;
        function openPhotoModal(photoPath) {
            currentPhotoPath = photoPath;
            document.getElementById('fullSizePhoto').src = photoPath;
            document.getElementById('photoModal').style.display = 'block';
        }

        // Cerrar modal de foto
        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
        }

        // Descargar foto
        function downloadPhoto() {
            const link = document.createElement('a');
            link.href = currentPhotoPath;
            link.download = currentPhotoPath.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Validar filtros y reiniciar paginación para obtener datos
        function validateAndFetchModal() {
            modalCurrentPage = 1;
            modalHasMoreData = true;
            fetchTrips();
        }

        // Obtener viajes del vehículo
        function fetchTrips() {
            if (modalIsLoading || !modalHasMoreData) return;
            modalIsLoading = true;
            document.getElementById('modalLoading').style.display = 'block';

            const vehicleId = document.getElementById('modal_vehicle_id').value;
            const conductorId = document.getElementById('modal_conductor_id').value;
            const destino = document.getElementById('modal_destino').value;
            const dateStart = document.getElementById('modal_date_start').value;
            const dateEnd = document.getElementById('modal_date_end').value;
            const kmMin = document.getElementById('modal_km_min').value;
            const sortBy = document.getElementById('modal_sort_by').value;

            if (!vehicleId) {
                document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="13">Por favor, selecciona un vehículo.</td></tr>';
                document.getElementById('modalKilometrajeSummary').style.display = 'none';
                document.getElementById('modalLoading').style.display = 'none';
                modalIsLoading = false;
                return;
            }

            let url = `get_vehicle_trips.php?id=${encodeURIComponent(vehicleId)}&page=${modalCurrentPage}&limit=1000`;
            if (conductorId) url += `&conductor_id=${encodeURIComponent(conductorId)}`;
            if (destino) url += `&destino=${encodeURIComponent(destino)}`;
            if (dateStart) url += `&date_start=${encodeURIComponent(dateStart)}`;
            if (dateEnd) url += `&date_end=${encodeURIComponent(dateEnd)}`;
            if (kmMin) url += `&km_min=${encodeURIComponent(kmMin)}`;
            if (sortBy) url += `&sort_by=${encodeURIComponent(sortBy)}`;

            if (modalCurrentPage === 1) {
                document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="13">Cargando...</td></tr>';
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('modalReportBody');
                    if (modalCurrentPage === 1) tbody.innerHTML = '';

                    if (data.error) {
                        tbody.innerHTML = `<tr><td colspan="13">${escapeHTML(data.error)}</td></tr>`;
                        document.getElementById('modalKilometrajeSummary').style.display = 'none';
                        modalHasMoreData = false;
                    } else if (data.data.length > 0) {
                        data.data.forEach(trip => {
                            const kmDifference = parseFloat(trip.km_difference);
                            const kmClass = kmDifference > 1000 ? 'highlight-high' : kmDifference > 500 ? 'highlight-medium' : '';
                            const row = `
                                <tr>
                                    <td>${escapeHTML(trip.trip_id)}</td>
                                    <td>${escapeHTML(trip.id_vehiculo)}</td>
                                    <td>${escapeHTML(trip.id_conductor)}</td>
                                    <td>${escapeHTML(trip.conductor)}</td>
                                    <td>${parseFloat(trip.km_inicial).toLocaleString()}</td>
                                    <td>${parseFloat(trip.km_final).toLocaleString()}</td>
                                    <td class="${kmClass}">${kmDifference.toLocaleString()}</td>
                                    <td>${escapeHTML(trip.destino)}</td>
                                    <td>${new Date(trip.fecha_registro).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' })}</td>
                                    <td>${trip.total_litros ? parseFloat(trip.total_litros).toFixed(2) : '-'}</td>
                                    <td>${trip.total_pesos ? parseFloat(trip.total_pesos).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '-'}</td>
                                    <td>
                                        ${trip.foto_kilometraje_final_path ?
                                            `<img src="${escapeHTML(trip.foto_kilometraje_final_path)}" class="thumbnail" onclick="openPhotoModal('${escapeHTML(trip.foto_kilometraje_final_path)}')" alt="Foto de kilometraje">` :
                                            '-'}
                                    </td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });

                        const selectedVehicle = document.getElementById('modal_vehicle_id').options[document.getElementById('modal_vehicle_id').selectedIndex];
                        const kilometrajeActual = parseInt(selectedVehicle.getAttribute('data-kilometraje-actual'));
                        const initialKm = data.data[0].initial_vehicle_km;
                        const totalKilometraje = kilometrajeActual - initialKm;
                        document.getElementById('modalTotalKilometraje').textContent = totalKilometraje.toLocaleString();
                        document.getElementById('modalKilometrajeSummary').style.display = 'block';

                        const dateStart = document.getElementById('modal_date_start').value;
                        const dateEnd = document.getElementById('modal_date_end').value;
                        const dateRangeElem = document.getElementById('modalDateRangeKilometraje');
                        if (dateStart && dateEnd && data.data.length > 0) {
                            const firstKm = parseFloat(data.data[0].km_final);
                            const lastKm = parseFloat(data.data[data.data.length - 1].km_inicial);
                            const dateRangeKm = firstKm - lastKm;
                            dateRangeElem.style.display = 'block';
                            dateRangeElem.querySelector('span').textContent = Math.abs(dateRangeKm).toLocaleString();
                        } else {
                            dateRangeElem.style.display = 'none';
                            dateRangeElem.querySelector('span').textContent = '';
                        }

                        modalHasMoreData = data.data.length === 1000;
                    } else {
                        if (modalCurrentPage === 1) {
                            tbody.innerHTML = '<tr><td colspan="13">No hay viajes registrados para este vehículo.</td></tr>';
                            document.getElementById('modalKilometrajeSummary').style.display = 'none';
                        }
                        modalHasMoreData = false;
                    }

                    document.getElementById('modalLoading').style.display = 'none';
                    modalIsLoading = false;

                    const tableContainer = document.getElementById('modalTableContainer');
                    tableContainer.scrollTop = 0;
                    tableContainer.scrollLeft = 0;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="13">Error al cargar los datos.</td></tr>';
                    document.getElementById('modalKilometrajeSummary').style.display = 'none';
                    document.getElementById('modalLoading').style.display = 'none';
                    modalIsLoading = false;
                    modalHasMoreData = false;
                });
        }
    </script>
</body>
</html>

<?php
$result->close();
$conn->close();
?>