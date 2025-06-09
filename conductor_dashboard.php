<?php
session_start();
require_once 'config/db_connect.php';

// Validate if current user is a conductor.
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'conductor') {
    $_SESSION['error'] = "Acceso denegado. Debes ser un conductor para entrar aquí.";
    header("Location: index.php");
    exit();
}

// Query to get vehicles and their responsible users.
$sql = "SELECT v.id, v.marca, v.modelo, u.nombre AS responsable 
        FROM vehiculos v 
        LEFT JOIN usuarios u ON v.responsable_vehiculo = u.id";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing query: " . $conn->error);
    die("Error en la consulta de vehículos.");
}

if (!$stmt->execute()) {
    error_log("Error executing query: " . $stmt->error);
    die("Error en la consulta de vehículos.");
}

$result_vehiculos = $stmt->get_result();
?>

<?php
require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<!-- Add styles.css explicitly -->
<link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="assets/css/modal_styles.css">

<div class="content">
    <!-- Sección de Bienvenida -->
    <div class="welcome">
        <h2>Bienvenido, <?php echo isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Conductor'; ?></h2>
        <p>Registra el kilometraje de tu vehículo con precisión y estilo.</p>
        <div class="datetime" id="datetime">Fecha y hora actual</div>
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
    </div>

    <!-- Formulario de Registro de Kilometraje -->
    <div class="wizard-container">
        <h2>Registro de Kilometraje</h2>
        <!-- Indicadores de progreso -->
        <div class="stepper">
            <div class="step-indicator active" data-step="1"><span>1</span><p>Vehículo</p></div>
            <div class="step-indicator" data-step="2"><span>2</span><p>Detalles</p></div>
            <div class="step-indicator" data-step="3"><span>3</span><p>Final</p></div>
            <div class="step-indicator" data-step="4"><span>4</span><p>Resumen</p></div>
        </div>
        <form id="kilometrajeForm" action="save_km.php" method="POST" enctype="multipart/form-data">
            <!-- Paso 1: Seleccionar Vehículo -->
            <div class="step active" id="step1">
                <div class="form-group">
                    <label for="id_vehiculo">Vehículo:</label>
                    <select name="id_vehiculo" id="id_vehiculo" required>
                        <option value="">Selecciona un vehículo</option>
                        <?php 
                        $result_vehiculos->data_seek(0);
                        while ($row = $result_vehiculos->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['marca'] . ' ' . $row['modelo']) . ' - Responsable: ' . htmlspecialchars($row['responsable'] ?? 'Sin asignar'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="btn-container">
                    <button type="button" class="btn" onclick="nextStep(2)">Siguiente</button>
                </div>
            </div>

            <!-- Paso 2: Kilometraje Inicial, Destino y Combustible -->
            <div class="step" id="step2">
                <div class="form-group">
                    <label for="km_inicial">Kilometraje Inicial (km):</label>
                    <div class="fixed-value" id="km_inicial_display">--</div>
                    <span class="hidden"></span>
                    <input type="hidden" name="km_inicial" id="km_inicial">
                    <div class="info">Último kilometraje final registrado.</div>
                </div>
                <div class="form-group">
                    <label for="destino">Destino:</label>
                    <select name="destino" id="destino" required onchange="toggleOtroDestino()">
                        <option value="">Selecciona un destino</option>
                        <option value="Corporativo">Corporativo</option>
                        <option value="Matriz">Matriz</option>
                        <option value="Tequila">Tequila</option>
                        <option value="Astacinga">Astacinga</option>
                        <option value="Temaxcalapa">Temaxcalapa</option>
                        <option value="Atlahulico">Atlahullico</option>
                        <option value="Xoxocotla">Xoxocotla</option>
                        <option value="Tuxpanguillo">Tuxpanguillo</option>
                        <option value="Jalapilla">Jalapilla</option>
                        <option value="Tehuipango">Tehuipango</option>
                        <option value="Vicente Guerrero">Vicente Guerrero</option>
                        <option value="Mixtla">Mixtla</option>
                        <option value="Texhuacan">Texhuacan</option>
                        <option value="Fortin">Fortin</option>
                        <option value="Tehuacan">Tehuacan</option>
                        <option value="Boca del Monte">Boca del Monte</option>
                        <option value="Cotaxtla">Cotaxtla</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <div id="otroDestinoContainer">
                        <label for="otro_destino">Especifica el destino:</label>
                        <input type="text" name="otro_destino" id="otro_destino" placeholder="Especifica el destino" maxlength="50">
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-fuel" onclick="toggleFuelForm()">¿Cargarás combustible?</button>
                    <div id="fuelFormContainer">
                        <div class="form-group">
                            <label for="litros">Combustible en litros:</label>
                            <input type="number" step="0.01" name="litros" id="litros" min="0">
                        </div>
                        <div class="form-group">
                            <label for="pesos">Combustible en pesos:</label>
                            <input type="number" step="0.01" name="pesos" id="pesos" min="0">
                        </div>
                        <div class="btn-container">
                            <button type="button" class="btn btn-save-fuel" onclick="saveFuel()">Registrar</button>
                            <button type="button" class="btn btn-cancel-fuel" onclick="cancelFuel()">Cancelar</button>
                        </div>
                    </div>
                </div>
                <div class="btn-container">
                    <button type="button" class="btn" onclick="nextStep(3)">Siguiente</button>
                </div>
            </div>

            <!-- Paso 3: Kilometraje Final (Manual) -->
            <div class="step" id="step3">
                <div class="form-group">
                    <label for="km_final">Kilometraje Final (km):</label>
                    <input type="number" name="km_final" id="km_final" required step="1" min="0">
                </div>
                <div class="form-group">
                    <label>Foto del Kilometraje Final:</label>
                    <div class="btn-container">
                        <button type="button" class="btn-camera" onclick="startCamera('final')">Abrir Cámara</button>
                        <button type="button" class="btn-camera btn-cancel" onclick="stopCamera()" style="display: none;" id="closeCameraBtn">Cerrar Cámara</button>
                    </div>
                    <div class="video-container" id="videoContainerFinal">
                        <video id="video_final" class="video" autoplay playsinline></video>
                    </div>
                    <div class="btn-container" style="margin-top: 10px;">
                        <button type="button" class="btn-camera btn-zoom" onclick="zoomIn()">+</button>
                        <button type="button" class="btn-camera btn-zoom" onclick="zoomOut()">-</button>
                    </div>
                    <img id="photoPreviewFinal" class="photo-preview" src="" alt="Vista previa de la foto final">
                    <input type="hidden" name="foto_kilometraje_final" id="foto_kilometraje_final">
                </div>
                <div class="btn-container">
                    <button type="button" class="btn" id="captureBtnFinal" onclick="capturePhoto('final')" disabled>Capturar Foto</button>
                    <button type="button" class="btn" onclick="nextStep(4)">Siguiente</button>
                </div>
            </div>

            <!-- Paso 4: Resumen del Recorrido -->
            <div class="step" id="step4">
                <div class="summary-container">
                    <h3>Resumen del Recorrido</h3>
                    <div class="summary-item">
                        <span class="summary-label">Vehículo:</span>
                        <span id="summary_vehicle" class="summary-value">--</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Nombre:</span>
                        <span id="summary_driver" class="summary-value"><?php echo isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Conductor'; ?></span>
                    </div>
                    <div class="summary-item highlight">
                        <span class="summary-label">Kilometraje Inicial:</span>
                        <span id="summary_km_initial" class="summary-value" style="color: #ff6b35; font-size: 20px;">--</span>
                    </div>
                    <div class="summary-item highlight">
                        <span class="summary-label">Kilometraje Final:</span>
                        <span id="summary_km_final" class="summary-value" style="color: #ff6b35; font-size: 20px;">--</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Kilometraje Recorrido:</span>
                        <span id="summary_distance" class="summary-value">--</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Combustible Cargado:</span>
                        <span id="summary_fuel" class="summary-value">--</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Destino:</span>
                        <span id="summary_destination" class="summary-value">--</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Fecha:</span>
                        <span id="summary_date" class="summary-value">--</span>
                    </div>
                </div>
                <div class="btn-container">
                    <button type="button" class="btn btn-back" onclick="nextStep(3)">Regresar</button>
                    <button type="submit" class="btn" id="finishBtn">Finalizar Recorrido</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Added logout button with close-logo-button style -->
       <button class="close-logo-button" id="logoutButton" title="Cerrar Sesión" onclick="location.href='logout.php';">
     <span class="button-text">Cerrar Sesión</span>
       </button>

<!-- Modal Confirmation Dialog -->
<div id="confirmationModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close-icon" onclick="closeConfirmationModal()">×</span>
    <p id="confirmationMessage">¿Deseas guardar los cambios registrados?</p>
    <div class="modal-buttons">
      <button id="confirmYes" class="btn btn-save-fuel">Sí</button>
      <button id="confirmNo" class="btn btn-cancel">No</button>
    </div>
  </div>
</div>

<!-- Error Modal Dialog -->
<div id="errorModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close-icon" onclick="closeErrorModal()">×</span>
    <p id="errorMessage">Mensaje de error</p>
    <div class="modal-buttons">
      <button class="btn btn-cancel" onclick="closeErrorModal()">Cerrar</button>
    </div>
  </div>
</div>

<script src="assets/js/conductor.js"></script>
</body>
</html>