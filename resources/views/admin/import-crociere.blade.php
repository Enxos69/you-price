@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h2 class="card-title mb-0">
                        <i class="fas fa-ship me-3"></i>Importa Crociere
                    </h2>
                    <p class="mb-0 mt-2 opacity-75">Carica il tuo file CSV per importare le crociere nel sistema</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Alert Messages -->
                    <div id="alert-container">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Upload Form -->
                    <form id="importForm" action="{{ route('cruises.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Upload Zone -->
                        <div class="upload-zone border-2 border-dashed rounded-3 p-5 text-center mb-4" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-dark mb-2">Trascina il file qui o clicca per selezionare</h5>
                            <p class="text-muted mb-0">Formati supportati: CSV, TXT (max 10MB)</p>
                            <input type="file" class="d-none" id="csvFile" name="csv_file" accept=".csv,.txt" required>
                        </div>
                        
                        <!-- File Preview -->
                        <div class="file-preview d-none mb-4" id="filePreview">
                            <div class="bg-light rounded-3 p-3 d-flex align-items-center">
                                <i class="fas fa-file-csv text-success me-3" style="font-size: 2rem;"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" id="fileName"></h6>
                                    <small class="text-muted" id="fileSize"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Stats Preview -->
                        <div class="stats-preview d-none mb-4" id="statsPreview">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-chart-bar me-2"></i>Anteprima File
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-1" id="rowCount">0</h4>
                                            <small class="text-muted">Righe</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-1" id="columnCount">0</h4>
                                            <small class="text-muted">Colonne</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-primary mb-1" id="fileFormat">CSV</h4>
                                        <small class="text-muted">Formato</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Import Options -->
                        <div class="bg-light rounded-3 p-4 mb-4">
                            <h6 class="text-dark mb-3">
                                <i class="fas fa-cog me-2"></i>Opzioni di Importazione
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skip_duplicates" checked>
                                <label class="form-check-label" for="skipDuplicates">
                                    Salta record duplicati
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="validateData" name="validate_data" checked>
                                <label class="form-check-label" for="validateData">
                                    Valida dati durante l'importazione
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="createBackup" name="create_backup">
                                <label class="form-check-label" for="createBackup">
                                    Crea backup prima dell'importazione
                                </label>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="progress-container d-none mb-4" id="progressContainer">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     id="progressBar" style="width: 0%"></div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Importazione in corso...</small>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="loadingSpinner"></span>
                            <span id="btnText">Seleziona un file per continuare</span>
                        </button>
                    </form>
                    
                    <!-- Format Info -->
                    <div class="mt-4">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <h6 class="text-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>Formato File CSV Richiesto
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success me-2"></i>Separatore: punto e virgola (;)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Codifica: UTF-8</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Prima riga: nomi colonne</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success me-2"></i>Colonne richieste: ship, cruise, line</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Date: formato YYYY-MM-DD</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Prezzi: numeri senza simboli</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.upload-zone {
    cursor: pointer;
    transition: all 0.3s ease;
    border-color: #dee2e6 !important;
}

.upload-zone:hover {
    border-color: #667eea !important;
    background-color: rgba(102, 126, 234, 0.05);
}

.upload-zone.dragover {
    border-color: #28a745 !important;
    background-color: rgba(40, 167, 69, 0.1);
    transform: scale(1.02);
}

.upload-zone.has-file {
    border-color: #28a745 !important;
    background-color: rgba(40, 167, 69, 0.05);
}

.file-preview {
    animation: slideIn 0.3s ease;
}

.stats-preview {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.btn-lg {
    padding: 12px 30px;
    border-radius: 10px;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('csvFile');
    const filePreview = document.getElementById('filePreview');
    const statsPreview = document.getElementById('statsPreview');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const importForm = document.getElementById('importForm');
    const alertContainer = document.getElementById('alert-container');

    // Drag and drop functionality
    uploadZone.addEventListener('click', () => fileInput.click());
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    document.getElementById('removeFile').addEventListener('click', () => {
        resetFileSelection();
    });
    
    function handleFileSelect(file) {
        // Validate file type
        if (!file.name.match(/\.(csv|txt)$/i)) {
            showAlert('Formato file non supportato. Seleziona un file CSV o TXT.', 'danger');
            return;
        }
        
        // Validate file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            showAlert('File troppo grande. Dimensione massima: 10MB.', 'danger');
            return;
        }
        
        // Update UI
        uploadZone.classList.add('has-file');
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        filePreview.classList.remove('d-none');
        
        // Enable submit button
        submitBtn.disabled = false;
        btnText.textContent = 'Importa Crociere';
        
        // Parse CSV preview
        parseCSVPreview(file);
    }
    
    function resetFileSelection() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        statsPreview.classList.add('d-none');
        uploadZone.classList.remove('has-file');
        submitBtn.disabled = true;
        btnText.textContent = 'Seleziona un file per continuare';
    }
    
    function parseCSVPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const csvData = e.target.result;
            const lines = csvData.split('\n').filter(line => line.trim());
            const columns = lines[0] ? lines[0].split(';').length : 0;
            
            document.getElementById('rowCount').textContent = Math.max(0, lines.length - 1);
            document.getElementById('columnCount').textContent = columns;
            
            statsPreview.classList.remove('d-none');
            
            // Validate required columns
            if (lines.length > 0) {
                const headers = lines[0].toLowerCase().split(';');
                const requiredColumns = ['ship', 'cruise', 'line'];
                const missingColumns = requiredColumns.filter(col => 
                    !headers.some(header => header.trim().includes(col))
                );
                
                if (missingColumns.length > 0) {
                    showAlert(`Attenzione: potrebbero mancare le colonne: ${missingColumns.join(', ')}`, 'warning');
                } else {
                    showAlert('File validato correttamente!', 'success');
                }
            }
        };
        reader.readAsText(file);
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function showAlert(message, type) {
        const existingAlerts = alertContainer.querySelectorAll('.alert:not(.show)');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Form submission
    importForm.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            showAlert('Seleziona un file prima di continuare.', 'danger');
            return;
        }
        
        // Start loading state
        submitBtn.disabled = true;
        loadingSpinner.classList.remove('d-none');
        btnText.textContent = 'Importazione in corso...';
        progressContainer.classList.remove('d-none');
        
        // Simulate progress for better UX
        simulateProgress();
    });
    
    function simulateProgress() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 90) progress = 90; // Stop at 90% until real completion
            
            progressBar.style.width = progress + '%';
            
            if (progress >= 90) {
                clearInterval(interval);
            }
        }, 300);
    }
});
</script>
@endsection