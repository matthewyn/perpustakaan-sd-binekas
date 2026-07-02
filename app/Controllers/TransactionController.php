<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\TransactionTrait;
use App\Traits\UserTrait;
use App\Traits\BookTrait;
use App\Traits\ClassTrait;

ini_set("max_execution_time", 1000);
ini_set("memory_limit", "512M");

class TransactionController extends Controller
{
    use TransactionTrait;
    use BookTrait;
    use UserTrait;
    use ClassTrait;
    private $supabaseUrl;
    private $supabaseKey;
    private $cache;
    private $maxCompletedTransactions = 60;

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
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 60,
            CURLOPT_TCP_KEEPINTVL => 30,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => false,
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

    public function peminjaman()
    {
        $currentPicName = session()->get("name");
        $currentRole = session()->get("role");

        // Get all transactions with pagination
        $transactions = $this->fetchAllTransactions([
            "select" => "id,user_id,book_id,tanggal,type,status",
            "order" => "created_at.desc",
        ]);

        // Get users, classes, books with pagination and caching
        $users = $this->fetchAllUsers(["select" => "id,nama,class_id"]);
        $classes = $this->fetchAllClasses(["select" => "id,nama_kelas"]);
        $books = $this->fetchAllBooks(["select" => "id,title"]);

        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user["id"]] = $user;
        }

        $classesById = [];
        foreach ($classes as $class) {
            $classesById[$class["id"]] = $class;
        }

        $booksById = [];
        foreach ($books as $book) {
            $booksById[$book["id"]] = $book;
        }

        // Proses transactions
        $borrowRows = [];
        $returnRows = [];

        foreach ($transactions as $t) {
            if (
                $currentRole !== "admin" &&
                ($t["pic_name"] ?? null) !== $currentPicName
            ) {
                continue;
            }

            $userId = $t["user_id"];
            $bookId = $t["book_id"];

            $user =
                $userId && isset($usersById[$userId])
                    ? $usersById[$userId]
                    : null;
            $nama = $user ? $user["nama"] ?? "-" : "-";

            // Get class name from user's class_id
            $classId = $user["class_id"];
            $className =
                $classId && isset($classesById[$classId])
                    ? $classesById[$classId]["nama_kelas"] ?? "-"
                    : "-";

            // Try to get judul from books table
            $judul = "-";
            $judul =
                $bookId && isset($booksById[$bookId])
                    ? $booksById[$bookId]["title"] ?? "-"
                    : "-";

            $row = [
                "nama" => $nama,
                "judul" => $judul,
                "class" => $className,
                "tanggal" => $t["tanggal"],
                "status" => $t["status"],
                "user_id" => $userId,
                "book_id" => $bookId,
            ];

            if ($t["type"] === "borrow") {
                $borrowRows[] = $row;
            } elseif ($t["type"] === "return") {
                $returnRows[] = $row;
            }
        }

        // Calculate statistics
        $totalAvailable = count($books);

        // Chart data (daily, monthly, yearly)
        $borrowingsByDay = [];
        $returnsByDay = [];

        foreach ($transactions as $t) {
            $date = $t["tanggal"] ?? null;
            if (!$date) {
                continue;
            }

            if ($t["type"] === "borrow") {
                $borrowingsByDay[$date] = ($borrowingsByDay[$date] ?? 0) + 1;
            } elseif ($t["type"] === "return") {
                $returnsByDay[$date] = ($returnsByDay[$date] ?? 0) + 1;
            }
        }

        // Aggregate by month and year
        $borrowingsByMonth = [];
        $returnsByMonth = [];
        $borrowingsByYear = [];
        $returnsByYear = [];

        foreach ($borrowingsByDay as $date => $count) {
            $month = substr($date, 0, 7);
            $year = substr($date, 0, 4);

            $borrowingsByMonth[$month] =
                ($borrowingsByMonth[$month] ?? 0) + $count;
            $borrowingsByYear[$year] = ($borrowingsByYear[$year] ?? 0) + $count;
        }

        foreach ($returnsByDay as $date => $count) {
            $month = substr($date, 0, 7);
            $year = substr($date, 0, 4);

            $returnsByMonth[$month] = ($returnsByMonth[$month] ?? 0) + $count;
            $returnsByYear[$year] = ($returnsByYear[$year] ?? 0) + $count;
        }

        $chartData = [
            "harian" => [
                "borrowings" => $borrowingsByDay,
                "returns" => $returnsByDay,
            ],
            "bulanan" => [
                "borrowings" => $borrowingsByMonth,
                "returns" => $returnsByMonth,
            ],
            "tahunan" => [
                "borrowings" => $borrowingsByYear,
                "returns" => $returnsByYear,
            ],
        ];

        $currentMonth = date("Y-m");
        $prevMonth = date("Y-m", strtotime("-1 month"));

        $totalBorrowed = $borrowingsByMonth[$currentMonth] ?? 0;
        $totalReturned = $returnsByMonth[$currentMonth] ?? 0;
        $prevBorrowed = $borrowingsByMonth[$prevMonth] ?? 0;
        $prevReturned = $returnsByMonth[$prevMonth] ?? 0;

        $totalBorrowedPercent =
            $prevBorrowed > 0
                ? round(
                    (($totalBorrowed - $prevBorrowed) / $prevBorrowed) * 100,
                    1
                )
                : 0;

        $totalReturnedPercent =
            $prevReturned > 0
                ? round(
                    (($totalReturned - $prevReturned) / $prevReturned) * 100,
                    1
                )
                : 0;

        // Calculate percentage change based on net books (borrowed - returned)
        $currentNetBooks = $totalBorrowed - $totalReturned;
        $prevNetBooks = $prevBorrowed - $prevReturned;

        $totalAvailablePercent =
            $prevNetBooks > 0
                ? round(
                    (($currentNetBooks - $prevNetBooks) / $prevNetBooks) * 100,
                    1
                )
                : ($currentNetBooks > 0
                    ? 100
                    : 0);

        $data = [
            "borrowings" => $borrowRows,
            "returns" => $returnRows,
            "totalBorrowed" => $totalBorrowed,
            "totalReturned" => $totalReturned,
            "totalAvailable" => $totalAvailable,
            "totalBorrowedPercent" => $totalBorrowedPercent,
            "totalReturnedPercent" => $totalReturnedPercent,
            "totalAvailablePercent" => $totalAvailablePercent,
            "chartData" => $chartData,
        ];

        return view("peminjaman_perpustakaan", $data);
    }

    public function addBorrowing()
    {
        try {
            $userId = $this->request->getPost("user_id");
            $bookId = $this->request->getPost("book_id");
            $namaCari = $this->request->getPost("namaCari");
            $judulCari = $this->request->getPost("judulCari");
            $tanggal = $this->request->getPost("tanggal") ?: date("Y-m-d");
            $picName = session()->get("name");
            $picUsername = session()->get("username");
            $picId = session()->get("user_id");

            if (empty($userId) || empty($bookId)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "User, dan Buku wajib dipilih",
                ]);
            }

            // Get book data
            $book = $this->supabaseRequest("GET", "books", null, [
                "id" => "eq." . $bookId,
                "select" => "id,quantity,is_one_day_book",
                "limit" => 1,
            ]);

            if (isset($book["error"]) || empty($book)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Buku tidak ditemukan",
                ]);
            }

            $bookData = $book[0];
            $currentQty = (int) $bookData["quantity"];
            $currentAvailable = $bookData["available"] ?? false;

            if ($currentQty < 1) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Stok buku di perpustakaan habis",
                ]);
            }

            // Get user data for trust score validation
            $user = $this->supabaseRequest("GET", "users", null, [
                "id" => "eq." . $userId,
                "select" => "id,maxBorrow,num_borrows",
                "limit" => 1,
            ]);

            if (isset($user["error"]) || empty($user)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "User tidak ditemukan",
                ]);
            }

            $userData = $user[0];
            $maxBorrow = (int) $userData["maxBorrow"];
            $userActiveBorrows = $this->fetchAllTransactions([
                "user_id" => "eq." . $userId,
                "type" => "eq.borrow",
                "status" => "eq.active",
                "select" => "id",
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
                "pic_name" => $picName,
                "pic_username" => $picUsername,
                "pic_id" => $picId,
                "transaction_location" => "perpustakaan",
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

            // Update num_borrows for user
            $numBorrows = (int) $userData["num_borrows"];
            $this->supabaseRequest("PATCH", "users?id=eq." . $userId, [
                "num_borrows" => $numBorrows + 1,
            ]);

            $newQuantity = $currentQty - 1;
            $this->supabaseRequest("PATCH", "books?id=eq." . $bookId, [
                "quantity" => $newQuantity,
                "available" => $newQuantity > 0,
            ]);

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

    public function addReturn()
    {
        try {
            $selectedLoans = $this->request->getPost("selectedLoans");
            $loanIds = $this->request->getPost("loan_id");

            $loansToProcess = [];
            $loanStatusMap = [];

            if (!empty($selectedLoans)) {
                $decoded = json_decode($selectedLoans, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $loan) {
                        $loanId = $loan["loanId"] ?? ($loan["loan_id"] ?? null);
                        if ($loanId) {
                            $loansToProcess[] = $loanId;
                            $loanStatusMap[$loanId] = $loan["status"] ?? "baik";
                        }
                    }
                }
            } elseif (!empty($loanIds)) {
                if (!is_array($loanIds)) {
                    $loansToProcess = [$loanIds];
                } else {
                    $loansToProcess = $loanIds;
                }
            }

            if (empty($loansToProcess)) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" =>
                        "Pilih minimal satu peminjaman untuk dikembalikan",
                ]);
            }

            $processedCount = 0;
            $errors = [];
            $now = date("Y-m-d H:i:s");

            foreach ($loansToProcess as $loanId) {
                if (empty($loanId)) {
                    continue;
                }

                // Get borrow transaction
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
                    $errors[] = "Peminjaman ID " . $loanId . " tidak ditemukan";
                    continue;
                }

                $borrow = $borrowTransaction[0];
                $userId = $borrow["user_id"];
                $bookId = $borrow["book_id"];
                $borrowDate = $borrow["tanggal"];
                $dueDate = $borrow["due_date"];
                $bookCondition = $loanStatusMap[$loanId] ?? "baik";

                // Create return transaction
                $returnData = [
                    "user_id" => $userId,
                    "book_id" => $bookId,
                    "type" => "return",
                    "tanggal" => $now,
                    "status" => "completed",
                    "book_condition" => $bookCondition,
                    "pic_name" => session()->get("name"),
                    "pic_username" => session()->get("username"),
                    "pic_id" => session()->get("user_id"),
                    "transaction_location" => "perpustakaan",
                    "created_at" => $now,
                    "completed_at" => $now,
                    "completed_by_name" => session()->get("name"),
                    "completed_by_username" => session()->get("username"),
                    "due_date" => $dueDate,
                ];

                $result = $this->supabaseRequest(
                    "POST",
                    "transactions",
                    $returnData
                );

                if (isset($result["error"])) {
                    $errors[] =
                        "Gagal menyimpan pengembalian untuk peminjaman ID " .
                        $loanId;
                    continue;
                }

                // Update borrow status
                $this->supabaseRequest(
                    "PATCH",
                    "transactions?id=eq." . $loanId,
                    [
                        "status" => "completed",
                        "completed_at" => $now,
                        "completed_by_name" => session()->get("name"),
                        "completed_by_username" => session()->get("username"),
                    ]
                );

                // Update book quantity
                $book = $this->supabaseRequest("GET", "books", null, [
                    "id" => "eq." . $bookId,
                    "limit" => 1,
                ]);

                if (!isset($book["error"]) && !empty($book)) {
                    $currentQty = (int) $book[0]["quantity"];
                    $newQty = $currentQty + 1;
                    $this->supabaseRequest("PATCH", "books?id=eq." . $bookId, [
                        "quantity" => $newQty,
                        "available" => true,
                    ]);
                }

                $newTrustScore = $this->calculateTrustScore($userId);
                $this->supabaseRequest("PATCH", "users?id=eq." . $userId, [
                    "trust_score" => $newTrustScore,
                ]);

                $this->cache->delete("book_borrowers_" . $bookId);
                $this->invalidateBooksCache(["order" => "created_at.desc"]);

                $processedCount++;
            }

            $message =
                "Pengembalian berhasil untuk " . $processedCount . " buku!";
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
            log_message("error", "Error in addReturn: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ]);
        }
    }

    public function getBorrowings()
    {
        try {
            $currentRole = session()->get("role");
            $currentPicName = session()->get("name");
            $classIdFilter = $this->request->getVar("class_id");

            log_message(
                "info",
                "getBorrowings called with class_id filter: " .
                    ($classIdFilter ?? "none")
            );

            $params = [
                "type" => "eq.borrow",
                "status" => "eq.active",
                "select" => "id,user_id,book_id,tanggal,status",
                "order" => "created_at.desc",
            ];

            // Apply PIC filter for non-admin
            if ($currentRole !== "admin") {
                $params["pic_name"] = "eq." . $currentPicName;
            }

            // Fetch all transactions with pagination
            $transactions = $this->fetchAllTransactions($params);

            if (!empty($transactions)) {
                $allUsers = $this->fetchAllUsers([
                    "select" => "id,nama,class_id",
                ]);

                // Create user lookup map
                $userMap = [];
                foreach ($allUsers as $user) {
                    $userMap[$user["id"]] = [
                        "nama" => $user["nama"] ?? "-",
                        "class_id" => $user["class_id"] ?? null,
                    ];
                }

                $allBooks = $this->fetchAllBooks(["select" => "id,title"]);
                $bookMap = [];
                foreach ($allBooks as $book) {
                    if (isset($book["id"]) && isset($book["title"])) {
                        $bookId = (string) $book["id"];
                        $bookMap[$bookId] = $book["title"];
                    }
                }

                $activeTransactions = [];
                foreach ($transactions as $t) {
                    // Add book title
                    if (!empty($t["book_id"])) {
                        $bookId = (string) $t["book_id"];
                        $t["book_title"] = isset($bookMap[$bookId])
                            ? $bookMap[$bookId]
                            : "-";
                    } else {
                        $t["book_title"] = "-";
                    }

                    // Add user info and class_id
                    if (
                        !empty($t["user_id"]) &&
                        isset($userMap[$t["user_id"]])
                    ) {
                        $t["user_name"] = $userMap[$t["user_id"]]["nama"];
                        $t["user_class_id"] =
                            $userMap[$t["user_id"]]["class_id"];
                    } else {
                        $t["user_name"] = "-";
                        $t["user_class_id"] = null;
                    }

                    // Filter by class_id if specified
                    if ($classIdFilter !== null) {
                        if ($t["user_class_id"] == $classIdFilter) {
                            $activeTransactions[] = $t;
                        }
                    } else {
                        $activeTransactions[] = $t;
                    }
                }

                $transactions = $activeTransactions;
            }

            return $this->response->setJSON([
                "success" => true,
                "borrowings" => $transactions,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Error in getBorrowings: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "borrowings" => [],
            ]);
        }
    }

    public function getAllBorrowings()
    {
        try {
            $currentRole = session()->get("role");
            $currentPicName = session()->get("name");
            $classIdFilter = $this->request->getVar("class_id");
            $page = (int) ($this->request->getVar("page") ?? 1);
            $limit = (int) ($this->request->getVar("limit") ?? 10);
            $returnAll = $this->request->getVar("all") == "1";

            log_message(
                "info",
                "getAllBorrowings called with class_id filter: " .
                    ($classIdFilter ?? "none")
            );

            $allParams = [
                "type" => "eq.borrow",
                "select" => "id,user_id,book_id,tanggal,status",
                "order" => "created_at.desc",
            ];

            if ($currentRole !== "admin") {
                $allParams["pic_name"] = "eq." . $currentPicName;
            }

            $allTransactions = $this->fetchAllTransactions($allParams);

            $totalCount = count($allTransactions);

            if ($returnAll) {
                $transactions = $allTransactions;
            } else {
                $offset = ($page - 1) * $limit;
                $transactions = array_slice($allTransactions, $offset, $limit);
            }

            if (!empty($transactions)) {
                $allBooks = $this->fetchAllBooks(["select" => "id,title"]);
                $bookMap = [];
                foreach ($allBooks as $book) {
                    if (isset($book["id"]) && isset($book["title"])) {
                        $bookId = (string) $book["id"];
                        $bookMap[$bookId] = $book["title"];
                    }
                }

                foreach ($transactions as &$t) {
                    $t["book_title"] = isset($bookMap[$t["book_id"]])
                        ? $bookMap[$t["book_id"]]
                        : "-";
                }
                unset($t);
            }

            return $this->response->setJSON([
                "success" => true,
                "borrowings" => $transactions,
                "totalCount" => $totalCount,
                "page" => $page,
                "limit" => $limit,
            ]);
        } catch (\Exception $e) {
            log_message(
                "error",
                "Error in apiAllBorrowings: " . $e->getMessage()
            );
            return $this->response->setJSON([
                "success" => false,
                "borrowings" => [],
                "totalCount" => 0,
            ]);
        }
    }

    public function getReturns()
    {
        try {
            $currentRole = session()->get("role");
            $currentPicName = session()->get("name");
            $classIdFilter = $this->request->getVar("class_id");

            $params = [
                "type" => "eq.return",
                "select" => "id,user_id,book_id,tanggal,status",
                "order" => "created_at.desc",
            ];

            if ($currentRole !== "admin") {
                $params["pic_name"] = "eq." . $currentPicName;
            }

            $transactions = $this->fetchAllTransactions($params);

            if ($classIdFilter !== null && !empty($transactions)) {
                $allUsers = $this->fetchAllUsers([
                    "select" => "id,nama,class_id",
                ]);

                $userMap = [];
                foreach ($allUsers as $user) {
                    $userMap[$user["id"]] = [
                        "nama" => $user["nama"] ?? "-",
                        "class_id" => $user["class_id"] ?? null,
                    ];
                }

                $filteredTransactions = [];
                foreach ($transactions as $t) {
                    if (
                        !empty($t["user_id"]) &&
                        isset($userMap[$t["user_id"]])
                    ) {
                        $t["user_name"] = $userMap[$t["user_id"]]["nama"];
                        $t["user_class_id"] =
                            $userMap[$t["user_id"]]["class_id"];

                        if ($t["user_class_id"] == $classIdFilter) {
                            $filteredTransactions[] = $t;
                        }
                    }
                }
                $transactions = $filteredTransactions;
            }

            return $this->response->setJSON([
                "success" => true,
                "returns" => $transactions,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Error in getReturns: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "returns" => [],
            ]);
        }
    }

    public function getAllReturns()
    {
        try {
            $currentRole = session()->get("role");
            $currentPicName = session()->get("name");
            $classIdFilter = $this->request->getVar("class_id");
            $page = (int) ($this->request->getVar("page") ?? 1);
            $limit = (int) ($this->request->getVar("limit") ?? 10);
            $returnAll = $this->request->getVar("all") == "1";

            $allParams = [
                "type" => "eq.return",
                "select" => "id,user_id,book_id,tanggal,status",
                "order" => "created_at.desc",
            ];

            if ($currentRole !== "admin") {
                $allParams["pic_name"] = "eq." . $currentPicName;
            }

            $allTransactions = $this->fetchAllTransactions($allParams);

            $totalCount = count($allTransactions);

            if ($returnAll) {
                $transactions = $allTransactions;
            } else {
                $offset = ($page - 1) * $limit;
                $transactions = array_slice($allTransactions, $offset, $limit);
            }

            if (!empty($transactions)) {
                $allBooks = $this->fetchAllBooks(["select" => "id,title"]);
                $bookMap = [];
                foreach ($allBooks as $book) {
                    if (isset($book["id"]) && isset($book["title"])) {
                        $bookId = (string) $book["id"];
                        $bookMap[$bookId] = $book["title"];
                    }
                }

                foreach ($transactions as &$t) {
                    $t["book_title"] = isset($bookMap[$t["book_id"]])
                        ? $bookMap[$t["book_id"]]
                        : "-";
                }

                unset($t);
            }

            return $this->response->setJSON([
                "success" => true,
                "returns" => $transactions,
                "totalCount" => $totalCount,
                "page" => $page,
                "limit" => $limit,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Error in getAllReturns: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "returns" => [],
                "totalCount" => 0,
            ]);
        }
    }
}
