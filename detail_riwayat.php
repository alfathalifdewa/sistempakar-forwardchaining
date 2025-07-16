<?php
session_start();
require_once 'classes/ForwardChaining.php';

$fc = new ForwardChaining();

// Get and validate ID from URL
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID diagnosis tidak valid.";
    header('Location: riwayat.php');
    exit;
}

// Get detail riwayat
$detail = $fc->getRiwayatDetail($id);

if (!$detail) {
    $_SESSION['error_message'] = "Data riwayat tidak ditemukan.";
    header('Location: riwayat.php');
    exit;
}

// Parse gejala yang dipilih
$gejala_terpilih = json_decode($detail['gejala_terpilih'], true) ?? [];
$gejala_list = [];
if (!empty($gejala_terpilih)) {
    $gejala_list = $fc->getGejalaByKodes($gejala_terpilih);
}

// Get solusi berdasarkan hasil diagnosis
$solusi = $fc->getSolusiByKerusakan($detail['hasil_diagnosis']);

// Format tanggal untuk Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan_angka = date('m', $timestamp);
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $hari . ' ' . $bulan[$bulan_angka] . ' ' . $tahun . ', ' . $jam . ' WIB';
}

// Get confidence level color
function getConfidenceColor($tingkat) {
    if ($tingkat >= 80) return '#27ae60';
    if ($tingkat >= 60) return '#f39c12';
    return '#e74c3c';
}

// Handle export/download actions
if (isset($_POST['export_pdf'])) {
    // Logic for PDF export would go here
    $_SESSION['success_message'] = "Export PDF berhasil!";
}

if (isset($_POST['send_email'])) {
    // Logic for email sending would go here
    $_SESSION['success_message'] = "Email berhasil dikirim!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Diagnosis #<?php echo str_pad($detail['id'], 4, '0', STR_PAD_LEFT); ?> - KlikCare Cibinong</title>
    <meta name="description" content="Detail hasil diagnosis kerusakan smartphone untuk <?php echo htmlspecialchars($detail['nama']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: #3498db;
        }

        .nav {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Breadcrumb */
        .breadcrumb {
            background: rgba(255,255,255,0.9);
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .breadcrumb-content {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card h2 i {
            color: #3498db;
        }

        .card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        /* Back Button */
        .back-btn {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-radius: 12px;
            border-left: 5px solid #3498db;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value {
            color: #495057;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Diagnosis Result */
        .diagnosis-result {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .diagnosis-result::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .diagnosis-result-content {
            position: relative;
            z-index: 1;
        }

        .diagnosis-result h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .diagnosis-title {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .confidence-level {
            font-size: 1.3rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .confidence-bar {
            width: 200px;
            height: 10px;
            background: rgba(255,255,255,0.3);
            border-radius: 5px;
            overflow: hidden;
            margin: 10px auto;
        }

        .confidence-fill {
            height: 100%;
            background: white;
            border-radius: 5px;
            transition: width 1s ease;
        }

        /* Gejala List */
        .gejala-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .gejala-item {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid #e74c3c;
            transition: all 0.3s ease;
            position: relative;
        }

        .gejala-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.2);
        }

        .gejala-item::before {
            content: '⚠️';
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.2rem;
        }

        .gejala-code {
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .gejala-name {
            color: #2c3e50;
            font-weight: 500;
        }

        /* Solusi Section */
        .solusi-section {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 2px solid #ffd93d;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
        }

        .solusi-section::before {
            content: '💡';
            position: absolute;
            top: -15px;
            left: 30px;
            background: #ffd93d;
            padding: 10px;
            border-radius: 50%;
            font-size: 1.5rem;
        }

        .solusi-section h3 {
            color: #856404;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            margin-top: 10px;
        }

        .solusi-list {
            list-style: none;
            counter-reset: solusi-counter;
        }

        .solusi-list li {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 4px solid #f39c12;
            position: relative;
            counter-increment: solusi-counter;
            padding-left: 60px;
            transition: all 0.3s ease;
        }

        .solusi-list li:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .solusi-list li::before {
            content: counter(solusi-counter);
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: #f39c12;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .solusi-list li:last-child {
            margin-bottom: 0;
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #219a52);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            text-align: center;
            padding: 50px 0;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
            color: #3498db;
            font-size: 1.3rem;
        }

        .footer-section p {
            line-height: 1.8;
            color: #bdc3c7;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 30px;
            margin-top: 30px;
            color: #bdc3c7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            .nav {
                flex-direction: column;
                gap: 10px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .gejala-list {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .diagnosis-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .card {
                padding: 20px;
            }

            .diagnosis-result {
                padding: 25px;
            }

            .diagnosis-title {
                font-size: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Print Styles */
        @media print {
            .header,
            .breadcrumb,
            .page-header,
            .back-btn,
            .actions,
            .footer {
                display: none;
            }

            .container {
                max-width: none;
                padding: 0;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
                break-inside: avoid;
            }

            .diagnosis-result {
                background: #f8f9fa !important;
                color: #333 !important;
                border: 2px solid #27ae60;
            }

            .diagnosis-result h3,
            .diagnosis-title {
                color: #333 !important;
            }

            .main-content {
                padding: 20px 0;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-mobile-alt"></i>
                    KlikCare Cibinong
                </div>
                <nav>
                    <ul class="nav">
                        <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
                        <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
                        <li><a href="admin/login.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <a href="index.php">Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="riwayat.php">Riwayat</a>
                <i class="fas fa-chevron-right"></i>
                <span>Detail Diagnosis #<?php echo str_pad($detail['id'], 4, '0', STR_PAD_LEFT); ?></span>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1><i class="fas fa-file-medical"></i> Detail Riwayat Diagnosis</h1>
                <p>Informasi lengkap hasil diagnosis kerusakan smartphone</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <a href="riwayat.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </a>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Patient Information -->
            <div class="card">
                <h2><i class="fas fa-user"></i> Informasi Pasien</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($detail['nama']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i> Email
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($detail['email'] ?: 'Tidak tersedia'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i> Tanggal Diagnosis
                        </div>
                        <div class="info-value"><?php echo formatTanggalIndonesia($detail['tanggal_diagnosis']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-hashtag"></i> ID Diagnosis
                        </div>
                        <div class="info-value">#<?php echo str_pad($detail['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                </div>
            </div>

            <!-- Diagnosis Result -->
            <div class="diagnosis-result" style="background: linear-gradient(135deg, <?php echo getConfidenceColor($detail['tingkat_keyakinan']); ?>, <?php echo getConfidenceColor($detail['tingkat_keyakinan']); ?>dd);">
                <div class="diagnosis-result-content">
                    <h3><i class="fas fa-stethoscope"></i> Hasil Diagnosis</h3>
                    <div class="diagnosis-title"><?php echo htmlspecialchars($detail['hasil_diagnosis']); ?></div>
                    <div class="confidence-level">
                        <i class="fas fa-chart-line"></i> Tingkat Keyakinan: <?php echo $detail['tingkat_keyakinan']; ?>%
                    </div>
                    <div class="confidence-bar">
                        <div class="confidence-fill" style="width: <?php echo $detail['tingkat_keyakinan']; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Gejala yang Dipilih -->
            <div class="card">
                <h2><i class="fas fa-exclamation-triangle"></i> Gejala yang Dipilih</h2>
                <?php if (!empty($gejala_list)): ?>
                    <div class="gejala-list">
                        <?php foreach ($gejala_list as $gejala): ?>
                            <div class="gejala-item">
                                <div class="gejala-code"><?php echo htmlspecialchars($gejala['kode']); ?></div>
                                <div class="gejala-name"><?php echo htmlspecialchars($gejala['nama']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-question-circle"></i>
                        <p>Tidak ada gejala yang tercatat dalam diagnosis ini.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Solusi dan Rekomendasi -->
            <?php if (!empty($solusi)): ?>
                <div class="solusi-section">
                    <h3><i class="fas fa-lightbulb"></i> Solusi dan Rekomendasi</h3>
                    <ul class="solusi-list">
                        <?php foreach ($solusi as $s): ?>
                            <li><?php echo htmlspecialchars($s['deskripsi']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="card">
                    <h3><i class="fas fa-info-circle"></i> Solusi dan Rekomendasi</h3>
                    <p>Tidak ada solusi khusus yang tersedia untuk diagnosis ini. Silakan konsultasi dengan teknisi untuk penanganan lebih lanjut.</p>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="card">
                <h2><i class="fas fa-tools"></i> Tindakan Lanjutan</h2>
                <div class="actions">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                    <a href="index.php" class="btn btn-success">
                        <i class="fas fa-redo"></i> Diagnosis Baru
                    </a>
                    <a href="riwayat.php" class="btn btn-warning">
                        <i class="fas fa-list"></i> Lihat Riwayat
                    </a>
                    <button onclick="shareResult()" class="btn btn-info">
                        <i class="fas fa-share-alt"></i> Bagikan Hasil
                    </button>
                </div>
            </div></div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>KlikCare Cibinong</h3>
                    <p>Sistem pakar diagnosis kerusakan smartphone yang membantu Anda mengidentifikasi masalah pada perangkat mobile dengan akurat dan cepat.</p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>
                        <i class="fas fa-phone"></i> +62 21 8765 4321<br>
                        <i class="fas fa-envelope"></i> info@klikcare-cibinong.com<br>
                        <i class="fas fa-map-marker-alt"></i> Jl. Raya Cibinong No. 123, Bogor
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Layanan</h3>
                    <p>
                        • Diagnosis Kerusakan<br>
                        • Konsultasi Teknisi<br>
                        • Riwayat Diagnosis<br>
                        • Solusi Perbaikan
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 KlikCare Cibinong. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading">
        <div class="spinner"></div>
        <p>Memproses permintaan...</p>
    </div>

    <!-- JavaScript -->
    <script>
        // Loading functions
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        // Share result function
        function shareResult() {
            const diagnosisTitle = "<?php echo htmlspecialchars($detail['hasil_diagnosis']); ?>";
            const confidence = "<?php echo $detail['tingkat_keyakinan']; ?>";
            const patientName = "<?php echo htmlspecialchars($detail['nama']); ?>";
            
            if (navigator.share) {
                navigator.share({
                    title: 'Hasil Diagnosis KlikCare',
                    text: `Diagnosis: ${diagnosisTitle}\nTingkat Keyakinan: ${confidence}%\nPasien: ${patientName}`,
                    url: window.location.href
                }).then(() => {
                    showNotification('Berhasil membagikan hasil diagnosis!', 'success');
                }).catch((error) => {
                    console.log('Error sharing:', error);
                    copyToClipboard();
                });
            } else {
                copyToClipboard();
            }
        }

        // Copy to clipboard function
        function copyToClipboard() {
            const text = `Hasil Diagnosis KlikCare Cibinong\n\n` +
                        `Diagnosis: <?php echo htmlspecialchars($detail['hasil_diagnosis']); ?>\n` +
                        `Tingkat Keyakinan: <?php echo $detail['tingkat_keyakinan']; ?>%\n` +
                        `Pasien: <?php echo htmlspecialchars($detail['nama']); ?>\n` +
                        `Tanggal: <?php echo formatTanggalIndonesia($detail['tanggal_diagnosis']); ?>\n\n` +
                        `Link: ${window.location.href}`;
            
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Link dan informasi diagnosis telah disalin ke clipboard!', 'success');
            }).catch(() => {
                showNotification('Gagal menyalin ke clipboard', 'error');
            });
        }

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Print function with custom styling
        function printReport() {
            const printContent = document.querySelector('.main-content').innerHTML;
            const printWindow = window.open('', '', 'width=800,height=600');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Diagnosis - KlikCare Cibinong</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        .card { border: 1px solid #ddd; margin-bottom: 20px; padding: 20px; border-radius: 8px; }
                        .card h2 { color: #2c3e50; margin-bottom: 15px; }
                        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
                        .info-item { border: 1px solid #eee; padding: 10px; border-radius: 5px; }
                        .info-label { font-weight: bold; margin-bottom: 5px; }
                        .diagnosis-result { background: #f8f9fa; border: 2px solid #27ae60; padding: 20px; text-align: center; margin-bottom: 20px; border-radius: 8px; }
                        .diagnosis-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 10px; }
                        .gejala-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
                        .gejala-item { border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
                        .gejala-code { font-weight: bold; color: #e74c3c; }
                        .solusi-list { list-style: decimal; padding-left: 20px; }
                        .solusi-list li { margin-bottom: 10px; }
                        .back-btn, .actions { display: none; }
                        @media print { .no-print { display: none; } }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>KlikCare Cibinong</h1>
                            <p>Laporan Diagnosis Kerusakan Smartphone</p>
                            <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</p>
                        </div>
                        ${printContent}
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        // Animation for confidence bar
        document.addEventListener('DOMContentLoaded', function() {
            const confidenceFill = document.querySelector('.confidence-fill');
            if (confidenceFill) {
                setTimeout(() => {
                    confidenceFill.style.width = '<?php echo $detail['tingkat_keyakinan']; ?>%';
                }, 500);
            }
        });

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .confidence-fill {
                transition: width 2s ease-in-out;
            }
        `;
        document.head.appendChild(style);

        // Enhanced print function
        function enhancedPrint() {
            showLoading();
            setTimeout(() => {
                printReport();
                hideLoading();
            }, 1000);
        }

        // Update print button onclick
        document.addEventListener('DOMContentLoaded', function() {
            const printBtn = document.querySelector('button[onclick="window.print()"]');
            if (printBtn) {
                printBtn.setAttribute('onclick', 'enhancedPrint()');
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'p':
                        e.preventDefault();
                        enhancedPrint();
                        break;
                    case 's':
                        e.preventDefault();
                        shareResult();
                        break;
                }
            }
        });

        // Add tooltips
        function addTooltips() {
            const elements = document.querySelectorAll('[data-tooltip]');
            elements.forEach(element => {
                element.addEventListener('mouseenter', function(e) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = e.target.getAttribute('data-tooltip');
                    tooltip.style.cssText = `
                        position: absolute;
                        background: #333;
                        color: white;
                        padding: 5px 10px;
                        border-radius: 4px;
                        font-size: 12px;
                        z-index: 1000;
                        pointer-events: none;
                        top: ${e.pageY - 30}px;
                        left: ${e.pageX}px;
                    `;
                    document.body.appendChild(tooltip);
                    
                    e.target.addEventListener('mouseleave', function() {
                        document.body.removeChild(tooltip);
                    });
                });
            });
        }

        // Initialize tooltips
        addTooltips();
    </script>
</body>
</html>