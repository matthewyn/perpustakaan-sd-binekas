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

        // Get students from users table where class_id matches
        $students = $this->supabaseRequest('GET', 'users', null, [
            'class_id' => 'eq.' . $classId,
            'role' => 'eq.murid',
            'select' => '*',
            'order' => 'nama.asc'
        ]);

        if (isset($students['error'])) {
            $students = [];
        }

        // Get books from class_books table
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

    public function getClassTransactions()
    {
        $classId = $this->request->getGet('class_id');
        $type = $this->request->getGet('type');

        if (empty($classId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Class ID tidak ditemukan'
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

        // Get transactions
        $params = [
            'transaction_location' => 'eq.' . $className,
            'select' => '*',
            'order' => 'created_at.desc'
        ];

        if (!empty($type)) {
            $params['type'] = 'eq.' . $type;
        }

        $transactions = $this->supabaseRequest('GET', 'transactions', null, $params);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        // Get users and books for displaying names
        $users = $this->supabaseRequest('GET', 'users', null, ['select' => '*']);
        $users = isset($users['error']) ? [] : $users;

        $books = $this->supabaseRequest('GET', 'books', null, ['select' => '*']);
        $books = isset($books['error']) ? [] : $books;

        $usersById = [];
        foreach ($users as $user) {
            $usersById[$user['id']] = $user;
        }

        $booksById = [];
        foreach ($books as $book) {
            $booksById[$book['id']] = $book;
        }

        // Process transactions
        $processedTransactions = [];
        foreach ($transactions as $t) {
            $userId = $t['user_id'] ?? null;
            $bookId = $t['book_id'] ?? null;

            $nama = $userId && isset($usersById[$userId]) ? ($usersById[$userId]['nama'] ?? '-') : '-';
            $judul = $bookId && isset($booksById[$bookId]) ? ($booksById[$bookId]['title'] ?? '-') : '-';

            $processedTransactions[] = [
                'id' => $t['id'] ?? null,
                'nama' => $nama,
                'judul' => $judul,
                'tanggal' => $t['tanggal'] ?? '-',
                'status' => $t['status'] ?? 'active',
                'user_id' => $userId,
                'book_id' => $bookId
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'transactions' => $processedTransactions
        ]);
    }

    // Add borrowing for class - CHANGED TO USE NAME
    public function addBorrowing()
    {
        log_message('info', '=== ADD CLASS BORROWING ===');
        log_message('info', 'POST Data: ' . json_encode($this->request->getPost()));

        $nama = $this->request->getPost('namaCari'); // CHANGED FROM nisnCari
        $judul = $this->request->getPost('judulCari');
        $uidCari = trim($this->request->getPost('uidCari') ?? '');
        $classId = $this->request->getPost('class_id');

        if (empty($nama) || empty($judul) || empty($classId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama, Judul buku, dan Kelas harus diisi'
            ]);
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
        $className = $classData['nama_kelas'];

        // Get user by name AND class_id
        $users = $this->supabaseRequest('GET', 'users', null, [
            'nama' => 'eq.' . $nama,
            'class_id' => 'eq.' . $classId,
            'role' => 'eq.murid',
            'limit' => 1
        ]);

        if (isset($users['error']) || empty($users)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Siswa dengan nama ' . $nama . ' tidak ditemukan di kelas ini'
            ]);
        }

        $user = $users[0];
        $userId = $user['id'];

        // Get book from class_books
        $books = $this->supabaseRequest('GET', 'books', null, [
            'title' => 'eq.' . $judul,
            'limit' => 1
        ]);

        if (isset($books['error']) || empty($books)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Buku "' . $judul . '" tidak ditemukan'
            ]);
        }

        $book = $books[0];
        $bookId = $book['id'];

        // Check if book is assigned to this class
        $classBook = $this->supabaseRequest('GET', 'class_books', null, [
            'class_id' => 'eq.' . $classId,
            'book_id' => 'eq.' . $bookId,
            'limit' => 1
        ]);

        if (isset($classBook['error']) || empty($classBook)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Buku tidak tersedia di kelas ' . $className
            ]);
        }

        // Check class book quantity
        $classBookQuantity = $classBook[0]['quantity'] ?? 0;
        if ($classBookQuantity < 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Stok buku di kelas habis'
            ]);
        }

        // UID is OPTIONAL for class books - only validate if provided
        if (!empty($uidCari)) {
            $bookUids = $book['uid'] ?? [];
            if (is_array($bookUids) && !empty($bookUids)) {
                $uidFound = false;
                foreach ($bookUids as $bookUid) {
                    if (strcasecmp(trim($bookUid), trim($uidCari)) === 0) {
                        $uidFound = true;
                        break;
                    }
                }

                if (!$uidFound) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'UID tidak valid untuk buku ini'
                    ]);
                }
            }
        }

        // Calculate due date
        $isOneDayBook = $book['is_one_day_book'] ?? false;
        $dueDays = $isOneDayBook ? 1 : 7;
        $dueDate = date('Y-m-d', strtotime("+$dueDays days"));

        // Create transaction
        $transactionData = [
            'user_id' => $userId,
            'book_id' => $bookId,
            'uid' => $uidCari ?: null,
            'type' => 'borrow',
            'tanggal' => date('Y-m-d'),
            'status' => 'active',
            'pic_name' => session()->get('name') ?? 'Admin',
            'pic_username' => session()->get('username') ?? 'admin',
            'pic_id' => session()->get('id') ?? null,
            'transaction_location' => $className,
            'created_at' => date('Y-m-d H:i:s'),
            'due_date' => $dueDate,
            'completed_at' => null,
            'completed_by_name' => null,
            'completed_by_username' => null
        ];

        $result = $this->supabaseRequest('POST', 'transactions', $transactionData);

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi peminjaman'
            ]);
        }

        // Update class_books quantity
        $newClassQty = $classBookQuantity - 1;
        $this->supabaseRequest('PATCH', 'class_books?class_id=eq.' . $classId . '&book_id=eq.' . $bookId, [
            'quantity' => $newClassQty
        ]);

        // Update books quantity (main books table)
        $bookQuantity = $book['quantity'] ?? 0;
        $newBookQty = $bookQuantity - 1;
        $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
            'quantity' => $newBookQty,
            'available' => $newBookQty > 0
        ]);

        // Clear cache
        $this->cache->delete('class_data_' . $classId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Peminjaman berhasil! Jatuh tempo: ' . date('d/m/Y', strtotime($dueDate)) . ' (' . $dueDays . ' hari)'
        ]);
    }

    // Add return for class - CHANGED TO USE NAME
    public function addReturn()
    {
        $nama = $this->request->getPost('namaCariPengembalian'); // CHANGED FROM nisnCariPengembalian
        $judul = $this->request->getPost('judulCariPengembalian');
        $classId = $this->request->getPost('class_id');

        if (empty($nama) || empty($judul) || empty($classId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama, Judul buku, dan Kelas harus diisi'
            ]);
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

        $className = $class[0]['nama_kelas'];

        // Find user by name AND class_id
        $users = $this->supabaseRequest('GET', 'users', null, [
            'nama' => 'eq.' . $nama,
            'class_id' => 'eq.' . $classId,
            'role' => 'eq.murid',
            'limit' => 1
        ]);

        if (isset($users['error']) || empty($users)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Siswa tidak ditemukan di kelas ini'
            ]);
        }

        $user = $users[0];
        $userId = $user['id'];

        // Find book
        $books = $this->supabaseRequest('GET', 'books', null, [
            'title' => 'eq.' . $judul,
            'limit' => 1
        ]);

        if (isset($books['error']) || empty($books)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Buku tidak ditemukan'
            ]);
        }

        $book = $books[0];
        $bookId = $book['id'];

        // Find active borrow in this class
        $borrows = $this->supabaseRequest('GET', 'transactions', null, [
            'user_id' => 'eq.' . $userId,
            'book_id' => 'eq.' . $bookId,
            'type' => 'eq.borrow',
            'status' => 'eq.active',
            'transaction_location' => 'eq.' . $className,
            'limit' => 1
        ]);

        if (isset($borrows['error']) || empty($borrows)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data peminjaman tidak ditemukan di kelas ' . $className
            ]);
        }

        $borrow = $borrows[0];
        $borrowId = $borrow['id'];
        $borrowDate = $borrow['tanggal'];
        $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));

        // Create return transaction
        $returnData = [
            'user_id' => $userId,
            'book_id' => $bookId,
            'type' => 'return',
            'tanggal' => date('Y-m-d'),
            'status' => 'completed',
            'pic_name' => session()->get('name'),
            'pic_username' => session()->get('role'),
            'transaction_location' => $className
        ];

        $result = $this->supabaseRequest('POST', 'transactions', $returnData);

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan pengembalian'
            ]);
        }

        // Update borrow status
        $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $borrowId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'completed_by_name' => session()->get('name'),
            'completed_by_username' => session()->get('role')
        ]);

        // Update class_books quantity (not main books table)
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

        // Update books quantity (main books table)
        $bookQuantity = $book['quantity'] ?? 0;
        $newBookQty = $bookQuantity + 1;
        $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
            'quantity' => $newBookQty,
            'available' => true
        ]);

        // Update trust score
        $this->updateTrustScore($userId, $borrowDate, $dueDate);

        // Clear cache
        $this->cache->delete('class_data_' . $classId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pengembalian berhasil dicatat!'
        ]);
    }

    private function updateTrustScore($userId, $borrowDate, $dueDate)
    {
        $user = $this->supabaseRequest('GET', 'users', null, [
            'id' => 'eq.' . $userId,
            'select' => 'trust_score',
            'limit' => 1
        ]);

        if (isset($user['error']) || empty($user)) {
            return false;
        }

        $currentScore = (float)($user[0]['trust_score'] ?? 100);
        $returnDate = strtotime(date('Y-m-d'));
        $dueTimestamp = strtotime($dueDate);

        // If returned before or on due date: increase score by 0.5
        if ($returnDate <= $dueTimestamp) {
            $newScore = $currentScore + 0.5;
        } else {
            // If returned after due date: no change to score
            $newScore = $currentScore;
        }

        $newScore = max(0, min(100, $newScore));

        $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
            'trust_score' => $newScore
        ]);

        return true;
    }

    /**
     * Apply daily late penalties for overdue book borrowings
     * Called by Google Cloud Scheduler
     */
    public function applyLatePenalties()
    {
        try {
            log_message('info', '=== APPLYING LATE PENALTIES ===');
            
            // Get all active borrowings that are overdue
            $borrowings = $this->supabaseRequest('GET', 'transactions', null, [
                'select' => '*',
                'status' => 'eq.active',
                'order' => 'created_at.desc'
            ]);

            if (isset($borrowings['error'])) {
                log_message('error', 'Error fetching borrowings: ' . json_encode($borrowings));
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Error fetching borrowings'
                ]);
            }

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

                // Get current user trust score
                $user = $this->supabaseRequest('GET', 'users', null, [
                    'id' => 'eq.' . $userId,
                    'select' => 'id,nama,trust_score',
                    'limit' => 1
                ]);

                if (!empty($user) && !isset($user['error'])) {
                    $currentScore = (float)($user[0]['trust_score'] ?? 100);
                    $penalty = 2; // 2 points per day
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