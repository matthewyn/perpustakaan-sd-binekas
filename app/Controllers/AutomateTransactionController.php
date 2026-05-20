<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class AutomateTransactionController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $cache;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        $this->cache = \Config\Services::cache();
    }

    private function supabaseRequest($method, $endpoint, $data = null, $queryParams = [])
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message('error', 'Supabase credentials not configured');
            return ['error' => 'Supabase credentials not configured'];
        }

        $url = rtrim($this->supabaseUrl, '/') . '/rest/v1/' . $endpoint;
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

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
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'cURL Error: ' . $error);
            return ['error' => $error];
        }

        if ($httpCode >= 400) {
            log_message('error', 'HTTP Error ' . $httpCode . ': ' . $response);
            return ['error' => 'HTTP Error ' . $httpCode, 'response' => $response];
        }

        return json_decode($response, true);
    }

    private function fetchAllBooks($queryParams = [])
    {
        $cacheKey    = 'all_books_automate_' . md5(json_encode($queryParams));
        $cachedBooks = $this->cache->get($cacheKey);

        if ($cachedBooks !== null) {
            log_message('info', 'Books fetched from cache: ' . count($cachedBooks) . ' books');
            return $cachedBooks;
        }

        $allBooks = [];
        $limit    = 1000;
        $offset   = 0;
        $hasMore  = true;

        log_message('info', 'Starting fetchAllBooks with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit'  => $limit,
                'offset' => $offset
            ]);

            $books = $this->supabaseRequest('GET', 'books', null, $params);

            if (isset($books['error']) || !is_array($books)) {
                log_message('error', 'Error fetching books at offset ' . $offset);
                break;
            }

            $count = count($books);
            log_message('info', "Fetched {$count} books at offset {$offset}");

            if ($count > 0) {
                $allBooks = array_merge($allBooks, $books);
                $offset  += $limit;

                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total books fetched: ' . count($allBooks));
        $this->cache->save($cacheKey, $allBooks, 300);

        return $allBooks;
    }

    private function fetchAllTransactions($queryParams = [])
    {
        $allTransactions = [];
        $limit   = 1000;
        $offset  = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllTransactions with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit'  => $limit,
                'offset' => $offset
            ]);

            $transactions = $this->supabaseRequest('GET', 'transactions', null, $params);

            if (isset($transactions['error']) || !is_array($transactions)) {
                log_message('error', 'Error fetching transactions at offset ' . $offset);
                break;
            }

            $count = count($transactions);
            log_message('info', "Fetched {$count} transactions at offset {$offset}");

            if ($count > 0) {
                $allTransactions = array_merge($allTransactions, $transactions);
                $offset += $limit;

                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total transactions fetched: ' . count($allTransactions));
        return $allTransactions;
    }

    // Calculate trust score based on all transactions
    private function calculateTrustScore($userId)
    {
        // Get all borrowing transactions
        $borrowTransactions = $this->fetchAllTransactions([
            'user_id' => 'eq.' . $userId,
            'type' => 'eq.borrow',
            'select' => '*'
        ]);

        // Get all return transactions to get book_condition
        $returnTransactions = $this->fetchAllTransactions([
            'user_id' => 'eq.' . $userId,
            'type' => 'eq.return',
            'select' => '*'
        ]);

        $totalBorrowing = count($borrowTransactions);

        // Default score for new users
        if ($totalBorrowing <= 0) {
            return 100;
        }

        // Create a map of return transactions by book_id for quick lookup
        $returnMap = [];
        foreach ($returnTransactions as $ret) {
            $key = $ret['book_id'] . '_' . $ret['user_id'];
            $returnMap[$key] = $ret;
        }

        $totalLate = 0;
        $totalOnTime = 0;
        $totalDamaged = 0;

        foreach ($borrowTransactions as $borrow) {

            $dueDate = $borrow['due_date'] ?? null;
            $completedAt = $borrow['completed_at'] ?? null;

            // Skip transactions that have not been returned
            if (!$completedAt || !$dueDate) {
                continue;
            }

            // =========================
            // Late / On-Time Detection
            // =========================

            if (strtotime($completedAt) > strtotime($dueDate)) {
                $totalLate++;
            } else {
                $totalOnTime++;
            }

            // =========================
            // Damaged / Lost Detection
            // =========================

            // Check corresponding return transaction for book_condition
            $key = $borrow['book_id'] . '_' . $borrow['user_id'];
            if (isset($returnMap[$key])) {
                $returnTrx = $returnMap[$key];
                if (
                    isset($returnTrx['book_condition']) &&
                    in_array(
                        strtolower($returnTrx['book_condition']),
                        ['rusak', 'hilang']
                    )
                ) {
                    $totalDamaged++;
                }
            }
        }

        // Prevent division by zero
        $effectiveTransactions = max(
            1,
            ($totalLate + $totalOnTime)
        );

        // =========================
        // Feature Calculation
        // =========================

        // f1 = delay behavior
        $f1 = 1 - ($totalLate / $effectiveTransactions);

        // f2 = on-time return rate
        $f2 = $totalOnTime / $effectiveTransactions;

        // f3 = damaged/lost behavior
        $f3 = 1 - ($totalDamaged / $effectiveTransactions);

        // Clamp values between 0 and 1
        $f1 = max(0, min(1, $f1));
        $f2 = max(0, min(1, $f2));
        $f3 = max(0, min(1, $f3));

        // =========================
        // Feature Weights
        // =========================

        $a1 = 0.45; // delay behavior
        $a2 = 0.35; // on-time return
        $a3 = 0.20; // damaged/lost

        // =========================
        // Cluster Scaling
        // =========================

        if ($f1 >= 0.90 && $f2 >= 0.90 && $f3 >= 0.95) {
            $L = 700; // Excellent
        } elseif ($f1 >= 0.75) {
            $L = 500; // Good
        } elseif ($f1 >= 0.50) {
            $L = 300; // Moderate
        } else {
            $L = 100; // Poor
        }

        // =========================
        // Final Trust Score
        // =========================

        $a1_f1 = $a1 * $f1;
        $a2_f2 = $a2 * $f2;
        $a3_f3 = $a3 * $f3;

        log_message('info', "Trust Score Calculation for User $userId:");
        log_message('info', "Effective Transactions for User $userId: $effectiveTransactions");
        log_message('info', "  Total Late: $totalLate");
        log_message('info', "  Total On-Time: $totalOnTime");
        log_message('info', "  Total Damaged/Lost: $totalDamaged");
        log_message('info', "  a1 * f1 (delay behavior): $a1 * $f1 = $a1_f1");
        log_message('info', "  a2 * f2 (on-time rate): $a2 * $f2 = $a2_f2");
        log_message('info', "  a3 * f3 (damage/lost): $a3 * $f3 = $a3_f3");
        log_message('info', "  L (cluster): $L");

        $trustScore = $L * (
            $a1_f1 +
            $a2_f2 +
            $a3_f3
        );

        // Normalize score
        return round(
            min(1000, max(0, $trustScore)),
            2
        );
    }

    public function automateView()
    {
        return view('peminjaman_otomatis');
    }

    public function automateTransaction()
    {
        // uidScan  = UID kartu RFID buku
        // userUid  = UID kartu RFID user (sebelumnya NISN/NIP)
        $uidScan = trim($this->request->getPost('uid') ?? '');
        $userUid = trim($this->request->getPost('user_uid') ?? '');

        if (empty($uidScan)) {
            return $this->response->setJSON(['success' => false, 'message' => 'UID buku wajib diisi']);
        }

        if (empty($userUid)) {
            return $this->response->setJSON(['success' => false, 'message' => 'UID user wajib diisi']);
        }

        try {
            // ── 1. Cari buku berdasarkan UID RFID buku ──────────────────────────
            $allBooks = $this->fetchAllBooks();

            if (empty($allBooks)) {
                log_message('error', 'Failed to fetch books');
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengambil data buku']);
            }

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
            $userResult = $this->supabaseRequest('GET', 'users', null, [
                'uid'   => 'eq.' . $userUid,
                'limit' => 1
            ]);

            if (isset($userResult['error']) || empty($userResult)) {
                return $this->response->setJSON(['success' => false, 'message' => 'UID user tidak ditemukan']);
            }

            $userData = $userResult[0];

            // ── 3. Deteksi tipe transaksi (borrow / return) ─────────────────────
            $activeTx = $this->fetchAllTransactions([
                'uid'    => 'eq.' . $uidScan,
                'user_id' => 'eq.' . $userData['id'],
                'status' => 'eq.active',
                'type'   => 'eq.borrow'
            ]);

            $type = (!empty($activeTx)) ? 'return' : 'borrow';

            $picName     = session()->get('name') ?? 'Admin';
            $picUsername = session()->get('username') ?? 'admin';
            $picId       = session()->get('user_id') ?? null;

            // ── 4a. Peminjaman ──────────────────────────────────────────────────
            if ($type === 'borrow') {

                // Cek apakah buku sedang dipinjam user lain (ownership validation)
                $anyActiveBorrow = $this->fetchAllTransactions([
                    'uid'    => 'eq.' . $uidScan,
                    'status' => 'eq.active',
                    'type'   => 'eq.borrow'
                ]);

                if (!empty($anyActiveBorrow)) {
                    $activeOwner = $anyActiveBorrow[0];
                    if ($activeOwner['user_id'] != $userData['id']) {
                        $ownerUser = $this->supabaseRequest('GET', 'users', null, [
                            'id'     => 'eq.' . $activeOwner['user_id'],
                            'select' => 'nama',
                            'limit'  => 1
                        ]);
                        $ownerName = (!isset($ownerUser['error']) && !empty($ownerUser)) ? $ownerUser[0]['nama'] : 'Tidak terbaca';
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Buku sedang dipinjam oleh: ' . $ownerName . '. Tidak bisa dipinjam oleh user lain.'
                        ]);
                    }
                }

                $currentQty = (int)($bookData['quantity'] ?? 0);
                if ($currentQty < 1) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Stok buku habis']);
                }

                $trustScore = (float)($userData['trust_score'] ?? 100);
                $maxBorrow  = (int)($userData['maxBorrow'] ?? 1);

                $userActiveBorrows = $this->fetchAllTransactions([
                    'user_id' => 'eq.' . $userData['id'],
                    'type'    => 'eq.borrow',
                    'status'  => 'eq.active'
                ]);

                $activeBorrowCount = count($userActiveBorrows);

                if ($activeBorrowCount >= $maxBorrow) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai. Trust Score: " . number_format($trustScore, 1)
                    ]);
                }

                $isOneDayBook = $bookData['is_one_day_book'] ?? false;
                $dueDays      = $isOneDayBook ? 1 : 7;
                $dueDate      = date('Y-m-d', strtotime("+$dueDays days"));

                $transactionData = [
                    'user_id'                => $userData['id'],
                    'book_id'                => $bookData['id'],
                    'uid'                    => $uidScan,
                    'type'                   => 'borrow',
                    'tanggal'                => date('Y-m-d'),
                    'due_date'               => $dueDate,
                    'status'                 => 'active',
                    'pic_name'               => $picName,
                    'pic_username'           => $picUsername,
                    'pic_id'                 => $picId,
                    'transaction_location'   => 'perpustakaan',
                    'created_at'             => date('Y-m-d H:i:s'),
                    'completed_at'           => null,
                    'completed_by_name'      => null,
                    'completed_by_username'  => null
                ];

                $insertTx = $this->supabaseRequest('POST', 'transactions', $transactionData);

                if (isset($insertTx['error'])) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan transaksi peminjaman']);
                }

                $newQuantity = $currentQty - 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookData['id'], [
                    'quantity'  => $newQuantity,
                    'available' => $newQuantity > 0
                ]);

                $this->cache->delete('all_books_automate_' . md5(json_encode([])));

                log_message('info', 'Borrowing success: User=' . $userData['nama'] . ', Book=' . $bookData['title']);

                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => 'Peminjaman berhasil',
                    'book'       => $bookData['title'] ?? '-',
                    'type'       => 'borrow',
                    'user'       => $userData['nama'] ?? '-',
                    'due_date'   => $dueDate,
                    'trust_score' => number_format($trustScore, 1),
                    'max_borrow' => $maxBorrow
                ]);

            // ── 4b. Pengembalian ────────────────────────────────────────────────
            } else {

                $borrowTx  = $activeTx[0];
                $borrowDate = $borrowTx['tanggal'];
                $dueDate    = $borrowTx['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));

                $transactionData = [
                    'user_id'               => $userData['id'],
                    'book_id'               => $bookData['id'],
                    'uid'                   => $uidScan,
                    'type'                  => 'return',
                    'tanggal'               => date('Y-m-d'),
                    'status'                => 'completed',
                    'pic_name'              => $picName,
                    'pic_username'          => $picUsername,
                    'pic_id'                => $picId,
                    'transaction_location'  => 'perpustakaan',
                    'created_at'            => date('Y-m-d H:i:s'),
                    'completed_at'          => date('Y-m-d H:i:s'),
                    'completed_by_name'     => $picName,
                    'completed_by_username' => $picUsername,
                    'due_date'              => $dueDate
                ];

                $insertReturn = $this->supabaseRequest('POST', 'transactions', $transactionData);

                if (isset($insertReturn['error'])) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan transaksi pengembalian']);
                }

                $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $borrowTx['id'], [
                    'status'                 => 'completed',
                    'completed_at'           => date('Y-m-d H:i:s'),
                    'completed_by_name'      => $picName,
                    'completed_by_username'  => $picUsername
                ]);

                $currentQty  = (int)($bookData['quantity'] ?? 0);
                $newQuantity = $currentQty + 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookData['id'], [
                    'quantity'  => $newQuantity,
                    'available' => true
                ]);

                $this->cache->delete('all_books_automate_' . md5(json_encode([])));

                $newScore = $this->calculateTrustScore($userData['id']);
                $this->supabaseRequest('PATCH', 'users?id=eq.' . $userData['id'], [
                    'trust_score' => $newScore
                ]);

                $updatedUser = $this->supabaseRequest('GET', 'users', null, [
                    'id'     => 'eq.' . $userData['id'],
                    'select' => 'trust_score,maxBorrow',
                    'limit'  => 1
                ]);

                $newTrustScore = !isset($updatedUser['error']) && !empty($updatedUser)
                    ? (float)$updatedUser[0]['trust_score']
                    : (float)($userData['trust_score'] ?? 100);

                $newMaxBorrow = (int)($updatedUser[0]['maxBorrow'] ?? $userData['maxBorrow'] ?? 1);

                $isLate      = strtotime(date('Y-m-d')) > strtotime($dueDate);
                $lateMessage = $isLate ? ' (TERLAMBAT)' : ' (TEPAT WAKTU: +1 poin)';

                log_message('info', 'Return success: User=' . $userData['nama'] . ', Book=' . $bookData['title']);

                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => 'Pengembalian berhasil' . $lateMessage,
                    'book'       => $bookData['title'] ?? '-',
                    'type'       => 'return',
                    'user'       => $userData['nama'] ?? '-',
                    'trust_score' => number_format($newTrustScore, 1),
                    'max_borrow' => $newMaxBorrow,
                    'was_late'   => $isLate
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Automate Transaction Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }
}