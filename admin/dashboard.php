<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$db = new Database();

// Ambil statistik
$stats = [];
$stats['total_gejala'] = $db->query("SELECT COUNT(*) as count FROM gejala")->fetch_assoc()['count'];
$stats['total_kerusakan'] = $db->query("SELECT COUNT(*) as count FROM kerusakan")->fetch_assoc()['count'];
$stats['total_aturan'] = $db->query("SELECT COUNT(*) as count FROM aturan")->fetch_assoc()['count'];
$stats['total_diagnosis'] = $db->query("SELECT COUNT(*) as count FROM riwayat_diagnosis")->fetch_assoc()['count'];

// Ambil riwayat diagnosis terbaru
$riwayat_terbaru = [];
$result = $db->query("SELECT * FROM riwayat_diagnosis ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $riwayat_terbaru[] = $row;
    }
}

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KlikCare Cibinong</title>
    <link rel="stylesheet" href="../assets/css/adminstyle.css">
</head>
<body>
    <!-- Header -->
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
            <!-- Sidebar -->
            <div class="sidebar">
                <h3>Menu Admin</h3>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="gejala.php">Data Gejala</a></li>
                    <li><a href="kerusakan.php">Data Kerusakan</a></li>
                    <li><a href="aturan.php">Aturan Diagnosis</a></li>
                    <li><a href="riwayat.php">Riwayat Diagnosis</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <a href="../index.php" class="website-btn">Lihat Website</a>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-number"><?php echo $stats['total_gejala']; ?></div>
                        <div class="stat-title">Total Gejala</div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-number"><?php echo $stats['total_kerusakan']; ?></div>
                        <div class="stat-title">Total Kerusakan</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-number"><?php echo $stats['total_aturan']; ?></div>
                        <div class="stat-title">Total Aturan</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-number"><?php echo $stats['total_diagnosis']; ?></div>
                        <div class="stat-title">Total Diagnosis</div>
                    </div>
                </div>

                <!-- Recent Diagnosis Table -->
                <div class="table-section">
                    <div class="table-header">
                        <h3 class="table-title">Riwayat Diagnosis Terbaru</h3>
                    </div>
                    
                    <?php if (empty($riwayat_terbaru)): ?>
                        <div class="no-data">
                            <p>Belum ada riwayat diagnosis</p>
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
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($riwayat_terbaru as $riwayat): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($riwayat['nama_user']); ?></td>
                                            <td><?php echo htmlspecialchars($riwayat['email'] ?? 'Tidak ada'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                            <td>
                                                <button class="btn-view" onclick="viewDiagnosis(<?php echo $riwayat['id']; ?>)">
                                                    Lihat Detail
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
                <div class="loading">Memuat data diagnosis</div>
            </div>
            <div class="modal-footer">
                <button class="btn-close" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function viewDiagnosis(id) {
            // Show modal
            document.getElementById('diagnosisModal').style.display = 'block';
            
            // Reset modal body to loading state
            document.getElementById('diagnosisModalBody').innerHTML = '<div class="loading">Memuat data diagnosis</div>';
            
            // Fetch diagnosis detail
            fetch(`dashboard.php?action=get_diagnosis_detail&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDiagnosisDetail(data.data);
                    } else {
                        document.getElementById('diagnosisModalBody').innerHTML = `
                            <div class="no-data">
                                <p>❌ ${data.message || 'Gagal memuat data diagnosis'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('diagnosisModalBody').innerHTML = `
                        <div class="no-data">
                            <p>❌ Terjadi kesalahan saat memuat data</p>
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
                <!-- Diagnose User Info -->
                <div class="info-card">
                    <h4>Informasi Pendiagnosa</h4>
                    <div class="info-row">
                        <div class="info-col">
                            <div class="info-label">Nama:</div>
                            <div class="info-value">${data.nama_user}</div>
                        </div>
                        <div class="info-col">
                            <div class="info-label">Email:</div>
                            <div class="info-value">${data.email || 'Tidak ada'}</div>
                        </div>
                        <div class="info-col">
                            <div class="info-label">Tanggal Diagnosis:</div>
                            <div class="info-value">${formatDate(data.created_at)}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Symptoms -->
                <div class="info-card">
                    <h4>✅ Gejala yang Dipilih</h4>
                    <div class="gejala-list">
            `;
            
            data.gejala_names.forEach((gejala, index) => {
                html += `<div class="gejala-item">${gejala}</div>`;
            });
            
            html += `
                    </div>
                </div>
                
                <!-- Diagnosis Results -->
                <div class="info-card">
                    <h4>Hasil Diagnosis</h4>
            `;
            
            data.hasil_diagnosis.forEach((diagnosis, index) => {
                html += `
                    <div class="diagnosis-card">
                        <div class="diagnosis-header">
                            <h5 class="diagnosis-title">${diagnosis.nama_kerusakan}</h5>
                            <div class="percentage-badge">${diagnosis.persentase}% Match</div>
                        </div>
                        <div class="diagnosis-body">
                            <div class="diagnosis-info">
                                <div class="diagnosis-right">
                                    <div class="solution-box">
                                        <div class="solution-title">Solusi Rekomendasi:</div>
                                        <div class="solution-text">${diagnosis.solusi.replace(/\n/g, '<br>')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            document.getElementById('diagnosisModalBody').innerHTML = html;
        }
        
        function closeModal() {
            document.getElementById('diagnosisModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('diagnosisModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>