<?php
session_start();

// Cek apakah ada hasil diagnosis
if (!isset($_SESSION['hasil_diagnosis'])) {
    header('Location: index.php');
    exit;
}

// Ambil data dari session
$hasil_diagnosis = $_SESSION['hasil_diagnosis'];
$gejala_terpilih = $_SESSION['gejala_terpilih'];
$nama_user = $_SESSION['nama_user'];

// Ambil diagnosis utama (yang pertama)
$diagnosis_utama = $hasil_diagnosis[0];

// Ambil nama gejala dari database
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .user-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .user-info h2 {
            color: #27ae60;
            margin: 0;
            font-size: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .gejala-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .gejala-item {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .gejala-item:last-child {
            border-bottom: none;
        }

        .gejala-item::before {
            content: "✓ ";
            color: #27ae60;
            font-weight: bold;
        }

        .diagnosis-result {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .diagnosis-result h3 {
            margin: 0 0 15px 0;
            font-size: 24px;
        }

        .diagnosis-info {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .solusi-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .solusi-box h3 {
            color: #856404;
            margin-top: 0;
        }

        .solusi-content {
            line-height: 1.6;
            color: #495057;
        }

        .button-group {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .peringatan {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin-top: 20px;
        }

        .peringatan h4 {
            margin-top: 0;
            color: #dc3545;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .diagnosis-result h3 {
                font-size: 20px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }

        /* Print styling */
        @media print {
            .button-group {
                display: none;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hasil Diagnosis Smartphone</h1>
        
        <div class="user-info">
            <h2>Halo, <?php echo htmlspecialchars($nama_user); ?>!</h2>
            <p>Berikut adalah hasil diagnosis untuk smartphone Anda</p>
        </div>

        <div class="section">
            <h3>Gejala yang Anda Pilih</h3>
            <div class="gejala-list">
                <?php foreach ($gejala_names as $gejala): ?>
                    <div class="gejala-item">
                        <?php echo htmlspecialchars($gejala); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="diagnosis-result">
            <h3>Diagnosis Kerusakan</h3>
            <div class="diagnosis-info">
                <strong><?php echo htmlspecialchars($diagnosis_utama['nama_kerusakan']); ?></strong>
            </div>
            <div class="diagnosis-info">
                Kode: <?php echo htmlspecialchars($diagnosis_utama['kode_kerusakan']); ?>
            </div>
        </div>

        <div class="solusi-box">
            <h3>💡 Solusi dan Cara Perbaikan</h3>
            <div class="solusi-content">
                <?php echo nl2br(htmlspecialchars($diagnosis_utama['solusi'])); ?>
            </div>
        </div>

        <div class="peringatan">
            <h4>Peringatan Penting!</h4>
            <ul>
                <li>Hasil ini hanya sebagai panduan awal</li>
                <li>Untuk kerusakan serius, bawa ke teknisi profesional</li>
                <li>Backup data penting sebelum melakukan perbaikan</li>
                <li>Hati-hati saat membongkar smartphone</li>
            </ul>
        </div>

        <div class="button-group">
            <a href="index.php" class="btn btn-primary">Diagnosis Lagi</a>
            <button onclick="window.print()" class="btn btn-secondary">Cetak Hasil</button>
        </div>
    </div>
</body>
</html>

<?php
// Hapus data session setelah ditampilkan
unset($_SESSION['hasil_diagnosis']);
unset($_SESSION['gejala_terpilih']);
unset($_SESSION['nama_user']);
?>