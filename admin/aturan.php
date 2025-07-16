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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            
            case 'add':
                $kode_kerusakan = trim($_POST['kode_kerusakan']);
                $kode_gejala = trim($_POST['kode_gejala']);
                
                if (!empty($kode_kerusakan) && !empty($kode_gejala)) {
                    $stmt_check = $db->prepare("SELECT id FROM aturan WHERE kode_kerusakan = ? AND kode_gejala = ?");
                    $stmt_check->bind_param("ss", $kode_kerusakan, $kode_gejala);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("INSERT INTO aturan (kode_kerusakan, kode_gejala) VALUES (?, ?)");
                        $stmt->bind_param("ss", $kode_kerusakan, $kode_gejala);
                        
                        if ($stmt->execute()) {
                            $message = "Aturan berhasil ditambahkan";
                            $message_type = "success";
                        } else {
                            $message = "Gagal menambahkan aturan";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Aturan sudah ada";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                } else {
                    $message = "Semua field harus diisi";
                    $message_type = "warning";
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($id > 0) {
                    $stmt = $db->prepare("DELETE FROM aturan WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $message = "Aturan berhasil dihapus";
                        $message_type = "success";
                    } else {
                        $message = "Gagal menghapus aturan";
                        $message_type = "error";
                    }
                    $stmt->close();
                }
                break;
        }
    }
}

// Get aturan data with joins
$aturan_list = [];
$sql = "SELECT a.id, a.kode_kerusakan, a.kode_gejala, 
               k.nama_kerusakan, g.nama_gejala
        FROM aturan a
        LEFT JOIN kerusakan k ON a.kode_kerusakan = k.kode_kerusakan
        LEFT JOIN gejala g ON a.kode_gejala = g.kode_gejala
        ORDER BY a.kode_kerusakan, a.kode_gejala";

$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $aturan_list[] = $row;
    }
}

// Get kerusakan list for dropdown
$kerusakan_list = [];
$result = $db->query("SELECT kode_kerusakan, nama_kerusakan FROM kerusakan ORDER BY kode_kerusakan");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $kerusakan_list[] = $row;
    }
}

// Get gejala list for dropdown
$gejala_list = [];
$result = $db->query("SELECT kode_gejala, nama_gejala FROM gejala ORDER BY kode_gejala");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $gejala_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aturan Diagnosis - KlikCare Admin</title>
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
                    <li><a href="aturan.php" class="active">Aturan Diagnosis</a></li>
                    <li><a href="riwayat.php">Riwayat Diagnosis</a></li>
                </ul>
            </div>

            <div class="main-content">
                <div class="page-header">
                    <h1 class="page-title">Aturan Diagnosis</h1>
                    <button class="btn-primary" onclick="openAddModal()">Tambah Aturan</button>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-section">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Aturan Diagnosis</h3>
                    </div>

                    <?php if (empty($aturan_list)): ?>
                        <div class="no-data"><p>Belum ada aturan diagnosis</p></div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Kerusakan</th>
                                        <th>Nama Kerusakan</th>
                                        <th>Kode Gejala</th>
                                        <th>Nama Gejala</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aturan_list as $index => $aturan): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><span class="kode-badge-kerusakan"><?php echo $aturan['kode_kerusakan']; ?></span></td>
                                            <td><?php echo htmlspecialchars($aturan['nama_kerusakan'] ?? 'Data tidak ditemukan'); ?></td>
                                            <td><span class="kode-badge-gejala"><?php echo $aturan['kode_gejala']; ?></span></td>
                                            <td><?php echo htmlspecialchars($aturan['nama_gejala'] ?? 'Data tidak ditemukan'); ?></td>
                                            <td>
                                                <button class="btn-delete" onclick="deleteAturan(<?php echo $aturan['id']; ?>, '<?php echo addslashes($aturan['kode_kerusakan']); ?>', '<?php echo addslashes($aturan['kode_gejala']); ?>')">Hapus</button>
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

    <!-- Modal Tambah -->
    <div id="aturanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Aturan Diagnosis</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <?php if (empty($kerusakan_list) || empty($gejala_list)): ?>
                        <div class="message warning">
                            <p><strong>⚠️ Peringatan:</strong></p>
                            <p>
                                <?php if (empty($kerusakan_list)): ?>
                                    Belum ada data kerusakan. <a href="kerusakan.php">Tambah data kerusakan</a> terlebih dahulu.
                                <?php endif; ?>
                                <?php if (empty($gejala_list)): ?>
                                    Belum ada data gejala. <a href="gejala.php">Tambah data gejala</a> terlebih dahulu.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="kode_kerusakan" class="form-label">Kerusakan</label>
                            <select class="form-control" id="kode_kerusakan" name="kode_kerusakan" required>
                                <option value="">Pilih Kerusakan</option>
                                <?php foreach ($kerusakan_list as $kerusakan): ?>
                                    <option value="<?php echo $kerusakan['kode_kerusakan']; ?>">
                                        <?php echo $kerusakan['kode_kerusakan']; ?> - <?php echo htmlspecialchars($kerusakan['nama_kerusakan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kode_gejala" class="form-label">Gejala</label>
                            <select class="form-control" id="kode_gejala" name="kode_gejala" required>
                                <option value="">Pilih Gejala</option>
                                <?php foreach ($gejala_list as $gejala): ?>
                                    <option value="<?php echo $gejala['kode_gejala']; ?>">
                                        <?php echo $gejala['kode_gejala']; ?> - <?php echo htmlspecialchars($gejala['nama_gejala']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <?php if (!empty($kerusakan_list) && !empty($gejala_list)): ?>
                        <button type="submit" class="btn-primary">Tambah Aturan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
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
                    <p>Apakah Anda yakin ingin menghapus aturan ini?</p>
                    <p><strong>Kerusakan:</strong> <span id="delete_kerusakan"></span></p>
                    <p><strong>Gejala:</strong> <span id="delete_gejala"></span></p>
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
        function openAddModal() {
            document.getElementById('aturanModal').style.display = 'block';
        }

        function deleteAturan(id, kode_kerusakan, kode_gejala) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_kerusakan').textContent = kode_kerusakan;
            document.getElementById('delete_gejala').textContent = kode_gejala;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('aturanModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('aturanModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == modal) modal.style.display = 'none';
            if (event.target == deleteModal) deleteModal.style.display = 'none';
        }

        // Auto-hide message after 3 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>