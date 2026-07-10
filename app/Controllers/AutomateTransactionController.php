<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\BookTrait;
use App\Traits\TransactionTrait;

class AutomateTransactionController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $cache;
    use BookTrait;
    use TransactionTrait;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_API_KEY");
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

        if (empty($uidScan) || empty($userUid)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "UID buku dan UID user wajib diisi",
            ]);
        }

        try {
            $allBooks = $this->supabaseRequest("GET", "books", null);

            $bookData = null;
            foreach ($allBooks as $book) {
                $bookUids = $book['uid'] ?? [];
                if (!is_array($bookUids)) {
                    $bookUids = [$bookUids];
                }

                foreach ($bookUids as $bookUid) {
                    if (strcasecmp(trim((string)$bookUid), $uidScan) === 0) {
                        $bookData = $book;
                        break 2;
                    }
                }
            }

            if (!$bookData) {
                return $this->response->setJSON(['success' => false, 'message' => 'UID buku tidak ditemukan']);
            }

            // ── 2. Cari user berdasarkan UID RFID user ──────────────────────────
            $userResult = $this->supabaseRequest("GET", "users", null, [
                "uid" => "eq." . $userUid,
                "limit" => 1,
            ]);

            if (isset($userResult["error"]) || empty($userResult)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "UID user tidak ditemukan",
                ]);
            }

            $userData = $userResult[0];

            // ── 3. Deteksi tipe transaksi (borrow / return) ─────────────────────
            $activeTx = $this->fetchAllTransactions([
                "uid" => "eq." . $uidScan,
                "user_id" => "eq." . $userData["id"],
                "status" => "eq.active",
                "type" => "eq.borrow",
                "select" => "id,user_id,tanggal,due_date",
            ]);

            $type = !empty($activeTx) ? "return" : "borrow";

            $picName = session()->get("name") ?? "Admin";
            $picUsername = session()->get("username") ?? "admin";
            $picId = session()->get("user_id") ?? null;

            // ── 4a. Peminjaman ──────────────────────────────────────────────────
            if ($type === "borrow") {
                // Cek apakah buku sedang dipinjam user lain (ownership validation)
                $anyActiveBorrow = $this->fetchAllTransactions([
                    "uid" => "eq." . $uidScan,
                    "status" => "eq.active",
                    "type" => "eq.borrow",
                    "select" => "user_id",
                ]);

                if (!empty($anyActiveBorrow)) {
                    $activeOwner = $anyActiveBorrow[0];
                    if ($activeOwner["user_id"] != $userData["id"]) {
                        $ownerUser = $this->supabaseRequest(
                            "GET",
                            "users",
                            null,
                            [
                                "id" => "eq." . $activeOwner["user_id"],
                                "select" => "nama",
                                "limit" => 1,
                            ]
                        );
                        $ownerName =
                            !isset($ownerUser["error"]) && !empty($ownerUser)
                                ? $ownerUser[0]["nama"]
                                : "Tidak terbaca";
                        return $this->response->setJSON([
                            "success" => false,
                            "message" =>
                                "Buku sedang dipinjam oleh: " .
                                $ownerName .
                                ". Tidak bisa dipinjam oleh user lain.",
                        ]);
                    }
                }

                $currentQty = (int) $bookData["quantity"];
                if ($currentQty < 1) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Stok buku habis",
                    ]);
                }

                $maxBorrow = (int) $userData["maxBorrow"];

                $userActiveBorrows = $this->fetchAllTransactions([
                    "user_id" => "eq." . $userData["id"],
                    "type" => "eq.borrow",
                    "select" => "id",
                    "status" => "eq.active",
                ]);

                $activeBorrowCount = count($userActiveBorrows);

                if ($activeBorrowCount >= $maxBorrow) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" =>
                            "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai. Trust Score: " .
                            number_format($trustScore, 1),
                    ]);
                }

                $isOneDayBook = $bookData["is_one_day_book"] ?? false;
                $dueDays = $isOneDayBook ? 1 : 7;
                $dueDate = date("Y-m-d", strtotime("+$dueDays days"));

                $transactionData = [
                    "user_id" => $userData["id"],
                    "book_id" => $bookData["id"],
                    "uid" => $uidScan,
                    "type" => "borrow",
                    "tanggal" => date("Y-m-d"),
                    "due_date" => $dueDate,
                    "status" => "active",
                    "pic_name" => $picName,
                    "pic_username" => $picUsername,
                    "pic_id" => $picId,
                    "transaction_location" => "perpustakaan",
                    "created_at" => date("Y-m-d H:i:s"),
                ];

                $insertTx = $this->supabaseRequest(
                    "POST",
                    "transactions",
                    $transactionData
                );

                if (isset($insertTx["error"])) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Gagal menyimpan transaksi peminjaman",
                    ]);
                }

                // Update num_borrows for user
                $numBorrows = (int) $userData["num_borrows"];
                $this->supabaseRequest(
                    "PATCH",
                    "users?id=eq." . $userData["id"],
                    [
                        "num_borrows" => $numBorrows + 1,
                    ]
                );

                $newQuantity = $currentQty - 1;
                $this->supabaseRequest(
                    "PATCH",
                    "books?id=eq." . $bookData["id"],
                    [
                        "quantity" => $newQuantity,
                        "available" => $newQuantity > 0,
                    ]
                );

                // Clear cache
                $this->cache->delete("book_borrowers_" . $bookData["id"]);
                $this->invalidateBooksCache([
                    "select" => "id,title,quantity,is_one_day_book",
                ]);
                $this->invalidateBooksCache(["order" => "created_at.desc"]);

                log_message(
                    "info",
                    "Borrowing success: User=" .
                        $userData["nama"] .
                        ", Book=" .
                        $bookData["title"]
                );

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Peminjaman berhasil",
                    "book" => $bookData["title"] ?? "-",
                    "type" => "borrow",
                    "user" => $userData["nama"] ?? "-",
                    "due_date" => $dueDate,
                    "max_borrow" => $maxBorrow,
                ]);

                // ── 4b. Pengembalian ────────────────────────────────────────────────
            } else {
                $borrowTx = $activeTx[0];
                $borrowDate = $borrowTx["tanggal"];
                $dueDate = $borrowTx["due_date"];

                $transactionData = [
                    "user_id" => $userData["id"],
                    "book_id" => $bookData["id"],
                    "uid" => $uidScan,
                    "type" => "return",
                    "tanggal" => date("Y-m-d"),
                    "status" => "completed",
                    "pic_name" => $picName,
                    "pic_username" => $picUsername,
                    "pic_id" => $picId,
                    "transaction_location" => "perpustakaan",
                    "created_at" => date("Y-m-d H:i:s"),
                    "completed_at" => date("Y-m-d H:i:s"),
                    "completed_by_name" => $picName,
                    "completed_by_username" => $picUsername,
                    "due_date" => $dueDate,
                ];

                $insertReturn = $this->supabaseRequest(
                    "POST",
                    "transactions",
                    $transactionData
                );

                if (isset($insertReturn["error"])) {
                    return $this->response->setJSON([
                        "success" => false,
                        "message" => "Gagal menyimpan transaksi pengembalian",
                    ]);
                }

                $this->supabaseRequest(
                    "PATCH",
                    "transactions?id=eq." . $borrowTx["id"],
                    [
                        "status" => "completed",
                        "completed_at" => date("Y-m-d H:i:s"),
                        "completed_by_name" => $picName,
                        "completed_by_username" => $picUsername,
                    ]
                );

                $currentQty = (int) $bookData["quantity"];
                $newQuantity = $currentQty + 1;
                $this->supabaseRequest(
                    "PATCH",
                    "books?id=eq." . $bookData["id"],
                    [
                        "quantity" => $newQuantity,
                        "available" => true,
                    ]
                );

                $this->cache->delete("book_borrowers_" . $bookData["id"]);
                $this->invalidateBooksCache([
                    "select" => "id,title,quantity,is_one_day_book",
                ]);
                $this->invalidateBooksCache(["order" => "created_at.desc"]);

                $newTrustScore = $this->calculateTrustScore($userData["id"]);
                $this->supabaseRequest(
                    "PATCH",
                    "users?id=eq." . $userData["id"],
                    [
                        "trust_score" => $newTrustScore,
                    ]
                );

                log_message(
                    "info",
                    "Return success: User=" .
                        $userData["nama"] .
                        ", Book=" .
                        $bookData["title"]
                );

                return $this->response->setJSON([
                    "success" => true,
                    "message" => "Pengembalian berhasil",
                    "book" => $bookData["title"] ?? "-",
                    "type" => "return",
                    "user" => $userData["nama"] ?? "-",
                ]);
            }
        } catch (\Exception $e) {
            log_message(
                "error",
                "Automate Transaction Error: " . $e->getMessage()
            );
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan sistem: " . $e->getMessage(),
            ]);
        }
    }
}
