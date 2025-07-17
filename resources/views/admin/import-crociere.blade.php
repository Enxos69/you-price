<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importa Crociere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #00d4aa;
            --warning-color: #ffc107;
            --danger-color: #ff6b6b;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .import-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .import-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            border: none;
        }

        .card-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .card-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .card-body {
            padding: 3rem;
        }

        .upload-zone {
            border: 3px dashed #e9ecef;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: translateY(-2px);
        }

        .upload-zone.dragover {
            border-color: var(--success-color);
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.1), rgba(0, 212, 170, 0.05));
            transform: scale(1.02);
        }

        .upload-zone.has-file {
            border-color: var(--success-color);
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.1), rgba(0, 212, 170, 0.05));
        }

        .upload-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .upload-zone:hover .upload-icon {
            transform: scale(1.1);
            color: var(--secondary-color);
        }

        .upload-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .file-preview {
            display: none;
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.1), rgba(0, 212, 170, 0.05));
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
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

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-icon {
            font-size: 2rem;
            color: var(--success-color);
        }

        .file-details h5 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        .file-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .remove-file {
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-file:hover {
            background: #ff5252;
            transform: scale(1.1);
        }

        .import-options {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .import-options h5 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .custom-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .loading-spinner {
            display: none;
            margin-right: 0.5rem;
        }

        .progress-container {
            display: none;
            margin-top: 1rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transition: width 0.3s ease;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.1), rgba(0, 212, 170, 0.05));
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(255, 107, 107, 0.05));
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .format-info {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .format-info h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .format-info ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .format-info li {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .stats-preview {
            display: none;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            animation: slideIn 0.3s ease;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 2rem;
            }
            
            .upload-zone {
                padding: 2rem;
            }
            
            .card-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="import-card">
                        <div class="card-header">
                            <h1><i class="fas fa-ship me-3"></i>Importa Crociere</h1>
                            <p>Carica il tuo file CSV per importare le crociere nel sistema</p>
                        </div>
                        
                        <div class="card-body">
                            <!-- Alert Messages -->
                            <div id="alert-container"></div>
                            
                            <!-- Upload Form -->
                            <form id="importForm" action="/admin/import-crociere" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="mock_csrf_token">
                                
                                <!-- Upload Zone -->
                                <div class="upload-zone" id="uploadZone">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <div class="upload-text">Trascina il file qui o clicca per selezionare</div>
                                    <div class="upload-hint">Formati supportati: CSV, TXT (max 10MB)</div>
                                    <input type="file" class="file-input" id="csvFile" name="csv_file" accept=".csv,.txt" required>
                                </div>
                                
                                <!-- File Preview -->
                                <div class="file-preview" id="filePreview">
                                    <div class="file-info">
                                        <i class="fas fa-file-csv file-icon"></i>
                                        <div class="file-details">
                                            <h5 id="fileName"></h5>
                                            <p id="fileSize"></p>
                                        </div>
                                        <button type="button" class="remove-file" id="removeFile">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Stats Preview -->
                                <div class="stats-preview" id="statsPreview">
                                    <h6><i class="fas fa-chart-bar me-2"></i>Anteprima File</h6>
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <span class="stat-number" id="rowCount">0</span>
                                            <div class="stat-label">Righe</div>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number" id="columnCount">0</span>
                                            <div class="stat-label">Colonne</div>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number" id="fileFormat">CSV</span>
                                            <div class="stat-label">Formato</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Import Options -->
                                <div class="import-options">
                                    <h5><i class="fas fa-cog me-2"></i>Opzioni di Importazione</h5>
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="skipDuplicates" name="skip_duplicates" checked>
                                        <label for="skipDuplicates">Salta record duplicati</label>
                                    </div>
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="validateData" name="validate_data" checked>
                                        <label for="validateData">Valida dati durante l'importazione</label>
                                    </div>
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="createBackup" name="create_backup">
                                        <label for="createBackup">Crea backup prima dell'importazione</label>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="progress-container" id="progressContainer">
                                    <div class="progress">
                                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Importazione in corso...</small>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                                    <i class="fas fa-spinner fa-spin loading-spinner"></i>
                                    <span class="btn-text">Seleziona un file per continuare</span>
                                </button>
                            </form>
                            
                            <!-- Format Info -->
                            <div class="format-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Formato File CSV</h6>
                                <ul>
                                    <li>Separatore: punto e virgola (;)</li>
                                    <li>Codifica: UTF-8</li>
                                    <li>Prima riga deve contenere i nomi delle colonne</li>
                                    <li>Colonne richieste: ship, cruise, line, partenza, arrivo</li>
                                    <li>Formato date: YYYY-MM-DD</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadZone = document.getElementById('uploadZone');
            const fileInput = document.getElementById('csvFile');
            const filePreview = document.getElementById('filePreview');
            const statsPreview = document.getElementById('statsPreview');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.querySelector('.btn-text');
            const loadingSpinner = document.querySelector('.loading-spinner');
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
                fileInput.value = '';
                filePreview.style.display = 'none';
                statsPreview.style.display = 'none';
                uploadZone.classList.remove('has-file');
                submitBtn.disabled = true;
                btnText.textContent = 'Seleziona un file per continuare';
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
                filePreview.style.display = 'block';
                
                // Enable submit button
                submitBtn.disabled = false;
                btnText.textContent = 'Importa Crociere';
                
                // Parse CSV preview
                parseCSVPreview(file);
            }
            
            function parseCSVPreview(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const csvData = e.target.result;
                    const lines = csvData.split('\n').filter(line => line.trim());
                    const columns = lines[0] ? lines[0].split(';').length : 0;
                    
                    document.getElementById('rowCount').textContent = Math.max(0, lines.length - 1);
                    document.getElementById('columnCount').textContent = columns;
                    
                    statsPreview.style.display = 'block';
                    
                    // Validate required columns
                    if (lines.length > 0) {
                        const headers = lines[0].toLowerCase().split(';');
                        const requiredColumns = ['ship', 'cruise', 'line', 'partenza', 'arrivo'];
                        const missingColumns = requiredColumns.filter(col => 
                            !headers.some(header => header.trim().includes(col))
                        );
                        
                        if (missingColumns.length > 0) {
                            showAlert(`Colonne mancanti: ${missingColumns.join(', ')}`, 'warning');
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
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
                    ${message}
                `;
                
                alertContainer.innerHTML = '';
                alertContainer.appendChild(alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
            
            // Form submission
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!fileInput.files.length) {
                    showAlert('Seleziona un file prima di continuare.', 'danger');
                    return;
                }
                
                // Start loading state
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'inline-block';
                btnText.textContent = 'Importazione in corso...';
                progressContainer.style.display = 'block';
                
                // Simulate progress
                simulateProgress();
                
                // In a real application, you would submit the form here
                // For demo purposes, we'll simulate the process
                setTimeout(() => {
                    showAlert('Importazione completata con successo!', 'success');
                    resetForm();
                }, 3000);
            });
            
            function simulateProgress() {
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 100) progress = 100;
                    
                    progressBar.style.width = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                    }
                }, 200);
            }
            
            function resetForm() {
                setTimeout(() => {
                    submitBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    btnText.textContent = 'Seleziona un file per continuare';
                    progressContainer.style.display = 'none';
                    progressBar.style.width = '0%';
                    
                    // Reset file input
                    fileInput.value = '';
                    filePreview.style.display = 'none';
                    statsPreview.style.display = 'none';
                    uploadZone.classList.remove('has-file');
                    submitBtn.disabled = true;
                }, 2000);
            }
        });
    </script>
</body>
</html>