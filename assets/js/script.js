/**
 * KlikCare Cibinong System JavaScript
 * Sistem Pakar Diagnosis Kerusakan Smartphone
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functions
    initSmoothScrolling();
    initFormValidation();
    initGejalaSelection();
    initAnimations();
    initTooltips();
    initPrintFunctionality();
    initAdminDashboard();
});

/**
 * Smooth Scrolling for anchor links
 */
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation, form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.id === 'diagnosis-form' || form.querySelector('input[name="diagnose"]')) {
                if (!validateDiagnosisForm(form)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
            
            form.classList.add('was-validated');
        });
    });
}

/**
 * Validate Diagnosis Form
 */
function validateDiagnosisForm(form) {
    const namaInput = form.querySelector('input[name="nama"]');
    const gejalaCheckboxes = form.querySelectorAll('input[name="gejala[]"]:checked');
    
    let isValid = true;
    
    // Validate nama
    if (!namaInput || namaInput.value.trim() === '') {
        showAlert('Nama lengkap harus diisi!', 'warning');
        isValid = false;
    }
    
    // Validate gejala selection
    if (gejalaCheckboxes.length === 0) {
        showAlert('Silakan pilih minimal satu gejala!', 'warning');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Gejala Selection Handler
 */
function initGejalaSelection() {
    const gejalaItems = document.querySelectorAll('.gejala-item');
    const gejalaCheckboxes = document.querySelectorAll('input[name="gejala[]"]');
    
    // Add click handler to gejala items
    gejalaItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateGejalaSelection();
                }
            }
        });
        
        // Add hover effects
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(10px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Add change handler to checkboxes
    gejalaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateGejalaSelection);
    });
    
    updateGejalaSelection();
}

/**
 * Update Gejala Selection Display
 */
function updateGejalaSelection() {
    const selectedGejala = document.querySelectorAll('input[name="gejala[]"]:checked');
    const selectedCount = selectedGejala.length;
    
    // Update counter if exists
    const counterElement = document.getElementById('selected-count');
    if (counterElement) {
        counterElement.textContent = selectedCount;
    }
    
    // Update submit button state
    const submitButton = document.querySelector('button[name="diagnose"]');
    if (submitButton) {
        if (selectedCount > 0) {
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-primary');
            submitButton.disabled = false;
        } else {
            submitButton.classList.remove('btn-primary');
            submitButton.classList.add('btn-secondary');
            submitButton.disabled = true;
        }
    }
    
    // Show selected gejala preview
    showSelectedGejalaPreview(selectedGejala);
}

/**
 * Show Selected Gejala Preview
 */
function showSelectedGejalaPreview(selectedGejala) {
    const previewContainer = document.getElementById('selected-gejala-preview');
    if (!previewContainer) return;
    
    if (selectedGejala.length === 0) {
        previewContainer.innerHTML = '<p class="text-muted">Belum ada gejala yang dipilih</p>';
        return;
    }
    
    let previewHTML = '<h6>Gejala yang dipilih:</h6><div class="d-flex flex-wrap gap-2">';
    
    selectedGejala.forEach(checkbox => {
        const label = checkbox.nextElementSibling;
        if (label) {
            const gejalaText = label.textContent.trim();
            previewHTML += `<span class="badge bg-primary">${gejalaText}</span>`;
        }
    });
    
    previewHTML += '</div>';
    previewContainer.innerHTML = previewHTML;
}

/**
 * Initialize Animations
 */
function initAnimations() {
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-up');
            }
        });
    }, observerOptions);
    
    // Observe elements
    const elementsToAnimate = document.querySelectorAll('.card, .feature-box, .gejala-item');
    elementsToAnimate.forEach(el => {
        observer.observe(el);
    });
    
    // Progress bar animations
    animateProgressBars();
}

/**
 * Animate Progress Bars
 */
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const targetWidth = bar.style.width || bar.getAttribute('aria-valuenow') + '%';
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease-out';
            bar.style.width = targetWidth;
        }, 500);
    });
}

/**
 * Initialize Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Print Functionality
 */
function initPrintFunctionality() {
    const printButtons = document.querySelectorAll('[onclick*="print"]');
    
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add print-specific styles
            const printStyle = document.createElement('style');
            printStyle.textContent = `
                @media print {
                    .no-print, .navbar, .btn, footer {
                        display: none !important;
                    }
                    .card {
                        box-shadow: none !important;
                        border: 1px solid #ddd !important;
                    }
                    .progress-bar {
                        background-color: #333 !important;
                    }
                }
            `;
            document.head.appendChild(printStyle);
            
            // Print
            setTimeout(() => {
                window.print();
                document.head.removeChild(printStyle);
            }, 500);
        });
    });
}

/**
 * Admin Dashboard Functions
 */
function initAdminDashboard() {
    if (window.location.pathname.includes('admin/')) {
        initDataTables();
        initModalForms();
        initDeleteConfirmations();
        updateDashboardStats();
    }
}

/**
 * Initialize DataTables
 */
function initDataTables() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        if (table.rows.length > 1) {
            // Add search functionality
            addTableSearch(table);
            
            // Add sorting
            addTableSorting(table);
        }
    });
}

/**
 * Add Table Search
 */
function addTableSearch(table) {
    const searchContainer = document.createElement('div');
    searchContainer.className = 'mb-3';
    searchContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Cari data..." id="table-search-${table.id}">
            </div>
        </div>
    `;
    
    table.parentNode.insertBefore(searchContainer, table);
    
    const searchInput = searchContainer.querySelector('input');
    searchInput.addEventListener('input', function() {
        filterTable(table, this.value);
    });
}

/**
 * Filter Table
 */
function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

/**
 * Add Table Sorting
 */
function addTableSorting(table) {
    const headers = table.querySelectorAll('thead th');
    
    headers.forEach((header, index) => {
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
        
        header.addEventListener('click', function() {
            sortTable(table, index);
        });
    });
}

/**
 * Sort Table
 */
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const sortedRows = rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers
        const aNum = parseFloat(aValue);
        const bNum = parseFloat(bValue);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        
        return aValue.localeCompare(bValue);
    });
    
    // Clear tbody and append sorted rows
    tbody.innerHTML = '';
    sortedRows.forEach(row => tbody.appendChild(row));
}

/**
 * Initialize Modal Forms
 */
function initModalForms() {
    const modalForms = document.querySelectorAll('.modal form');
    
    modalForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="loading"></span> Menyimpan...';
            }
        });
    });
}

/**
 * Initialize Delete Confirmations
 */
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-action="delete"]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.getAttribute('data-name') || 'item ini';
            
            if (confirm(`Apakah Anda yakin ingin menghapus ${itemName}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = this.href || this.getAttribute('data-url');
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete';
                input.value = '1';
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
}

/**
 * Update Dashboard Stats
 */
function updateDashboardStats() {
    const statCards = document.querySelectorAll('.card h2');
    
    statCards.forEach(card => {
        const finalValue = parseInt(card.textContent);
        card.textContent = '0';
        
        animateCounter(card, 0, finalValue, 1000);
    });
}

/**
 * Animate Counter
 */
function animateCounter(element, start, end, duration) {
    const startTime = Date.now();
    const range = end - start;
    
    function updateCounter() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(start + (range * progress));
        
        element.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Show Alert
 */
function showAlert(message, type = 'info') {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const alertContainer = document.querySelector('.alert-container') || document.querySelector('.container');
    if (alertContainer) {
        alertContainer.insertAdjacentHTML('afterbegin', alertHTML);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}

/**
 * Loading State Management
 */
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<span class="loading"></span> Loading...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

/**
 * AJAX Helper Functions
 */
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        
        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                resolve(xhr.responseText);
            } else {
                reject(new Error(`Request failed: ${xhr.status}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        xhr.send(data);
    });
}

/**
 * Form Data Helper
 */
function serializeForm(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }
    
    return params.toString();
}

/**
 * Local Storage Helper
 */
function saveToStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (e) {
        console.warn('Could not save to localStorage:', e);
    }
}

function getFromStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.warn('Could not retrieve from localStorage:', e);
        return null;
    }
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for use in other scripts
window.KlikCare = {
    showAlert,
    showLoading,
    hideLoading,
    makeRequest,
    serializeForm,
    saveToStorage,
    getFromStorage,
    debounce,
    throttle
};