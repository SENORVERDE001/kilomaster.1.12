const usuarioId = document.body.dataset.usuarioId || 'default';

function loadState() {
    const state = localStorage.getItem(`conductorState_${usuarioId}`);
    return state ? JSON.parse(state) : {
        id_vehiculo: '',
        km_inicial: '',
        destino: '',
        otro_destino: '',
        litros: null,
        pesos: null,
        km_final: '',
        foto_kilometraje_final: '',
        paso_actual: 1
    };
}

function saveState(state) {
    localStorage.setItem(`conductorState_${usuarioId}`, JSON.stringify(state));
}

function clearState() {
    localStorage.removeItem(`conductorState_${usuarioId}`);
}

let currentStep = 1;
let stream = null;
let fuelDataSaved = false;
let photoCaptured = false;
let zoomLevel = 1; // Default zoom level

// Actualizar fecha y hora
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        timeZone: 'America/Mexico_City'
    };
    document.getElementById('datetime').innerText = now.toLocaleString('es-MX', options);
}

// Cargar estado inicial al cargar la página
window.onload = function() {
    updateDateTime();
    setInterval(updateDateTime, 1000);
    const state = loadState();
    currentStep = state.paso_actual;
    document.querySelectorAll('.step').forEach(step => step.classList.remove('active'));
    document.getElementById(`step${currentStep}`).classList.add('active');
    restoreState(state);
    document.querySelector('.content').style.opacity = '1';
};

// Restaurar estado en la interfaz
function restoreState(state) {
    if (state.id_vehiculo) document.getElementById('id_vehiculo').value = state.id_vehiculo;
    if (state.km_inicial) {
        document.getElementById('km_inicial').value = state.km_inicial;
        document.getElementById('km_inicial_display').innerText = `${state.km_inicial} km`;
    }
    if (state.destino) document.getElementById('destino').value = state.destino;
    if (state.destino === 'Otro' && state.otro_destino) {
        document.getElementById('otroDestinoContainer').style.display = 'block';
        document.getElementById('otro_destino').value = state.otro_destino;
    }
    if (state.litros && state.pesos) {
        fuelDataSaved = true;
        document.getElementById('fuelFormContainer').style.display = 'block';
        document.getElementById('litros').value = state.litros;
        document.getElementById('pesos').value = state.pesos;
    }
    if (state.km_final) document.getElementById('km_final').value = state.km_final;
    if (state.foto_kilometraje_final) {
        document.getElementById('foto_kilometraje_final').value = state.foto_kilometraje_final;
        document.getElementById('photoPreviewFinal').src = state.foto_kilometraje_final;
        document.getElementById('photoPreviewFinal').style.display = 'block';
        photoCaptured = true;
    }
    if (currentStep === 4) updateSummary();
}

function toggleOtroDestino() {
    const destinoSelect = document.getElementById('destino');
    const otroDestinoContainer = document.getElementById('otroDestinoContainer');
    const otroDestinoInput = document.getElementById('otro_destino');
    if (destinoSelect.value === 'Otro') {
        otroDestinoContainer.style.display = 'block';
        otroDestinoInput.required = true;
    } else {
        otroDestinoContainer.style.display = 'none';
        otroDestinoInput.required = false;
        otroDestinoInput.value = '';
    }
    updateState();
}

function toggleFuelForm() {
    const fuelFormContainer = document.getElementById('fuelFormContainer');
    fuelFormContainer.style.display = fuelFormContainer.style.display === 'block' ? 'none' : 'block';
}

function cancelFuel() {
    document.getElementById('litros').value = '';
    document.getElementById('pesos').value = '';
    document.getElementById('fuelFormContainer').style.display = 'none';
    fuelDataSaved = false;
    updateState();
}

function saveFuel() {
    const idVehiculo = document.getElementById('id_vehiculo').value;
    const litros = document.getElementById('litros').value;
    const pesos = document.getElementById('pesos').value;

    if (!idVehiculo) {
        showErrorModal('Por favor, selecciona un vehículo antes de registrar combustible.');
        return;
    }
    if (!litros || !pesos || litros <= 0 || pesos <= 0) {
        showErrorModal('Por favor, ingresa valores válidos para litros y pesos (mayores a 0).');
        return;
    }

    fuelDataSaved = true;
    updateState();
    showSuccessMessage('Carga de combustible registrada correctamente.');
    document.getElementById('fuelFormContainer').style.display = 'none';
}

function updateState() {
    const state = {
        id_vehiculo: document.getElementById('id_vehiculo').value,
        km_inicial: document.getElementById('km_inicial').value,
        destino: document.getElementById('destino').value,
        otro_destino: document.getElementById('otro_destino').value,
        litros: fuelDataSaved ? document.getElementById('litros').value : null,
        pesos: fuelDataSaved ? document.getElementById('pesos').value : null,
        km_final: document.getElementById('km_final').value,
        foto_kilometraje_final: document.getElementById('foto_kilometraje_final').value,
        paso_actual: currentStep
    };
    saveState(state);
    if (currentStep === 4) updateSummary();
}

function nextStep(step) {
    if (step === 2 && !document.getElementById('id_vehiculo').value) {
        showErrorModal('Por favor, selecciona un vehículo.');
        return;
    }
    if (step === 3) {
        if (!document.getElementById('km_inicial').value) {
            showErrorModal('No se pudo cargar el kilometraje inicial. Selecciona un vehículo válido.');
            return;
        }
        const destino = document.getElementById('destino').value;
        const otroDestino = document.getElementById('otro_destino').value;
        if (!destino) {
            showErrorModal('Por favor, selecciona un destino.');
            return;
        }
        if (destino === 'Otro' && !otroDestino.trim()) {
            showErrorModal('Por favor, especifica el destino en el campo "Otro".');
            return;
        }
    }
    if (step === 4) {
        const kmFinal = parseInt(document.getElementById('km_final').value);
        const kmInicial = parseInt(document.getElementById('km_inicial').value) || 0;
        if (!kmFinal || isNaN(kmFinal)) {
            showErrorModal('Por favor, ingresa un kilometraje final válido.');
            return;
        }
        if (kmFinal <= kmInicial) {
            showErrorModal(`El kilometraje final (${kmFinal}) debe ser mayor que el inicial (${kmInicial}).`);
            return;
        }
        if (!photoCaptured) {
            showErrorModal('Por favor, captura la foto del kilometraje final.');
            return;
        }
    }

    const content = document.querySelector('.content');
    content.style.opacity = '0';
    setTimeout(() => {
        document.querySelectorAll('.step-indicator').forEach(indicator => {
            indicator.classList.remove('active');
            if (parseInt(indicator.dataset.step) === step) {
                indicator.classList.add('active');
            }
        });

        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${step}`).classList.add('active');
        currentStep = step;

        if (step === 2) {
            const idVehiculo = document.getElementById('id_vehiculo').value;
            fetch(`get_last_km.php?id_vehiculo=${idVehiculo}`)
                .then(response => response.text())
                .then(data => {
                    const kmInicial = data || '0';
                    document.getElementById('km_inicial_display').innerText = `${kmInicial} km`;
                    document.getElementById('km_inicial').value = kmInicial;
                    updateState();
                })
                .catch(err => {
                    console.error('Error al obtener último km:', err);
                    document.getElementById('km_inicial_display').innerText = 'Error al cargar';
                    document.getElementById('km_inicial').value = '';
                    showErrorModal('Error al cargar el kilometraje inicial.');
                });
        } else if (step === 3) {
            updateState();
            stopCamera();
        } else if (step === 4) {
            updateState();
            updateSummary();
        }

        setTimeout(() => {
            content.style.opacity = '1';
        }, 100);
    }, 300);
}

function updateSummary() {
    const state = loadState();
    
    const vehicleSelect = document.getElementById('id_vehiculo');
    const selectedVehicle = vehicleSelect.options[vehicleSelect.selectedIndex]?.text || '--';
    document.getElementById('summary_vehicle').innerText = selectedVehicle;

    const kmInicial = state.km_inicial || '--';
    document.getElementById('summary_km_initial').innerText = kmInicial !== '--' ? `${kmInicial} km` : '--';

    const kmFinal = state.km_final || '--';
    document.getElementById('summary_km_final').innerText = kmFinal !== '--' ? `${kmFinal} km` : '--';

    const kmInicialNum = parseInt(state.km_inicial) || 0;
    const kmFinalNum = parseInt(state.km_final) || 0;
    const distance = kmFinalNum - kmInicialNum;
    document.getElementById('summary_distance').innerText = distance > 0 ? `${distance} km` : '--';

    const fuelText = state.litros && state.pesos 
        ? `${state.litros} litros / $${state.pesos} MXN` 
        : 'No se cargó combustible';
    document.getElementById('summary_fuel').innerText = fuelText;

    const destination = state.destino === 'Otro' && state.otro_destino 
        ? state.otro_destino 
        : state.destino || '--';
    document.getElementById('summary_destination').innerText = destination;

    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        timeZone: 'America/Mexico_City'
    };
    document.getElementById('summary_date').innerText = now.toLocaleString('es-MX', options);
}

function resetWizard() {
    currentStep = 1;
    document.querySelectorAll('.step').forEach(step => step.classList.remove('active'));
    document.getElementById('step1').classList.add('active');
    document.querySelectorAll('.step-indicator').forEach(indicator => {
        indicator.classList.remove('active');
        if (parseInt(indicator.dataset.step) === 1) {
            indicator.classList.add('active');
        }
    });
    document.getElementById('id_vehiculo').value = '';
    document.getElementById('km_inicial').value = '';
    document.getElementById('km_inicial_display').innerText = '--';
    document.getElementById('destino').value = '';
    document.getElementById('otro_destino').value = '';
    document.getElementById('otroDestinoContainer').style.display = 'none';
    document.getElementById('litros').value = '';
    document.getElementById('pesos').value = '';
    document.getElementById('fuelFormContainer').style.display = 'none';
    document.getElementById('km_final').value = '';
    document.getElementById('foto_kilometraje_final').value = '';
    document.getElementById('photoPreviewFinal').style.display = 'none';
    document.getElementById('captureBtnFinal').disabled = true;
    fuelDataSaved = false;
    photoCaptured = false;
    clearState();
    stopCamera();
    window.scrollTo(0, 0);
}

document.getElementById('kilometrajeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const state = loadState();
    document.getElementById('id_vehiculo').value = state.id_vehiculo || '';
    document.getElementById('km_inicial').value = state.km_inicial || '';
    document.getElementById('destino').value = state.destino || '';
    document.getElementById('otro_destino').value = state.otro_destino || '';
    if (state.litros && state.pesos) {
        document.getElementById('litros').value = state.litros;
        document.getElementById('pesos').value = state.pesos;
    }
    document.getElementById('km_final').value = state.km_final || '';

    const formData = new FormData(this);
    fetch('save_km.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en el servidor');
        return response.text();
    })
    .then(data => {
        console.log('Respuesta de save_km.php:', data);
        const welcomeDiv = document.querySelector('.welcome');
        welcomeDiv.innerHTML = `
            <h2>Bienvenido, ${document.body.dataset.usuarioNombre || 'Conductor'}</h2>
            <p>Registra el kilometraje de tu vehículo con precisión y estilo.</p>
            <div class="datetime" id="datetime">Fecha y hora actual</div>
            <div class="message success">Kilometraje registrado correctamente.</div>
        `;
        updateDateTime();
        resetWizard();
    })
    .catch(error => {
        console.error('Error al enviar formulario:', error);
        const welcomeDiv = document.querySelector('.welcome');
        welcomeDiv.innerHTML = `
            <h2>Bienvenido, ${document.body.dataset.usuarioNombre || 'Conductor'}</h2>
            <p>Registra el kilometraje de tu vehículo con precisión y estilo.</p>
            <div class="datetime" id="datetime">Fecha y hora actual</div>
            <div class="message error">Hubo un error al registrar el kilometraje: ${error.message}</div>
        `;
        updateDateTime();
    });
});

// Modal dialog functions
function showConfirmationModal(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmationModal');
        const messageElem = document.getElementById('confirmationMessage');
        const yesBtn = document.getElementById('confirmYes');
        const noBtn = document.getElementById('confirmNo');

        messageElem.textContent = message;
        modal.style.display = 'flex';

        function cleanUp() {
            yesBtn.removeEventListener('click', onYes);
            noBtn.removeEventListener('click', onNo);
            modal.style.display = 'none';
        }

        function onYes() {
            cleanUp();
            resolve(true);
        }

        function onNo() {
            cleanUp();
            resolve(false);
        }

        yesBtn.addEventListener('click', onYes);
        noBtn.addEventListener('click', onNo);
    });
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.style.display = 'none';
}

function showErrorModal(message) {
    const modal = document.getElementById('errorModal');
    const messageElem = document.getElementById('errorMessage');
    messageElem.textContent = message;
    modal.style.display = 'flex';
}

function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    modal.style.display = 'none';
}

function showSuccessMessage(message) {
    const welcomeDiv = document.querySelector('.welcome');
    welcomeDiv.innerHTML = `
        <h2>Bienvenido, ${document.body.dataset.usuarioNombre || 'Conductor'}</h2>
        <p>Registra el kilometraje de tu vehículo con precisión y estilo.</p>
        <div class="datetime" id="datetime">Fecha y hora actual</div>
        <div class="message success">${message}</div>
    `;
    updateDateTime();
}

function startCamera(type) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showErrorModal('Tu navegador no soporta acceso a la cámara. Por favor, usa un navegador moderno.');
        return;
    }

    const videoContainer = document.getElementById(`videoContainer${type.charAt(0).toUpperCase() + type.slice(1)}`);
    const videoElement = document.getElementById(`video_${type}`);
    const captureBtn = document.getElementById('captureBtnFinal');
    const closeCameraBtn = document.getElementById('closeCameraBtn');

    // Check if all required elements exist
    if (!videoContainer || !videoElement || !captureBtn || !closeCameraBtn) {
        showErrorModal('Error: No se encontraron los elementos necesarios en la página para usar la cámara.');
        console.error('Missing DOM elements:', {
            videoContainer: !videoContainer,
            videoElement: !videoElement,
            captureBtn: !captureBtn,
            closeCameraBtn: !closeCameraBtn
        });
        return;
    }

    // Check camera permissions
    navigator.permissions.query({ name: 'camera' }).then(permissionStatus => {
        if (permissionStatus.state === 'denied') {
            showErrorModal('El acceso a la cámara está denegado. Por favor, habilita los permisos en la configuración de tu navegador.');
            return;
        }

        if (!stream) {
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            })
            .then(mediaStream => {
                stream = mediaStream;
                videoContainer.style.display = 'block';
                videoElement.srcObject = stream;
                videoElement.play();
                captureBtn.disabled = false;
                closeCameraBtn.style.display = 'block';
                applyZoom(); // Apply initial zoom
            })
            .catch(err => {
                console.error('Error al acceder a la cámara:', err);
                if (err.name === 'NotAllowedError') {
                    showErrorModal('No se otorgaron permisos para acceder a la cámara. Por favor, permite el acceso en la configuración de tu navegador.');
                } else if (err.name === 'NotFoundError') {
                    showErrorModal('No se encontró una cámara en tu dispositivo.');
                } else {
                    showErrorModal(`Error al abrir la cámara: ${err.message}`);
                }
            });
        }
    }).catch(err => {
        console.error('Error al verificar permisos de cámara:', err);
        showErrorModal('No se pudo verificar el estado de los permisos de la cámara.');
    });
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
        const videoContainer = document.getElementById('videoContainerFinal');
        const captureBtn = document.getElementById('captureBtnFinal');
        const closeCameraBtn = document.getElementById('closeCameraBtn');

        if (videoContainer) videoContainer.style.display = 'none';
        if (captureBtn) captureBtn.disabled = true;
        if (closeCameraBtn) closeCameraBtn.style.display = 'none';
        zoomLevel = 1; // Reset zoom
        applyZoom();
    }
}

function zoomIn() {
    zoomLevel = Math.min(zoomLevel + 0.1, 3); // Max zoom 3x
    applyZoom();
}

function zoomOut() {
    zoomLevel = Math.max(zoomLevel - 0.1, 1); // Min zoom 1x
    applyZoom();
}

function applyZoom() {
    const video = document.getElementById('video_final');
    if (video) {
        video.style.transform = `scale(${zoomLevel})`;
    }
}

function capturePhoto(type) {
    const video = document.getElementById(`video_${type}`);
    const canvas = document.createElement('canvas');
    const videoContainer = document.getElementById(`videoContainer${type.charAt(0).toUpperCase() + type.slice(1)}`);

    // Set canvas to high resolution
    canvas.width = 1280;
    canvas.height = 720;

    const ctx = canvas.getContext('2d');

    // Get video dimensions
    const videoWidth = video.videoWidth;
    const videoHeight = video.videoHeight;

    // Apply 80% cropping
    const cropPercent = 0.8;
    let cropWidth = videoWidth * cropPercent;
    let cropHeight = videoHeight * cropPercent;

    // Adjust for zoom level
    cropWidth /= zoomLevel;
    cropHeight /= zoomLevel;

    // Center the cropped area
    const sx = (videoWidth - cropWidth) / 2;
    const sy = (videoHeight - cropHeight) / 2;

    // Draw the zoomed and cropped portion
    ctx.drawImage(video, sx, sy, cropWidth, cropHeight, 0, 0, canvas.width, canvas.height);

    const photoData = canvas.toDataURL('image/jpeg', 0.95);
    document.getElementById(`foto_kilometraje_${type}`).value = photoData;
    document.getElementById(`photoPreview${type.charAt(0).toUpperCase() + type.slice(1)}`).src = photoData;
    document.getElementById(`photoPreview${type.charAt(0).toUpperCase() + type.slice(1)}`).style.display = 'block';
    photoCaptured = true;
    stopCamera();
    updateState();
}

document.querySelectorAll('.step-indicator').forEach(indicator => {
    indicator.addEventListener('click', () => {
        const targetStep = parseInt(indicator.dataset.step);
        if (targetStep < currentStep || 
            (targetStep === 2 && document.getElementById('id_vehiculo').value) || 
            (targetStep === 3 && document.getElementById('km_inicial').value) || 
            (targetStep === 4 && document.getElementById('km_final').value && photoCaptured) || 
            targetStep === 1) {
            nextStep(targetStep);
        }
    });
});



// Assuming there is a button with id 'closeLogoBtn' for the single logo close button
document.getElementById('closeLogoBtn')?.addEventListener('click', () => {
    // Implement the close action here, for example hiding a menu or modal
    const menu = document.querySelector('.menu-container'); // Adjust selector as needed
    if (menu) {
        menu.style.display = 'none';
    }
});
