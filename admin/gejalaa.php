<?php
session_start();

if (!isset($_SESSION['hasil_diagnosis'])) {
    header('Location: index.php');
    exit;
}

$hasil_diagnosis = $_SESSION['hasil_diagnosis'];
$gejala_terpilih = $_SESSION['gejala_terpilih'];
$nama_user = $_SESSION['nama_user'];

// Ambil nama gejala untuk ditampilkan
require_once 'config/database.php';
$db = new Database();
$gejala_names = [];

foreach ($gejala_terpilih as $kode_gejala) {
    $result = $db->query("SELECT nama_gejala FROM gejala WHERE kode_gejala = '$kode_gejala'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $gejala_names[] = $row['nama_gejala'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis - KlikCare Cibinong</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mobile-alt me-2"></i>
                KlikCare Cibinong
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header Results -->
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Hasil Diagnosis untuk <?php echo htmlspecialchars($nama_user); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary">Gejala yang Anda Pilih:</h5>
                        <div class="row">
                            <?php foreach ($gejala_names as $index => $gejala): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span><?php echo htmlspecialchars($gejala); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis Results -->
                <div class="row">
                    <?php foreach ($hasil_diagnosis as $index => $diagnosis): ?>
                        <div class="col-12 mb-4">
                            <div class="card shadow-lg">
                                <div class="card-header <?php echo $index == 0 ? 'bg-danger' : 'bg-warning'; ?> text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <?php echo htmlspecialchars($diagnosis['nama_kerusakan']); ?>
                                        </h4>
                                        <div class="badge <?php echo $index == 0 ? 'bg-light text-dark' : 'bg-light text-dark'; ?> fs-6">
                                            <?php echo $diagnosis['persentase']; ?>% Match
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6 class="text-primary">Informasi Diagnosis:</h6>
                                            <p><strong>Kode Kerusakan:</strong> <?php echo $diagnosis['kode_kerusakan']; ?></p>
                                            <p><strong>Gejala yang Cocok:</strong> <?php echo $diagnosis['gejala_cocok']; ?> dari <?php echo $diagnosis['total_gejala']; ?> gejala</p>
                                            <div class="progress mb-3">
                                                <div class="progress-bar <?php echo $index == 0 ? 'bg-danger' : 'bg-warning'; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $diagnosis['persentase']; ?>%" 
                                                     aria-valuenow="<?php echo $diagnosis['persentase']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo $diagnosis['persentase']; ?>%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-primary">Solusi dan Rekomendasi:</h6>
                                            <div class="alert alert-info">
                                                <i class="fas fa-lightbulb me-2"></i>
                                                <?php echo nl2br(htmlspecialchars($diagnosis['solusi'])); ?>
                                            </div>
                                            <?php if ($index == 0): ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-star me-2"></i>
                                                    <strong>Diagnosis Utama:</strong> Ini adalah diagnosis dengan tingkat kesesuaian tertinggi berdasarkan gejala yang Anda pilih.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Actions -->
                <div class="text-center">
                    <a href="index.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-redo me-2"></i>
                        Diagnosis Ulang
                    </a>
                    <button onclick="window.print()" class="btn btn-secondary btn-lg">
                        <i class="fas fa-print me-2"></i>
                        Cetak Hasil
                    </button>
                </div>

                <!-- Disclaimer -->
                <div class="alert alert-warning mt-4">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Penting untuk Diketahui:</h6>
                    <ul class="mb-0">
                        <li>Hasil diagnosis ini bersifat sebagai panduan awal dan tidak menggantikan pemeriksaan langsung oleh teknisi profesional.</li>
                        <li>Untuk kerusakan yang kompleks, disarankan untuk membawa smartphone ke service center resmi.</li>
                        <li>Pastikan untuk backup data penting sebelum melakukan perbaikan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>KlikCare Cibinong</h5>
                    <p>Sistem Pakar Diagnosis Kerusakan Smartphone</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 KlikCare Cibinong. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Clear session setelah menampilkan hasil
unset($_SESSION['hasil_diagnosis']);
unset($_SESSION['gejala_terpilih']);
unset($_SESSION['nama_user']);
?>