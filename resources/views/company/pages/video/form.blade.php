{{-- resources/views/company/pages/video/form.blade.php --}}

<!-- Modal de carga de video -->
<div class="modal fade" id="uploadVideoModal" tabindex="-1" role="dialog" aria-labelledby="uploadVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadVideoModalLabel">Subir Nuevo Video</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('company.video.store') }}" method="POST" enctype="multipart/form-data" id="videoUploadForm">
            @csrf

            <div class="row">
              <div class="col-md-6">
                <!-- Información del Video -->
                <div class="form-group mb-3">
                  <label for="title" class="form-control-label">Título del Video <span class="text-danger">*</span></label>
                  <input class="form-control" type="text" id="title" name="title" required>
                  <div class="invalid-feedback" id="titleFeedback"></div>
                </div>

                <div class="form-group mb-3">
                  <label for="description" class="form-control-label">Descripción <span class="text-danger">*</span></label>
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

                  <!-- La vista previa se insertará aquí por JavaScript -->

                  <input type="file" class="d-none" id="video_file" name="video_file" accept="video/*" required>
                </div>

                <!-- Área de progreso (inicialmente oculta) -->
                <div class="progress-area mb-3" id="progressArea" style="display: none;">
                  <label class="form-control-label small mb-1">Subiendo video... <span id="progressPercentage">0%</span></label>
                  <div class="progress" style="height: 15px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="uploadProgress"
                         role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
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
      // Referencias a elementos del DOM
      const videoFileInput = document.getElementById('video_file');
      const browseButton = document.getElementById('browseButton');
      const uploadPlaceholder = document.querySelector('.upload-placeholder');
      const publishBtn = document.getElementById('publishVideoBtn');

      // Área donde mostraremos la vista previa
      const previewContainer = document.createElement('div');
      previewContainer.className = 'video-preview-container';
      previewContainer.style.display = 'none';
      uploadPlaceholder.parentNode.appendChild(previewContainer);

      // Crear elemento de video para la vista previa
      const videoPreview = document.createElement('video');
      videoPreview.className = 'w-100 rounded';
      videoPreview.controls = true;
      previewContainer.appendChild(videoPreview);

      // Crear área de información y botón para quitar
      const previewInfo = document.createElement('div');
      previewInfo.className = 'd-flex justify-content-between align-items-center mt-2';
      previewInfo.innerHTML = `
          <span class="badge bg-primary" id="videoInfo"></span>
          <button type="button" class="btn btn-sm btn-danger" id="removeVideoBtn">
              <i class="fas fa-trash me-1"></i> Quitar
          </button>
      `;
      previewContainer.appendChild(previewInfo);

      // Manejar clic en el botón de examinar
      browseButton.addEventListener('click', function() {
          videoFileInput.click();
      });

      // Permitir arrastrar y soltar en el área de carga
      uploadPlaceholder.addEventListener('dragover', function(e) {
          e.preventDefault();
          uploadPlaceholder.classList.add('border-primary');
      });

      uploadPlaceholder.addEventListener('dragleave', function() {
          uploadPlaceholder.classList.remove('border-primary');
      });

      uploadPlaceholder.addEventListener('drop', function(e) {
          e.preventDefault();
          uploadPlaceholder.classList.remove('border-primary');

          if (e.dataTransfer.files.length) {
              handleFileSelection(e.dataTransfer.files[0]);
          }
      });

      // Escuchar cambios en el input de archivo
      videoFileInput.addEventListener('change', function() {
          if (this.files && this.files[0]) {
              handleFileSelection(this.files[0]);
          }
      });

      // Manejar la selección de archivo
      function handleFileSelection(file) {
          // Verificar tipo de archivo
          const validTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
          if (!validTypes.includes(file.type)) {
              alert('Formato de archivo no válido. Sólo se permiten archivos MP4, MOV o AVI.');
              resetVideoSelection();
              return;
          }

          // Verificar tamaño del archivo (100MB max)
          if (file.size > 100 * 1024 * 1024) {
              alert('El archivo es demasiado grande. El tamaño máximo permitido es 100MB.');
              resetVideoSelection();
              return;
          }

          // Mostrar vista previa
          const videoURL = URL.createObjectURL(file);
          videoPreview.src = videoURL;

          // Mostrar información del archivo
          const videoInfo = document.getElementById('videoInfo');
          const fileSize = formatFileSize(file.size);
          const fileType = file.type.split('/')[1].toUpperCase();
          videoInfo.textContent = `${fileType} · ${fileSize}`;

          // Mostrar área de vista previa y ocultar placeholder
          uploadPlaceholder.style.display = 'none';
          previewContainer.style.display = 'block';

          // Activar botón de publicar
          publishBtn.disabled = false;

          // Configurar botón para quitar el video
          document.getElementById('removeVideoBtn').addEventListener('click', resetVideoSelection);
      }

      // Resetear la selección de video
      function resetVideoSelection() {
          videoFileInput.value = '';
          videoPreview.src = '';
          previewContainer.style.display = 'none';
          uploadPlaceholder.style.display = 'block';

          // Ocultar barra de progreso si está visible
          const progressArea = document.getElementById('progressArea');
          if (progressArea) {
              progressArea.style.display = 'none';
          }

          // Desactivar botón de publicar
          publishBtn.disabled = true;
      }

      // Función para formatear el tamaño del archivo
      function formatFileSize(bytes) {
          if (bytes === 0) return '0 Bytes';

          const k = 1024;
          const sizes = ['Bytes', 'KB', 'MB', 'GB'];
          const i = Math.floor(Math.log(bytes) / Math.log(k));

          return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      }

      // Manejar la subida del video con AJAX
      function handleVideoUpload() {
          // Verificar que el formulario sea válido
          const titleInput = document.getElementById('title');
          const descriptionInput = document.getElementById('description');

          if (!titleInput.value.trim()) {
              titleInput.classList.add('is-invalid');
              document.getElementById('titleFeedback').textContent = 'El título es obligatorio';
              return false;
          } else {
              titleInput.classList.remove('is-invalid');
          }

          if (!descriptionInput.value.trim()) {
              descriptionInput.classList.add('is-invalid');
              document.getElementById('descriptionFeedback').textContent = 'La descripción es obligatoria';
              return false;
          } else {
              descriptionInput.classList.remove('is-invalid');
          }

          if (!videoFileInput.files[0]) {
              alert('Por favor selecciona un video.');
              return false;
          }

          // Desactivar el botón de publicar y mostrar indicador de carga
          publishBtn.disabled = true;
          publishBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';

          // Mostrar la barra de progreso
          const progressArea = document.getElementById('progressArea');
          progressArea.style.display = 'block';

          // Preparar los datos del formulario
          const form = document.getElementById('videoUploadForm');
          const formData = new FormData(form);

          // Crear y configurar la solicitud AJAX
          const xhr = new XMLHttpRequest();

          // Configurar los eventos de la solicitud
          xhr.upload.addEventListener('progress', function(e) {
              if (e.lengthComputable) {
                  const percentComplete = Math.round((e.loaded / e.total) * 100);
                  updateProgressBar(percentComplete);
              }
          });

          xhr.addEventListener('load', function() {
              if (xhr.status >= 200 && xhr.status < 300) {
                  try {
                      const response = JSON.parse(xhr.responseText);

                      if (response.success) {
                          // Mostrar mensaje de éxito
                          showAlert('success', response.message || 'Video subido correctamente');

                          // Cerrar el modal
                          const modal = document.getElementById('uploadVideoModal');
                          const modalInstance = bootstrap.Modal.getInstance(modal);
                          modalInstance.hide();

                          // Recargar la página para mostrar el nuevo video
                          setTimeout(function() {
                              window.location.reload();
                          }, 1000);
                      } else {
                          showAlert('danger', response.message || 'Error al subir el video');
                          resetUploadButton();
                      }
                  } catch (error) {
                      // Podría ser HTML de un error
                      showAlert('danger', 'Error en el servidor. Por favor, inténtalo de nuevo.');
                      resetUploadButton();
                  }
              } else {
                  showAlert('danger', 'Error en el servidor: ' + xhr.status);
                  resetUploadButton();
              }
          });

          xhr.addEventListener('error', function() {
              showAlert('danger', 'Error de conexión. Por favor, verifica tu conexión a internet.');
              resetUploadButton();
          });

          xhr.addEventListener('abort', function() {
              showAlert('warning', 'Carga cancelada.');
              resetUploadButton();
          });

          // Abrir y enviar la solicitud
          xhr.open('POST', form.action, true);
          xhr.send(formData);

          return false; // Evitar el envío normal del formulario
      }

      // Actualizar la barra de progreso
      function updateProgressBar(percentage) {
          const progressBar = document.getElementById('uploadProgress');
          const progressText = document.getElementById('progressPercentage');

          if (progressBar && progressText) {
              progressBar.style.width = percentage + '%';
              progressBar.setAttribute('aria-valuenow', percentage);
              progressText.textContent = percentage + '%';
          }
      }

      // Restablecer el botón de publicar
      function resetUploadButton() {
          if (publishBtn) {
              publishBtn.disabled = false;
              publishBtn.innerHTML = '<i class="fas fa-save me-1"></i> Publicar Video';
          }
      }

      // Mostrar mensajes de alerta
      function showAlert(type, message) {
          // Crear elemento de alerta
          const alertElement = document.createElement('div');
          alertElement.className = `alert alert-${type} alert-dismissible fade show`;
          alertElement.innerHTML = `
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;

          // Insertar en el DOM
          const modalBody = document.querySelector('.modal-body');
          modalBody.insertBefore(alertElement, modalBody.firstChild);

          // Eliminar después de unos segundos
          setTimeout(function() {
              alertElement.remove();
          }, 5000);
      }

      // Vincular al botón de publicar
      publishBtn.addEventListener('click', handleVideoUpload);

      // Resetear cuando se cierra el modal
      const modal = document.getElementById('uploadVideoModal');
      modal.addEventListener('hidden.bs.modal', function() {
          resetVideoSelection();
          document.getElementById('title').value = '';
          document.getElementById('description').value = '';
          document.getElementById('title').classList.remove('is-invalid');
          document.getElementById('description').classList.remove('is-invalid');
      });
  });
  </script>
