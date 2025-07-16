-- Membuat database
-- CREATE DATABASE ;
-- USE smartphone_expert;

-- Tabel admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default
INSERT INTO admin (username, password, nama) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
-- Password: password

-- Tabel gejala
CREATE TABLE gejala (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_gejala VARCHAR(10) NOT NULL UNIQUE,
    nama_gejala TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data gejala
INSERT INTO gejala (kode_gejala, nama_gejala) VALUES
('G001', 'Smartphone tidak bisa menyala sama sekali'),
('G002', 'Layar retak atau pecah'),
('G003', 'Layar tidak menampilkan apapun (blank/hitam)'),
('G004', 'Touch screen tidak responsif'),
('G005', 'Baterai cepat habis'),
('G006', 'Smartphone sering hang atau lemot'),
('G007', 'Tidak bisa mengisi daya (charging)'),
('G008', 'Suara speaker tidak keluar'),
('G009', 'Microphone tidak berfungsi'),
('G010', 'Kamera tidak berfungsi'),
('G011', 'WiFi tidak bisa connect'),
('G012', 'Bluetooth tidak berfungsi'),
('G013', 'Smartphone cepat panas'),
('G014', 'Tombol power tidak berfungsi'),
('G015', 'Sinyal lemah atau tidak ada sinyal');

-- Tabel kerusakan
CREATE TABLE kerusakan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_kerusakan VARCHAR(10) NOT NULL UNIQUE,
    nama_kerusakan VARCHAR(255) NOT NULL,
    solusi TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data kerusakan
INSERT INTO kerusakan (kode_kerusakan, nama_kerusakan, solusi) VALUES
('K001', 'Kerusakan Baterai', 'Ganti baterai dengan yang baru. Pastikan menggunakan baterai original atau berkualitas baik.'),
('K002', 'Kerusakan Layar LCD/OLED', 'Ganti layar dengan yang baru. Disarankan menggunakan layar original untuk kualitas terbaik.'),
('K003', 'Kerusakan Touchscreen', 'Ganti digitizer touchscreen atau layar lengkap jika touchscreen menyatu dengan LCD.'),
('K004', 'Kerusakan Sistem/Software', 'Lakukan factory reset, update firmware, atau install ulang sistem operasi.'),
('K005', 'Kerusakan Port Charging', 'Bersihkan atau ganti port charging. Periksa juga kabel charger.'),
('K006', 'Kerusakan Speaker', 'Ganti speaker dengan yang baru atau bersihkan dari debu dan kotoran.'),
('K007', 'Kerusakan Microphone', 'Ganti microphone atau bersihkan dari debu. Periksa juga pengaturan audio.'),
('K008', 'Kerusakan Kamera', 'Ganti modul kamera atau periksa koneksi kabel kamera ke motherboard.'),
('K009', 'Kerusakan Modul WiFi/Bluetooth', 'Ganti modul WiFi/Bluetooth atau lakukan reset network settings.'),
('K010', 'Kerusakan Tombol Power', 'Ganti tombol power atau perbaiki jalur tombol power di motherboard.');

-- Tabel aturan (rules)
CREATE TABLE aturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_kerusakan VARCHAR(10) NOT NULL,
    kode_gejala VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kode_kerusakan) REFERENCES kerusakan(kode_kerusakan) ON DELETE CASCADE,
    FOREIGN KEY (kode_gejala) REFERENCES gejala(kode_gejala) ON DELETE CASCADE
);

-- Insert data aturan
INSERT INTO aturan (kode_kerusakan, kode_gejala) VALUES
-- Kerusakan Baterai
('K001', 'G001'),
('K001', 'G005'),
('K001', 'G007'),
('K001', 'G013'),

-- Kerusakan Layar LCD/OLED
('K002', 'G002'),
('K002', 'G003'),

-- Kerusakan Touchscreen
('K003', 'G004'),
('K003', 'G002'),

-- Kerusakan Sistem/Software
('K004', 'G006'),
('K004', 'G001'),
('K004', 'G013'),

-- Kerusakan Port Charging
('K005', 'G007'),
('K005', 'G001'),

-- Kerusakan Speaker
('K006', 'G008'),

-- Kerusakan Microphone
('K007', 'G009'),

-- Kerusakan Kamera
('K008', 'G010'),

-- Kerusakan Modul WiFi/Bluetooth
('K009', 'G011'),
('K009', 'G012'),

-- Kerusakan Tombol Power
('K010', 'G014'),
('K010', 'G001');

-- Tabel riwayat diagnosis
CREATE TABLE riwayat_diagnosis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_user VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    gejala_terpilih TEXT NOT NULL,
    hasil_diagnosis TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);