<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\TransactionTrait;
use App\Traits\BookTrait;
use App\Traits\UserTrait;

ini_set("max_execution_time", 1000);
ini_set("memory_limit", "512M");

class ClassTransactionController extends Controller
{
    use TransactionTrait;
    use BookTrait;
    use UserTrait;

    private $supabaseUrl;
    private $supabaseKey;
    private $cache;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_SERVICE_ROLE_KEY") ?: getenv("SUPABASE_API_KEY");
        $this->cache = \Config\Services::cache();
    }

    private function supabaseRequest(
        $method,
        $endpoint,
        $data = null,
        $queryParams = []
    ) {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
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
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            return ["error" => "HTTP Error " . $httpCode];
        }

        return json_decode($response, true);
    }

    public function index()
    {
        $cacheKey = "classes_list";
        $classes = $this->cache->get($cacheKey);

        if ($classes === null) {
            $classes = $this->supabaseRequest("GET", "classes", null, [
                "select" => "*",
                "order" => "nama_kelas.asc",
            ]);

            if (isset($classes["error"])) {
                $classes = [];
            }

            $this->cache->save($cacheKey, $classes, 24*60*60);
        }

        $data = [
            "classes" => $classes,
            "borrowings" => [],
            "returns" => [],
        ];

        return view("peminjaman_kelas", $data);
    }

    public function getClassData()
    {
        $classId = $this->request->getGet("class_id");

        if (empty($classId)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Class ID tidak ditemukan",
            ]);
        }

        // Try cache first
        $cacheKey = "class_data_" . $classId;
        $cachedData = $this->cache->get($cacheKey);

        if ($cachedData !== null) {
            return $this->response->setJSON($cachedData);
        }

        // Get class data
        $class = $this->supabaseRequest("GET", "classes", null, [
            "id" => "eq." . $classId,
            "limit" => 1,
        ]);

        if (isset($class["error"]) || empty($class)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Kelas tidak ditemukan",
            ]);
        }

        $classData = $class[0];

        $students = $this->fetchAllUsers([
            "class_id" => "eq." . $classId,
            "role" => "eq.murid",
            "select" => "id,nama",
        ]);

        if (isset($students["error"])) {
            $students = [];
        }

        $responseData = [
            "success" => true,
            "class" => $classData,
            "students" => $students,
        ];

        // Cache the result
        $this->cache->save($cacheKey, $responseData, 24 * 60 * 60); // 24 hours

        return $this->response->setJSON($responseData);
    }

    public function getClassTransactions()
    {
        $body = $this->request->getJSON(true);
        $classId = $body["class_id"] ?? null;
        $type = $body["type"] ?? null;
        $students = $body["students"] ?? null;

        if (!$classId) {
            return $this->response->setJSON([
                "success" => false,
                "transactions" => [],
            ]);
        }

        // Fall back to fetching students if not provided (e.g. direct API calls)
        if (empty($students)) {
            $students = $this->fetchAllUsers([
                "class_id" => "eq." . $classId,
                "role" => "eq.murid",
                "select" => "id,nama",
            ]);
        }

        $studentIds = array_column($students, "id");
        $studentMap = array_column($students, "nama", "id");

        if (empty($studentIds)) {
            return $this->response->setJSON([
                "success" => true,
                "transactions" => [],
            ]);
        }

        $queryParams = [
            "user_id" => "in.(" . implode(",", $studentIds) . ")",
            "select" => "id,user_id,book_id,tanggal,status",
            "order" => "created_at.desc",
        ];

        if ($type) {
            $queryParams["type"] = "eq." . $type;
        }

        $transactions = $this->fetchAllTransactions($queryParams);
        $allBooks = $this->fetchAllBooks(["select" => "id,title"]);
        $bookMap = array_column($allBooks, "title", "id");

        foreach ($transactions as &$transaction) {
            $transaction["user_name"] =
                $studentMap[$transaction["user_id"]] ?? "-";
            $transaction["book_title"] =
                $bookMap[$transaction["book_id"]] ?? "-";
            $transaction["borrowed_from"] =
                $transaction["transaction_location"] ?? "perpustakaan";
        }

        return $this->response->setJSON([
            "success" => true,
            "transactions" => $transactions,
        ]);
    }

    public function addBorrowing()
    {
        try {
            $classId = $this->request->getPost("class_id");
            $userId = $this->request->getPost("user_id");
            $bookId = $this->request->getPost("book_id");
            $tanggal = $this->request->getPost("tanggal") ?: date("Y-m-d");

            if (empty($classId) || empty($userId) || empty($bookId)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Class, User, dan Buku wajib dipilih",
                ]);
            }

            // Get class name
            $class = $this->supabaseRequest("GET", "classes", null, [
                "id" => "eq." . $classId,
                "limit" => 1,
            ]);

            if (isset($class["error"]) || empty($class)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Kelas tidak ditemukan",
                ]);
            }

            $className = $class[0]["nama_kelas"];

            // Get book data
            $book = $this->supabaseRequest("GET", "books", null, [
                "id" => "eq." . $bookId,
                'select' => 'id,quantity,available,is_one_day_book',
                "limit" => 1,
            ]);

            if (isset($book["error"]) || empty($book)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Buku tidak ditemukan",
                ]);
            }

            $bookData = $book[0];
            $availableQuantity = (int) ($bookData["available"] ?? 0);

            if ($availableQuantity < 1) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Stok buku di perpustakaan habis",
                ]);
            }

            // Get user data
            $user = $this->supabaseRequest("GET", "users", null, [
                "id" => "eq." . $userId,
                'select' => 'id,max_borrow,num_borrows',
                "limit" => 1,
            ]);

            if (isset($user["error"]) || empty($user)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "User tidak ditemukan",
                ]);
            }

            $userData = $user[0];
            $maxBorrow = (int) ($userData["max_borrow"] ?? $userData["maxBorrow"] ?? 0);
            $userActiveBorrows = $this->fetchAllTransactions([
                "user_id" => "eq." . $userId,
                "type" => "eq.borrow",
                "select" => "id",
                "status" => "eq.active",
            ]);

            $activeBorrowCount = count($userActiveBorrows);

            if ($activeBorrowCount >= $maxBorrow) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai",
                ]);
            }

            // Calculate due date
            $isOneDayBook = $bookData["is_one_day_book"];
            $dueDays = $isOneDayBook ? 1 : 7;
            $dueDate = date("Y-m-d", strtotime($tanggal . " +$dueDays days"));

            $transactionData = [
                "user_id" => $userId,
                "book_id" => $bookId,
                "type" => "borrow",
                "tanggal" => $tanggal,
                "due_date" => $dueDate,
                "status" => "active",
                "pic_id" => session()->get("user_id"),
                "transaction_location" => "kelas",
                "created_at" => date("Y-m-d H:i:s"),
            ];

            $result = $this->supabaseRequest(
                "POST",
                "transactions",
                $transactionData
            );

            if (isset($result["error"])) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Gagal menyimpan transaksi peminjaman",
                ]);
            }

            // Clear cache
            $this->cache->delete("class_data_" . $classId);
            $this->cache->delete("book_borrowers_" . $bookId);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);

            return $this->response->setJSON([
                "success" => true,
                "message" => "Peminjaman berhasil dicatat",
            ]);
        } catch (\Exception $e) {
            log_message("error", "Error in addBorrowing: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ]);
        }
    }

    public function returnMultiple()
    {
        try {
            $classId = $this->request->getPost("class_id");

            $selectedLoansJson = $this->request->getPost("selectedLoans");
            $selectedLoans = !empty($selectedLoansJson)
                ? json_decode($selectedLoansJson, true)
                : [];

            $loanIds = [];
            $loanStatusMap = [];
            if (is_array($selectedLoans)) {
                foreach ($selectedLoans as $loan) {
                    $loanIds[] = $loan["loanId"];
                    $loanStatusMap[$loan["loanId"]] = $loan["status"] ?? "baik";
                }
                $loanIds = array_filter($loanIds);
            }

            if (empty($classId) || empty($loanIds)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Class dan Loan IDs wajib diisi",
                ]);
            }

            $class = $this->supabaseRequest("GET", "classes", null, [
                "id" => "eq." . $classId,
                "limit" => 1,
            ]);

            if (isset($class["error"]) || empty($class)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Kelas tidak ditemukan",
                ]);
            }

            $className = $class[0]["nama_kelas"];

            $processedCount = 0;
            $errors = [];
            $now = date("Y-m-d H:i:s");

            foreach ($loanIds as $loanId) {
                $borrowTransaction = $this->supabaseRequest(
                    "GET",
                    "transactions",
                    null,
                    [
                        "id" => "eq." . $loanId,
                        "type" => "eq.borrow",
                        "status" => "eq.active",
                        "limit" => 1,
                    ]
                );

                if (
                    isset($borrowTransaction["error"]) ||
                    empty($borrowTransaction)
                ) {
                    $errors[] = "Loan ID $loanId tidak ditemukan atau sudah diselesaikan";
                    continue;
                }

                $borrow = $borrowTransaction[0];
                $userId = $borrow["user_id"];
                $bookId = $borrow["book_id"];
                $borrowDate = $borrow["tanggal"];
                $dueDate = $borrow["due_date"];
                $originalLocation = $borrow["transaction_location"] ?? "perpustakaan";
                $bookCondition = $loanStatusMap[$loanId] ?? "baik";

                // Create return transaction
                $returnData = [
                    "user_id" => $userId,
                    "book_id" => $bookId,
                    "type" => "return",
                    "tanggal" => $now,
                    "status" => "completed",
                    "book_condition" => $bookCondition,
                    "pic_id" => session()->get("user_id"),
                    "transaction_location" => "kelas",
                    "created_at" => $now,
                    "completed_at" => $now,
                    "completed_by_id" => session()->get("user_id"),
                    "due_date" => $dueDate,
                ];

                $result = $this->supabaseRequest(
                    "POST",
                    "transactions",
                    $returnData
                );

                if (isset($result["error"])) {
                    $errors[] = "Gagal menyimpan pengembalian untuk Loan ID $loanId";
                    continue;
                }

                // Update borrow status
                $this->supabaseRequest(
                    "PATCH",
                    "transactions?id=eq." . $loanId,
                    [
                        "status" => "completed",
                        "completed_at" => $now,
                        "completed_by_id" => session()->get("user_id"),
                    ]
                );

                $newTrustScore = $this->calculateTrustScore($userId);
                $this->supabaseRequest("PATCH", "users?id=eq." . $userId, [
                    "trust_score" => $newTrustScore,
                ]);

                $this->cache->delete("book_borrowers_" . $bookId);

                $processedCount++;
            }

            // Clear cache
            $this->cache->delete("class_data_" . $classId);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);

            $message =
                "Pengembalian berhasil dicatat untuk " .
                $processedCount .
                " buku!";
            if (!empty($errors)) {
                $message .= " Terdapat " . count($errors) . " kesalahan.";
            }

            return $this->response->setJSON([
                "success" => true,
                "message" => $message,
                "processed" => $processedCount,
                "errors" => $errors,
            ]);
        } catch (\Exception $e) {
            log_message(
                "error",
                "Exception in returnMultiple: " . $e->getMessage()
            );
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ]);
        }
    }
}
