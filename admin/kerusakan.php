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
                $nama_kerusakan = trim($_POST['nama_kerusakan']);
                $solusi = trim($_POST['solusi']);
                
                if (!empty($kode_kerusakan) && !empty($nama_kerusakan) && !empty($solusi)) {
                    // Check if kode already exists using prepared statement
                    $stmt_check = $db->prepare("SELECT kode_kerusakan FROM kerusakan WHERE kode_kerusakan = ?");
                    $stmt_check->bind_param("s", $kode_kerusakan);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("INSERT INTO kerusakan (kode_kerusakan, nama_kerusakan, solusi) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $kode_kerusakan, $nama_kerusakan, $solusi);
                        
                        if ($stmt->execute()) {
                            $message = "Data kerusakan berhasil ditambahkan";
                            $message_type = "success";
                        } else {
                            $message = "Gagal menambahkan data kerusakan";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Kode kerusakan sudah ada";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                } else {
                    $message = "Semua field harus diisi";
                    $message_type = "warning";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $kode_kerusakan = trim($_POST['kode_kerusakan']);
                $nama_kerusakan = trim($_POST['nama_kerusakan']);
                $solusi = trim($_POST['solusi']);
                
                if (!empty($kode_kerusakan) && !empty($nama_kerusakan) && !empty($solusi)) {
                    // Check if kode already exists for other records using prepared statement
                    $stmt_check = $db->prepare("SELECT id FROM kerusakan WHERE kode_kerusakan = ? AND id != ?");
                    $stmt_check->bind_param("si", $kode_kerusakan, $id);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("UPDATE kerusakan SET kode_kerusakan = ?, nama_kerusakan = ?, solusi = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $kode_kerusakan, $nama_kerusakan, $solusi, $id);
                        
                        if ($stmt->execute()) {
                            $message = "Data kerusakan berhasil diupdate";
                            $message_type = "success";
                        } else {
                            $message = "Gagal mengupdate data kerusakan";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Kode kerusakan sudah ada";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                } else {
                    $message = "Semua field harus diisi";
                    $message_type = "warning";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Check if kerusakan is used in aturan using prepared statement
                $stmt_check = $db->prepare("SELECT id FROM aturan WHERE kode_kerusakan = (SELECT kode_kerusakan FROM kerusakan WHERE id = ?)");
                $stmt_check->bind_param("i", $id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if ($result_check->num_rows == 0) {
                    $stmt_delete = $db->prepare("DELETE FROM kerusakan WHERE id = ?");
                    $stmt_delete->bind_param("i", $id);
                    
                    if ($stmt_delete->execute()) {
                        $message = "Data kerusakan berhasil dihapus";
                        $message_type = "success";
                    } else {
                        $message = "Gagal menghapus data kerusakan";
                        $message_type = "error";
                    }
                    $stmt_delete->close();
                } else {
                    $message = "Data kerusakan tidak dapat dihapus karena masih digunakan dalam aturan diagnosis";
                    $message_type = "warning";
                }
                $stmt_check->close();
                break;
        }
    }
}

// Get all kerusakan data
$kerusakan_list = [];
$result = $db->query("SELECT * FROM kerusakan ORDER BY kode_kerusakan");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $kerusakan_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kerusakan - KlikCare Admin</title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="gejala.php">Data Gejala</a></li>
                    <li><a href="kerusakan.php" class="active">Data Kerusakan</a></li>
                    <li><a href="aturan.php">Aturan Diagnosis</a></li>
                    <li><a href="riwayat.php">Riwayat Diagnosis</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">Data Kerusakan</h1>
                    <button class="btn-primary" onclick="openAddModal()">
                        <i class="icon-plus"></i>Tambah Kerusakan
                    </button>
                </div>

                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Kerusakan Table -->
                <div class="table-section">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Kerusakan Smartphone</h3>
                    </div>
                    
                    <?php if (empty($kerusakan_list)): ?>
                        <div class="no-data">
                            <p>Belum ada data kerusakan</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Kerusakan</th>
                                        <th>Solusi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kerusakan_list as $index => $kerusakan): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><span class="kode-badge-kerusakan"><?php echo $kerusakan['kode_kerusakan']; ?></span></td>
                                            <td><?php echo htmlspecialchars($kerusakan['nama_kerusakan']); ?></td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($kerusakan['solusi']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn-edit" onclick="editKerusakan(<?php echo htmlspecialchars(json_encode($kerusakan)); ?>)">
                                                    Edit
                                                </button>
                                                <button class="btn-delete" onclick="deleteKerusakan(<?php echo $kerusakan['id']; ?>, '<?php echo htmlspecialchars($kerusakan['nama_kerusakan']); ?>')">
                                                    Hapus
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

    <!-- Add Kerusakan Modal -->
    <div id="addKerusakanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Kerusakan Baru</h3>
                <button class="close" onclick="closeModal('addKerusakanModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label">Kode Kerusakan</label>
                        <input type="text" class="form-control" name="kode_kerusakan" required placeholder="Contoh: K001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Kerusakan</label>
                        <input type="text" class="form-control" name="nama_kerusakan" required placeholder="Contoh: Layar Retak">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Solusi</label>
                        <textarea class="form-control" name="solusi" required placeholder="Masukkan solusi untuk kerusakan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('addKerusakanModal')">Batal</button>
                    <button type="submit" class="btn-primary">Tambah Kerusakan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Kerusakan Modal -->
    <div id="editKerusakanModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Kerusakan</h3>
                <button class="close" onclick="closeModal('editKerusakanModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label class="form-label">Kode Kerusakan</label>
                        <input type="text" class="form-control" id="edit_kode_kerusakan" name="kode_kerusakan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Kerusakan</label>
                        <input type="text" class="form-control" id="edit_nama_kerusakan" name="nama_kerusakan" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Solusi</label>
                        <textarea class="form-control" id="edit_solusi" name="solusi" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editKerusakanModal')">Batal</button>
                    <button type="submit" class="btn-primary">Update Kerusakan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus kerusakan <strong id="delete_name"></strong>?</p>
                    <p style="color: #e74c3c; font-size: 14px;">Aksi ini tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Batal</button>
                    <button type="submit" class="btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addKerusakanModal').style.display = 'block';
        }

        function editKerusakan(kerusakan) {
            document.getElementById('edit_id').value = kerusakan.id;
            document.getElementById('edit_kode_kerusakan').value = kerusakan.kode_kerusakan;
            document.getElementById('edit_nama_kerusakan').value = kerusakan.nama_kerusakan;
            document.getElementById('edit_solusi').value = kerusakan.solusi;
            
            document.getElementById('editKerusakanModal').style.display = 'block';
        }

        function deleteKerusakan(id, nama) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = nama;
            
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>