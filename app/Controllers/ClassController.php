<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ClassController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $classTable = 'classes';
    private $classBookTable = 'class_books';

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        
        log_message('info', '=== ClassController Initialized ===');
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
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            log_message('info', 'Request Body: ' . $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message('info', 'Response Code: ' . $httpCode);
        log_message('info', 'Response Body: ' . $response);

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

    public function index()
    {
        // Get all classes with student and book counts
        $classes = $this->supabaseRequest('GET', $this->classTable, null, [
            'order' => 'created_at.desc'
        ]);

        if (isset($classes['error'])) {
            $classes = [];
        }

        // Enrich classes with counts
        foreach ($classes as &$class) {
            // Count students in this class
            $students = $this->supabaseRequest('GET', 'users', null, [
                'class_id' => 'eq.' . $class['id'],
                'role' => 'eq.murid',
                'select' => 'id'
            ]);
            $class['student_count'] = isset($students['error']) ? 0 : count($students);

            // Count books in this class
            $classBooks = $this->supabaseRequest('GET', $this->classBookTable, null, [
                'class_id' => 'eq.' . $class['id'],
                'select' => 'quantity'
            ]);
            $totalBooks = 0;
            if (!isset($classBooks['error'])) {
                foreach ($classBooks as $cb) {
                    $totalBooks += $cb['quantity'];
                }
            }
            $class['book_count'] = $totalBooks;
        }

        return view('management_class', [
            'classes' => $classes
        ]);
    }

    public function list()
    {
        $classes = $this->supabaseRequest('GET', $this->classTable, null, [
            'order' => 'created_at.desc'
        ]);

        return $this->response->setJSON([
            'success' => !isset($classes['error']),
            'classes' => $classes ?? []
        ]);
    }

    public function detail($id)
    {
        $class = $this->supabaseRequest('GET', $this->classTable, null, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ]);

        if (isset($class['error']) || empty($class)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'class' => $class[0]
        ]);
    }

    public function add()
    {
        log_message('info', 'Add Class POST Data: ' . json_encode($this->request->getPost()));

        $namaKelas = $this->request->getPost('nama_kelas');
        
        if (empty($namaKelas)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama kelas harus diisi'
            ]);
        }

        $data = [
            'nama_kelas' => $namaKelas,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->supabaseRequest('POST', $this->classTable, $data);

        log_message('info', 'Add Class Response: ' . json_encode($result));

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menambahkan kelas: ' . ($result['response'] ?? 'Unknown error')
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $result
        ]);
    }

    public function update($id = null)
    {
        log_message('info', 'Update Class ID: ' . $id . ' POST Data: ' . json_encode($this->request->getPost()));

        if (empty($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID kelas tidak valid'
            ]);
        }

        $existingClass = $this->supabaseRequest('GET', $this->classTable, null, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ]);

        if (isset($existingClass['error']) || empty($existingClass)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ]);
        }

        // Handle name update
        $namaKelas = $this->request->getPost('nama_kelas');
        if (!empty($namaKelas)) {
            $updateData = [
                'nama_kelas' => $namaKelas,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $endpoint = $this->classTable . '?id=eq.' . $id;
            $result = $this->supabaseRequest('PATCH', $endpoint, $updateData);

            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengupdate nama kelas'
                ]);
            }
        }

        // Handle student assignments
        $studentIds = $this->request->getPost('student_ids');
        if ($studentIds !== null) {
            if (is_string($studentIds)) {
                $studentIds = json_decode($studentIds, true);
            }
            $studentIds = is_array($studentIds) ? $studentIds : [];

            // Get current students in this class
            $currentStudents = $this->supabaseRequest('GET', 'users', null, [
                'class_id' => 'eq.' . $id,
                'role' => 'eq.murid',
                'select' => 'id'
            ]);
            $currentStudentIds = isset($currentStudents['error']) ? [] : array_column($currentStudents, 'id');

            // Students to remove (set class_id to null)
            $toRemove = array_diff($currentStudentIds, $studentIds);
            foreach ($toRemove as $userId) {
                $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, ['class_id' => null]);
            }

            // Students to add (set class_id to this class)
            $toAdd = array_diff($studentIds, $currentStudentIds);
            foreach ($toAdd as $userId) {
                $this->supabaseRequest('PATCH', 'users?id=eq.' . $userId, ['class_id' => (int)$id]);
            }
        }

        // Handle book assignments
        $bookData = $this->request->getPost('book_data');
        if ($bookData !== null) {
            if (is_string($bookData)) {
                $bookData = json_decode($bookData, true);
            }
            $bookData = is_array($bookData) ? $bookData : [];

            // Get current books in this class
            $currentClassBooks = $this->supabaseRequest('GET', $this->classBookTable, null, [
                'class_id' => 'eq.' . $id
            ]);
            $currentBookIds = isset($currentClassBooks['error']) ? [] : array_column($currentClassBooks, 'book_id');
            $newBookIds = array_keys($bookData);

            // Books to remove
            $toRemove = array_diff($currentBookIds, $newBookIds);
            foreach ($toRemove as $bookId) {
                $this->supabaseRequest('DELETE', $this->classBookTable . '?class_id=eq.' . $id . '&book_id=eq.' . $bookId);
            }

            // Books to add or update
            foreach ($bookData as $bookId => $quantity) {
                if (in_array($bookId, $currentBookIds)) {
                    // Update existing
                    $this->supabaseRequest('PATCH', 
                        $this->classBookTable . '?class_id=eq.' . $id . '&book_id=eq.' . $bookId,
                        ['quantity' => (int)$quantity]
                    );
                } else {
                    // Add new
                    $this->supabaseRequest('POST', $this->classBookTable, [
                        'class_id' => (int)$id,
                        'book_id' => (int)$bookId,
                        'quantity' => (int)$quantity
                    ]);
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kelas berhasil diupdate'
        ]);
    }

    public function delete($id = null)
    {
        log_message('info', 'Delete Class ID: ' . $id);

        if (empty($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID kelas tidak valid'
            ]);
        }

        $class = $this->supabaseRequest('GET', $this->classTable, null, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ]);

        if (isset($class['error']) || empty($class)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ]);
        }

        // Remove class_id from all students in this class
        $this->supabaseRequest('PATCH', 'users?class_id=eq.' . $id, ['class_id' => null]);

        // Delete all class_books entries
        $this->supabaseRequest('DELETE', $this->classBookTable . '?class_id=eq.' . $id);

        // Delete the class
        $endpoint = $this->classTable . '?id=eq.' . $id;
        $result = $this->supabaseRequest('DELETE', $endpoint);

        log_message('info', 'Delete Class Response: ' . json_encode($result));

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus kelas'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kelas berhasil dihapus'
        ]);
    }

    public function getUnassignedStudents()
    {
        // Get students where class_id is null
        $students = $this->supabaseRequest('GET', 'users', null, [
            'role' => 'eq.murid',
            'class_id' => 'is.null',
            'order' => 'nama.asc'
        ]);

        return $this->response->setJSON([
            'success' => true,
            'students' => isset($students['error']) ? [] : $students
        ]);
    }

    public function getUnassignedBooks()
    {
        // Get all books
        $allBooks = $this->supabaseRequest('GET', 'books', null, [
            'order' => 'title.asc'
        ]);
        
        if (isset($allBooks['error'])) {
            return $this->response->setJSON([
                'success' => true,
                'books' => []
            ]);
        }

        // Get all books that are in class_books table
        $assignedBooks = $this->supabaseRequest('GET', $this->classBookTable, null, [
            'select' => 'book_id,quantity'
        ]);
        
        $assignedBookMap = [];
        if (!isset($assignedBooks['error'])) {
            foreach ($assignedBooks as $ab) {
                if (!isset($assignedBookMap[$ab['book_id']])) {
                    $assignedBookMap[$ab['book_id']] = 0;
                }
                $assignedBookMap[$ab['book_id']] += $ab['quantity'];
            }
        }

        // Filter books that still have available quantity
        $availableBooks = [];
        foreach ($allBooks as $book) {
            $totalQuantity = $book['quantity'] ?? 0;
            $assignedQuantity = $assignedBookMap[$book['id']] ?? 0;
            $availableQuantity = $totalQuantity - $assignedQuantity;
            
            if ($availableQuantity > 0) {
                $book['available_quantity'] = $availableQuantity;
                $availableBooks[] = $book;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'books' => $availableBooks
        ]);
    }

    public function getClassMembers($id)
    {
        $class = $this->supabaseRequest('GET', $this->classTable, null, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ]);

        if (isset($class['error']) || empty($class)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kelas tidak ditemukan'
            ]);
        }

        $classData = $class[0];

        // Get students in this class
        $students = $this->supabaseRequest('GET', 'users', null, [
            'class_id' => 'eq.' . $id,
            'role' => 'eq.murid',
            'order' => 'nama.asc'
        ]);
        $students = isset($students['error']) ? [] : $students;

        // Get books in this class with quantities
        $classBooks = $this->supabaseRequest('GET', $this->classBookTable, null, [
            'class_id' => 'eq.' . $id
        ]);
        
        $books = [];
        if (!isset($classBooks['error'])) {
            $bookIds = array_column($classBooks, 'book_id');
            if (!empty($bookIds)) {
                $allBooks = $this->supabaseRequest('GET', 'books', null, [
                    'id' => 'in.(' . implode(',', $bookIds) . ')'
                ]);
                
                if (!isset($allBooks['error'])) {
                    // Get all class_books assignments to calculate available quantity
                    $allClassBooks = $this->supabaseRequest('GET', $this->classBookTable, null, [
                        'select' => 'book_id,quantity'
                    ]);
                    
                    $assignedByBook = [];
                    if (!isset($allClassBooks['error'])) {
                        foreach ($allClassBooks as $cb) {
                            if (!isset($assignedByBook[$cb['book_id']])) {
                                $assignedByBook[$cb['book_id']] = 0;
                            }
                            $assignedByBook[$cb['book_id']] += $cb['quantity'];
                        }
                    }
                    
                    // Map quantity to each book
                    $quantityMap = [];
                    foreach ($classBooks as $cb) {
                        $quantityMap[$cb['book_id']] = $cb['quantity'];
                    }
                    
                    foreach ($allBooks as $book) {
                        $currentClassQty = $quantityMap[$book['id']] ?? 0;
                        $totalBookQty = $book['quantity'] ?? 0;
                        $otherClassesQty = ($assignedByBook[$book['id']] ?? 0) - $currentClassQty;
                        $availableQty = $totalBookQty - $otherClassesQty;
                        
                        $book['class_quantity'] = $currentClassQty;
                        $book['available_quantity'] = $availableQty;
                        $books[] = $book;
                    }
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'class' => $classData,
            'students' => $students,
            'books' => $books
        ]);
    }
}