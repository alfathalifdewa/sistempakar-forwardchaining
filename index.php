<?php
session_start();
require_once 'classes/ForwardChaining.php';

$fc = new ForwardChaining();
$gejala_list = $fc->getGejala();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['diagnose'])) {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $gejala_terpilih = $_POST['gejala'] ?? [];
    
    if (!empty($gejala_terpilih)) {
        $hasil_diagnosis = $fc->diagnose($gejala_terpilih);
        
        if ($hasil_diagnosis['success']) {
            // Simpan riwayat
            $fc->saveRiwayat($nama, $email, $gejala_terpilih, $hasil_diagnosis['data']);
            $_SESSION['hasil_diagnosis'] = $hasil_diagnosis['data'];
            $_SESSION['gejala_terpilih'] = $gejala_terpilih;
            $_SESSION['nama_user'] = $nama;
            header('Location: diagnosis.php');
            exit;
        } else {
            $error = $hasil_diagnosis['message'];
        }
    } else {
        $error = "Silakan pilih minimal satu gejala";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar Diagnosis Smartphone - KlikCare Cibinong</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">KlikCare Cibinong</div>
                <nav>
                    <ul class="nav">
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="riwayat.php">Riwayat</a></li>
                        <li><a href="admin/login.php">Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Sistem Pakar Diagnosis Kerusakan Smartphone</h1>
            <p>Dapatkan diagnosis akurat untuk kerusakan smartphone Anda dengan mudah dan cepat</p>
            <a href="#diagnosis-form" class="btn-primary">Mulai Diagnosis</a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Diagnosis Form -->
            <div class="card" id="diagnosis-form">
                <h2>Form Diagnosis Kerusakan Smartphone</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        ⚠️ <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email">Email (Opsional)</label>
                                <input type="email" id="email" name="email">
                            </div>
                        </div>
                    </div>

                    <div class="gejala-section">
                        <h3>Pilih Gejala yang Dialami Smartphone Anda:</h3>
                        <p>Centang semua gejala yang sesuai dengan kondisi smartphone Anda</p>
                        
                        <div class="gejala-grid">
                            <?php foreach ($gejala_list as $gejala): ?>
                                <div class="gejala-item">
                                    <input type="checkbox" 
                                           id="gejala_<?php echo $gejala['id']; ?>" 
                                           name="gejala[]" 
                                           value="<?php echo $gejala['kode_gejala']; ?>">
                                    <label for="gejala_<?php echo $gejala['id']; ?>">
                                        <span class="gejala-code"><?php echo $gejala['kode_gejala']; ?>:</span>
                                        <?php echo $gejala['nama_gejala']; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" name="diagnose" class="btn-submit">
                        Mulai Diagnosis
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Keunggulan Sistem Kami</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <h3>Sistem Pakar</h3>
                    <p>Menggunakan teknologi forward chaining untuk diagnosis yang akurat berdasarkan pengetahuan ahli</p>
                </div>
                <div class="feature-item">
                    <h3>Cepat & Akurat</h3>
                    <p>Dapatkan hasil diagnosis dalam hitungan detik dengan tingkat akurasi yang tinggi</p>
                </div>
                <div class="feature-item">
                    <h3>Solusi Praktis</h3>
                    <p>Setiap diagnosis dilengkapi dengan solusi dan rekomendasi perbaikan yang tepat</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>KlikCare Cibinong</h3>
                    <p>Sistem Pakar Diagnosis Kerusakan Smartphone yang mudah dan terpercaya</p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>Email: info@klikcare.com<br>
                       Telepon: (021) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KlikCare Cibinong. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>