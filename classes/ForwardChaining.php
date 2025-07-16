<?php
require_once 'config/database.php';

class ForwardChaining {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function diagnose($gejala_terpilih) {
        if (empty($gejala_terpilih)) {
            return ['success' => false, 'message' => 'Tidak ada gejala yang dipilih'];
        }
        
        // Konversi array ke string untuk query
        $gejala_string = "'" . implode("','", $gejala_terpilih) . "'";
        
        // Query untuk mencari kerusakan berdasarkan gejala - Fixed ORDER BY clause
        $sql = "SELECT k.kode_kerusakan, k.nama_kerusakan, k.solusi, 
                       COUNT(a.kode_gejala) as jumlah_gejala_cocok,
                       (SELECT COUNT(*) FROM aturan WHERE kode_kerusakan = k.kode_kerusakan) as total_gejala_kerusakan
                FROM kerusakan k
                JOIN aturan a ON k.kode_kerusakan = a.kode_kerusakan
                WHERE a.kode_gejala IN ($gejala_string)
                GROUP BY k.kode_kerusakan, k.nama_kerusakan, k.solusi
                ORDER BY COUNT(a.kode_gejala) DESC, (COUNT(a.kode_gejala)/(SELECT COUNT(*) FROM aturan WHERE kode_kerusakan = k.kode_kerusakan)) DESC";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $diagnoses = [];
            while ($row = $result->fetch_assoc()) {
                $persentase = ($row['jumlah_gejala_cocok'] / $row['total_gejala_kerusakan']) * 100;
                $diagnoses[] = [
                    'kode_kerusakan' => $row['kode_kerusakan'],
                    'nama_kerusakan' => $row['nama_kerusakan'],
                    'solusi' => $row['solusi'],
                    'persentase' => round($persentase, 2),
                    'gejala_cocok' => $row['jumlah_gejala_cocok'],
                    'total_gejala' => $row['total_gejala_kerusakan']
                ];
            }
            
            return ['success' => true, 'data' => $diagnoses];
        } else {
            return ['success' => false, 'message' => 'Tidak ditemukan kerusakan yang sesuai dengan gejala yang dipilih'];
        }
    }
    
    public function getGejala() {
        $sql = "SELECT * FROM gejala ORDER BY kode_gejala";
        $result = $this->db->query($sql);
        
        $gejala = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gejala[] = $row;
            }
        }
        
        return $gejala;
    }
    
    public function saveRiwayat($nama, $email, $gejala, $hasil) {
        $gejala_json = json_encode($gejala);
        $hasil_json = json_encode($hasil);
        
        $stmt = $this->db->prepare("INSERT INTO riwayat_diagnosis (nama_user, email, gejala_terpilih, hasil_diagnosis) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $gejala_json, $hasil_json);
        
        return $stmt->execute();
    }

    // Fixed getRiwayat method with pagination support
    public function getRiwayat($limit = 10, $offset = 0) {
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM riwayat_diagnosis";
        $count_result = $this->db->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
        
        // Get paginated data
        $sql = "SELECT 
                    id,
                    nama_user as nama,
                    email,
                    gejala_terpilih,
                    hasil_diagnosis,
                    created_at as tanggal_diagnosis
                FROM riwayat_diagnosis 
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = $this->db->query($sql);
        
        $riwayat = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gejala_data = json_decode($row['gejala_terpilih'], true);
                $hasil_data = json_decode($row['hasil_diagnosis'], true);
                
                // Extract the main diagnosis result
                $hasil_diagnosis = '';
                $tingkat_keyakinan = 0;
                
                if (is_array($hasil_data) && !empty($hasil_data)) {
                    // If hasil_diagnosis contains array of diagnoses, get the first one
                    if (isset($hasil_data[0])) {
                        $hasil_diagnosis = $hasil_data[0]['nama_kerusakan'] ?? '';
                        $tingkat_keyakinan = $hasil_data[0]['persentase'] ?? 0;
                    } else {
                        // If it's a single diagnosis object
                        $hasil_diagnosis = $hasil_data['nama_kerusakan'] ?? '';
                        $tingkat_keyakinan = $hasil_data['persentase'] ?? 0;
                    }
                }
                
                $riwayat[] = [
                    'id' => $row['id'],
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'gejala_terpilih' => $gejala_data,
                    'hasil_diagnosis' => $hasil_diagnosis,
                    'tingkat_keyakinan' => $tingkat_keyakinan,
                    'tanggal_diagnosis' => $row['tanggal_diagnosis'],
                    'hasil_lengkap' => $hasil_data
                ];
            }
        }
        
        return [
            'data' => $riwayat,
            'total' => $total
        ];
    }

    public function getRiwayatById($id) {
        $stmt = $this->db->prepare("SELECT 
                                        id,
                                        nama_user as nama,
                                        email,
                                        gejala_terpilih,
                                        hasil_diagnosis,
                                        created_at as tanggal_diagnosis
                                    FROM riwayat_diagnosis 
                                    WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $gejala_data = json_decode($row['gejala_terpilih'], true);
            $hasil_data = json_decode($row['hasil_diagnosis'], true);
            
            // Extract the main diagnosis result
            $hasil_diagnosis = '';
            $tingkat_keyakinan = 0;
            
            if (is_array($hasil_data) && !empty($hasil_data)) {
                // If hasil_diagnosis contains array of diagnoses, get the first one
                if (isset($hasil_data[0])) {
                    $hasil_diagnosis = $hasil_data[0]['nama_kerusakan'] ?? '';
                    $tingkat_keyakinan = $hasil_data[0]['persentase'] ?? 0;
                } else {
                    // If it's a single diagnosis object
                    $hasil_diagnosis = $hasil_data['nama_kerusakan'] ?? '';
                    $tingkat_keyakinan = $hasil_data['persentase'] ?? 0;
                }
            }
            
            return [
                'id' => $row['id'],
                'nama' => $row['nama'],
                'email' => $row['email'],
                'gejala_terpilih' => $gejala_data,
                'hasil_diagnosis' => $hasil_diagnosis,
                'tingkat_keyakinan' => $tingkat_keyakinan,
                'tanggal_diagnosis' => $row['tanggal_diagnosis'],
                'hasil_lengkap' => $hasil_data
            ];
        } else {
            return null; // No record found
        }
    }

    public function getRiwayatDetail($id) {
        $stmt = $this->db->prepare("SELECT 
                                        id,
                                        nama_user as nama,
                                        email,
                                        gejala_terpilih,
                                        hasil_diagnosis,
                                        created_at as tanggal_diagnosis
                                    FROM riwayat_diagnosis 
                                    WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $gejala_data = json_decode($row['gejala_terpilih'], true);
            $hasil_data = json_decode($row['hasil_diagnosis'], true);
            
            // Extract the main diagnosis result
            $hasil_diagnosis = '';
            $tingkat_keyakinan = 0;
            
            if (is_array($hasil_data) && !empty($hasil_data)) {
                // If hasil_diagnosis contains array of diagnoses, get the first one
                if (isset($hasil_data[0])) {
                    $hasil_diagnosis = $hasil_data[0]['nama_kerusakan'] ?? '';
                    $tingkat_keyakinan = $hasil_data[0]['persentase'] ?? 0;
                } else {
                    // If it's a single diagnosis object
                    $hasil_diagnosis = $hasil_data['nama_kerusakan'] ?? '';
                    $tingkat_keyakinan = $hasil_data['persentase'] ?? 0;
                }
            }
            
            return [
                'id' => $row['id'],
                'nama' => $row['nama'],
                'email' => $row['email'],
                'gejala_terpilih' => $gejala_data,
                'hasil_diagnosis' => $hasil_diagnosis,
                'tingkat_keyakinan' => $tingkat_keyakinan,
                'tanggal_diagnosis' => $row['tanggal_diagnosis'],
                'hasil_lengkap' => $hasil_data
            ];
        }
        
        return null;
    }
    
    // New method for search functionality
    public function searchRiwayat($search, $limit = 10, $offset = 0) {
        $search_param = '%' . $search . '%';
        
        // Get total count for search
        $count_sql = "SELECT COUNT(*) as total FROM riwayat_diagnosis 
                      WHERE nama_user LIKE ? OR email LIKE ? OR hasil_diagnosis LIKE ?";
        $count_stmt = $this->db->prepare($count_sql);
        $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
        
        // Get paginated search results
        $sql = "SELECT 
                    id,
                    nama_user as nama,
                    email,
                    gejala_terpilih,
                    hasil_diagnosis,
                    created_at as tanggal_diagnosis
                FROM riwayat_diagnosis 
                WHERE nama_user LIKE ? OR email LIKE ? OR hasil_diagnosis LIKE ?
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $riwayat = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gejala_data = json_decode($row['gejala_terpilih'], true);
                $hasil_data = json_decode($row['hasil_diagnosis'], true);
                
                // Extract the main diagnosis result
                $hasil_diagnosis = '';
                $tingkat_keyakinan = 0;
                
                if (is_array($hasil_data) && !empty($hasil_data)) {
                    // If hasil_diagnosis contains array of diagnoses, get the first one
                    if (isset($hasil_data[0])) {
                        $hasil_diagnosis = $hasil_data[0]['nama_kerusakan'] ?? '';
                        $tingkat_keyakinan = $hasil_data[0]['persentase'] ?? 0;
                    } else {
                        // If it's a single diagnosis object
                        $hasil_diagnosis = $hasil_data['nama_kerusakan'] ?? '';
                        $tingkat_keyakinan = $hasil_data['persentase'] ?? 0;
                    }
                }
                
                $riwayat[] = [
                    'id' => $row['id'],
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'gejala_terpilih' => $gejala_data,
                    'hasil_diagnosis' => $hasil_diagnosis,
                    'tingkat_keyakinan' => $tingkat_keyakinan,
                    'tanggal_diagnosis' => $row['tanggal_diagnosis'],
                    'hasil_lengkap' => $hasil_data
                ];
            }
        }
        
        return [
            'data' => $riwayat,
            'total' => $total
        ];
    }
}
?>