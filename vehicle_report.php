<?php
session_start();
include 'db_connect.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    $_SESSION['error'] = "Acceso denegado. Debes ser administrador.";
    header("Location: index.php");
    exit();
}

// Manejo de errores en la conexión
if ($conn->connect_error) {
    $_SESSION['error'] = "Error de conexión a la base de datos.";
    header("Location: index.php");
    exit();
}

// Obtener lista de vehículos para el select
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
<html lang="es">
<head>
    <title>Reporte de Vehículos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_styles.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <h1>Reporte de Vehículos</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <button type="button" class="open-report-btn" id="openReportBtn">Abrir Reporte en Modal</button>

        <!-- Formulario de filtros -->
        <div class="filter-form">
            <div class="form-group">
                <label for="vehicle_id">Vehículo:</label>
                <select id="vehicle_id" name="vehicle_id" required aria-required="true">
                    <option value="">Selecciona un vehículo</option>
                    <?php foreach ($vehiculos_array as $vehiculo): ?>
                        <option value="<?php echo $vehiculo['id']; ?>" data-kilometraje-actual="<?php echo $vehiculo['kilometraje_actual']; ?>">
                            <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="tableSearch">Buscar en la tabla:</label>
                <input type="text" id="tableSearch" placeholder="Buscar en la tabla..." onkeyup="filterTable()">
            </div>
            <div class="form-group">
                <label for="conductor_id">Conductor:</label>
                <input type="text" id="conductor_id" name="conductor_id" placeholder="ID del conductor">
            </div>
            <div class="form-group">
                <label for="destino">Destino:</label>
                <input type="text" id="destino" name="destino" placeholder="Destino del viaje">
            </div>
            <div class="form-group">
                <label for="date_start">Fecha Inicial:</label>
                <input type="date" id="date_start" name="date_start">
            </div>
            <div class="form-group">
                <label for="date_end">Fecha Final:</label>
                <input type="date" id="date_end" name="date_end">
            </div>
            <div class="form-group">
                <label for="km_min">Kilometraje Mínimo:</label>
                <input type="number" id="km_min" name="km_min" min="0" placeholder="Kilometraje mínimo">
            </div>
            <div class="form-group">
                <label for="sort_by">Ordenar por:</label>
                <select id="sort_by" name="sort_by">
                    <option value="fecha_registro DESC">Fecha (Más reciente)</option>
                    <option value="fecha_registro ASC">Fecha (Más antiguo)</option>
                    <option value="km_difference DESC">Kilometraje (Mayor)</option>
                    <option value="km_difference ASC">Kilometraje (Menor)</option>
                </select>
            </div>
            <div class="btn-container">
                <button type="button" onclick="validateAndFetch()">Aplicar Filtros</button>
                <button type="button" onclick="openCompareModal()">Comparar Kilometrajes</button>
                <button type="button" onclick="resetFilters()">Restablecer</button>
            </div>
        </div>

        <!-- Controles de tabla -->
        <div class="table-controls">
            <button onclick="toggleColumns()">Mostrar/Ocultar Columnas</button>
            <div id="columnToggles" style="display: none; margin: 10px 0;">
                <label><input type="checkbox" data-column="1" checked> ID Vehículo</label>
                <label><input type="checkbox" data-column="2" checked> ID Conductor</label>
                <label><input type="checkbox" data-column="9" checked> Litros</label>
                <label><input type="checkbox" data-column="10" checked> Pesos</label>
                <label><input type="checkbox" data-column="11" checked> Foto</label>
            </div>
        </div>

        <!-- Resumen de kilometraje recorrido -->
        <div id="kilometrajeSummary" class="summary" style="display: none;">
            <h3>Resumen del Vehículo</h3>
            <p>Kilometraje recorrido desde el registro: <span id="totalKilometraje"></span> km</p>
        </div>

        <!-- Tabla de reporte -->
        <div class="table-container" id="tableContainer">
            <table id="reportTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0, 'reportTable')">ID Viaje</th>
                        <th onclick="sortTable(1, 'reportTable')">ID Vehículo</th>
                        <th onclick="sortTable(2, 'reportTable')">ID Conductor</th>
                        <th onclick="sortTable(3, 'reportTable')">Conductor</th>
                        <th onclick="sortTable(4, 'reportTable')">KM Inicial</th>
                        <th onclick="sortTable(5, 'reportTable')">KM Final</th>
                        <th onclick="sortTable(6, 'reportTable')">Diferencia</th>
                        <th onclick="sortTable(7, 'reportTable')">Destino</th>
                        <th onclick="sortTable(8, 'reportTable')">Fecha</th>
                        <th onclick="sortTable(9, 'reportTable')">Litros</th>
                        <th onclick="sortTable(10, 'reportTable')">Pesos</th>
                        <th onclick="sortTable(11, 'reportTable')">Foto</th>
                    </tr>
                </thead>
                <tbody id="reportBody">
                    <tr><td colspan="12">Selecciona un vehículo y aplica los filtros para ver el reporte.</td></tr>
                </tbody>
            </table>
            <div class="total-registros" id="totalRowsMain">El total de registros es: 0</div>
            <div id="loading" style="display: none; text-align: center; padding: 10px;">Cargando más datos...</div>
        </div>

        <!-- Modal para reporte -->
        <div id="reportModal" class="modal" role="dialog" aria-labelledby="reportModalTitle">
            <div class="modal-content">
                <h3 id="reportModalTitle">Reporte de Vehículos</h3>
                <button type="button" class="modal-close-btn" onclick="closeReportModal()" aria-label="Cerrar modal" title="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x" viewBox="0 0 24 24" style="margin: 10px auto;">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div class="modal-content-inner">
                    <!-- Formulario de filtros completo -->
                    <div class="filter-form">
                        <div class="form-group">
                            <label for="modal_vehicle_id">Vehículo:</label>
                            <select id="modal_vehicle_id" name="modal_vehicle_id" required aria-required="true">
                                <option value="">Selecciona un vehículo</option>
                                <?php foreach ($vehiculos_array as $vehiculo): ?>
                                    <option value="<?php echo $vehiculo['id']; ?>" data-kilometraje-actual="<?php echo $vehiculo['kilometraje_actual']; ?>">
                                        <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modalTableSearch">Buscar en la tabla:</label>
                            <input type="text" id="modalTableSearch" placeholder="Buscar en la tabla..." onkeyup="filterModalTable()">
                        </div>
                        <div class="form-group">
                            <label for="modal_conductor_id">Conductor:</label>
                            <input type="text" id="modal_conductor_id" name="modal_conductor_id" placeholder="ID del conductor">
                        </div>
                        <div class="form-group">
                            <label for="modal_destino">Destino:</label>
                            <input type="text" id="modal_destino" name="modal_destino" placeholder="Destino del viaje">
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
                            <button type="button" onclick="openCompareModal()">Comparar Kilometrajes</button>
                            <button type="button" onclick="resetFiltersModal()">Restablecer</button>
                        </div>
                    </div>

                    <!-- Controles de tabla -->
                    <div class="table-controls">
                        <div id="modalColumnToggles" style="display: none; margin: 10px 0;">
                            <label><input type="checkbox" data-column="1" checked> ID Vehículo</label>
                            <label><input type="checkbox" data-column="2" checked> ID Conductor</label>
                            <label><input type="checkbox" data-column="9" checked> Litros</label>
                            <label><input type="checkbox" data-column="10" checked> Pesos</label>
                            <label><input type="checkbox" data-column="11" checked> Foto</label>
                        </div>
                    </div>

                    <!-- Resumen de kilometraje recorrido -->
                    <div id="modalKilometrajeSummary" class="summary" style="display: none;">
                        <h3>Resumen del Vehículo</h3>
                        <p>Kilometraje recorrido desde el registro: <span id="modalTotalKilometraje"></span> km</p>
                    </div>

                    <!-- Tabla de reporte -->
                    <div class="table-container" id="modalTableContainer">
                        <table id="modalReportTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable(0, 'modalReportTable')">ID Viaje</th>
                                    <th onclick="sortTable(1, 'modalReportTable')">ID Vehículo</th>
                                    <th onclick="sortTable(2, 'modalReportTable')">ID Conductor</th>
                                    <th onclick="sortTable(3, 'modalReportTable')">Conductor</th>
                                    <th onclick="sortTable(4, 'modalReportTable')">KM Inicial</th>
                                    <th onclick="sortTable(5, 'modalReportTable')">KM Final</th>
                                    <th onclick="sortTable(6, 'modalReportTable')">Diferencia</th>
                                    <th onclick="sortTable(7, 'modalReportTable')">Destino</th>
                                    <th onclick="sortTable(8, 'modalReportTable')">Fecha</th>
                                    <th onclick="sortTable(9, 'modalReportTable')">Litros</th>
                                    <th onclick="sortTable(10, 'modalReportTable')">Pesos</th>
                                    <th onclick="sortTable(11, 'modalReportTable')">Foto</th>
                                </tr>
                            </thead>
                            <tbody id="modalReportBody">
                                <tr><td colspan="12">Selecciona un vehículo y aplica los filtros para ver el reporte.</td></tr>
                            </tbody>
                        </table>
                        <div class="total-registros" id="totalRowsModal">El total de registros es: 0</div>
                        <div id="modalLoading" style="display: none; text-align: center; padding: 10px;">Cargando más datos...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para comparar kilometrajes -->
        <div id="compareModal" class="modal" role="dialog" aria-labelledby="compareModalTitle">
            <div class="modal-content">
                <h3 id="compareModalTitle">Comparar Kilometrajes</h3>
                <div class="form-group">
                    <label for="compare_vehicle_id">Vehículo:</label>
                    <select id="compare_vehicle_id" name="compare_vehicle_id" required aria-required="true">
                        <option value="">Selecciona un vehículo</option>
                        <?php foreach ($vehiculos_array as $vehiculo): ?>
                            <option value="<?php echo $vehiculo['id']; ?>">
                                <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="km_initial_date">Fecha Kilometraje Inicial:</label>
                    <input type="date" id="km_initial_date" name="km_initial_date" required aria-required="true">
                </div>
                <div class="form-group">
                    <label for="km_final_date">Fecha Kilometraje Final:</label>
                    <input type="date" id="km_final_date" name="km_final_date" required aria-required="true">
                </div>
                <div class="btn-container">
                    <button type="button" onclick="compareKilometers()">Comparar</button>
                    <button type="button" onclick="closeCompareModal()">Cerrar</button>
                </div>
                <div id="compareResult" class="message" style="display: none;"></div>
            </div>
        </div>

        <!-- Modal para ver foto -->
        <div id="photoModal" class="modal" role="dialog" aria-labelledby="photoModalTitle">
            <div class="modal-content">
                <h3 id="photoModalTitle">Foto del Kilometraje</h3>
                <img id="fullSizePhoto" src="" alt="Foto del Kilometraje">
                <div class="btn-container">
                    <button type="button" onclick="downloadPhoto()">Descargar</button>
                    <button type="button" onclick="closePhotoModal()">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Modal para detalles del viaje -->
        <div id="detailsModal" class="modal" role="dialog" aria-labelledby="detailsModalTitle">
            <div class="modal-content">
                <h3 id="detailsModalTitle">Detalles del Viaje</h3>
                <p><strong>ID Viaje:</strong> <span id="detailTripId"></span></p>
                <p><strong>Vehículo:</strong> <span id="detailVehicle"></span></p>
                <p><strong>Conductor:</strong> <span id="detailConductor"></span></p>
                <p><strong>KM Inicial:</strong> <span id="detailKmInicial"></span></p>
                <p><strong>KM Final:</strong> <span id="detailKmFinal"></span></p>
                <p><strong>Diferencia:</strong> <span id="detailKmDifference"></span></p>
                <p><strong>Destino:</strong> <span id="detailDestino"></span></p>
                <p><strong>Fecha:</strong> <span id="detailFecha"></span></p>
                <p><strong>Litros:</strong> <span id="detailLitros"></span></p>
                <p><strong>Pesos:</strong> <span id="detailPesos"></span></p>
                <div class="btn-container">
                    <button type="button" onclick="closeDetailsModal()">Cerrar</button>
                </div>
            </div>
        </div>

        <script>
            // Asegurar que el modal esté oculto al cargar la página
            document.addEventListener('DOMContentLoaded', function() {
                const reportModal = document.getElementById('reportModal');
                if (reportModal) {
                    reportModal.style.display = 'none';
                    console.log('Modal inicializado como oculto en DOMContentLoaded');
                    // Forzar ocultación después de 100ms para sobrescribir cualquier cambio
                    setTimeout(() => {
                        reportModal.style.display = 'none';
                        console.log('Modal forzado a oculto después de 100ms');
                    }, 100);
                } else {
                    console.error('El elemento reportModal no se encontró en el DOM');
                }

                // Añadir manejador de eventos para el botón de abrir reporte
                const openReportBtn = document.getElementById('openReportBtn');
                if (openReportBtn) {
                    openReportBtn.addEventListener('click', function(event) {
                        event.preventDefault();
                        console.log('Botón Abrir Reporte en Modal clicado');
                        openReportModal(6); // Pasar el parámetro 6
                    });
                } else {
                    console.error('El botón openReportBtn no se encontró en el DOM');
                }

                // Detectar cambios en el estilo display del modal para depuración
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'style' && reportModal.style.display !== 'none') {
                            console.warn('El modal reportModal cambió a visible inesperadamente', new Error().stack);
                        }
                    });
                });
                if (reportModal) {
                    observer.observe(reportModal, { attributes: true });
                }

                // Registrar cualquier llamada a openReportModal para depuración
                const originalOpenReportModal = openReportModal;
                window.openReportModal = function(...args) {
                    console.log('openReportModal llamado con argumentos:', args, new Error().stack);
                    originalOpenReportModal(...args);
                };
            });

            // Variables globales
            let currentPage = 1;
            const limit = 1000;
            let isLoading = false;
            let hasMoreData = true;
            let modalCurrentPage = 1;
            const modalLimit = 1000;
            let modalIsLoading = false;
            let modalHasMoreData = true;
            let currentPhotoPath = '';

            // Escapar HTML para prevenir XSS
            function escapeHTML(str) {
                return str.replace(/[&<>"']/g, match => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[match]));
            }

            // Abrir/cerrar modales
            function openReportModal(vehicleId = null) {
                console.log('Abriendo reportModal con vehicleId:', vehicleId);
                const reportModal = document.getElementById('reportModal');
                if (reportModal) {
                    reportModal.style.display = 'block';
                    if (vehicleId) {
                        const modalVehicleSelect = document.getElementById('modal_vehicle_id');
                        if (modalVehicleSelect) {
                            modalVehicleSelect.value = vehicleId;
                            console.log('Vehículo preseleccionado en modal:', vehicleId);
                        } else {
                            console.error('El select modal_vehicle_id no se encontró');
                        }
                    }
                    validateAndFetchModal();
                } else {
                    console.error('El elemento reportModal no se encontró al intentar abrirlo');
                }
            }

            function closeReportModal() {
                console.log('Cerrando reportModal');
                const reportModal = document.getElementById('reportModal');
                if (reportModal) {
                    reportModal.style.display = 'none';
                } else {
                    console.error('El elemento reportModal no se encontró al intentar cerrarlo');
                }
            }

            function openCompareModal() {
                document.getElementById('compareModal').style.display = 'block';
            }

            function closeCompareModal() {
                document.getElementById('compareModal').style.display = 'none';
                document.getElementById('compareResult').style.display = 'none';
            }

            function openPhotoModal(photoPath) {
                currentPhotoPath = photoPath;
                document.getElementById('fullSizePhoto').src = escapeHTML(photoPath);
                document.getElementById('photoModal').style.display = 'block';
            }

            function closePhotoModal() {
                document.getElementById('photoModal').style.display = 'none';
            }

            function downloadPhoto() {
                const link = document.createElement('a');
                link.href = currentPhotoPath;
                link.download = currentPhotoPath.split('/').pop();
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function openDetailsModal(trip) {
                document.getElementById('detailTripId').textContent = escapeHTML(trip.trip_id);
                document.getElementById('detailVehicle').textContent = escapeHTML(trip.id_vehiculo);
                document.getElementById('detailConductor').textContent = escapeHTML(trip.conductor);
                document.getElementById('detailKmInicial').textContent = parseFloat(trip.km_inicial).toLocaleString();
                document.getElementById('detailKmFinal').textContent = parseFloat(trip.km_final).toLocaleString();
                document.getElementById('detailKmDifference').textContent = parseFloat(trip.km_difference).toLocaleString();
                document.getElementById('detailDestino').textContent = escapeHTML(trip.destino);
                document.getElementById('detailFecha').textContent = new Date(trip.fecha_registro).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
                document.getElementById('detailLitros').textContent = trip.total_litros ? parseFloat(trip.total_litros).toFixed(2) : '-';
                document.getElementById('detailPesos').textContent = trip.total_pesos ? parseFloat(trip.total_pesos).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) : '-';
                document.getElementById('detailsModal').style.display = 'block';
            }

            function closeDetailsModal() {
                document.getElementById('detailsModal').style.display = 'none';
            }

            // Validar filtros
            function validateFilters() {
                const dateStart = document.getElementById('date_start').value;
                const dateEnd = document.getElementById('date_end').value;
                const kmMin = document.getElementById('km_min').value;

                if (dateStart && dateEnd && new Date(dateStart) > new Date(dateEnd)) {
                    alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                    return false;
                }

                if (kmMin && kmMin < 0) {
                    alert('El kilometraje mínimo no puede ser negativo.');
                    return false;
                }

                return true;
            }

            // Validar filtros en modal
            function validateFiltersModal() {
                const dateStart = document.getElementById('modal_date_start').value;
                const dateEnd = document.getElementById('modal_date_end').value;
                const kmMin = document.getElementById('modal_km_min').value;

                if (dateStart && dateEnd && new Date(dateStart) > new Date(dateEnd)) {
                    alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                    return false;
                }

                if (kmMin && kmMin < 0) {
                    alert('El kilometraje mínimo no puede ser negativo.');
                    return false;
                }

                return true;
            }

            // Función para filtrar la tabla
            function filterTable() {
                const input = document.getElementById('tableSearch');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('reportTable');
                const tbody = table.querySelector('tbody');
                const rows = tbody.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;
                    for (let j = 0; j < cells.length; j++) {
                        const cellText = cells[j].textContent || cells[j].innerText;
                        if (cellText.toLowerCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            }

            function filterModalTable() {
                const input = document.getElementById('modalTableSearch');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('modalReportTable');
                const tbody = table.querySelector('tbody');
                const rows = tbody.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;
                    for (let j = 0; j < cells.length; j++) {
                        const cellText = cells[j].textContent || cells[j].innerText;
                        if (cellText.toLowerCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                    rows[i].style.display = match ? '' : 'none';
                }
            }

            // Función para ordenar la tabla
            function sortTable(columnIndex, tableId = 'reportTable') {
                const table = document.getElementById(tableId);
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const isAscending = table.querySelectorAll('th')[columnIndex].classList.contains('sorted-asc');

                table.querySelectorAll('th').forEach(th => {
                    th.classList.remove('sorted-asc', 'sorted-desc');
                });

                rows.sort((a, b) => {
                    const aValue = a.cells[columnIndex].textContent.trim();
                    const bValue = b.cells[columnIndex].textContent.trim();
                    if ([0, 5].includes(columnIndex)) {
                        return isAscending ? parseFloat(bValue) - parseFloat(aValue) : parseFloat(aValue) - parseFloat(bValue);
                    }
                    return isAscending ? bValue.localeCompare(aValue) : aValue.localeCompare(bValue);
                });

                const th = table.querySelectorAll('th')[columnIndex];
                th.classList.add(isAscending ? 'sorted-desc' : 'sorted-asc');

                rows.forEach(row => tbody.appendChild(row));
            }

            // Obtener datos de viajes
            function fetchTrips(page = 1, append = false) {
                if (isLoading || !hasMoreData) return;
                isLoading = true;
                document.getElementById('loading').style.display = 'block';

                const vehicleId = document.getElementById('vehicle_id').value;
                const conductorId = document.getElementById('conductor_id').value;
                const destino = document.getElementById('destino').value;
                const dateStart = document.getElementById('date_start').value;
                const dateEnd = document.getElementById('date_end').value;
                const kmMin = document.getElementById('km_min').value;
                const sortBy = document.getElementById('sort_by').value;

                if (!vehicleId) {
                    document.getElementById('reportBody').innerHTML = '<tr><td colspan="12">Por favor, selecciona un vehículo.</td></tr>';
                    document.getElementById('kilometrajeSummary').style.display = 'none';
                    document.getElementById('totalRowsMain').textContent = 'El total de registros es: 0';
                    document.getElementById('loading').style.display = 'none';
                    isLoading = false;
                    return;
                }

                let url = `get_vehicle_trips.php?id=${encodeURIComponent(vehicleId)}&page=${page}&limit=${limit}`;
                if (conductorId) url += `&conductor_id=${encodeURIComponent(conductorId)}`;
                if (destino) url += `&destino=${encodeURIComponent(destino)}`;
                if (dateStart) url += `&date_start=${encodeURIComponent(dateStart)}`;
                if (dateEnd) url += `&date_end=${encodeURIComponent(dateEnd)}`;
                if (kmMin) url += `&km_min=${encodeURIComponent(kmMin)}`;
                if (sortBy) url += `&sort_by=${encodeURIComponent(sortBy)}`;

                if (!append) {
                    document.getElementById('reportBody').innerHTML = '<tr><td colspan="12">Cargando...</td></tr>';
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('reportBody');
                        if (!append) tbody.innerHTML = '';

                        if (data.error) {
                            tbody.innerHTML = `<tr><td colspan="12">${escapeHTML(data.error)}</td></tr>`;
                            document.getElementById('kilometrajeSummary').style.display = 'none';
                            document.getElementById('totalRowsMain').textContent = 'El total de registros es: 0';
                            hasMoreData = false;
                        } else if (data.data.length > 0) {
                            data.data.forEach(trip => {
                                const kmDifference = parseFloat(trip.km_difference);
                                const kmClass = kmDifference > 1000 ? 'highlight-high' : kmDifference > 500 ? 'highlight-medium' : '';
                                const row = `
                                    <tr>
                                        <td><a href="#" onclick='openDetailsModal(${JSON.stringify(trip).replace(/'/g, "\\'")})'>${escapeHTML(trip.trip_id)}</a></td>
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

                            document.getElementById('totalRowsMain').textContent = `El total de registros es: ${data.total}`;
                            document.getElementById('kilometrajeSummary').style.display = 'block';
                            const selectedVehicle = document.getElementById('vehicle_id');
                            const kilometrajeActual = selectedVehicle.options[selectedVehicle.selectedIndex].dataset.kilometrajeActual;
                            const totalKilometraje = data.data.reduce((sum, trip) => sum + parseFloat(trip.km_difference), 0);
                            document.getElementById('totalKilometraje').textContent = totalKilometraje.toLocaleString();
                            hasMoreData = data.data.length === limit;
                        } else {
                            tbody.innerHTML = '<tr><td colspan="12">No se encontraron registros.</td></tr>';
                            document.getElementById('kilometrajeSummary').style.display = 'none';
                            document.getElementById('totalRowsMain').textContent = 'El total de registros es: 0';
                            hasMoreData = false;
                        }
                        document.getElementById('loading').style.display = 'none';
                        isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching trips:', error);
                        document.getElementById('reportBody').innerHTML = '<tr><td colspan="12">Error al cargar los datos.</td></tr>';
                        document.getElementById('kilometrajeSummary').style.display = 'none';
                        document.getElementById('totalRowsMain').textContent = 'El total de registros es: 0';
                        document.getElementById('loading').style.display = 'none';
                        isLoading = false;
                    });
            }

            // Obtener datos de viajes para el modal
            function validateAndFetchModal() {
                if (!validateFiltersModal()) return;
                modalCurrentPage = 1;
                modalHasMoreData = true;
                fetchModalTrips(modalCurrentPage, false);
            }

            function fetchModalTrips(page = 1, append = false) {
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
                    document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="12">Por favor, selecciona un vehículo.</td></tr>';
                    document.getElementById('modalKilometrajeSummary').style.display = 'none';
                    document.getElementById('totalRowsModal').textContent = 'El total de registros es: 0';
                    document.getElementById('modalLoading').style.display = 'none';
                    modalIsLoading = false;
                    return;
                }

                let url = `get_vehicle_trips.php?id=${encodeURIComponent(vehicleId)}&page=${page}&limit=${modalLimit}`;
                if (conductorId) url += `&conductor_id=${encodeURIComponent(conductorId)}`;
                if (destino) url += `&destino=${encodeURIComponent(destino)}`;
                if (dateStart) url += `&date_start=${encodeURIComponent(dateStart)}`;
                if (dateEnd) url += `&date_end=${encodeURIComponent(dateEnd)}`;
                if (kmMin) url += `&km_min=${encodeURIComponent(kmMin)}`;
                if (sortBy) url += `&sort_by=${encodeURIComponent(sortBy)}`;

                if (!append) {
                    document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="12">Cargando...</td></tr>';
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('modalReportBody');
                        if (!append) tbody.innerHTML = '';

                        if (data.error) {
                            tbody.innerHTML = `<tr><td colspan="12">${escapeHTML(data.error)}</td></tr>`;
                            document.getElementById('modalKilometrajeSummary').style.display = 'none';
                            document.getElementById('totalRowsModal').textContent = 'El total de registros es: 0';
                            modalHasMoreData = false;
                        } else if (data.data.length > 0) {
                            data.data.forEach(trip => {
                                const kmDifference = parseFloat(trip.km_difference);
                                const kmClass = kmDifference > 1000 ? 'highlight-high' : kmDifference > 500 ? 'highlight-medium' : '';
                                const row = `
                                    <tr>
                                        <td><a href="#" onclick='openDetailsModal(${JSON.stringify(trip).replace(/'/g, "\\'")})'>${escapeHTML(trip.trip_id)}</a></td>
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

                            document.getElementById('totalRowsModal').textContent = `El total de registros es: ${data.total}`;
                            document.getElementById('modalKilometrajeSummary').style.display = 'block';
                            const selectedVehicle = document.getElementById('modal_vehicle_id');
                            const kilometrajeActual = selectedVehicle.options[selectedVehicle.selectedIndex].dataset.kilometrajeActual;
                            const totalKilometraje = data.data.reduce((sum, trip) => sum + parseFloat(trip.km_difference), 0);
                            document.getElementById('modalTotalKilometraje').textContent = totalKilometraje.toLocaleString();
                            modalHasMoreData = data.data.length === modalLimit;
                        } else {
                            tbody.innerHTML = '<tr><td colspan="12">No se encontraron registros.</td></tr>';
                            document.getElementById('modalKilometrajeSummary').style.display = 'none';
                            document.getElementById('totalRowsModal').textContent = 'El total de registros es: 0';
                            modalHasMoreData = false;
                        }
                        document.getElementById('modalLoading').style.display = 'none';
                        modalIsLoading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching modal trips:', error);
                        document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="12">Error al cargar los datos.</td></tr>';
                        document.getElementById('modalKilometrajeSummary').style.display = 'none';
                        document.getElementById('totalRowsModal').textContent = 'El total de registros es: 0';
                        document.getElementById('modalLoading').style.display = 'none';
                        modalIsLoading = false;
                    });
            }

            // Validar y obtener datos principales
            function validateAndFetch() {
                if (!validateFilters()) return;
                currentPage = 1;
                hasMoreData = true;
                fetchTrips(currentPage, false);
            }

            // Restablecer filtros
            function resetFilters() {
                document.getElementById('vehicle_id').value = '';
                document.getElementById('conductor_id').value = '';
                document.getElementById('destino').value = '';
                document.getElementById('date_start').value = '';
                document.getElementById('date_end').value = '';
                document.getElementById('km_min').value = '';
                document.getElementById('sort_by').value = 'fecha_registro DESC';
                document.getElementById('reportBody').innerHTML = '<tr><td colspan="12">Selecciona un vehículo y aplica los filtros para ver el reporte.</td></tr>';
                document.getElementById('kilometrajeSummary').style.display = 'none';
                document.getElementById('totalRowsMain').textContent = 'El total de registros es: 0';
            }

            function resetFiltersModal() {
                document.getElementById('modal_vehicle_id').value = '';
                document.getElementById('modal_conductor_id').value = '';
                document.getElementById('modal_destino').value = '';
                document.getElementById('modal_date_start').value = '';
                document.getElementById('modal_date_end').value = '';
                document.getElementById('modal_km_min').value = '';
                document.getElementById('modal_sort_by').value = 'fecha_registro DESC';
                document.getElementById('modalReportBody').innerHTML = '<tr><td colspan="12">Selecciona un vehículo y aplica los filtros para ver el reporte.</td></tr>';
                document.getElementById('modalKilometrajeSummary').style.display = 'none';
                document.getElementById('totalRowsModal').textContent = 'El total de registros es: 0';
            }

            // Comparar kilometrajes
            function compareKilometers() {
                const vehicleId = document.getElementById('compare_vehicle_id').value;
                const initialDate = document.getElementById('km_initial_date').value;
                const finalDate = document.getElementById('km_final_date').value;

                if (!vehicleId || !initialDate || !finalDate) {
                    document.getElementById('compareResult').textContent = 'Por favor, completa todos los campos.';
                    document.getElementById('compareResult').className = 'message error';
                    document.getElementById('compareResult').style.display = 'block';
                    return;
                }

                if (new Date(initialDate) > new Date(finalDate)) {
                    document.getElementById('compareResult').textContent = 'La fecha inicial no puede ser mayor que la fecha final.';
                    document.getElementById('compareResult').className = 'message error';
                    document.getElementById('compareResult').style.display = 'block';
                    return;
                }

                const url = `compare_kilometers.php?id=${encodeURIComponent(vehicleId)}&initial_date=${encodeURIComponent(initialDate)}&final_date=${encodeURIComponent(finalDate)}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            document.getElementById('compareResult').textContent = data.error;
                            document.getElementById('compareResult').className = 'message error';
                        } else {
                            document.getElementById('compareResult').textContent = `Kilometraje recorrido: ${data.km_difference.toLocaleString()} km`;
                            document.getElementById('compareResult').className = 'message success';
                        }
                        document.getElementById('compareResult').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error comparing kilometers:', error);
                        document.getElementById('compareResult').textContent = 'Error al comparar kilometrajes.';
                        document.getElementById('compareResult').className = 'message error';
                        document.getElementById('compareResult').style.display = 'block';
                    });
            }

            // Mostrar/ocultar columnas
            function toggleColumns() {
                const toggles = document.getElementById('columnToggles');
                toggles.style.display = toggles.style.display === 'none' ? 'block' : 'none';

                const checkboxes = toggles.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const columnIndex = parseInt(this.dataset.column);
                        const table = document.getElementById('reportTable');
                        const modalTable = document.getElementById('modalReportTable');
                        const cells = table.querySelectorAll(`th:nth-child(${columnIndex + 1}), td:nth-child(${columnIndex + 1})`);
                        const modalCells = modalTable.querySelectorAll(`th:nth-child(${columnIndex + 1}), td:nth-child(${columnIndex + 1})`);

                        cells.forEach(cell => {
                            cell.style.display = this.checked ? '' : 'none';
                        });
                        modalCells.forEach(cell => {
                            cell.style.display = this.checked ? '' : 'none';
                        });
                    });
                });
            }
        </script>
    </div>
</body>
</html>