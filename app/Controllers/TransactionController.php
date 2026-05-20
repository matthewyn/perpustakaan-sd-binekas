<?php

namespace App\Controllers;

use CodeIgniter\Controller;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class TransactionController extends Controller
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
        curl_close($ch);

        if ($httpCode >= 400) {
            return ['error' => 'HTTP Error ' . $httpCode];
        }

        return json_decode($response, true);
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

    private function fetchAllBooks($queryParams = [])
    {
        $cacheKey = 'all_books_' . md5(json_encode($queryParams));
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

    private function fetchAllUsers($queryParams = [])
    {
        $cacheKey = 'all_users_' . md5(json_encode($queryParams));
        $cachedUsers = $this->cache->get($cacheKey);

        if ($cachedUsers !== null) {
            log_message('info', 'Users fetched from cache: ' . count($cachedUsers) . ' users');
            return $cachedUsers;
        }

        $allUsers = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllUsers with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit' => $limit,
                'offset' => $offset
            ]);

            $users = $this->supabaseRequest('GET', 'users', null, $params);

            if (isset($users['error']) || !is_array($users)) {
                log_message('error', 'Error fetching users at offset ' . $offset);
                break;
            }

            $count = count($users);
            log_message('info', "Fetched {$count} users at offset {$offset}");

            if ($count > 0) {
                $allUsers = array_merge($allUsers, $users);
                $offset += $limit;
                
                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total users fetched: ' . count($allUsers));
        
        // Cache for 5 minutes
        $this->cache->save($cacheKey, $allUsers, 300);
        
        return $allUsers;
    }

    /**
     * Fetch all classes with pagination
     */
    private function fetchAllClasses($queryParams = [])
    {
        // Try cache first
        $cacheKey = 'all_classes_' . md5(json_encode($queryParams));
        $cachedClasses = $this->cache->get($cacheKey);

        if ($cachedClasses !== null) {
            log_message('info', 'Classes fetched from cache: ' . count($cachedClasses) . ' classes');
            return $cachedClasses;
        }

        $allClasses = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllClasses with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit' => $limit,
                'offset' => $offset
            ]);

            $classes = $this->supabaseRequest('GET', 'classes', null, $params);

            if (isset($classes['error']) || !is_array($classes)) {
                log_message('error', 'Error fetching classes at offset ' . $offset);
                break;
            }

            $count = count($classes);
            log_message('info', "Fetched {$count} classes at offset {$offset}");

            if ($count > 0) {
                $allClasses = array_merge($allClasses, $classes);
                $offset += $limit;
                
                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total classes fetched: ' . count($allClasses));
        
        // Cache for 5 minutes
        $this->cache->save($cacheKey, $allClasses, 300);
        
        return $allClasses;
    }

    public function peminjaman()
    {
        $currentPicName = session()->get('name');
        $currentRole = session()->get('role');

        // Get all transactions with pagination
        $transactions = $this->fetchAllTransactions([
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        // Get users, classes, books with pagination and caching
        $users = $this->fetchAllUsers(['select' => '*']);
        $classes = $this->fetchAllClasses(['select' => '*']);
        $books = $this->fetchAllBooks(['select' => '*']);
        
        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $classesById = [];
        foreach ($classes as $class) {
            $classesById[$class['id']] = $class;
        }

        $booksById = [];
        foreach ($books as $book) {
            $booksById[$book['id']] = $book;
        }

        // Proses transactions
        $borrowRows = [];
        $returnRows = [];

        foreach ($transactions as $t) {
            if ($currentRole !== 'admin' && ($t['pic_name'] ?? null) !== $currentPicName) {
                continue;
            }

            $userId = $t['user_id'] ?? null;
            $bookId = $t['book_id'] ?? null;

            $user = $userId && isset($usersById[$userId]) ? $usersById[$userId] : null;
            $nama = $user ? ($user['nama'] ?? '-') : '-';
            
            // Get class name from user's class_id
            $classId = $user ? ($user['class_id'] ?? null) : null;
            $className = ($classId && isset($classesById[$classId])) ? ($classesById[$classId]['nama_kelas'] ?? '-') : '-';
            
            // Try to get judul from books table
            $judul = '-';
            if ($bookId) {
                if (isset($booksById[$bookId])) {
                    // Book found in our cache
                    $judul = $booksById[$bookId]['title'] ?? '-';
                } else {
                    // Book not in cache, try to fetch it individually
                    $singleBook = $this->supabaseRequest('GET', 'books', null, [
                        'id' => 'eq.' . $bookId,
                        'limit' => 1
                    ]);
                    
                    if (!isset($singleBook['error']) && !empty($singleBook)) {
                        $judul = $singleBook[0]['title'] ?? '-';
                        // Cache it for future use in this loop
                        $booksById[$bookId] = $singleBook[0];
                    }
                }
            }

            $row = [
                'nama' => $nama,
                'judul' => $judul,
                'class' => $className,
                'tanggal' => $t['tanggal'] ?? '-',
                'status' => $t['status'] ?? 'active',
                'user_id' => $userId,
                'book_id' => $bookId
            ];

            if ($t['type'] === 'borrow') {
                $borrowRows[] = $row;
            } elseif ($t['type'] === 'return') {
                $returnRows[] = $row;
            }
        }

        // Calculate statistics
        $totalAvailable = count($books);
        
        // Chart data (daily, monthly, yearly)
        $borrowingsByDay = [];
        $returnsByDay = [];

        foreach ($transactions as $t) {
            $date = $t['tanggal'] ?? null;
            if (!$date) continue;

            if ($t['type'] === 'borrow') {
                $borrowingsByDay[$date] = ($borrowingsByDay[$date] ?? 0) + 1;
            } elseif ($t['type'] === 'return') {
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
            
            $borrowingsByMonth[$month] = ($borrowingsByMonth[$month] ?? 0) + $count;
            $borrowingsByYear[$year] = ($borrowingsByYear[$year] ?? 0) + $count;
        }

        foreach ($returnsByDay as $date => $count) {
            $month = substr($date, 0, 7);
            $year = substr($date, 0, 4);
            
            $returnsByMonth[$month] = ($returnsByMonth[$month] ?? 0) + $count;
            $returnsByYear[$year] = ($returnsByYear[$year] ?? 0) + $count;
        }

        $chartData = [
            'harian' => ['borrowings' => $borrowingsByDay, 'returns' => $returnsByDay],
            'bulanan' => ['borrowings' => $borrowingsByMonth, 'returns' => $returnsByMonth],
            'tahunan' => ['borrowings' => $borrowingsByYear, 'returns' => $returnsByYear],
        ];

        $currentMonth = date('Y-m');
        $prevMonth = date('Y-m', strtotime('-1 month'));

        $totalBorrowed = $borrowingsByMonth[$currentMonth] ?? 0;
        $totalReturned = $returnsByMonth[$currentMonth] ?? 0;
        $prevBorrowed = $borrowingsByMonth[$prevMonth] ?? 0;
        $prevReturned = $returnsByMonth[$prevMonth] ?? 0;

        $totalBorrowedPercent = $prevBorrowed > 0 
            ? round((($totalBorrowed - $prevBorrowed) / $prevBorrowed) * 100, 1)
            : 0;

        $totalReturnedPercent = $prevReturned > 0 
            ? round((($totalReturned - $prevReturned) / $prevReturned) * 100, 1)
            : 0;

        // Calculate percentage change based on net books (borrowed - returned)
        $currentNetBooks = $totalBorrowed - $totalReturned;
        $prevNetBooks = $prevBorrowed - $prevReturned;
        
        $totalAvailablePercent = $prevNetBooks > 0 
            ? round((($currentNetBooks - $prevNetBooks) / $prevNetBooks) * 100, 1)
            : ($currentNetBooks > 0 ? 100 : 0);

        $data = [
            'borrowings' => $borrowRows,
            'returns' => $returnRows,
            'totalBorrowed' => $totalBorrowed,
            'totalReturned' => $totalReturned,
            'totalAvailable' => $totalAvailable,
            'totalBorrowedPercent' => $totalBorrowedPercent,
            'totalReturnedPercent' => $totalReturnedPercent,
            'totalAvailablePercent' => $totalAvailablePercent,
            'chartData' => $chartData,
        ];

        return view('peminjaman_perpustakaan', $data);
    }

    public function addBorrowing()
    {
        try {
            $userId = $this->request->getPost('user_id');
            $bookId = $this->request->getPost('book_id');
            $namaCari = $this->request->getPost('namaCari');
            $judulCari = $this->request->getPost('judulCari');
            $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');
            $picName = session()->get('name');
            $picUsername = session()->get('username');
            $picId = session()->get('user_id');

            if (!empty($namaCari) && empty($userId)) {
                $users = $this->supabaseRequest('GET', 'users', null, [
                    'nama' => 'ilike.' . $namaCari,
                    'role' => 'eq.murid',
                    'limit' => 1
                ]);

                if (isset($users['error']) || empty($users)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Siswa tidak ditemukan'
                    ]);
                }

                $userId = $users[0]['id'];
            }

            if (!empty($judulCari) && empty($bookId)) {
                $books = $this->supabaseRequest('GET', 'books', null, [
                    'title' => 'ilike.' . $judulCari,
                    'limit' => 1
                ]);

                if (isset($books['error']) || empty($books)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Buku tidak ditemukan'
                    ]);
                }

                $bookId = $books[0]['id'];
            }

            if (empty($userId) || empty($bookId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User dan buku wajib dipilih'
                ]);
            }

            // Get book data
            $book = $this->supabaseRequest('GET', 'books', null, [
                'id' => 'eq.' . $bookId,
                'limit' => 1
            ]);

            if (isset($book['error']) || empty($book)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Buku tidak ditemukan'
                ]);
            }

            $bookData = $book[0];
            $currentQty = (int)($bookData['quantity'] ?? 0);

            if ($currentQty < 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Stok buku habis'
                ]);
            }

            // Get user data for trust score validation
            $user = $this->supabaseRequest('GET', 'users', null, [
                'id' => 'eq.' . $userId,
                'limit' => 1
            ]);

            if (isset($user['error']) || empty($user)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ]);
            }

            $userData = $user[0];
            $maxBorrow = (int)($userData['maxBorrow'] ?? 1);

            // Count active borrows using pagination
            $userActiveBorrows = $this->fetchAllTransactions([
                'user_id' => 'eq.' . $userId,
                'type' => 'eq.borrow',
                'status' => 'eq.active'
            ]);

            $activeBorrowCount = count($userActiveBorrows);

            if ($activeBorrowCount >= $maxBorrow) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai"
                ]);
            }

            // Calculate due date
            $isOneDayBook = $bookData['is_one_day_book'] ?? false;
            $dueDays = $isOneDayBook ? 1 : 7;
            $dueDate = date('Y-m-d', strtotime($tanggal . " +$dueDays days"));

            $transactionData = [
                'user_id' => $userId,
                'book_id' => $bookId,
                'type' => 'borrow',
                'tanggal' => $tanggal,
                'due_date' => $dueDate,
                'status' => 'active',
                'pic_name' => $picName,
                'pic_username' => $picUsername,
                'pic_id' => $picId,
                'transaction_location' => 'perpustakaan',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->supabaseRequest('POST', 'transactions', $transactionData);

            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi peminjaman'
                ]);
            }

            // Update num_borrows for user
            $numBorrows = (int)($userData['num_borrows'] ?? 0);
            $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
                'num_borrows' => $numBorrows + 1
            ]);

            $newQuantity = $currentQty - 1;
            $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                'quantity' => $newQuantity,
                'available' => $newQuantity > 0
            ]);

            $this->cache->delete('all_books_' . md5(json_encode(['select' => '*'])));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Peminjaman berhasil dicatat'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in addBorrowing: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function addReturn()
    {
        try {
            $selectedLoans = $this->request->getPost('selectedLoans');
            $loanIds = $this->request->getPost('loan_id');
            
            $loansToProcess = [];
            $loanStatusMap = [];

            if (!empty($selectedLoans)) {
                $decoded = json_decode($selectedLoans, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $loan) {
                        $loanId = $loan['loanId'] ?? $loan['loan_id'] ?? null;
                        if ($loanId) {
                            $loansToProcess[] = $loanId;
                            $loanStatusMap[$loanId] = $loan['status'] ?? 'baik';
                        }
                    }
                }
            }
            elseif (!empty($loanIds)) {
                if (!is_array($loanIds)) {
                    $loansToProcess = [$loanIds];
                } else {
                    $loansToProcess = $loanIds;
                }
            }

            if (empty($loansToProcess)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pilih minimal satu peminjaman untuk dikembalikan'
                ]);
            }

            $processedCount = 0;
            $errors = [];

            foreach ($loansToProcess as $loanId) {
                if (empty($loanId)) continue;

                // Get borrow transaction
                $borrowTransaction = $this->supabaseRequest('GET', 'transactions', null, [
                    'id' => 'eq.' . $loanId,
                    'type' => 'eq.borrow',
                    'status' => 'eq.active',
                    'limit' => 1
                ]);

                if (isset($borrowTransaction['error']) || empty($borrowTransaction)) {
                    $errors[] = 'Peminjaman ID ' . $loanId . ' tidak ditemukan';
                    continue;
                }

                $borrow = $borrowTransaction[0];
                $userId = $borrow['user_id'];
                $bookId = $borrow['book_id'];
                $borrowDate = $borrow['tanggal'];
                $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));
                $bookCondition = $loanStatusMap[$loanId] ?? 'baik';

                // Create return transaction
                $returnData = [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                    'type' => 'return',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'completed',
                    'book_condition' => $bookCondition,
                    'pic_name' => session()->get('name'),
                    'pic_username' => session()->get('username'),
                    'pic_id' => session()->get('user_id'),
                    'transaction_location' => 'perpustakaan',
                    'created_at' => date('Y-m-d H:i:s'),
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => session()->get('name'),
                    'completed_by_username' => session()->get('username'),
                    'due_date' => $dueDate
                ];

                $result = $this->supabaseRequest('POST', 'transactions', $returnData);

                if (isset($result['error'])) {
                    $errors[] = 'Gagal menyimpan pengembalian untuk peminjaman ID ' . $loanId;
                    continue;
                }

                // Update borrow status
                $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $loanId, [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => session()->get('name'),
                    'completed_by_username' => session()->get('username')
                ]);

                // Update book quantity
                $book = $this->supabaseRequest('GET', 'books', null, [
                    'id' => 'eq.' . $bookId,
                    'limit' => 1
                ]);

                if (!isset($book['error']) && !empty($book)) {
                    $currentQty = (int)($book[0]['quantity'] ?? 0);
                    $newQty = $currentQty + 1;
                    $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                        'quantity' => $newQty,
                        'available' => true
                    ]);
                }

                // Recalculate trust score
                $newScore = $this->calculateTrustScore($userId);
                $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
                    'trust_score' => $newScore
                ]);

                $processedCount++;
            }

            // Clear relevant caches
            $this->cache->delete('all_books_' . md5(json_encode(['select' => '*'])));
            $this->cache->delete('all_users_' . md5(json_encode(['select' => '*'])));

            $message = 'Pengembalian berhasil untuk ' . $processedCount . ' buku!';
            if (!empty($errors)) {
                $message .= ' Terdapat ' . count($errors) . ' kesalahan.';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'processed' => $processedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in addReturn: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
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

        $trustScore = $L * (
            ($a1 * $f1) +
            ($a2 * $f2) +
            ($a3 * $f3)
        );

        // Normalize score
        return round(
            min(1000, max(0, $trustScore)),
            2
        );
    }

    public function apiBorrowings()
    {
        try {
            $currentRole = session()->get('role');
            $currentPicName = session()->get('name');
            $classIdFilter = $this->request->getVar('class_id');

            log_message('info', 'apiBorrowings called with class_id filter: ' . ($classIdFilter ?? 'none'));

            $params = [
                'type' => 'eq.borrow',
                'status' => 'eq.active',
                'select' => '*',
                'order' => 'created_at.desc'
            ];

            // Apply PIC filter for non-admin
            if ($currentRole !== 'admin') {
                $params['pic_name'] = 'eq.' . $currentPicName;
            }

            // Fetch all transactions with pagination
            $transactions = $this->fetchAllTransactions($params);

            if (!empty($transactions)) {
                $allUsers = $this->fetchAllUsers(['select' => 'id,nama,class_id']);
                
                // Create user lookup map
                $userMap = [];
                foreach ($allUsers as $user) {
                    $userMap[$user['id']] = [
                        'nama' => $user['nama'] ?? '-',
                        'class_id' => $user['class_id'] ?? null
                    ];
                }

                // Fetch all books with pagination and caching
                $allBooks = $this->fetchAllBooks(['select' => 'id,title']);
                
                // Create book lookup map
                $bookMap = [];
                foreach ($allBooks as $book) {
                    if (isset($book['id']) && isset($book['title'])) {
                        $bookId = (string)$book['id'];
                        $bookMap[$bookId] = $book['title'];
                    }
                }
                
                $activeTransactions = [];
                foreach ($transactions as $t) {
                    // Add book title
                    if (!empty($t['book_id'])) {
                        $bookId = (string)$t['book_id'];
                        $t['book_title'] = isset($bookMap[$bookId]) ? $bookMap[$bookId] : '-';
                    } else {
                        $t['book_title'] = '-';
                    }

                    // Add user info and class_id
                    if (!empty($t['user_id']) && isset($userMap[$t['user_id']])) {
                        $t['user_name'] = $userMap[$t['user_id']]['nama'];
                        $t['user_class_id'] = $userMap[$t['user_id']]['class_id'];
                    } else {
                        $t['user_name'] = '-';
                        $t['user_class_id'] = null;
                    }
                    
                    // Filter by class_id if specified
                    if ($classIdFilter !== null) {
                        if ($t['user_class_id'] == $classIdFilter) {
                            $activeTransactions[] = $t;
                        }
                    } else {
                        $activeTransactions[] = $t;
                    }
                }
                
                $transactions = $activeTransactions;
            }

            return $this->response->setJSON([
                'success' => true,
                'borrowings' => $transactions
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in apiBorrowings: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'borrowings' => []
            ]);
        }
    }

    public function apiAllBorrowings()
    {
        try {
            $currentRole = session()->get('role');
            $currentPicName = session()->get('name');
            $classIdFilter = $this->request->getVar('class_id'); 
            $page = (int)($this->request->getVar('page') ?? 1);
            $limit = (int)($this->request->getVar('limit') ?? 10);

            log_message('info', 'apiAllBorrowings called with class_id filter: ' . ($classIdFilter ?? 'none'));

            $allParams = [
                'type' => 'eq.borrow',
                'select' => '*',
                'order' => 'created_at.desc'
            ];

            if ($currentRole !== 'admin') {
                $allParams['pic_name'] = 'eq.' . $currentPicName;
            }

            $allTransactions = $this->fetchAllTransactions($allParams);

            if ($classIdFilter !== null && !empty($allTransactions)) {
                $allUsers = $this->fetchAllUsers(['select' => 'id,class_id']);
                $userClassMap = [];
                foreach ($allUsers as $user) {
                    $userClassMap[$user['id']] = $user['class_id'] ?? null;
                }

                $filteredTransactions = [];
                foreach ($allTransactions as $t) {
                    $userClassId = isset($userClassMap[$t['user_id']]) ? $userClassMap[$t['user_id']] : null;
                    if ($userClassId == $classIdFilter) {
                        $filteredTransactions[] = $t;
                    }
                }
                $allTransactions = $filteredTransactions;
            }

            $totalCount = count($allTransactions);
            $offset = ($page - 1) * $limit;
            $transactions = array_slice($allTransactions, $offset, $limit);

            if (!empty($transactions)) {
                $bookIds = array_unique(array_filter(array_column($transactions, 'book_id')));
                
                if (!empty($bookIds)) {
                    $books = $this->supabaseRequest('GET', 'books', null, [
                        'id' => 'in.(' . implode(',', $bookIds) . ')',
                        'select' => 'id,title'
                    ]);
                    
                    $bookMap = [];
                    if (!isset($books['error']) && is_array($books)) {
                        foreach ($books as $book) {
                            $bookMap[$book['id']] = $book['title'];
                        }
                    }
                    
                    foreach ($transactions as &$t) {
                        $t['book_title'] = isset($bookMap[$t['book_id']]) ? $bookMap[$t['book_id']] : '-';
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'borrowings' => $transactions,
                'totalCount' => $totalCount,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in apiAllBorrowings: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'borrowings' => [],
                'totalCount' => 0
            ]);
        }
    }

    public function apiReturns()
    {
        try {
            $currentRole = session()->get('role');
            $currentPicName = session()->get('name');
            $classIdFilter = $this->request->getVar('class_id');

            $params = [
                'type' => 'eq.return',
                'select' => '*',
                'order' => 'created_at.desc'
            ];

            if ($currentRole !== 'admin') {
                $params['pic_name'] = 'eq.' . $currentPicName;
            }

            $transactions = $this->fetchAllTransactions($params);

            if ($classIdFilter !== null && !empty($transactions)) {
                $allUsers = $this->fetchAllUsers(['select' => 'id,nama,class_id']);
                
                $userMap = [];
                foreach ($allUsers as $user) {
                    $userMap[$user['id']] = [
                        'nama' => $user['nama'] ?? '-',
                        'class_id' => $user['class_id'] ?? null
                    ];
                }

                $filteredTransactions = [];
                foreach ($transactions as $t) {
                    if (!empty($t['user_id']) && isset($userMap[$t['user_id']])) {
                        $t['user_name'] = $userMap[$t['user_id']]['nama'];
                        $t['user_class_id'] = $userMap[$t['user_id']]['class_id'];
                        
                        if ($t['user_class_id'] == $classIdFilter) {
                            $filteredTransactions[] = $t;
                        }
                    }
                }
                $transactions = $filteredTransactions;
            }

            return $this->response->setJSON([
                'success' => true,
                'returns' => $transactions
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in apiReturns: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'returns' => []
            ]);
        }
    }

    public function apiAllReturns()
    {
        try {
            $currentRole = session()->get('role');
            $currentPicName = session()->get('name');
            $classIdFilter = $this->request->getVar('class_id');
            $page = (int)($this->request->getVar('page') ?? 1);
            $limit = (int)($this->request->getVar('limit') ?? 10);

            $allParams = [
                'type' => 'eq.return',
                'select' => '*',
                'order' => 'created_at.desc'
            ];

            if ($currentRole !== 'admin') {
                $allParams['pic_name'] = 'eq.' . $currentPicName;
            }

            $allTransactions = $this->fetchAllTransactions($allParams);

            if ($classIdFilter !== null && !empty($allTransactions)) {
                $allUsers = $this->fetchAllUsers(['select' => 'id,class_id']);
                $userClassMap = [];
                foreach ($allUsers as $user) {
                    $userClassMap[$user['id']] = $user['class_id'] ?? null;
                }

                $filteredTransactions = [];
                foreach ($allTransactions as $t) {
                    $userClassId = isset($userClassMap[$t['user_id']]) ? $userClassMap[$t['user_id']] : null;
                    if ($userClassId == $classIdFilter) {
                        $filteredTransactions[] = $t;
                    }
                }
                $allTransactions = $filteredTransactions;
            }

            $totalCount = count($allTransactions);
            $offset = ($page - 1) * $limit;
            $transactions = array_slice($allTransactions, $offset, $limit);

            if (!empty($transactions)) {
                $bookIds = array_unique(array_filter(array_column($transactions, 'book_id')));
                
                if (!empty($bookIds)) {
                    $books = $this->supabaseRequest('GET', 'books', null, [
                        'id' => 'in.(' . implode(',', $bookIds) . ')',
                        'select' => 'id,title'
                    ]);
                    
                    $bookMap = [];
                    if (!isset($books['error']) && is_array($books)) {
                        foreach ($books as $book) {
                            $bookMap[$book['id']] = $book['title'];
                        }
                    }
                    
                    foreach ($transactions as &$t) {
                        $t['book_title'] = isset($bookMap[$t['book_id']]) ? $bookMap[$t['book_id']] : '-';
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'returns' => $transactions,
                'totalCount' => $totalCount,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in apiAllReturns: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'returns' => [],
                'totalCount' => 0
            ]);
        }
    }
}