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
                $kode_gejala = trim($_POST['kode_gejala']);
                $nama_gejala = trim($_POST['nama_gejala']);
                
                if (!empty($kode_gejala) && !empty($nama_gejala)) {
                    $stmt_check = $db->prepare("SELECT kode_gejala FROM gejala WHERE kode_gejala = ?");
                    $stmt_check->bind_param("s", $kode_gejala);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("INSERT INTO gejala (kode_gejala, nama_gejala) VALUES (?, ?)");
                        $stmt->bind_param("ss", $kode_gejala, $nama_gejala);
                        
                        if ($stmt->execute()) {
                            $message = "Data gejala berhasil ditambahkan";
                            $message_type = "success";
                        } else {
                            $message = "Gagal menambahkan data gejala";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Kode gejala sudah ada";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                } else {
                    $message = "Semua field harus diisi";
                    $message_type = "warning";
                }
                break;

            case 'edit':
                $id = (int)$_POST['id'];
                $kode_gejala = trim($_POST['kode_gejala']);
                $nama_gejala = trim($_POST['nama_gejala']);
                
                if (!empty($kode_gejala) && !empty($nama_gejala) && $id > 0) {
                    // Check if the code already exists for other records
                    $stmt_check = $db->prepare("SELECT id FROM gejala WHERE kode_gejala = ? AND id != ?");
                    $stmt_check->bind_param("si", $kode_gejala, $id);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("UPDATE gejala SET kode_gejala = ?, nama_gejala = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $kode_gejala, $nama_gejala, $id);
                        
                        if ($stmt->execute()) {
                            $message = "Data gejala berhasil diupdate";
                            $message_type = "success";
                        } else {
                            $message = "Gagal mengupdate data gejala";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Kode gejala sudah ada";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                } else {
                    $message = "Kode gejala dan nama gejala harus diisi";
                    $message_type = "warning";
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($id > 0) {
                    // Check if the symptom is used in rules
                    $stmt_check = $db->prepare("SELECT id FROM aturan WHERE kode_gejala = (SELECT kode_gejala FROM gejala WHERE id = ?)");
                    $stmt_check->bind_param("i", $id);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    
                    if ($result_check->num_rows == 0) {
                        $stmt = $db->prepare("DELETE FROM gejala WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        
                        if ($stmt->execute()) {
                            $message = "Data gejala berhasil dihapus";
                            $message_type = "success";
                        } else {
                            $message = "Gagal menghapus data gejala";
                            $message_type = "error";
                        }
                        $stmt->close();
                    } else {
                        $message = "Data gejala tidak dapat dihapus karena masih digunakan dalam aturan diagnosis";
                        $message_type = "warning";
                    }
                    $stmt_check->close();
                }
                break;
        }
    }
}

$gejala_list = [];
$result = $db->query("SELECT * FROM gejala ORDER BY kode_gejala");
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
    <title>Data Gejala - KlikCare Admin</title>
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
                    <li><a href="gejala.php" class="active">Data Gejala</a></li>
                    <li><a href="kerusakan.php">Data Kerusakan</a></li>
                    <li><a href="aturan.php">Aturan Diagnosis</a></li>
                    <li><a href="riwayat.php">Riwayat Diagnosis</a></li>
                </ul>
            </div>

            <div class="main-content">
                <div class="page-header">
                    <h1 class="page-title">Data Gejala</h1>
                    <button class="btn-primary" onclick="openAddModal()">Tambah Gejala</button>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-section">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Gejala Kerusakan Smartphone</h3>
                    </div>

                    <?php if (empty($gejala_list)): ?>
                        <div class="no-data"><p>Belum ada data gejala</p></div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Gejala</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gejala_list as $index => $gejala): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><span class="kode-badge-gejala"><?php echo $gejala['kode_gejala']; ?></span></td>
                                            <td><?php echo htmlspecialchars($gejala['nama_gejala']); ?></td>
                                            <td>
                                                <button class="btn-edit" onclick="editGejala(<?php echo $gejala['id']; ?>, '<?php echo addslashes($gejala['kode_gejala']); ?>', '<?php echo addslashes($gejala['nama_gejala']); ?>')">Edit</button>
                                                <button class="btn-delete" onclick="deleteGejala(<?php echo $gejala['id']; ?>, '<?php echo addslashes($gejala['nama_gejala']); ?>')"> Hapus</button>
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

    <!-- Modal Tambah/Edit -->
    <div id="gejalaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Gejala Baru</h3>
                <button class="close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="gejalaForm">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="gejalaId">
                    <div class="form-group">
                        <label for="kode_gejala" class="form-label">Kode Gejala</label>
                        <input type="text" class="form-control" id="kode_gejala" name="kode_gejala" required placeholder="Contoh: G001">
                    </div>
                    <div class="form-group">
                        <label for="nama_gejala" class="form-label">Nama Gejala</label>
                        <input type="text" class="form-control" id="nama_gejala" name="nama_gejala" required placeholder="Contoh: Tidak bisa dinyalakan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Tambah Gejala</button>
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
                    <p>Apakah Anda yakin ingin menghapus gejala <strong id="delete_name"></strong>?</p>
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
            document.getElementById('modalTitle').textContent = 'Tambah Gejala Baru';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitBtn').textContent = 'Tambah Gejala';
            document.getElementById('gejalaForm').reset();
            document.getElementById('gejalaId').value = '';
            document.getElementById('gejalaModal').style.display = 'block';
        }

        function editGejala(id, kode, nama) {
            document.getElementById('modalTitle').textContent = 'Edit Gejala';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('submitBtn').textContent = 'Update Gejala';
            document.getElementById('gejalaId').value = id;
            document.getElementById('kode_gejala').value = kode;
            document.getElementById('nama_gejala').value = nama;
            document.getElementById('gejalaModal').style.display = 'block';
        }

        function deleteGejala(id, nama) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = nama;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('gejalaModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('gejalaModal');
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