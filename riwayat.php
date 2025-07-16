<?php
session_start();
require_once 'classes/ForwardChaining.php';

$fc = new ForwardChaining();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get riwayat with pagination
$riwayat_result = $fc->getRiwayat($limit, $offset);
$riwayat_list = $riwayat_result['data'];
$total_records = $riwayat_result['total'];
$total_pages = ceil($total_records / $limit);

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $riwayat_result = $fc->searchRiwayat($search, $limit, $offset);
    $riwayat_list = $riwayat_result['data'];
    $total_records = $riwayat_result['total'];
    $total_pages = ceil($total_records / $limit);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosis - KlikCare Cibinong</title>
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
                        <li><a href="riwayat.php" class="active">Riwayat</a></li>
                        <li><a href="admin/login.php">Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Riwayat Diagnosis</h1>
            <p>Lihat riwayat diagnosis kerusakan smartphone yang telah dilakukan</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Search Form -->
            <div class="search-form">
                <form method="GET" action="">
                    <div class="search-row">
                        <div class="search-group">
                            <label for="search">Cari Riwayat</label>
                            <input type="text" id="search" name="search" 
                                   placeholder="Cari berdasarkan nama, email, atau hasil diagnosis..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div>
                            <button type="submit" class="btn-search">Cari</button>
                            <?php if (!empty($search)): ?>
                                <a href="riwayat.php" class="btn-clear">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Riwayat Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Daftar Riwayat Diagnosis</h3>
                    <div>
                        <?php if (!empty($search)): ?>
                            <small>Menampilkan hasil pencarian untuk: "<strong><?php echo htmlspecialchars($search); ?></strong>"</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($riwayat_list)): ?>
                    <div class="table-responsive">
                        <table class="riwayat-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Hasil Diagnosis</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1;
                                foreach ($riwayat_list as $riwayat): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($riwayat['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($riwayat['email']) ?: '-'; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($riwayat['hasil_diagnosis']); ?></strong>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($riwayat['tanggal_diagnosis'])); ?></td>
                                        <td>
                                            <span class="badge badge-success">Selesai</span>
                                        </td>
                                        <td>
                                            <a href="detail_riwayat.php?id=<?php echo $riwayat['id']; ?>" 
                                               class="btn-detail">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Belum Ada Riwayat Diagnosis</h3>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan riwayat diagnosis yang sesuai dengan pencarian Anda.
                            <?php else: ?>
                                Belum ada riwayat diagnosis yang tersimpan di sistem.
                            <?php endif; ?>
                        </p>
                        <a href="index.php" class="btn-primary">Mulai Diagnosis</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo; Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Selanjutnya &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

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