<?php

namespace App\Controllers;

use CodeIgniter\Controller;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class TransactionController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
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

    public function peminjaman()
    {
        $currentPicName = session()->get('name');
        $currentRole = session()->get('role');

        // Get all transactions
        $transactions = $this->supabaseRequest('GET', 'transactions', null, [
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        // Get users
        $users = $this->supabaseRequest('GET', 'users', null, [
            'select' => '*'
        ]);
        $users = isset($users['error']) ? [] : $users;

        // Get classes
        $classes = $this->supabaseRequest('GET', 'classes', null, [
            'select' => '*'
        ]);
        $classes = isset($classes['error']) ? [] : $classes;

        // Get books
        $books = $this->supabaseRequest('GET', 'books', null, [
            'select' => '*'
        ]);
        $books = isset($books['error']) ? [] : $books;
        
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
            
            $judul = $bookId && isset($booksById[$bookId]) ? ($booksById[$bookId]['title'] ?? '-') : '-';

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
        $totalAvailable = count(array_filter($books, fn($b) => $b['available'] ?? false));
        
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

        // Calculate percentage changes
        $currentMonth = date('Y-m');
        $prevMonth = date('Y-m', strtotime('-1 month'));

        $borrowCurrent = $borrowingsByMonth[$currentMonth] ?? 0;
        $borrowPrev = $borrowingsByMonth[$prevMonth] ?? 0;
        $returnCurrent = $returnsByMonth[$currentMonth] ?? 0;
        $returnPrev = $returnsByMonth[$prevMonth] ?? 0;

        $calcPercent = function($now, $prev) {
            if ($prev == 0) return $now > 0 ? 100 : 0;
            return round((($now - $prev) / $prev) * 100);
        };

        return view('peminjaman', [
            'totalAvailable' => count($books),
            'availableBooks' => $books,
            'borrowings' => $borrowRows,
            'returns' => $returnRows,
            'totalBorrowed' => $borrowCurrent,
            'totalBorrowedPercent' => $calcPercent($borrowCurrent, $borrowPrev),
            'totalReturned' => $returnCurrent,
            'totalReturnedPercent' => $calcPercent($returnCurrent, $returnPrev),
            'totalAvailablePercent' => 0,
            'chartData' => $chartData
        ]);
    }

    // Tambah bintang dan num_borrows hanya saat pengembalian sukses
    private function updateBintangDanNumBorrows($userId)
    {
        // Ambil bintang & num_borrows
        $user = $this->supabaseRequest('GET', 'users', null, [
            'id' => 'eq.' . $userId,
            'select' => 'trust_score,num_borrows',
            'limit' => 1
        ]);
        if (isset($user['error']) || empty($user)) {
            return false;
        }
        $currentTrustScore = (int)($user[0]['trust_score'] ?? 0);
        $currentNumBorrows = (int)($user[0]['num_borrows'] ?? 0);

        $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, [
            'trust_score' => $currentTrustScore + 1,
            'num_borrows' => $currentNumBorrows + 1
        ]);
        return true;
    }

    public function addBorrowing()
    {
        // Debug logging
        log_message('info', '=== ADD BORROWING DEBUG ===');
        log_message('info', 'POST Data: ' . json_encode($this->request->getPost()));
        
        $nama = $this->request->getPost('namaCari');
        $judul = $this->request->getPost('judulCari');
        $uidCari = trim($this->request->getPost('uidCari') ?? '');
        
        log_message('info', "Nama: $nama, Judul: $judul, UID: $uidCari");

        // Validasi input
        if (empty($nama) || empty($judul)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama Siswa dan Judul buku harus diisi'
            ]);
        }

        // get user
        $users = $this->supabaseRequest('GET', 'users', null, [
            'nama' => 'eq.' . $nama,
            'limit' => 1
        ]);
        
        log_message('info', 'User Query Result: ' . json_encode($users));

        if (isset($users['error']) || empty($users)) {
            log_message('error', 'User not found for Nama: ' . $nama);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Siswa dengan nama ' . $nama . ' tidak ditemukan'
            ]);
        }

        $user = $users[0];
        $userId = $user['id'];
        $trustScore = (int)($user['trust_score'] ?? 0);

        log_message('info', "Found User ID: $userId, Trust Score: $trustScore");

        // Get max borrow from user table
        $maxBorrow = (int)($user['maxBorrow'] ?? 1);
        
        // Count active borrowings
        $activeBorrows = $this->supabaseRequest('GET', 'transactions', null, [
            'user_id' => 'eq.' . $userId,
            'type' => 'eq.borrow',
            'status' => 'eq.active'
        ]);

        $activeBorrows = isset($activeBorrows['error']) ? [] : $activeBorrows;
        
        log_message('info', 'Active Borrows: ' . count($activeBorrows) . ' / Max: ' . $maxBorrow);

        if (count($activeBorrows) >= $maxBorrow) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Batas maksimal peminjaman ($maxBorrow buku) telah tercapai. Trust Score: " . number_format($trustScore, 1)
            ]);
        }

        // Find book
        $books = $this->supabaseRequest('GET', 'books', null, [
            'title' => 'eq.' . $judul,
            'limit' => 1
        ]);
        
        log_message('info', 'Book Query Result: ' . json_encode($books));

        if (isset($books['error']) || empty($books)) {
            log_message('error', 'Book not found: ' . $judul);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Buku "' . $judul . '" tidak ditemukan'
            ]);
        }

        $book = $books[0];
        $bookId = $book['id'];

        log_message('info', "Found Book ID: $bookId, Quantity: " . ($book['quantity'] ?? 0));

        // Check quantity
        if (($book['quantity'] ?? 0) < 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Stok buku habis'
            ]);
        }

        // Validate UID if book has UIDs
        $bookUids = $book['uid'] ?? [];
        if (is_array($bookUids) && !empty($bookUids)) {
            if (empty($uidCari)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'UID wajib diisi untuk buku ini. Silakan tap/ketik UID buku.'
                ]);
            }

            $uidFound = false;
            foreach ($bookUids as $bookUid) {
                if (strcasecmp(trim($bookUid), trim($uidCari)) === 0) {
                    $uidFound = true;
                    break;
                }
            }

            if (!$uidFound) {
                log_message('error', 'UID not valid. Input: ' . $uidCari . ', Available: ' . json_encode($bookUids));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'UID tidak valid untuk buku ini'
                ]);
            }
            
            log_message('info', 'UID validated successfully: ' . $uidCari);
        }

        // due date
        $isOneDayBook = $book['is_one_day_book'] ?? false;
        $dueDays = $isOneDayBook ? 1 : 7;
        $dueDate = date('Y-m-d', strtotime("+$dueDays days"));

        log_message('info', "Due Date: $dueDate (Days: $dueDays)");

        //transaction
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
            'transaction_location' => 'perpustakaan',
            'created_at' => date('Y-m-d H:i:s'),
            'due_date' => $dueDate,
            'completed_at' => null,
            'completed_by_name' => null,
            'completed_by_username' => null
        ];

        log_message('info', 'Creating transaction: ' . json_encode($transactionData));

        $result = $this->supabaseRequest('POST', 'transactions', $transactionData);

        log_message('info', 'Transaction Result: ' . json_encode($result));

        if (isset($result['error'])) {
            log_message('error', 'Failed to create transaction: ' . json_encode($result));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi peminjaman'
            ]);
        }

        // Update book quantity
        $newQty = $book['quantity'] - 1;
        $updateResult = $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
            'quantity' => $newQty,
            'available' => $newQty > 0
        ]);
        
        // Hapus penambahan bintang di sini
        // $this->updateBintang($userId); // HAPUS BARIS INI

        log_message('info', 'Book Update Result: ' . json_encode($updateResult));

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Peminjaman berhasil!'
        ]);
    }

    public function addReturn()
    {
        // Support both single and multiple returns
        $selectedLoansJson = $this->request->getPost('selectedLoans');
        if ($selectedLoansJson) {
            // Multiple returns
            $selectedLoans = json_decode($selectedLoansJson, true);
            if (!is_array($selectedLoans) || empty($selectedLoans)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data pengembalian yang dipilih.'
                ]);
            }

            $successCount = 0;
            $failCount = 0;
            $messages = [];

            foreach ($selectedLoans as $loan) {
                $loanId = $loan['loanId'] ?? null;
                $userId = $loan['userId'] ?? null;
                $bookId = $loan['bookId'] ?? null;

                if (!$loanId || !$userId || !$bookId) {
                    $failCount++;
                    $messages[] = "Data tidak lengkap untuk pengembalian.";
                    continue;
                }

                // Get user
                $users = $this->supabaseRequest('GET', 'users', null, [
                    'id' => 'eq.' . $userId,
                    'limit' => 1
                ]);
                if (isset($users['error']) || empty($users)) {
                    $failCount++;
                    $messages[] = "Siswa tidak ditemukan.";
                    continue;
                }
                $user = $users[0];

                // Get book
                $books = $this->supabaseRequest('GET', 'books', null, [
                    'id' => 'eq.' . $bookId,
                    'limit' => 1
                ]);
                if (isset($books['error']) || empty($books)) {
                    $failCount++;
                    $messages[] = "Buku tidak ditemukan.";
                    continue;
                }
                $book = $books[0];

                // Get active borrow transaction
                $borrows = $this->supabaseRequest('GET', 'transactions', null, [
                    'id' => 'eq.' . $loanId,
                    'type' => 'eq.borrow',
                    'status' => 'eq.active',
                    'limit' => 1
                ]);
                if (isset($borrows['error']) || empty($borrows)) {
                    $failCount++;
                    $messages[] = "Data peminjaman tidak ditemukan.";
                    continue;
                }
                $borrow = $borrows[0];
                $borrowDate = $borrow['tanggal'];
                $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));

                // Insert return transaction
                $returnData = [
                    'user_id' => $userId,
                    'book_id' => $bookId,
                    'type' => 'return',
                    'tanggal' => date('Y-m-d'),
                    'status' => 'completed',
                    'pic_name' => session()->get('name'),
                    'pic_username' => session()->get('role'),
                    'transaction_location' => 'perpustakaan'
                ];
                $result = $this->supabaseRequest('POST', 'transactions', $returnData);
                if (isset($result['error'])) {
                    $failCount++;
                    $messages[] = "Gagal menyimpan pengembalian.";
                    continue;
                }

                // Update status pinjam
                $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $loanId, [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'completed_by_name' => session()->get('name'),
                    'completed_by_username' => session()->get('role')
                ]);

                // Update quantity buku
                $newQty = ($book['quantity'] ?? 0) + 1;
                $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
                    'quantity' => $newQty,
                    'available' => true
                ]);

                // Update bintang & num_borrows hanya saat pengembalian sukses
                $this->updateBintangDanNumBorrows($userId);

                $successCount++;
            }

            $msg = "Pengembalian berhasil: $successCount, gagal: $failCount.";
            if (!empty($messages)) {
                $msg .= " " . implode(' ', $messages);
            }
            return $this->response->setJSON([
                'success' => $successCount > 0,
                'message' => $msg
            ]);
        }

        // ...existing code for single return...
        $nama = $this->request->getPost('namaCariPengembalian');
        $judul = $this->request->getPost('judulCariPengembalian');

        // Find user
        $users = $this->supabaseRequest('GET', 'users', null, [
            'nama' => 'eq.' . $nama,
            'limit' => 1
        ]);

        if (isset($users['error']) || empty($users)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
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

        // Find active borrow
        $borrows = $this->supabaseRequest('GET', 'transactions', null, [
            'user_id' => 'eq.' . $userId,
            'book_id' => 'eq.' . $bookId,
            'type' => 'eq.borrow',
            'status' => 'eq.active',
            'limit' => 1
        ]);

        if (isset($borrows['error']) || empty($borrows)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data peminjaman tidak ditemukan'
            ]);
        }

        $borrow = $borrows[0];
        $borrowId = $borrow['id'];
        $borrowDate = $borrow['tanggal'];
        $dueDate = $borrow['due_date'] ?? date('Y-m-d', strtotime($borrowDate . ' +7 days'));

        $returnData = [
            'user_id' => $userId,
            'book_id' => $bookId,
            'type' => 'return',
            'tanggal' => date('Y-m-d'),
            'status' => 'completed',
            'pic_name' => session()->get('name'),
            'pic_username' => session()->get('role'),
            'transaction_location' => 'perpustakaan'
        ];

        $result = $this->supabaseRequest('POST', 'transactions', $returnData);

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan pengembalian'
            ]);
        }

        // Update status pinjam
        $this->supabaseRequest('PATCH', 'transactions?id=eq.' . $borrowId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'completed_by_name' => session()->get('name'),
            'completed_by_username' => session()->get('role')
        ]);

        // Update quantity buku
        $newQty = ($book['quantity'] ?? 0) + 1;
        $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, [
            'quantity' => $newQty,
            'available' => true
        ]);

        // Update bintang & num_borrows hanya saat pengembalian sukses
        $this->updateBintangDanNumBorrows($userId);
        $updatedUser = $this->supabaseRequest('GET', 'users', null, [
            'id' => 'eq.' . $userId,
            'select' => 'trust_score,num_borrows',
            'limit' => 1
        ]);

        $newTrustScore = !isset($updatedUser['error']) && !empty($updatedUser) 
            ? (int)$updatedUser[0]['trust_score'] 
            : (int)($user['trust_score'] ?? 0);

        $newNumBorrows = !isset($updatedUser['error']) && !empty($updatedUser)
            ? (int)$updatedUser[0]['num_borrows']
            : (int)($user['num_borrows'] ?? 0);

        $isLate = strtotime(date('Y-m-d')) > strtotime($dueDate);
        $scoreChange = $isLate ? '-2' : '+1';
        $message = 'Pengembalian berhasil. Trust Score: ' . $newTrustScore . ', Total Peminjaman: ' . $newNumBorrows . " ($scoreChange)";

        return $this->response->setJSON([
            'success' => true,
            'message' => $message
        ]);
    }

    public function apiBorrowings()
    {
        $transactions = $this->supabaseRequest('GET', 'transactions', null, [
            'type' => 'eq.borrow',
            'status' => 'eq.active',
            'select' => '*'
        ]);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        return $this->response->setJSON([
            'success' => true,
            'borrowings' => $transactions
        ]);
    }

    public function apiAllBorrowings()
    {
        $currentRole = session()->get('role');
        $currentPicName = session()->get('name');
        $page = (int)($this->request->getVar('page') ?? 1);
        $limit = (int)($this->request->getVar('limit') ?? 10);
        $offset = ($page - 1) * $limit;

        // Get paginated data
        $params = [
            'type' => 'eq.borrow',
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ];

        if ($currentRole !== 'admin') {
            $params['pic_name'] = 'eq.' . $currentPicName;
        }

        $transactions = $this->supabaseRequest('GET', 'transactions', null, $params);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        // Get total count for pagination
        $countParams = [
            'type' => 'eq.borrow',
            'select' => 'id'
        ];

        if ($currentRole !== 'admin') {
            $countParams['pic_name'] = 'eq.' . $currentPicName;
        }

        $allTransactions = $this->supabaseRequest('GET', 'transactions', null, $countParams);
        $totalCount = isset($allTransactions['error']) ? 0 : count($allTransactions);

        return $this->response->setJSON([
            'success' => true,
            'borrowings' => $transactions,
            'totalCount' => $totalCount,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    public function apiReturns()
    {
        $transactions = $this->supabaseRequest('GET', 'transactions', null, [
            'type' => 'eq.return',
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        return $this->response->setJSON([
            'success' => true,
            'returns' => $transactions
        ]);
    }

    public function apiAllReturns()
    {
        $currentRole = session()->get('role');
        $currentPicName = session()->get('name');
        $page = (int)($this->request->getVar('page') ?? 1);
        $limit = (int)($this->request->getVar('limit') ?? 10);
        $offset = ($page - 1) * $limit;

        // Get paginated data
        $params = [
            'type' => 'eq.return',
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ];

        if ($currentRole !== 'admin') {
            $params['pic_name'] = 'eq.' . $currentPicName;
        }

        $transactions = $this->supabaseRequest('GET', 'transactions', null, $params);

        if (isset($transactions['error'])) {
            $transactions = [];
        }

        // Get total count for pagination
        $countParams = [
            'type' => 'eq.return',
            'select' => 'id'
        ];

        if ($currentRole !== 'admin') {
            $countParams['pic_name'] = 'eq.' . $currentPicName;
        }

        $allTransactions = $this->supabaseRequest('GET', 'transactions', null, $countParams);
        $totalCount = isset($allTransactions['error']) ? 0 : count($allTransactions);

        return $this->response->setJSON([
            'success' => true,
            'returns' => $transactions,
            'totalCount' => $totalCount,
            'page' => $page,
            'limit' => $limit
        ]);
    }
}