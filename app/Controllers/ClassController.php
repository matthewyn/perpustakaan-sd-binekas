<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\UserTrait;

class ClassController extends Controller
{
    use UserTrait;
    private $supabaseUrl;
    private $supabaseKey;
    private $classTable = "classes";
    private $cache;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_API_KEY");
        $this->cache = \Config\Services::cache();

        log_message("info", "=== ClassController Initialized ===");
    }

    private function supabaseRequest(
        $method,
        $endpoint,
        $data = null,
        $queryParams = []
    ) {
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

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
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
            log_message("error", "cURL Error: " . $error);
            return ["error" => $error];
        }

        if ($httpCode >= 400) {
            log_message("error", "HTTP Error " . $httpCode . ": " . $response);
            return [
                "error" => "HTTP Error " . $httpCode,
                "response" => $response,
            ];
        }

        return json_decode($response, true);
    }

    public function index()
    {
        // Get all classes with student and book counts
        $classes = $this->supabaseRequest("GET", $this->classTable, null, [
            "order" => "created_at.desc",
        ]);

        if (isset($classes["error"])) {
            $classes = [];
        }

        // Enrich classes with counts
        foreach ($classes as &$class) {
            // Count students in this class
            $students = $this->supabaseRequest("GET", "users", null, [
                "class_id" => "eq." . $class["id"],
                "role" => "eq.murid",
                "select" => "id",
            ]);
            $class["student_count"] = isset($students["error"])
                ? 0
                : count($students);
        }

        return view("management_class", [
            "classes" => $classes,
        ]);
    }

    public function list()
    {
        $classes = $this->supabaseRequest("GET", $this->classTable, null, [
            "order" => "created_at.desc",
        ]);

        return $this->response->setJSON([
            "success" => !isset($classes["error"]),
            "classes" => $classes ?? [],
        ]);
    }

    public function add()
    {
        log_message(
            "info",
            "Add Class POST Data: " . json_encode($this->request->getPost())
        );

        $namaKelas = $this->request->getPost("nama_kelas");

        if (empty($namaKelas)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Nama kelas harus diisi",
            ]);
        }

        $data = [
            "nama_kelas" => $namaKelas,
            "created_at" => date("Y-m-d H:i:s"),
        ];

        $result = $this->supabaseRequest("POST", $this->classTable, $data);

        log_message("info", "Add Class Response: " . json_encode($result));

        if (isset($result["error"])) {
            return $this->response->setJSON([
                "success" => false,
                "message" =>
                    "Gagal menambahkan kelas: " .
                    ($result["response"] ?? "Unknown error"),
            ]);
        }

        return $this->response->setJSON([
            "success" => true,
            "message" => "Kelas berhasil ditambahkan",
            "data" => $result,
        ]);
    }

    public function update($id = null)
    {
        log_message(
            "info",
            "Update Class ID: " .
                $id .
                " POST Data: " .
                json_encode($this->request->getPost())
        );

        if (empty($id)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "ID kelas tidak valid",
            ]);
        }

        $existingClass = $this->supabaseRequest(
            "GET",
            $this->classTable,
            null,
            [
                "id" => "eq." . $id,
                "limit" => 1,
            ]
        );

        if (isset($existingClass["error"]) || empty($existingClass)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Kelas tidak ditemukan",
            ]);
        }

        // Handle name update
        $namaKelas = $this->request->getPost("nama_kelas");
        if (!empty($namaKelas)) {
            $updateData = [
                "nama_kelas" => $namaKelas,
                "updated_at" => date("Y-m-d H:i:s"),
            ];

            $endpoint = $this->classTable . "?id=eq." . $id;
            $result = $this->supabaseRequest("PATCH", $endpoint, $updateData);

            if (isset($result["error"])) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Gagal mengupdate nama kelas",
                ]);
            }
        }

        // Handle student assignments
        $studentIds = $this->request->getPost("student_ids");
        if ($studentIds !== null) {
            if (is_string($studentIds)) {
                $studentIds = json_decode($studentIds, true);
            }
            $studentIds = is_array($studentIds) ? $studentIds : [];

            // Get current students in this class
            $currentStudents = $this->supabaseRequest("GET", "users", null, [
                "class_id" => "eq." . $id,
                "role" => "eq.murid",
                "select" => "id",
            ]);
            $currentStudentIds = isset($currentStudents["error"])
                ? []
                : array_column($currentStudents, "id");

            // Students to remove (set class_id to null)
            $toRemove = array_diff($currentStudentIds, $studentIds);
            foreach ($toRemove as $userId) {
                $this->supabaseRequest("PATCH", "users?id=eq." . $userId, [
                    "class_id" => null,
                ]);
            }

            // Students to add (set class_id to this class)
            $toAdd = array_diff($studentIds, $currentStudentIds);
            foreach ($toAdd as $userId) {
                $this->supabaseRequest("PATCH", "users?id=eq." . $userId, [
                    "class_id" => (int) $id,
                ]);
            }
        }

        $this->cache->delete("class_data_" . $id);
        $this->invalidateUserCache([
            "class_id" => "eq." . $id,
            "role" => "eq.murid",
            "select" => "id,nama",
        ]);
        $this->invalidateUserCache([
            "class_id" => "eq." . $id,
            "role" => "eq.murid",
            "select" => "id,nama,nisn",
        ]);
        $this->invalidateUserCache(['select' => 'id,nama,class_id']);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Kelas berhasil diupdate",
        ]);
    }

    public function delete($id = null)
    {
        log_message("info", "Delete Class ID: " . $id);

        if (empty($id)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "ID kelas tidak valid",
            ]);
        }

        $class = $this->supabaseRequest("GET", $this->classTable, null, [
            "id" => "eq." . $id,
            "limit" => 1,
        ]);

        if (isset($class["error"]) || empty($class)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Kelas tidak ditemukan",
            ]);
        }

        // Remove class_id from all students in this class
        $this->supabaseRequest("PATCH", "users?class_id=eq." . $id, [
            "class_id" => null,
        ]);

        // Delete the class
        $endpoint = $this->classTable . "?id=eq." . $id;
        $result = $this->supabaseRequest("DELETE", $endpoint);

        log_message("info", "Delete Class Response: " . json_encode($result));

        if (isset($result["error"])) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Gagal menghapus kelas",
            ]);
        }

        $this->cache->delete("class_data_" . $id);
        $this->invalidateUserCache([
            "class_id" => "eq." . $id,
            "role" => "eq.murid",
            "select" => "id,nama",
        ]);
        $this->invalidateUserCache([
            "class_id" => "eq." . $id,
            "role" => "eq.murid",
            "select" => "id,nama,nisn",
        ]);
        $this->invalidateUserCache(['select' => 'id,nama,class_id']);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Kelas berhasil dihapus",
        ]);
    }

    public function getUnassignedStudents()
    {
        // Get students where class_id is null
        $students = $this->supabaseRequest("GET", "users", null, [
            "role" => "eq.murid",
            "class_id" => "is.null",
            "order" => "nama.asc",
        ]);

        return $this->response->setJSON([
            "success" => true,
            "students" => isset($students["error"]) ? [] : $students,
        ]);
    }

    public function getClassMembers($id)
    {
        $class = $this->supabaseRequest("GET", $this->classTable, null, [
            "id" => "eq." . $id,
            "limit" => 1,
        ]);

        if (isset($class["error"]) || empty($class)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Kelas tidak ditemukan",
            ]);
        }

        $classData = $class[0];

        // Get students in this class
        $students = $this->fetchAllUsers([
            "class_id" => "eq." . $id,
            "role" => "eq.murid",
            "select" => "id,nama,nisn",
        ]);
        $students = isset($students["error"]) ? [] : $students;

        return $this->response->setJSON([
            "success" => true,
            "class" => $classData,
            "students" => $students,
        ]);
    }
}
