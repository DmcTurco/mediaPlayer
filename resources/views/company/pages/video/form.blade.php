<div class="modal fade" id="uploadVideoModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="uploadVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadVideoModalLabel">Subir Nuevo Video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="alertContainer"></div>
                <form action="{{ route('company.video.store') }}" method="POST" enctype="multipart/form-data"
                    id="videoUploadForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Información del Video -->
                            <div class="form-group mb-3">
                                <label for="title" class="form-control-label">Título del Video <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="title" name="title" required>
                                <div class="invalid-feedback" id="titleFeedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="link" class="form-control-label">Link</label>
                                <input class="form-control" type="text" id="link" name="link" required>
                                <div class="invalid-feedback" id="linkFeedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description" class="form-control-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                <div class="invalid-feedback" id="descriptionFeedback"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Área de carga y previsualización del video -->
                            <div class="upload-area mb-3">
                                <div class="upload-placeholder text-center p-4 border border-dashed rounded">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                                    <h6>Arrastra y suelta tu video aquí</h6>
                                    <p class="text-muted small mb-2">o</p>
                                    <button type="button" class="btn btn-sm btn-primary" id="browseButton">
                                        <i class="fas fa-file-video me-1"></i> Seleccionar archivo
                                    </button>
                                    <p class="text-muted small mt-2">MP4, MOV o AVI (max. 100MB)</p>
                                </div>

                                <div class="video-preview-container" style="display: none;">
                                    <video class="w-100 rounded" controls></video>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="badge bg-primary" id="videoInfo"></span>
                                        <button type="button" class="btn btn-sm btn-danger" id="removeVideoBtn">
                                            <i class="fas fa-trash me-1"></i> Quitar
                                        </button>
                                    </div>
                                </div>

                                <input type="file" class="d-none" id="video_file" name="video_file"
                                    accept="video/mp4,video/quicktime,video/x-msvideo" required>
                            </div>

                            <!-- Área de progreso (inicialmente oculta) -->
                            <div class="progress-area mb-3" id="progressArea" style="display: none;">
                                <label class="form-control-label small mb-1">Subiendo video... <span
                                        id="progressPercentage">0%</span></label>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        id="uploadProgress" role="progressbar" style="width: 0%;" aria-valuenow="0"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicador de AJAX -->
                    <input type="hidden" name="ajax_request" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="publishVideoBtn" disabled>
                    <i class="fas fa-save me-1"></i> Publicar Video
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS para el modal de carga de videos -->
<style>
    .upload-placeholder {
        border-style: dashed !important;
        border-width: 2px !important;
        border-color: #d1d3e2 !important;
        background-color: #f8f9fa;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .upload-placeholder:hover {
        border-color: #5e72e4 !important;
        background-color: #f1f3fa;
    }

    .border-primary {
        border-color: #5e72e4 !important;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .video-preview-container {
        width: 100%;
        background-color: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
    }

    .video-preview-container video {
        background-color: #000;
        width: 100%;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        max-height: 250px;
        object-fit: contain;
    }
</style>

<!-- Script para manejar la selección y carga de video -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Constantes de configuración 
        const CONFIG = {
            maxFileSize: 100 * 1024 * 1024, // 100MB
            validTypes: ['video/mp4', 'video/quicktime', 'video/x-msvideo']
        };

        // Objetos DOM principales
        const elements = {
            form: document.getElementById('videoUploadForm'),
            videoInput: document.getElementById('video_file'),
            title: document.getElementById('title'),
            link: document.getElementById('link'),
            description: document.getElementById('description'),
            browseBtn: document.getElementById('browseButton'),
            publishBtn: document.getElementById('publishVideoBtn'),
            uploadPlaceholder: document.querySelector('.upload-placeholder'),
            previewContainer: document.querySelector('.video-preview-container'),
            videoPreview: document.querySelector('.video-preview-container video'),
            removeBtn: document.getElementById('removeVideoBtn'),
            videoInfo: document.getElementById('videoInfo'),
            progressArea: document.getElementById('progressArea'),
            progressBar: document.getElementById('uploadProgress'),
            progressText: document.getElementById('progressPercentage'),
            alertContainer: document.getElementById('alertContainer'),
            modal: document.getElementById('uploadVideoModal')
        };

        // Inicializar eventos
        initEvents();

        function initEvents() {
            // Botones principales
            elements.browseBtn.addEventListener('click', triggerFileBrowser);
            elements.removeBtn.addEventListener('click', resetVideoSelection);
            elements.publishBtn.addEventListener('click', handleVideoUpload);

            // Drag & Drop
            elements.uploadPlaceholder.addEventListener('dragover', handleDragOver);
            elements.uploadPlaceholder.addEventListener('dragleave', handleDragLeave);
            elements.uploadPlaceholder.addEventListener('drop', handleDrop);

            // Input de archivo
            elements.videoInput.addEventListener('change', handleFileInputChange);

            // Modal
            elements.modal.addEventListener('hidden.bs.modal', resetModal);
        }

        // Eventos de interfaz
        function triggerFileBrowser() {
            elements.videoInput.click();
        }

        function handleDragOver(e) {
            e.preventDefault();
            elements.uploadPlaceholder.classList.add('border-primary');
        }

        function handleDragLeave() {
            elements.uploadPlaceholder.classList.remove('border-primary');
        }

        function handleDrop(e) {
            e.preventDefault();
            elements.uploadPlaceholder.classList.remove('border-primary');

            if (e.dataTransfer.files.length) {
                // Asignar el archivo al input de archivo además de procesarlo visualmente
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                elements.videoInput.files = dt.files;

                handleFileSelection(e.dataTransfer.files[0]);
            }
        }

        function handleFileInputChange() {
            if (this.files && this.files[0]) {
                handleFileSelection(this.files[0]);
            }
        }

        // Manejo de archivos
        function handleFileSelection(file) {
            // Validar tipo de archivo
            if (!CONFIG.validTypes.includes(file.type)) {
                showAlert('danger', 'Formato de archivo no válido. Sólo se permiten archivos MP4, MOV o AVI.');
                resetVideoSelection();
                return;
            }

            // Validar tamaño del archivo
            if (file.size > CONFIG.maxFileSize) {
                showAlert('danger', 'El archivo es demasiado grande. El tamaño máximo permitido es 100MB.');
                resetVideoSelection();
                return;
            }

            // Mostrar vista previa
            const videoURL = URL.createObjectURL(file);
            elements.videoPreview.src = videoURL;

            // Mostrar información del archivo
            const fileSize = formatFileSize(file.size);
            const fileType = file.type.split('/')[1].toUpperCase();
            elements.videoInfo.textContent = `${fileType} · ${fileSize}`;

            // Actualizar interfaz
            elements.uploadPlaceholder.style.display = 'none';
            elements.previewContainer.style.display = 'block';
            elements.publishBtn.disabled = false;
        }

        function resetVideoSelection() {
            elements.videoInput.value = '';
            elements.videoPreview.src = '';
            elements.previewContainer.style.display = 'none';
            elements.uploadPlaceholder.style.display = 'block';
            elements.progressArea.style.display = 'none';
            elements.publishBtn.disabled = true;
        }

        function resetModal() {
            resetVideoSelection();
            elements.title.value = '';
            elements.description.value = '';
            elements.title.classList.remove('is-invalid');
            elements.description.classList.remove('is-invalid');
            clearAlerts();
        }

        // Validación de formulario
        function validateForm() {
            let isValid = true;

            // Validar título
            if (!elements.title.value.trim()) {
                elements.title.classList.add('is-invalid');
                document.getElementById('titleFeedback').textContent = 'El título es obligatorio';
                isValid = false;
            } else {
                elements.title.classList.remove('is-invalid');
            }

            // Validar descripción
            if (!elements.description.value.trim()) {
                elements.description.classList.add('is-invalid');
                document.getElementById('descriptionFeedback').textContent = 'La descripción es obligatoria';
                isValid = false;
            } else {
                elements.description.classList.remove('is-invalid');
            }

            // Validar archivo
            if (!elements.videoInput.files[0]) {
                showAlert('danger', 'Por favor selecciona un video.');
                isValid = false;
            }

            return isValid;
        }

        // Manejo de la subida AJAX
        function handleVideoUpload() {
            if (!validateForm()) {
                return;
            }

            // Actualizar interfaz
            elements.publishBtn.disabled = true;
            elements.publishBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';
            elements.progressArea.style.display = 'block';

            // Preparar datos
            const formData = new FormData(elements.form);

            // Enviar solicitud
            const xhr = new XMLHttpRequest();
            configureXHR(xhr, formData);
        }

        function configureXHR(xhr, formData) {
            // PRIMERO: Abre la conexión
            xhr.open('POST', elements.form.action, true);

            // SEGUNDO: Añade el encabezado para que Laravel reconozca esto como AJAX
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // TERCERO: Configura los eventos (esto ya lo tienes)
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    updateProgressBar(percentComplete);
                }
            });

            // Eventos de respuesta
            xhr.addEventListener('load', handleXHRResponse);
            xhr.addEventListener('error', function() {
                showAlert('danger', 'Error de conexión. Por favor, verifica tu conexión a internet.');
                resetUploadButton();
            });
            xhr.addEventListener('abort', function() {
                showAlert('warning', 'Carga cancelada.');
                resetUploadButton();
            });

            // CUARTO: Envía la solicitud con los datos
            xhr.send(formData);
        }

        function handleXHRResponse() {
            // Verificar si la respuesta parece ser HTML
            const contentType = this.getResponseHeader('Content-Type');
            const isHtmlResponse = contentType && contentType.includes('text/html') ||
                this.responseText.trim().startsWith('<!DOCTYPE') ||
                this.responseText.trim().startsWith('<html');

            if (isHtmlResponse) {
                console.error('El servidor devolvió HTML en lugar de JSON');
                showAlert('danger',
                    'El servidor devolvió una respuesta incorrecta. Contacta al administrador.');
                resetUploadButton();
                return;
            }

            try {
                const response = JSON.parse(this.responseText);
                console.log('Respuesta del servidor:', response);

                // Manejar respuesta exitosa (código 200-299)
                if (this.status >= 200 && this.status < 300) {
                    if (response.success) {
                        // Éxito
                        showAlert('success', response.message || 'Video subido correctamente');

                        // Cerrar modal y recargar página
                        closeModalAndReload();
                    } else {
                        // Error con respuesta JSON correcta pero success=false
                        showAlert('danger', response.message || 'Error al subir el video');
                        resetUploadButton();
                    }
                }
                // Manejar error de validación (código 422)
                else if (this.status === 422) {
                    // Mostrar mensaje general
                    showAlert('danger', response.message || 'Error de validación');

                    // Mostrar errores específicos en los campos
                    if (response.errors) {
                        // Limpiar errores previos
                        elements.title.classList.remove('is-invalid');
                        elements.description.classList.remove('is-invalid');

                        // Mostrar errores de título
                        if (response.errors.title) {
                            elements.title.classList.add('is-invalid');
                            document.getElementById('titleFeedback').textContent = response.errors.title[0];
                        }

                        // Mostrar errores de descripción
                        if (response.errors.description) {
                            elements.description.classList.add('is-invalid');
                            document.getElementById('descriptionFeedback').textContent = response.errors
                                .description[0];
                        }

                        // Mostrar errores del archivo de video
                        if (response.errors.video_file) {
                            showAlert('danger', 'Error de video: ' + response.errors.video_file[0]);
                        }
                    }

                    resetUploadButton();
                }
                // Otros errores HTTP
                else {
                    showAlert('danger', response.message || `Error en el servidor: ${this.status}`);
                    resetUploadButton();
                }
            } catch (error) {
                console.error('Error al parsear respuesta JSON:', error);
                console.log('Respuesta recibida:', this.responseText);
                showAlert('danger', 'Error en el formato de respuesta. Por favor, contacta al administrador.');
                resetUploadButton();
            }
        }

        function closeModalAndReload() {
            // Primero cerrar el modal
            const modalInstance = bootstrap.Modal.getInstance(elements.modal);
            modalInstance.hide();

            // Mostrar SweetAlert
            Swal.fire({
                icon: 'success',
                title: '¡Video subido!',
                text: 'Tu video ha sido subido correctamente',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirigir después de que se cierre el SweetAlert
                window.location.reload();
            });
        }

        // Utilidades
        function updateProgressBar(percentage) {
            elements.progressBar.style.width = percentage + '%';
            elements.progressBar.setAttribute('aria-valuenow', percentage);
            elements.progressText.textContent = percentage + '%';
        }

        function resetUploadButton() {
            elements.publishBtn.disabled = false;
            elements.publishBtn.innerHTML = '<i class="fas fa-save me-1"></i> Publicar Video';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showAlert(type, message) {
            clearAlerts();

            // Crear alerta
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${type} alert-dismissible fade show`;
            alertElement.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

            // Insertar en el contenedor
            elements.alertContainer.appendChild(alertElement);

            // Auto-eliminar después de un tiempo
            setTimeout(function() {
                alertElement.remove();
            }, 5000);
        }

        function clearAlerts() {
            elements.alertContainer.innerHTML = '';
        }
    });
</script>
