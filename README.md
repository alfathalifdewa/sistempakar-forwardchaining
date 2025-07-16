# Sistem Pakar Diagnosis Kerusakan Smartphone

Proyek skripsi ini adalah **Sistem Pakar Diagnosis Kerusakan Smartphone** yang dikembangkan menggunakan metode **Forward Chaining**. Aplikasi ini berbasis **PHP Native** dan berjalan di browser lokal tanpa framework eksternal.

## 📚 Deskripsi Proyek

Sistem ini dirancang untuk membantu pengguna dalam mendiagnosa berbagai jenis kerusakan pada smartphone berdasarkan gejala-gejala yang dialami. Dengan memasukkan satu atau lebih gejala, sistem akan memberikan kemungkinan kerusakan beserta solusinya, berdasarkan aturan yang telah ditentukan oleh pakar.

## 🎯 Tujuan

- Membantu pengguna atau teknisi pemula dalam menganalisis kerusakan smartphone.
- Mengurangi waktu identifikasi masalah.
- Menyediakan solusi awal sebelum perbaikan lanjutan dilakukan.

## 🛠️ Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP Native
- **Database**: MySQL
- **Antarmuka**: HTML, CSS
- **Web Server**: XAMPP

## 🧠 Metode yang Digunakan

- **Forward Chaining**: Sistem menarik kesimpulan dari kumpulan fakta (gejala) yang diberikan menuju konklusi (kerusakan) berdasarkan aturan IF-THEN.


## 🧪 Fitur Utama

- ✅ Input gejala kerusakan dari pengguna
- ✅ Proses diagnosis menggunakan metode Forward Chaining
- ✅ Output hasil kerusakan beserta solusi
- ✅ Login admin
- ✅ CRUD data gejala, kerusakan, dan aturan
- ✅ Riwayat konsultasi pengguna (opsional)

## 💡 Contoh Gejala dan Kerusakan

| Kode Gejala | Gejala                            |
|-------------|-----------------------------------|
| G001        | Layar mati total                  |
| G002        | Tidak bisa dicas                  |
| G003        | Baterai cepat habis               |

| Kode Kerusakan | Kerusakan                     |
|----------------|-------------------------------|
| K001           | Kerusakan pada IC Power       |
| K002           | Baterai rusak                 |

## 🚀 Cara Menjalankan

1. Clone repository ini atau download sebagai ZIP.
2. Ekstrak ke dalam folder `htdocs` (jika menggunakan XAMPP).
3. Buat database `spforward_chaining` di phpMyAdmin.
4. Import file `.sql` yang ada dalam folder project.
5. Jalankan `http://localhost/nama_folder_anda/` di browser.
6. Login admin default:
   - Username: `admin`
   - Password: `password`

## 🧾 Lisensi

Proyek ini dibuat untuk keperluan akademik dan **tidak untuk tujuan komersial**.

---

📌 *Dikembangkan oleh:*  
**Alfathalif Dewa Listyoka**  
Mahasiswa Teknik Informatika  
Universitas Indraprasta PGRI  
2025
