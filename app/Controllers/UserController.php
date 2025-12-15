<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\PasswordHelper;

class UserController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $table;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        $this->table = 'users';
        
        log_message('info', '=== UserController Initialized ===');
        log_message('info', 'SUPABASE_URL: ' . ($this->supabaseUrl ?: 'NOT SET'));
        log_message('info', 'SUPABASE_API_KEY length: ' . strlen($this->supabaseKey ?: ''));
    }

    private function supabaseRequest($method, $endpoint, $data = null)
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message('error', 'Supabase credentials not configured');
            return ['error' => 'Supabase credentials not configured'];
        }

        $url = rtrim($this->supabaseUrl, '/') . '/rest/v1/' . $endpoint;

        $headers = [
            'apikey: ' . $this->supabaseKey,
            'Authorization: Bearer ' . $this->supabaseKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'Prefer: return=representation'
        ];

        log_message('info', 'Supabase Request: ' . $method . ' ' . $url);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        if ($data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            log_message('info', 'Request Body: ' . $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message('info', 'Response Code: ' . $httpCode);
        log_message('info', 'Response Body: ' . $response);

        if ($error) {
            log_message('error', 'Supabase CURL Error: ' . $error);
            return ['error' => $error];
        }

        if ($httpCode >= 400) {
            $responseData = json_decode($response, true);
            log_message('error', 'Supabase HTTP Error ' . $httpCode . ': ' . $response);
            return [
                'error' => 'HTTP Error ' . $httpCode,
                'response' => $response,
                'details' => $responseData
            ];
        }

        return json_decode($response, true);
    }

    private function getNextUserId()
    {
        // Get the maximum ID from users table
        $result = $this->supabaseRequest('GET', $this->table . '?select=id&order=id.desc&limit=1');
        
        if (isset($result['error']) || empty($result)) {
            // If no users exist, start from 1
            return 1;
        }
        
        return $result[0]['id'] + 1;
    }

    public function index()
    {
        // Fetch all classes for dropdown
        $classes = $this->supabaseRequest('GET', 'classes?order=nama_kelas.asc');
        
        return view('user', [
            'classes' => isset($classes['error']) ? [] : $classes
        ]);
    }

    public function list($role = null)
    {
        $endpoint = $this->table;
        if ($role) {
            $endpoint .= '?role=eq.' . urlencode($role) . '&order=id.desc';
        } else {
            $endpoint .= '?order=id.desc';
        }

        $data = $this->supabaseRequest('GET', $endpoint);

        return $this->response->setJSON([
            'success' => !isset($data['error']),
            'users' => $data ?? []
        ]);
    }

    public function addUser()
    {
        log_message('info', 'Add User - All POST: ' . json_encode($this->request->getPost()));
        
        $nama = $this->request->getPost('nama');
        $nisn = $this->request->getPost('nisn');
        $classId = $this->request->getPost('class_id');
        $maxBorrow = $this->request->getPost('maxBorrow');
        
        if (empty($nama) || empty($nisn) || empty($maxBorrow)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap. Nama, NISN, dan Maksimal Peminjaman wajib diisi.',
                'data' => null
            ]);
        }

        // Generate next ID
        $nextId = $this->getNextUserId();

        $data = [
            'id' => $nextId,
            'nama' => $nama,
            'nisn' => $nisn,
            'class_id' => !empty($classId) ? (int)$classId : null,
            'maxBorrow' => (int)$maxBorrow,
            'role' => 'murid',
            'trust_score' => 100.00,
            'password' => PasswordHelper::hashPassword($nisn),
        ];

        log_message('info', 'Add User - Data to insert: ' . json_encode($data));

        $result = $this->supabaseRequest('POST', $this->table, $data);

        log_message('info', 'Add User - Supabase Response: ' . json_encode($result));

        return $this->response->setJSON([
            'success' => !isset($result['error']),
            'message' => isset($result['error']) 
                ? ('Gagal menambahkan siswa: ' . json_encode($result['details'] ?? $result['error'])) 
                : 'Berhasil menambahkan siswa',
            'data' => $result
        ]);
    }

    public function updateUser($id = null)
    {
        log_message('info', 'Update User ID: ' . $id . ' POST Data: ' . json_encode($this->request->getPost()));

        $classId = $this->request->getPost('class_id');
        
        $data = [
            'nama' => $this->request->getPost('nama'),
            'nisn' => $this->request->getPost('nisn'),
            'class_id' => !empty($classId) ? (int)$classId : null,
            'maxBorrow' => (int)$this->request->getPost('maxBorrow'),
            'password' => PasswordHelper::hashPassword($this->request->getPost('nisn')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['nama']) || empty($data['nisn']) || empty($data['maxBorrow'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'data' => null
            ]);
        }

        $endpoint = $this->table . '?id=eq.' . $id;
        $result = $this->supabaseRequest('PATCH', $endpoint, $data);

        log_message('info', 'Update User Response: ' . json_encode($result));

        return $this->response->setJSON([
            'success' => !isset($result['error']),
            'message' => isset($result['error']) ? 'Gagal mengubah siswa' : 'Berhasil mengubah siswa',
            'data' => $result
        ]);
    }

    public function addGuru()
    {
        log_message('info', 'Add Guru POST Data: ' . json_encode($this->request->getPost()));

        // Generate next ID
        $nextId = $this->getNextUserId();

        $classId = $this->request->getPost('class_id');
        
        $data = [
            'id' => $nextId,
            'nama' => $this->request->getPost('namaGuru'),
            'nip' => $this->request->getPost('nip'),
            'jabatan' => $this->request->getPost('jabatan'),
            'class_id' => !empty($classId) ? (int)$classId : null,
            'role' => 'guru',
            'password' => PasswordHelper::hashPassword($this->request->getPost('nip')),
        ];

        if (empty($data['nama']) || empty($data['nip']) || empty($data['jabatan'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'data' => null
            ]);
        }

        $result = $this->supabaseRequest('POST', $this->table, $data);

        log_message('info', 'Add Guru Response: ' . json_encode($result));

        return $this->response->setJSON([
            'success' => !isset($result['error']),
            'message' => isset($result['error']) 
                ? 'Gagal menambahkan guru' 
                : 'Berhasil menambahkan guru',
            'data' => $result
        ]);
    }

    public function updateGuru($id = null)
    {
        log_message('info', 'Update Guru ID: ' . $id . ' POST Data: ' . json_encode($this->request->getPost()));

        $classIdUbah = $this->request->getPost('classIdUbah');
        
        $data = [
            'nama' => $this->request->getPost('namaGuruUbah'),
            'nip' => $this->request->getPost('nipUbah'),
            'jabatan' => $this->request->getPost('jabatanUbah'),
            'class_id' => !empty($classIdUbah) ? (int)$classIdUbah : null,
            'password' => PasswordHelper::hashPassword($this->request->getPost('nipUbah')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['nama']) || empty($data['nip']) || empty($data['jabatan'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'data' => null
            ]);
        }

        $endpoint = $this->table . '?id=eq.' . $id;
        $result = $this->supabaseRequest('PATCH', $endpoint, $data);

        log_message('info', 'Update Guru Response: ' . json_encode($result));

        return $this->response->setJSON([
            'success' => !isset($result['error']),
            'message' => isset($result['error']) ? 'Gagal mengubah guru' : 'Berhasil mengubah guru',
            'data' => $result
        ]);
    }   
}