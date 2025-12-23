<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\PasswordHelper;

class AuthController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $table;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        $this->table = 'users';
    }

    // db Supabase
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'Supabase CURL Error: ' . $error);
            return ['error' => $error];
        }

        if ($httpCode >= 400) {
            log_message('error', 'Supabase HTTP Error ' . $httpCode . ': ' . $response);
            return [
                'error' => 'HTTP Error ' . $httpCode,
                'response' => $response
            ];
        }

        return json_decode($response, true);
    }


    // Login Page
    public function login()
    {
        if (session()->get('role')) {
            return redirect()->to('/');
        }

        return view('login', [
            'title' => 'Login',
            'bodyClass' => 'login-page'
        ]);
    }

    //  Attempt Login
    public function attemptLogin()
    {
        $username = strtolower(trim($this->request->getPost('username')));
        $password = $this->request->getPost('password');

        log_message('info', 'Login attempt - Username: ' . $username);

        // Validasi input
        if (empty($username) || empty($password)) {
            return redirect()->back()->with('error', 'Username dan password harus diisi');
        }

        // CEK USER - include admin role
        $roles = ['admin', 'murid', 'guru', 'pustakawan', 'kepala sekolah', 'umum'];
        $allUsers = [];

        foreach ($roles as $role) {
            $endpoint = $this->table . '?role=eq.' . urlencode($role);
            $users = $this->supabaseRequest('GET', $endpoint);
            
            if (!isset($users['error']) && is_array($users)) {
                $allUsers = array_merge($allUsers, $users);
            }
        }

        log_message('info', 'Total users found: ' . count($allUsers));

        // CARI USER
        foreach ($allUsers as $user) {
            // Ambil nama depan saja (sebelum spasi pertama)
            $firstName = strtolower(explode(' ', $user['nama'])[0]);
            
            // Cek apakah username cocok dengan nama depan DAN password cocok
            if ($firstName === $username && PasswordHelper::verifyPassword($password, $user['password'])) {
                session()->set([
                    'role' => $user['role'],
                    'name' => $user['nama'],
                    'user_id' => $user['id'],
                    'nisn' => $user['nisn'] ?? null,
                    'nip' => $user['nip'] ?? null,
                    'class_id' => $user['class_id'] ?? null,
                    'trust_score' => $user['trust_score'] ?? $user['trust_score'] ?? 100
                ]);
                
                log_message('info', 'Login success - User: ' . $user['nama'] . ' (Role: ' . $user['role'] . ')');
                return redirect()->to('/');
            }
        }

        // LOGIN GAGAL
        log_message('warning', 'Login failed - Username: ' . $username);
        return redirect()->back()->with('error', 'Username atau password salah');
    }

    // Logout
    public function logout()
    {
        $name = session()->get('name');
        log_message('info', 'Logout - User: ' . ($name ?? 'Unknown'));
        
        session()->remove(['role', 'name', 'user_id', 'nisn', 'nip', 'trust_score']);
        session()->destroy();
        
        return redirect()->to('login')->with('success', 'Berhasil logout');
    }

    // Verify User (untuk reset password)
    public function verifyUser()
    {
        $request = $this->request->getJSON(true);
        $nama = $request['nama'] ?? '';
        $id_number = $request['id_number'] ?? '';

        log_message('info', 'Verify User - Nama: ' . $nama . ', ID: ' . $id_number);

        if (empty($nama) || empty($id_number)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama dan ID wajib diisi'
            ]);
        }

        // Ambil semua user
        $endpoint = $this->table . '?order=id.desc';
        $allUsers = $this->supabaseRequest('GET', $endpoint);

        if (isset($allUsers['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data user'
            ]);
        }

        // Cari user yang cocok
        foreach ($allUsers as $user) {
            $matchNisn = !empty($user['nisn']) && $user['nisn'] === $id_number;
            $matchNip = !empty($user['nip']) && $user['nip'] === $id_number;
            
            if ($user['nama'] === $nama && ($matchNisn || $matchNip)) {
                log_message('info', 'User verified - ID: ' . $user['id']);
                return $this->response->setJSON([
                    'success' => true,
                    'role' => $user['role'],
                    'id' => $user['id'],
                    'nama' => $user['nama']
                ]);
            }
        }

        log_message('warning', 'User verification failed - Nama: ' . $nama);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Data tidak ditemukan. Pastikan Nama dan NISN/NIP sudah benar.'
        ]);
    }

    // Reset Password
    public function resetPassword()
    {
        $request = $this->request->getJSON(true);
        $nama = $request['nama'] ?? '';
        $id_number = $request['id_number'] ?? '';
        $new_password = $request['new_password'] ?? '';

        log_message('info', 'Reset Password - Nama: ' . $nama . ', ID: ' . $id_number);

        if (empty($nama) || empty($id_number) || empty($new_password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Semua field harus diisi'
            ]);
        }

        //password minimal 4 karakter
        if (strlen($new_password) < 4) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Password minimal 4 karakter'
            ]);
        }

        $endpoint = $this->table . '?order=id.desc';
        $allUsers = $this->supabaseRequest('GET', $endpoint);

        if (isset($allUsers['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengambil data user'
            ]);
        }

        // Cari user yang cocok
        foreach ($allUsers as $user) {
            $matchNisn = !empty($user['nisn']) && $user['nisn'] === $id_number;
            $matchNip = !empty($user['nip']) && $user['nip'] === $id_number;
            
            if ($user['nama'] === $nama && ($matchNisn || $matchNip)) {
                // Update password
                $updateEndpoint = $this->table . '?id=eq.' . $user['id'];
                $updateData = [
                    'password' => PasswordHelper::hashPassword($new_password),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $result = $this->supabaseRequest('PATCH', $updateEndpoint, $updateData);
                
                if (isset($result['error'])) {
                    log_message('error', 'Reset password failed - Error: ' . json_encode($result));
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal mengubah password'
                    ]);
                }
                
                log_message('info', 'Password reset success - User ID: ' . $user['id']);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password berhasil diubah'
                ]);
            }
        }

        log_message('warning', 'Reset password failed - User not found');
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ]);
    }

    // Reset Password Page
    public function resetPasswordPage()
    {
        return view('forgot_password', [
            'title' => 'Reset Password',
            'bodyClass' => 'login-page'
        ]);
    }
}