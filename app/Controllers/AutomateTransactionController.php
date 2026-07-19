<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\BookTrait;
use App\Traits\TransactionTrait;

class AutomateTransactionController extends Controller
{
    use BookTrait;
    use TransactionTrait;

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
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

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

    public function automateView()
    {
        return view("peminjaman_otomatis");
    }

    public function automateTransaction()
    {
        $uidScan = trim($this->request->getPost("uid") ?? "");
        $userUid = trim($this->request->getPost("user_uid") ?? "");

        log_message(
            "info",
            "Automate Transaction Request: Book Copy UID=" .
                $uidScan .
                ", User UID=" .
                $userUid
        );

        if (empty($uidScan) || empty($userUid)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "UID buku dan UID user wajib diisi",
            ]);
        }

        try {
            $users = $this->supabaseRequest("GET", "users", null, [
                "uid" => "eq." . $userUid,
                "is_active" => "eq.true",
                "select" => "id,nama,max_borrow,num_borrows,trust_score,is_freezed",
                "limit" => 1,
            ]);

            if (isset($users["error"]) || empty($users)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "UID user tidak ditemukan",
                ]);
            }

            $userData = $users[0];

            if (!empty($userData["is_freezed"])) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "User sedang dibekukan dan tidak dapat melakukan transaksi",
                ]);
            }

            $copies = $this->supabaseRequest("GET", "book_copies", null, [
                "uid" => "eq." . $uidScan,
                "is_active" => "eq.true",
                "select" => "id,book_id,uid",
                "limit" => 1,
            ]);

            if (isset($copies["error"]) || empty($copies)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "UID buku tidak ditemukan",
                ]);
            }

            $copyData = $copies[0];
            $bookResult = $this->supabaseRequest("GET", "books", null, [
                "id" => "eq." . $copyData["book_id"],
                "is_test_data" => "eq.false",
                "select" => "id,title,available,quantity,is_one_day_book",
                "limit" => 1,
            ]);

            if (isset($bookResult["error"]) || empty($bookResult)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Data buku tidak ditemukan",
                ]);
            }

            $bookData = $bookResult[0];
            $activeTx = $this->fetchAllTransactions([
                "book_copy_id" => "eq." . $copyData["id"],
                "status" => "eq.active",
                "type" => "eq.borrow",
                "select" => "id,user_id,tanggal,due_date",
            ]);

            $picId = session()->get("user_id") ?? null;
            $now = date("Y-m-d H:i:s");

            if (!empty($activeTx)) {
                $borrowTx = $activeTx[0];
                if ((string) $borrowTx["user_id"] !== (string) $userData["id"]) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Buku sedang dipinjam oleh user lain",
                    ]);
                }

                $returnData = [
                    "user_id" => $userData["id"],
                    "book_id" => $bookData["id"],
                    "book_copy_id" => $copyData["id"],
                    "type" => "return",
                    "tanggal" => date("Y-m-d"),
                    "status" => "completed",
                    "book_condition" => "baik",
                    "pic_id" => $picId,
                    "transaction_location" => "perpustakaan",
                    "created_at" => $now,
                    "completed_at" => $now,
                    "completed_by_id" => $picId,
                    "due_date" => $borrowTx["due_date"] ?? null,
                ];

                $insertReturn = $this->supabaseRequest("POST", "transactions", $returnData);
                if (isset($insertReturn["error"])) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Gagal menyimpan transaksi pengembalian",
                        "data" => $insertReturn,
                    ]);
                }

                $updateBorrow = $this->supabaseRequest(
                    "PATCH",
                    "transactions?id=eq." . $borrowTx["id"],
                    [
                        "status" => "completed",
                        "completed_at" => $now,
                        "completed_by_id" => $picId,
                    ]
                );

                if (isset($updateBorrow["error"])) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Gagal menyelesaikan transaksi peminjaman",
                        "data" => $updateBorrow,
                    ]);
                }

                $newTrustScore = $this->calculateTrustScore($userData["id"]);
                $this->supabaseRequest("PATCH", "users?id=eq." . $userData["id"], [
                    "trust_score" => $newTrustScore,
                ]);

                $this->cache->delete("book_borrowers_" . $bookData["id"]);
                $this->invalidateBooksCache(["order" => "created_at.desc"]);

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Pengembalian berhasil",
                    "book" => $bookData["title"] ?? "-",
                    "type" => "return",
                    "user" => $userData["nama"] ?? "-",
                ]);
            }

            if ((int) ($bookData["available"] ?? 0) < 1) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Stok buku habis",
                ]);
            }

            $maxBorrow = (int) ($userData["max_borrow"] ?? 0);
            $userActiveBorrows = $this->fetchAllTransactions([
                "user_id" => "eq." . $userData["id"],
                "type" => "eq.borrow",
                "status" => "eq.active",
                "select" => "id",
            ]);

            if (count($userActiveBorrows) >= $maxBorrow) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai",
                ]);
            }

            $dueDays = !empty($bookData["is_one_day_book"]) ? 1 : 7;
            $dueDate = date("Y-m-d", strtotime("+$dueDays days"));

            $borrowData = [
                "user_id" => $userData["id"],
                "book_id" => $bookData["id"],
                "book_copy_id" => $copyData["id"],
                "type" => "borrow",
                "tanggal" => date("Y-m-d"),
                "due_date" => $dueDate,
                "status" => "active",
                "pic_id" => $picId,
                "transaction_location" => "perpustakaan",
                "created_at" => $now,
            ];

            $insertBorrow = $this->supabaseRequest("POST", "transactions", $borrowData);
            if (isset($insertBorrow["error"])) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Gagal menyimpan transaksi peminjaman",
                    "data" => $insertBorrow,
                ]);
            }

            $this->cache->delete("book_borrowers_" . $bookData["id"]);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);

            return $this->response->setJSON([
                "success" => true,
                "message" => "Peminjaman berhasil",
                "book" => $bookData["title"] ?? "-",
                "type" => "borrow",
                "user" => $userData["nama"] ?? "-",
                "due_date" => $dueDate,
                "max_borrow" => $maxBorrow,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Automate Transaction Error: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan sistem: " . $e->getMessage(),
            ]);
        }
    }
}
