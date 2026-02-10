<?php

namespace App\Controllers;

use CodeIgniter\Controller;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class ClassTransactionController extends Controller
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

    /**
     * Fetch all transactions with pagination
     */
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

    /**
     * Fetch all books with pagination and caching
     */
    private function fetchAllBooks($queryParams = [])
    {
        // Try cache first
        $cacheKey = 'all_books_class_' . md5(json_encode($queryParams));
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

    /**
     * Fetch all users with pagination and caching
     */
    private function fetchAllUsers($queryParams = [])
    {
        // Try cache first
        $cacheKey = 'all_users_class_' . md5(json_encode($queryParams));
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

    public function index()
    {
        $classes = $this->getClassesFromCache();

        $data = [
            'classes' => $classes,
            'borrowings' => [],
            'returns' => []
        ];

        return view('peminjaman_kelas', $data);
    }

    private function getClassesFromCache()
    {
        $cacheKey = 'classes_list';
        $classes = $this->cache->get($cacheKey);

        if ($classes === null) {
            $classes = $this->supabaseRequest('GET', 'classes', null, [
                'select' => '*',
                'order' => 'nama_kelas.asc'
            ]);

            if (isset($classes['error'])) {
                $classes = [];
            }

            $this->cache->save($cacheKey, $classes, 300); // Cache for 5 minutes
        }

        return $classes;
    }

    public function getClassData()
    {
        $classId = $this->request->getGet('class_id');

        if (empty($classId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Class ID tidak ditemukan'
            ]);
        }

        // Try cache first
        $cacheKey = 'class_data_' . $classId;
        $cachedData = $this->cache->get($cacheKey);

        if ($cachedData !== null) {
            return $this->response->setJSON($cachedData);
        }

        // Get class data
        $class = $this->supabaseRequest('GET', 'classes', null, [
            'id' => 'eq.' . $classId,
            'limit' => 1
        ]);

        if (isset($class['error']) || empty($class)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ]);
        }

        $classData = $class[0];

        $students = $this->supabaseRequest('GET', 'users', null, [
            'class_id' => 'eq.' . $classId,
            'role' => 'eq.murid',
            'select' => '*',
            'order' => 'nama.asc'
        ]);

        if (isset($students['error'])) {
            $students = [];
        }

        $classBooks = $this->supabaseRequest('GET', 'class_books', null, [
            'class_id' => 'eq.' . $classId,
            'select' => '*'
        ]);

        $books = [];
        if (!isset($classBooks['error']) && !empty($classBooks)) {
            $bookIds = array_column($classBooks, 'book_id');
            
            if (!empty($bookIds)) {
                $allBooks = $this->supabaseRequest('GET', 'books', null, [
                    'id' => 'in.(' . implode(',', $bookIds) . ')',
                    'select' => '*'
                ]);

                if (!isset($allBooks['error'])) {
                    // Map quantity from class_books to books
                    $quantityMap = [];
                    foreach ($classBooks as $cb) {
                        $quantityMap[$cb['book_id']] = $cb['quantity'];
                    }

                    foreach ($allBooks as $book) {
                        $book['class_quantity'] = $quantityMap[$book['id']] ?? 0;
                        $books[] = $book;
                    }
                }
            }
        }

        $responseData = [
            'success' => true,
            'class' => $classData,
            'students' => $students,
            'books' => $books
        ];

        // Cache the result
        $this->cache->save($cacheKey, $responseData, 300); // 5 minutes

        return $this->response->setJSON($responseData);
    }

    public function getAllBooks()
    {
        $books = $this->fetchAllBooks([
            'select' => '*',
            'order' => 'title.asc'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'books' => $books
        ]);
    }

    public function getClassTransactions()
    {
        $classId = $this->request->getVar('class_id');
        $type = $this->request->getVar('type');

        if (!$classId) {
            return $this->response->setJSON([
                'success' => false,
                'transactions' => []
            ]);
        }

        // Get class name
        $class = $this->supabaseRequest('GET', 'classes', null, [
            'id' => 'eq.' . $classId,
            'limit' => 1
        ]);

        if (isset($class['error']) || empty($class)) {
            return $this->response->setJSON([
                'success' => false,
                'transactions' => []
            ]);
        }

        $className = $class[0]['nama_kelas'];

        $students = $this->fetchAllUsers([
            'class_id' => 'eq.' . $classId,
            'role' => 'eq.murid',
            'select' => 'id,nama'
        ]);

        $studentIds = array_column($students, 'id');
        $studentMap = [];
        foreach ($students as $student) {
            $studentMap[$student['id']] = $student['nama'];
        }

        if (empty($studentIds)) {
            return $this->response->setJSON([
                'success' => true,
                'transactions' => []
            ]);
        }

        $queryParams = [
            'user_id' => 'in.(' . implode(',', $studentIds) . ')',
            'select' => '*',
            'order' => 'created_at.desc'
        ];

        if ($type) {
            $queryParams['type'] = 'eq.' . $type;
        }

        $transactions = $this->fetchAllTransactions($queryParams);
        $allBooks = $this->fetchAllBooks(['select' => 'id,title']);

        $bookMap = [];
        foreach ($allBooks as $book) {
            $bookMap[$book['id']] = $book['title'];
        }

        foreach ($transactions as &$transaction) {
            $transaction['user_name'] = $studentMap[$transaction['user_id']] ?? '-';
            $transaction['book_title'] = $bookMap[$transaction['book_id']] ?? '-';
            $transaction['borrowed_from'] = $transaction['transaction_location'] ?? 'perpustakaan';
        }

        return $this->response->setJSON([
            'success' => true,
            'transactions' => $transactions
        ]);
    }

    public function addBorrowing()
    {
        try {
            $classId = $this->request->getPost('class_id');
            $userId = $this->request->getPost('user_id');
            $bookId = $this->request->getPost('book_id');
            $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');

            if (empty($classId) || empty($userId) || empty($bookId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Class, User, dan Buku wajib dipilih'
                ]);
            }

            // Get class name
            $class = $this->supabaseRequest('GET', 'classes', null, [
                'id' => 'eq.' . $classId,
                'limit' => 1
            ]);

            if (isset($class['error']) || empty($class)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kelas tidak ditemukan'
                ]);
            }

            $className = $class[0]['nama_kelas'];

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
            $bookQuantity = (int)($bookData['quantity'] ?? 0);

            if ($bookQuantity < 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Stok buku di perpustakaan habis'
                ]);
            }

            $classBook = $this->supabaseRequest('GET', 'class_books', null, [
                'class_id' => 'eq.' . $classId,
                'book_id' => 'eq.' . $bookId,
                'limit' => 1
            ]);

            $classBookQty = 0;
            if (!isset($classBook['error']) && !empty($classBook)) {
                $classBookQty = (int)($classBook[0]['quantity'] ?? 0);
            }

            // Get user data
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
                'pic_name' => session()->get('name'),
                'pic_username' => session()->get('username'),
                'pic_id' => session()->get('user_id'),
                'transaction_location' => $className, // This is correct
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->supabaseRequest('POST', 'transactions', $transactionData);

            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi peminjaman'
                ]);
            }

            // Update main books quantity (REQUIRED)
            $newBookQty = $bookQuantity - 1;
            $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                'quantity' => $newBookQty,
                'available' => $newBookQty > 0
            ]);

            // Update class_books quantity if exists (OPTIONAL)
            if ($classBookQty > 0) {
                $newClassBookQty = $classBookQty - 1;
                $this->supabaseRequest('PATCH', 'class_books?class_id=eq.' . $classId . '&book_id=eq.' . $bookId, [
                    'quantity' => $newClassBookQty
                ]);
            }

            // Clear cache
            $this->cache->delete('class_data_' . $classId);
            $this->cache->delete('all_books_class_' . md5(json_encode(['select' => '*'])));

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
            $classId = $this->request->getPost('class_id');
            $loanId = $this->request->getPost('loan_id');

            if (empty($classId) || empty($loanId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Class dan Loan ID wajib diisi'
                ]);
            }

            // Get class name
            $class = $this->supabaseRequest('GET', 'classes', null, [
                'id' => 'eq.' . $classId,
                'limit' => 1
            ]);

            if (isset($class['error']) || empty($class)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kelas tidak ditemukan'
                ]);
            }

            $className = $class[0]['nama_kelas'];

            $borrowTransaction = $this->supabaseRequest('GET', 'transactions', null, [
                'id' => 'eq.' . $loanId,
                'type' => 'eq.borrow',
                'status' => 'eq.active',
                'limit' => 1
            ]);

            if (isset($borrowTransaction['error']) || empty($borrowTransaction)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Peminjaman tidak ditemukan atau sudah diselesaikan'
                ]);
            }

            $borrow = $borrowTransaction[0];
            $userId = $borrow['user_id'];
            $bookId = $borrow['book_id'];
            $borrowDate = $borrow['tanggal'];
            $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));
            $originalLocation = $borrow['transaction_location'] ?? 'perpustakaan';

            $returnData = [
                'user_id' => $userId,
                'book_id' => $bookId,
                'type' => 'return',
                'tanggal' => date('Y-m-d'),
                'status' => 'completed',
                'pic_name' => session()->get('name'),
                'pic_username' => session()->get('username'),
                'pic_id' => session()->get('user_id'),
                'transaction_location' => $className, // Where the return happens
                'created_at' => date('Y-m-d H:i:s'),
                'completed_at' => date('Y-m-d H:i:s'),
                'completed_by_name' => session()->get('name'),
                'completed_by_username' => session()->get('username'),
                'due_date' => $dueDate
            ];

            $result = $this->supabaseRequest('POST', 'transactions', $returnData);

            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi pengembalian'
                ]);
            }

            // Update borrow status
            $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $loanId, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'completed_by_name' => session()->get('name'),
                'completed_by_username' => session()->get('username')
            ]);

            // Get book data
            $book = $this->supabaseRequest('GET', 'books', null, [
                'id' => 'eq.' . $bookId,
                'limit' => 1
            ]);

            if (!isset($book['error']) && !empty($book)) {
                $bookQuantity = $book[0]['quantity'] ?? 0;
                $newBookQty = $bookQuantity + 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                    'quantity' => $newBookQty,
                    'available' => true
                ]);

                if ($originalLocation === $className) {
                    $classBook = $this->supabaseRequest('GET', 'class_books', null, [
                        'class_id' => 'eq.' . $classId,
                        'book_id' => 'eq.' . $bookId,
                        'limit' => 1
                    ]);

                    if (!isset($classBook['error']) && !empty($classBook)) {
                        $currentQty = $classBook[0]['quantity'] ?? 0;
                        $newQty = $currentQty + 1;
                        $this->supabaseRequest('PATCH', 'class_books?class_id=eq.' . $classId . '&book_id=eq.' . $bookId, [
                            'quantity' => $newQty
                        ]);
                    }
                }
            }

            $this->updateTrustScore($userId, $borrowDate, $dueDate);

            $this->cache->delete('class_data_' . $classId);
            $this->cache->delete('all_books_class_' . md5(json_encode(['select' => '*'])));
            $this->cache->delete('all_users_class_' . md5(json_encode(['select' => '*'])));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pengembalian berhasil dicatat'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in addReturn: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function returnMultiple()
    {
        try {
            $classId = $this->request->getPost('class_id');

            $selectedLoansJson = $this->request->getPost('selectedLoans');
            $selectedLoans = !empty($selectedLoansJson) ? json_decode($selectedLoansJson, true) : [];

            $loanIds = [];
            if (is_array($selectedLoans)) {
                $loanIds = array_column($selectedLoans, 'loanId');
                $loanIds = array_filter($loanIds);
            }

            if (empty($classId) || empty($loanIds)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Class dan Loan IDs wajib diisi'
                ]);
            }

            $class = $this->supabaseRequest('GET', 'classes', null, [
                'id' => 'eq.' . $classId,
                'limit' => 1
            ]);

            if (isset($class['error']) || empty($class)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Kelas tidak ditemukan'
                ]);
            }

            $className = $class[0]['nama_kelas'];

            $processedCount = 0;
            $errors = [];

            foreach ($loanIds as $loanId) {
                $borrowTransaction = $this->supabaseRequest('GET', 'transactions', null, [
                    'id' => 'eq.' . $loanId,
                    'type' => 'eq.borrow',
                    'status' => 'eq.active',
                    'limit' => 1
                ]);

                if (isset($borrowTransaction['error']) || empty($borrowTransaction)) {
                    $errors[] = "Loan ID $loanId tidak ditemukan atau sudah diselesaikan";
                    continue;
                }

                $borrow = $borrowTransaction[0];
                $userId = $borrow['user_id'];
                $bookId = $borrow['book_id'];
                $borrowDate = $borrow['tanggal'];
                $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));
                $originalLocation = $borrow['transaction_location'] ?? 'perpustakaan';

                // Create return transaction
                $returnData = [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                    'type' => 'return',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'completed',
                    'pic_name' => session()->get('name'),
                    'pic_username' => session()->get('username'),
                    'pic_id' => session()->get('user_id'),
                    'transaction_location' => $className,
                    'created_at' => date('Y-m-d H:i:s'),
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => session()->get('name'),
                    'completed_by_username' => session()->get('username'),
                    'due_date' => $dueDate
                ];

                $result = $this->supabaseRequest('POST', 'transactions', $returnData);

                if (isset($result['error'])) {
                    $errors[] = "Gagal menyimpan pengembalian untuk Loan ID $loanId";
                    continue;
                }

                // Update borrow status
                $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $loanId, [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => session()->get('name'),
                    'completed_by_username' => session()->get('username')
                ]);

                // Get book data
                $books = $this->supabaseRequest('GET', 'books', null, [
                    'id' => 'eq.' . $bookId,
                    'limit' => 1
                ]);

                if (!isset($books['error']) && !empty($books)) {
                    $book = $books[0];

                    // Update main books quantity (REQUIRED)
                    $bookQuantity = $book['quantity'] ?? 0;
                    $newBookQty = $bookQuantity + 1;
                    $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                        'quantity' => $newBookQty,
                        'available' => true
                    ]);

                    // Update class_books quantity if book was borrowed from this class (OPTIONAL)
                    if ($originalLocation === $className) {
                        $classBook = $this->supabaseRequest('GET', 'class_books', null, [
                            'class_id' => 'eq.' . $classId,
                            'book_id' => 'eq.' . $bookId,
                            'limit' => 1
                        ]);

                        if (!isset($classBook['error']) && !empty($classBook)) {
                            $currentQty = $classBook[0]['quantity'] ?? 0;
                            $newQty = $currentQty + 1;
                            $this->supabaseRequest('PATCH', 'class_books?class_id=eq.' . $classId . '&book_id=eq.' . $bookId, [
                                'quantity' => $newQty
                            ]);
                        }
                    }
                }

                // Update trust score
                $this->updateTrustScore($userId, $borrowDate, $dueDate);

                $processedCount++;
            }

            // Clear cache
            $this->cache->delete('class_data_' . $classId);
            $this->cache->delete('all_books_class_' . md5(json_encode(['select' => '*'])));
            $this->cache->delete('all_users_class_' . md5(json_encode(['select' => '*'])));

            $message = 'Pengembalian berhasil dicatat untuk ' . $processedCount . ' buku!';
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
            log_message('error', 'Exception in returnMultiple: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    private function updateTrustScore($userId, $borrowDate, $dueDate)
    {
        $user = $this->supabaseRequest('GET', 'users', null, [
            'id' => 'eq.' . $userId,
            'select' => 'trust_score,is_freezed',
            'limit' => 1
        ]);

        if (isset($user['error']) || empty($user)) {
            log_message('error', 'Failed to get user for trust score update');
            return false;
        }

        // Skip if user has is_freezed set to true
        if ($user[0]['is_freezed'] == 1 || $user[0]['is_freezed'] === true) {
            log_message('info', "Skipping trust score update for user $userId - trust score is frozen");
            return true;
        }

        $currentScore = (float)($user[0]['trust_score'] ?? 100);
        $returnDate = strtotime(date('Y-m-d'));
        $dueTimestamp = strtotime($dueDate);

        if ($returnDate <= $dueTimestamp) {
            $newScore = $currentScore + 1;
            $status = 'ontime';
        } else {
            $newScore = $currentScore;
            $status = 'late';
        }

        $newScore = max(0, $newScore);

        $updateResult = $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
            'trust_score' => $newScore
        ]);

        // Clear cache
        $this->cache->delete('all_users_class_' . md5(json_encode(['select' => '*'])));

        log_message('info', "Trust score updated for user $userId: $currentScore -> $newScore ($status)");

        return !isset($updateResult['error']);
    }

    /**
     * Apply daily late penalties for overdue book borrowings
     * Called by Google Cloud Scheduler
     */
    public function applyLatePenalties()
    {
        try {
            log_message('info', '=== APPLYING LATE PENALTIES ===');
            
            // Get all active borrowings with pagination
            $borrowings = $this->fetchAllTransactions([
                'select' => '*',
                'status' => 'eq.active',
                'order' => 'created_at.desc'
            ]);

            if (empty($borrowings)) {
                log_message('info', 'No borrowings found');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'No borrowings to process',
                    'processed' => 0
                ]);
            }

            $processedCount = 0;
            $penaltyAppliedToday = [];
            $today = date('Y-m-d');

            foreach ($borrowings as $borrowing) {
                $userId = $borrowing['user_id'];
                $dueDate = $borrowing['due_date'] ?? null;
                $lastPenaltyDate = $borrowing['last_penalty_date'] ?? null;

                // Skip if we already applied penalty today for this user
                if (in_array($userId, $penaltyAppliedToday)) {
                    continue;
                }

                // Skip if no due date
                if (!$dueDate) {
                    continue;
                }

                // Check if due date has passed
                if (strtotime($today) <= strtotime($dueDate)) {
                    continue;
                }

                // Skip if penalty already applied today
                if ($lastPenaltyDate === $today) {
                    continue;
                }

                // Get current user trust score and is_freezed status
                $user = $this->supabaseRequest('GET', 'users', null, [
                    'id' => 'eq.' . $userId,
                    'select' => 'id,nama,trust_score,is_freezed',
                    'limit' => 1
                ]);

                if (!empty($user) && !isset($user['error'])) {
                    if ($user[0]['is_freezed'] == 1 || $user[0]['is_freezed'] === true) {
                        log_message('info', "Skipping penalty for user {$user[0]['nama']} (ID: $userId) - trust score is frozen");
                        continue;
                    }

                    $currentScore = (float)($user[0]['trust_score'] ?? 100);
                    $penalty = 2;
                    $newScore = max(0, $currentScore - $penalty);

                    // Update user trust score
                    $updateResult = $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
                        'trust_score' => $newScore
                    ]);

                    if (!isset($updateResult['error'])) {
                        // Update the borrowing record to mark penalty as applied today
                        $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $borrowing['id'], [
                            'last_penalty_date' => $today
                        ]);

                        $penaltyAppliedToday[] = $userId;
                        $processedCount++;
                        log_message('info', "Applied 2-point penalty to user {$user[0]['nama']} (ID: $userId). Score: $currentScore → $newScore");
                    } else {
                        log_message('error', "Error updating score for user $userId: " . json_encode($updateResult));
                    }
                } else {
                    log_message('error', "User not found for ID: $userId");
                }
            }

            // Clear cache
            $this->cache->delete('all_users_class_' . md5(json_encode(['select' => '*'])));

            log_message('info', "=== PENALTIES APPLIED TO $processedCount USERS ===");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Late penalties applied successfully',
                'processed' => $processedCount,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Exception in applyLatePenalties: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}