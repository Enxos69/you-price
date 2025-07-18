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
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            
            if (progress >= 90) {
                clearInterval(interval);
            }
        }, 300);
    }
});
</script>