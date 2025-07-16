<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$db = new Database();

$message = '';
$message_type = '';

// Handle AJAX request untuk detail diagnosis
if (isset($_GET['action']) && $_GET['action'] == 'get_diagnosis_detail' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $db->query("SELECT * FROM riwayat_diagnosis WHERE id = $id");
    
    if ($result && $result->num_rows > 0) {
        $diagnosis = $result->fetch_assoc();
        
        // Decode JSON data
        $gejala_terpilih = json_decode($diagnosis['gejala_terpilih'], true) ?: [];
        $hasil_diagnosis = json_decode($diagnosis['hasil_diagnosis'], true) ?: [];
        
        // Ambil nama gejala
        $gejala_names = [];
        foreach ($gejala_terpilih as $kode_gejala) {
            $gejala_result = $db->query("SELECT nama_gejala FROM gejala WHERE kode_gejala = '$kode_gejala'");
            if ($gejala_result && $gejala_result->num_rows > 0) {
                $gejala_row = $gejala_result->fetch_assoc();
                $gejala_names[] = $gejala_row['nama_gejala'];
            }
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $diagnosis['id'],
                'nama_user' => $diagnosis['nama_user'],
                'email' => $diagnosis['email'],
                'created_at' => $diagnosis['created_at'],
                'gejala_names' => $gejala_names,
                'hasil_diagnosis' => $hasil_diagnosis
            ]
        ]);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Diagnosis tidak ditemukan']);
        exit;
    }
}

// Handle delete diagnosis
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $result = $db->query("DELETE FROM riwayat_diagnosis WHERE id = $id");
    
    if ($result) {
        $message = 'Riwayat diagnosis berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus riwayat diagnosis';
        $message_type = 'error';
    }
}

// Handle delete all
if (isset($_POST['action']) && $_POST['action'] == 'delete_all') {
    $result = $db->query("DELETE FROM riwayat_diagnosis");
    
    if ($result) {
        $message = 'Semua riwayat diagnosis berhasil dihapus';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus riwayat diagnosis';
        $message_type = 'error';
    }
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_escaped = addslashes($search);
    $search_condition = "WHERE nama_user LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%'";
}

// Get total records for pagination
$count_result = $db->query("SELECT COUNT(*) as total FROM riwayat_diagnosis $search_condition");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get diagnosis records
$riwayat_diagnosis = [];
$result = $db->query("SELECT * FROM riwayat_diagnosis $search_condition ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $riwayat_diagnosis[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosis - KlikCare Admin</title>
    <link rel="stylesheet" href="../assets/css/adminstyle.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">KlikCare Admin</div>
            <div class="user-info">
                <span class="user-name">Selamat datang, <?php echo $_SESSION['admin_nama']; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="main-wrapper">
            <div class="sidebar">
                <h3>Menu Admin</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="gejala.php">Data Gejala</a></li>
                    <li><a href="kerusakan.php">Data Kerusakan</a></li>
                    <li><a href="aturan.php">Aturan Diagnosis</a></li>
                    <li><a href="riwayat.php" class="active">Riwayat Diagnosis</a></li>
                </ul>
            </div>

            <div class="main-content">
                <div class="page-header">
                    <h1 class="page-title">Riwayat Diagnosis</h1>
                    <button class="btn-danger" onclick="confirmDeleteAll()">Hapus Semua Riwayat</button>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Search Section -->
                <div class="search-section">
                    <form class="search-form" method="GET">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Cari berdasarkan nama atau email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-primary">Cari</button>
                        <?php if (!empty($search)): ?>
                            <a href="riwayat.php" class="btn-secondary">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-section">
                    <div class="table-header">
                        <h3 class="table-title">
                            Daftar Riwayat Diagnosis 
                            <?php if (!empty($search)): ?>
                                <small>(Hasil pencarian: "<?php echo htmlspecialchars($search); ?>")</small>
                            <?php endif; ?>
                        </h3>
                        <p style="color: #7f8c8d; font-size: 14px;">
                            Total: <?php echo $total_records; ?> riwayat | 
                            Halaman <?php echo $page; ?> dari <?php echo max(1, $total_pages); ?>
                        </p>
                    </div>

                    <?php if (empty($riwayat_diagnosis)): ?>
                        <div class="no-data">
                            <p>
                                <?php echo !empty($search) ? 'Tidak ada hasil pencarian' : 'Belum ada riwayat diagnosis'; ?>
                            </p>
                            <?php if (!empty($search)): ?>
                                <a href="riwayat.php" class="btn-primary">Lihat Semua Riwayat</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama User</th>
                                        <th>Email</th>
                                        <th>Tanggal</th>
                                        <th>Hasil Diagnosis</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat_diagnosis as $index => $riwayat): ?>
                                        <?php 
                                        $hasil_diagnosis = json_decode($riwayat['hasil_diagnosis'], true) ?: [];
                                        $diagnosis_utama = !empty($hasil_diagnosis) ? $hasil_diagnosis[0] : null;
                                        ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td><strong><?php echo htmlspecialchars($riwayat['nama_user']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($riwayat['email'] ?? 'Tidak ada'); ?></td>
                                            <td>
                                                <span class="date-badge">
                                                    <?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($diagnosis_utama): ?>
                                                    <span class="status-badge">
                                                        <?php echo htmlspecialchars($diagnosis_utama['nama_kerusakan']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #7f8c8d;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn-view" onclick="viewDiagnosis(<?php echo $riwayat['id']; ?>)">
                                                    Lihat Detail
                                                </button>
                                                <button class="btn-delete" onclick="confirmDelete(<?php echo $riwayat['id']; ?>, '<?php echo htmlspecialchars($riwayat['nama_user']); ?>')">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <!-- Previous Page -->
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        « Sebelumnya
                                    </a>
                                <?php else: ?>
                                    <span class="disabled">« Sebelumnya</span>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <?php if ($i == $page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        Selanjutnya »
                                    </a>
                                <?php else: ?>
                                    <span class="disabled">Selanjutnya »</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Diagnosis -->
    <div id="diagnosisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Riwayat Diagnosis</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="diagnosisModalBody">
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data diagnosis...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus riwayat diagnosis dari <strong id="delete_name"></strong>?</p>
                    <p style="color: #e74c3c; font-size: 12px;">Aksi ini tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewDiagnosis(id) {
            document.getElementById('diagnosisModal').style.display = 'block';
            document.getElementById('diagnosisModalBody').innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data diagnosis...</p></div>';
            
            // Fetch diagnosis detail
            fetch(`riwayat.php?action=get_diagnosis_detail&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDiagnosisDetail(data.data);
                    } else {
                        document.getElementById('diagnosisModalBody').innerHTML = `
                            <div style="padding: 20px; color: #e74c3c;">
                                <p>Error: ${data.message || 'Gagal memuat data diagnosis'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('diagnosisModalBody').innerHTML = `
                        <div style="padding: 20px; color: #e74c3c;">
                            <p>Terjadi kesalahan saat memuat data</p>
                        </div>
                    `;
                });
        }
        
        function displayDiagnosisDetail(data) {
            const formatDate = (dateString) => {
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };
            
            let html = `
                <div class="detail-section">
                    <h4>Informasi Pasien</h4>
                    <div class="detail-item"><strong>Nama:</strong> ${data.nama_user}</div>
                    <div class="detail-item"><strong>Email:</strong> ${data.email || 'Tidak ada'}</div>
                    <div class="detail-item"><strong>Tanggal Diagnosis:</strong> ${formatDate(data.created_at)}</div>
                </div>
                
                <div class="detail-section">
                    <h4>Gejala yang Dipilih</h4>
                    <ul class="gejala-list">
            `;
            
            if (data.gejala_names && data.gejala_names.length > 0) {
                data.gejala_names.forEach(gejala => {
                    html += `<li>${gejala}</li>`;
                });
            } else {
                html += `<li style="color: #7f8c8d;">Tidak ada gejala yang dipilih</li>`;
            }
            
            html += `
                    </ul>
                </div>
                
                <div class="detail-section">
                    <h4>Hasil Diagnosis</h4>
            `;
            
            if (data.hasil_diagnosis && data.hasil_diagnosis.length > 0) {
                data.hasil_diagnosis.forEach((diagnosis, index) => {
                    html += `
                        <div class="diagnosis-item">
                            <h5>${diagnosis.nama_kerusakan}</h5>
                            <p><strong>Solusi:</strong> ${diagnosis.solusi || 'Tidak ada solusi'}</p>
                        </div>
                    `;
                });
            } else {
                html += `<p style="color: #7f8c8d;">Tidak ada hasil diagnosis</p>`;
            }
            
            html += `
                </div>
            `;
            
            document.getElementById('diagnosisModalBody').innerHTML = html;
        }
        
        function closeModal() {
            document.getElementById('diagnosisModal').style.display = 'none';
        }
        
        function confirmDelete(id, nama) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = nama;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        function confirmDeleteAll() {
            if (confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat diagnosis?\n\nAksi ini tidak dapat dibatalkan!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_all';
                
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const diagnosisModal = document.getElementById('diagnosisModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == diagnosisModal) {
                diagnosisModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
        
        // Auto-hide message after 5 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 300);
            }
        }, 5000);
        
        // Add fade-out animation to messages
        const messageElements = document.querySelectorAll('.message');
        messageElements.forEach(msg => {
            msg.style.transition = 'opacity 0.3s ease';
        });
        
        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // ESC key to close modals
            if (event.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
            
            // Ctrl+F to focus search
            if (event.ctrlKey && event.key === 'f') {
                event.preventDefault();
                const searchInput = document.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
        
        // Add loading state to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Memproses...';
                }
            });
        });
        
        // Add search functionality enhancement
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit search after 1 second of no typing
                    if (this.value.length >= 3 || this.value.length === 0) {
                        this.form.submit();
                    }
                }, 1000);
            });
        }
        
        // Add confirmation for bulk delete
        document.addEventListener('DOMContentLoaded', function() {
            const deleteAllBtn = document.querySelector('.btn-danger');
            if (deleteAllBtn && deleteAllBtn.textContent.includes('Hapus Semua')) {
                deleteAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    confirmDeleteAll();
                });
            }
        });
        
        // Add table row highlighting
        document.querySelectorAll('table tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
        
        // Add smooth scrolling for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>