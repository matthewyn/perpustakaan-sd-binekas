<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\PasswordHelper;
use App\Traits\UserTrait;

class UserController extends Controller
{
    use UserTrait;

    private $supabaseUrl;
    private $supabaseKey;
    private $table;
    private $cache;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_SERVICE_ROLE_KEY") ?: getenv("SUPABASE_API_KEY");
        $this->table = "users";
        $this->cache = \Config\Services::cache();

        log_message("info", "=== UserController Initialized ===");
        log_message(
            "info",
            "SUPABASE_URL: " . ($this->supabaseUrl ?: "NOT SET")
        );
        log_message(
            "info",
            "Supabase server key length: " . strlen($this->supabaseKey ?: "")
        );
    }

    private function supabaseRequest($method, $endpoint, $data = null, $queryParams = [])
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message("error", "Supabase credentials not configured");
            return ["error" => "Supabase credentials not configured"];
        }

        $url = rtrim($this->supabaseUrl, "/") . "/rest/v1/" . $endpoint;
        if (!empty($queryParams)) {
            $url .= "?" . http_build_query($queryParams);
        }

        $headers = [
            "apikey: " . $this->supabaseKey,
            "Authorization: Bearer " . $this->supabaseKey,
            "Content-Type: application/json",
            "Accept: application/json",
            "Prefer: return=representation",
        ];

        log_message("info", "Supabase Request: " . $method . " " . $url);

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
            log_message("info", "Request Body: " . $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message("info", "Response Code: " . $httpCode);
        log_message("info", "Response Body: " . $response);

        if ($error) {
            log_message("error", "Supabase CURL Error: " . $error);
            return ["error" => $error];
        }

        if ($httpCode >= 400) {
            $responseData = json_decode($response, true);
            log_message(
                "error",
                "Supabase HTTP Error " . $httpCode . ": " . $response
            );
            return [
                "error" => "HTTP Error " . $httpCode,
                "response" => $response,
                "details" => $responseData,
            ];
        }

        return json_decode($response, true);
    }

    private function getNextUserId()
    {
        $result = $this->supabaseRequest(
            "GET",
            $this->table . "?select=id&order=id.desc&limit=1"
        );

        if (isset($result["error"]) || empty($result)) {
            return 1;
        }

        return $result[0]["id"] + 1;
    }

    public function index()
    {
        $classes = $this->supabaseRequest(
            "GET",
            "classes?order=nama_kelas.asc"
        );

        return view("user", [
            "classes" => isset($classes["error"]) ? [] : $classes,
        ]);
    }

    public function list($role = null, $queryProjection = "")
    {
        $params = null;
        if ($role && $role == "murid") {
            if (!empty($queryProjection)) {
                $params = $queryProjection;
            } else {
            $params = 'select=id,nama,nisn,uid,num_borrows,class_id,max_borrow,trust_score';
            }
        } else {
            if (!empty($queryProjection)) {
                $params = $queryProjection;
            } else {
                $params = 'select=id,nama,nip,jabatan,uid,class_id';
            }
        }
        
        if ($role) {
            $params .= '&role=eq.' . urlencode($role);
        }
        
        $endpoint = 'users_view?' . $params . '&is_active=eq.true&order=id.desc';
        $data = $this->supabaseRequest('GET', $endpoint);
        if (is_array($data) && !isset($data["error"])) {
            $data = array_map(fn($user) => $this->normalizeUserRow($user), $data);
        }

        return $this->response->setJSON([
            'success' => !isset($data['error']),
            'users'   => $data ?? [],
        ]);
    }

    public function addUser()
    {
        log_message(
            "info",
            "Add User - All POST: " . json_encode($this->request->getPost())
        );

        $nama = $this->request->getPost("nama");
        $nisn = $this->request->getPost("nisn");
        $classId = $this->request->getPost("class_id");
        $maxBorrow = $this->request->getPost("maxBorrow");
        $isFreezed = $this->request->getPost("isFreezed");
        $uid = trim($this->request->getPost("uid") ?? "");

        if (
            empty($nama) ||
            empty($nisn) ||
            empty($maxBorrow) ||
            empty($classId)
        ) {
            return $this->response->setJSON([
                "success" => false,
                "message" =>
                    "Data tidak lengkap. Nama, NISN, Kelas, dan Maksimal Peminjaman wajib diisi.",
                "data" => null,
            ]);
        }

        if (!ctype_digit((string) $nisn) || !ctype_digit((string) $maxBorrow)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "NISN dan Maksimal Peminjaman harus berupa angka.",
                "data" => null,
            ]);
        }

        $data = [
            "nama" => $nama,
            "max_borrow" => (int) $maxBorrow,
            "role" => "murid",
            "uid" => $uid !== "" ? $uid : null,
            "trust_score" => 0.0,
            "is_freezed" => (int) $isFreezed,
            "password" => PasswordHelper::hashPassword($nisn),
        ];

        log_message("info", "Add User - Data to insert: " . json_encode($data));

        $result = $this->createNormalizedUser($data, [
            "nisn" => $nisn,
            "class_id" => (int) $classId,
        ]);

        log_message(
            "info",
            "Add User - Supabase Response: " . json_encode($result)
        );

        $this->cache->delete('class_data_' . $classId);
        $this->invalidateUserCache([
            'class_id' => 'eq.' . $classId,
            'role'     => 'eq.murid',
            'select'   => 'id,nama',
        ]);
        $this->invalidateUserCache(['select' => 'id,nama,class_id']);

        return $this->response->setJSON([
            "success" => !isset($result["error"]),
            "message" => isset($result["error"])
                ? "Gagal menambahkan siswa: " .
                    json_encode($result["details"] ?? $result["error"])
                : "Berhasil menambahkan siswa",
            "data" => $result,
        ]);
    }

    public function updateUser($id = null)
    {
        log_message(
            "info",
            "Update User ID: " .
                $id .
                " POST Data: " .
                json_encode($this->request->getPost())
        );

        $classId = $this->request->getPost("class_id");
        $trustScore = $this->request->getPost("trust_score");
        $isFreezed = $this->request->getPost("isFreezed");
        $nisn = $this->request->getPost("nisn");
        $maxBorrow = $this->request->getPost("maxBorrow");
        $uid = trim($this->request->getPost("uid") ?? "");
        if (!ctype_digit((string) $nisn) || !ctype_digit((string) $maxBorrow)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "NISN dan Maksimal Peminjaman harus berupa angka.",
                "data" => null,
            ]);
        }

        if (
            !empty($trustScore) &&
            (!is_numeric($trustScore) || $trustScore < 0 || $trustScore > 100)
        ) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Trust Score harus berupa angka antara 0 dan 100.",
                "data" => null,
            ]);
        }

        $data = [
            "nama" => $this->request->getPost("nama"),
            "uid" => $uid !== "" ? $uid : null,
            "max_borrow" => (int) $this->request->getPost("maxBorrow"),
            "trust_score" => (float) $trustScore,
            "is_freezed" => (int) $isFreezed,
            "password" => PasswordHelper::hashPassword(
                $this->request->getPost("nisn")
            ),
            "updated_at" => date("Y-m-d H:i:s"),
        ];

        $result = $this->updateNormalizedStudent((int) $id, $data, [
            "nisn" => $this->request->getPost("nisn"),
            "class_id" => (int) $classId,
        ]);

        $this->cache->delete('class_data_' . $classId);
        $this->invalidateUserCache([
            'class_id' => 'eq.' . $classId,
            'role'     => 'eq.murid',
            'select'   => 'id,nama',
        ]);
        $this->invalidateUserCache(['select' => 'id,nama,class_id']);
        log_message("info", "Update User Response: " . json_encode($result));

        return $this->response->setJSON([
            "success" => !isset($result["error"]),
            "message" => isset($result["error"])
                ? "Gagal mengubah siswa"
                : "Berhasil mengubah siswa",
            "data" => $result,
        ]);
    }

    public function deleteUser($id = null)
    {
        if (empty($id)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "ID user tidak valid",
            ]);
        }

        $result = $this->deleteNormalizedUser((int) $id);

        if (isset($result["error"])) {
            log_message("error", "Delete User Response: " . json_encode($result));
            return $this->response->setJSON([
                "success" => false,
                "message" => $result["message"] ?? "Gagal menghapus user",
                "data" => $result,
            ]);
        }

        if (!empty($result["archived_class_id"])) {
            $this->cache->delete("class_data_" . $result["archived_class_id"]);
        }
        $this->invalidateUserCache([]);

        return $this->response->setJSON([
            "success" => true,
            "message" => "User berhasil dihapus",
            "data" => $result,
        ]);
    }

    public function resetTrustScore()
    {
        $userResult = $this->supabaseRequest(
            "PATCH",
            "users?role=eq.murid",
            [
                "trust_score" => 0.0,
            ]
        );

        $transactionResult = $this->supabaseRequest(
            "PATCH",
            "transactions?is_finished_semester=eq.false",
            ["is_finished_semester" => true]
        );

        $userError = isset($userResult["error"]);
        $transactionError = isset($transactionResult["error"]);

        if ($userError || $transactionError) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Gagal mereset data. Silakan coba lagi.",
                "data" => [
                    "users" => $userResult,
                    "transactions" => $transactionResult,
                ],
            ]);
        }

        $this->invalidateUserCache(['select' => 'id,nama,class_id']);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Berhasil mereset trust score, jumlah peminjaman, dan status semester.",
            "data" => [
                "users" => $userResult,
                "transactions" => $transactionResult,
            ],
        ]);
    }

    public function addGuru()
    {
        log_message(
            "info",
            "Add Guru POST Data: " . json_encode($this->request->getPost())
        );

        $nama = $this->request->getPost("namaGuru");
        $nip = $this->request->getPost("nip");
        $jabatan = $this->request->getPost("jabatan");
        $uid = trim($this->request->getPost("uid") ?? "");
        $classId = $this->request->getPost("class_id");

        if (empty($nama) || empty($nip) || empty($jabatan) || empty($classId)) {
            return $this->response->setJSON([
                "success" => false,
                "message" =>
                    "Data tidak lengkap. Nama, NIP, Jabatan, dan Kelas wajib diisi.",
                "data" => null,
            ]);
        }

        if (!ctype_digit((string) $nip)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "NIP harus berupa angka.",
                "data" => null,
            ]);
        }

        $data = [
            "nama" => $nama,
            "role" => "guru",
            "uid" => $uid !== "" ? $uid : null,
            "password" => PasswordHelper::hashPassword($nip),
        ];

        $result = $this->createNormalizedUser($data, null, [
            "nip" => $nip,
            "jabatan" => $jabatan,
            "class_id" => (int) $classId,
        ]);

        log_message("info", "Add Guru Response: " . json_encode($result));

        return $this->response->setJSON([
            "success" => !isset($result["error"]),
            "message" => isset($result["error"])
                ? "Gagal menambahkan guru"
                : "Berhasil menambahkan guru",
            "data" => $result,
        ]);
    }

    public function updateGuru($id = null)
    {
        log_message(
            "info",
            "Update Guru ID: " .
                $id .
                " POST Data: " .
                json_encode($this->request->getPost())
        );

        $namaUbah = $this->request->getPost("namaGuruUbah");
        $nipUbah = $this->request->getPost("nipUbah");
        $jabatanUbah = $this->request->getPost("jabatanUbah");
        $classIdUbah = $this->request->getPost("classIdUbah");
        $uid = trim($this->request->getPost("uid") ?? "");

        if (empty($namaUbah) || empty($nipUbah) || empty($jabatanUbah)) {
            return $this->response->setJSON([
                "success" => false,
                "message" =>
                    "Data tidak lengkap. Nama, NIP, Jabatan, dan Kelas wajib diisi.",
                "data" => null,
            ]);
        }

        if (
            !ctype_digit((string) $nipUbah) ||
            !ctype_digit((string) $classIdUbah)
        ) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "NIP dan ID Kelas harus berupa angka.",
                "data" => null,
            ]);
        }

        $data = [
            "nama" => $namaUbah,
            "uid" => $uid !== "" ? $uid : null,
            "password" => PasswordHelper::hashPassword($nipUbah),
            "updated_at" => date("Y-m-d H:i:s"),
        ];

        $result = $this->updateNormalizedTeacher((int) $id, $data, [
            "nip" => $nipUbah,
            "jabatan" => $jabatanUbah,
            "class_id" => (int) $classIdUbah,
        ]);

        log_message("info", "Update Guru Response: " . json_encode($result));

        return $this->response->setJSON([
            "success" => !isset($result["error"]),
            "message" => isset($result["error"])
                ? "Gagal mengubah guru"
                : "Berhasil mengubah guru",
            "data" => $result,
        ]);
    }
}
