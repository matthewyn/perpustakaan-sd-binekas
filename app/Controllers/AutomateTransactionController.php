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
        // Try cache first
        $cacheKey = 'all_books_automate_' . md5(json_encode($queryParams));
        $cachedBooks = $this->cache->get($cacheKey);

        if ($cachedBooks !== null) {
            log_message('info', 'Books fetched from cache: ' . count($cachedBooks) . ' books');
            return $cachedBooks;
        }

        $allBooks = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllBooks with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit' => $limit,
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
                $offset += $limit;
                
                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total books fetched: ' . count($allBooks));
        
        // Cache for 5 minutes
        $this->cache->save($cacheKey, $allBooks, 300);
        
        return $allBooks;
    }

    private function fetchAllTransactions($queryParams = [])
    {
        $allTransactions = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllTransactions with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit' => $limit,
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

    // Update trust score
    private function updateTrustScore($userId, $borrowDate, $dueDate)
    {
        // Get user data
        $user = $this->supabaseRequest('GET', 'users', null, [
            'id' => 'eq.' . $userId,
            'select' => 'trust_score',
            'limit' => 1
        ]);

        if (isset($user['error']) || empty($user)) {
            log_message('error', 'Failed to get user for trust score update');
            return false;
        }

        $currentScore = (float)($user[0]['trust_score'] ?? 100);
        $returnDate = strtotime(date('Y-m-d'));
        $dueTimestamp = strtotime($dueDate);

        // Calculate new score
        if ($returnDate <= $dueTimestamp) {
            $newScore = $currentScore + 1; //tepat waktu
            $status = 'ontime';
        } else {
            $newScore = $currentScore; //telat, tidak ada perubahan
            $status = 'late';
        }

        // Keep score with no upper limit, only ensure non-negative
        $newScore = max(0, $newScore);

        // Update user
        $updateResult = $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
            'trust_score' => $newScore
        ]);

        // Clear cache
        $this->cache->delete('all_users_automate_' . md5(json_encode(['select' => '*'])));

        log_message('info', "Trust score updated for user $userId: $currentScore -> $newScore ($status)");

        return !isset($updateResult['error']);
    }

    public function automateView()
    {
        return view('peminjaman_automatis');
    }

    public function automateTransaction()
    {
        $uidScan = trim($this->request->getPost('uid'));
        $nisn = trim($this->request->getPost('nisn') ?? '');

        if (empty($uidScan)) {
            return $this->response->setJSON(['success' => false, 'message' => 'UID wajib diisi']);
        }

        if (empty($nisn)) {
            return $this->response->setJSON(['success' => false, 'message' => 'NISN/NIP wajib diisi']);
        }

        try {
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
                    if (strcasecmp(trim($bookUid), $uidScan) === 0) {
                        $bookData = $book;
                        break 2;
                    }
                }
            }

            if (!$bookData) {
                return $this->response->setJSON(['success' => false, 'message' => 'UID buku tidak ditemukan']);
            }

            // Cari dari nisn/nip
            $userData = null;
            
            $userByNisn = $this->supabaseRequest('GET', 'users', null, [
                'nisn' => 'eq.' . $nisn,
                'limit' => 1
            ]);
            
            if (!isset($userByNisn['error']) && !empty($userByNisn)) {
                $userData = $userByNisn[0];
            }
            
            if (!$userData) {
                $userByNip = $this->supabaseRequest('GET', 'users', null, [
                    'nip' => 'eq.' . $nisn,
                    'limit' => 1
                ]);
                
                if (!isset($userByNip['error']) && !empty($userByNip)) {
                    $userData = $userByNip[0];
                }
            }
            
            if (!$userData) {
                return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
            }

            // Validasi histori transaksi dengan pagination
            $activeTx = $this->fetchAllTransactions([
                'uid' => 'eq.' . $uidScan,
                'status' => 'eq.active',
                'type' => 'eq.borrow'
            ]);
            
            $type = (!empty($activeTx)) ? 'return' : 'borrow';

            // Get PIC info from session
            $picName = session()->get('name') ?? 'Admin';
            $picUsername = session()->get('username') ?? 'admin';
            $picId = session()->get('user_id') ?? null;

            if ($type === 'borrow') {

                // Peminjaman
                $currentQty = (int)($bookData['quantity'] ?? 0);
                if ($currentQty < 1) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Stok buku habis']);
                }

                $trustScore = (float)($userData['trust_score'] ?? 100);
                $maxBorrow = (int)($userData['maxBorrow'] ?? 1);
                
                // Count active borrows with pagination
                $userActiveBorrows = $this->fetchAllTransactions([
                    'user_id' => 'eq.' . $userData['id'],
                    'type' => 'eq.borrow',
                    'status' => 'eq.active'
                ]);
                
                $activeBorrowCount = count($userActiveBorrows);

                if ($activeBorrowCount >= $maxBorrow) {
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai. Trust Score: " . number_format($trustScore, 1)
                    ]);
                }

                // Calculate due date (default 7 hari)
                $isOneDayBook = $bookData['is_one_day_book'] ?? false;
                $dueDays = $isOneDayBook ? 1 : 7;
                $dueDate = date('Y-m-d', strtotime("+$dueDays days"));

                $transactionData = [
                    'user_id' => $userData['id'],
                    'book_id' => $bookData['id'],
                    'uid' => $uidScan,
                    'type' => 'borrow',
                    'tanggal' => date('Y-m-d'),
                    'due_date' => $dueDate,
                    'status' => 'active',
                    'pic_name' => $picName,
                    'pic_username' => $picUsername,
                    'pic_id' => $picId,
                    'transaction_location' => 'perpustakaan',
                    'created_at' => date('Y-m-d H:i:s'),
                    'completed_at' => null,
                    'completed_by_name' => null,
                    'completed_by_username' => null
                ];

                $insertTx = $this->supabaseRequest('POST', 'transactions', $transactionData);
                
                if (isset($insertTx['error'])) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan transaksi peminjaman']);
                }

                // Update book quantity
                $newQuantity = $currentQty - 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookData['id'], [
                    'quantity' => $newQuantity,
                    'available' => $newQuantity > 0
                ]);

                // Clear cache
                $this->cache->delete('all_books_automate_' . md5(json_encode([])));

                log_message('info', 'Borrowing success: User=' . $userData['nama'] . ', Book=' . $bookData['title']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Peminjaman berhasil',
                    'book' => $bookData['title'] ?? '-',
                    'type' => 'borrow',
                    'user' => $userData['nama'] ?? '-',
                    'due_date' => $dueDate,
                    'trust_score' => number_format($trustScore, 1),
                    'max_borrow' => $maxBorrow
                ]);

            } else {

                // RETURN
                $borrowTx = $activeTx[0];
                $borrowDate = $borrowTx['tanggal'];
                $dueDate = $borrowTx['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));
                $transactionData = [
                    'user_id' => $userData['id'],
                    'book_id' => $bookData['id'],
                    'uid' => $uidScan,
                    'type' => 'return',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'completed',
                    'pic_name' => $picName,
                    'pic_username' => $picUsername,
                    'pic_id' => $picId,
                    'transaction_location' => 'perpustakaan',
                    'created_at' => date('Y-m-d H:i:s'),
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => $picName,
                    'completed_by_username' => $picUsername,
                    'due_date' => $dueDate
                ];

                $insertReturn = $this->supabaseRequest('POST', 'transactions', $transactionData);
                
                if (isset($insertReturn['error'])) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan transaksi pengembalian']);
                }

                // Update borrow transaction status
                $activeTxId = $borrowTx['id'];
                $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $activeTxId, [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => $picName,
                    'completed_by_username' => $picUsername
                ]);

                // Update book quantity
                $currentQty = (int)($bookData['quantity'] ?? 0);
                $newQuantity = $currentQty + 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookData['id'], [
                    'quantity' => $newQuantity,
                    'available' => true
                ]);

                // Clear cache
                $this->cache->delete('all_books_automate_' . md5(json_encode([])));

                // Update trust score
                $this->updateTrustScore($userData['id'], $borrowDate, $dueDate);

                // Get updated user data
                $updatedUser = $this->supabaseRequest('GET', 'users', null, [
                    'id' => 'eq.' . $userData['id'],
                    'select' => 'trust_score',
                    'limit' => 1
                ]);

                $newTrustScore = !isset($updatedUser['error']) && !empty($updatedUser) 
                    ? (float)$updatedUser[0]['trust_score'] 
                    : (float)($userData['trust_score'] ?? 100);

                $newMaxBorrow = (int)($updatedUser[0]['maxBorrow'] ?? $userData['maxBorrow'] ?? 1);

                // Check if late
                $isLate = strtotime(date('Y-m-d')) > strtotime($dueDate);
                $lateMessage = $isLate ? ' (TERLAMBAT: -2 poin)' : ' (TEPAT WAKTU: +0.5 poin)';

                log_message('info', 'Return success: User=' . $userData['nama'] . ', Book=' . $bookData['title']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Pengembalian berhasil' . $lateMessage,
                    'book' => $bookData['title'] ?? '-',
                    'type' => 'return',
                    'user' => $userData['nama'] ?? '-',
                    'trust_score' => number_format($newTrustScore, 1),
                    'max_borrow' => $newMaxBorrow,
                    'was_late' => $isLate
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