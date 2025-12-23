<?php

namespace App\Controllers;

use CodeIgniter\Controller;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class BookManagementController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $cloudinaryName = 'dqx1ofl8j';
    private $cloudinaryPreset = 'ml_default';

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
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

    private function uploadToCloudinary($fileSource)
    {
        try {
            // Verify file exists
            if (!file_exists($fileSource)) {
                throw new \Exception('File does not exist: ' . $fileSource);
            }

            $ch = curl_init();
            
            $timestamp = time();
            $randomStr = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 7);
            $filename = 'book_' . $timestamp . '_' . $randomStr;

            // Use CURLFile for proper multipart/form-data encoding
            $postData = [
                'file' => new \CURLFile($fileSource),
                'upload_preset' => $this->cloudinaryPreset,
                'public_id' => $filename,
                'folder' => 'books'
            ];

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://api.cloudinary.com/v1_1/' . $this->cloudinaryName . '/image/upload',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            log_message('info', 'Uploading file to Cloudinary: ' . $fileSource);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message('info', 'Cloudinary response code: ' . $httpCode);
            log_message('info', 'Cloudinary response: ' . substr($response, 0, 500));

            if ($error) {
                log_message('error', 'cURL Error uploading to Cloudinary: ' . $error);
                throw new \Exception('Cloudinary upload error: ' . $error);
            }

            if ($httpCode >= 400) {
                log_message('error', 'Cloudinary HTTP Error ' . $httpCode . ': ' . $response);
                throw new \Exception('Cloudinary HTTP Error ' . $httpCode . ': ' . substr($response, 0, 200));
            }

            $data = json_decode($response, true);
            
            if (!isset($data['secure_url'])) {
                log_message('error', 'Cloudinary response missing secure_url: ' . $response);
                throw new \Exception('Invalid Cloudinary response: ' . $response);
            }

            log_message('info', 'Cloudinary upload successful: ' . $data['secure_url']);
            return $data['secure_url'];

        } catch (\Exception $e) {
            log_message('error', 'Exception in uploadToCloudinary: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getGenres(): array
    {
        $result = $this->supabaseRequest('GET', 'books', null, [
            'select' => 'genre'
        ]);

        if (isset($result['error'])) {
            return [];
        }

        $genres = array_unique(array_column($result, 'genre'));
        $genres = array_filter($genres); // Remove empty values
        sort($genres);
        return $genres;
    }

    public function index()
    {
        $books = $this->supabaseRequest('GET', 'books', null, [
            'order' => 'created_at.desc'
        ]);

        if (isset($books['error'])) {
            log_message('error', 'Failed to fetch books: ' . print_r($books, true));
            $books = [];
        }

        return view('management_buku', [
            'books' => $books,
            'genres' => $this->getGenres()
        ]);
    }

    // --- EXPORT TO CSV ---
    public function exportCsv()
    {
        $books = $this->supabaseRequest('GET', 'books', null, [
            'order' => 'created_at.desc'
        ]);
        if (isset($books['error'])) {
            return $this->response->setStatusCode(500)->setBody('Gagal mengambil data buku');
        }

        $filename = 'books_export_' . date('Ymd_His') . '.csv';
        $fields = [
            'id', 'code', 'title', 'author', 'publisher', 'year', 'genre', 'illustrator', 'series', 'isbn', 'quantity', 'notes', 'image', 'synopsis', 'uid', 'available', 'is_one_day_book', 'shelf_position'
        ];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $fields);

        foreach ($books as $book) {
            $row = [];
            foreach ($fields as $field) {
                $val = isset($book[$field]) ? $book[$field] : '';
                if (is_array($val)) $val = implode('|', $val);
                $row[] = $val;
            }
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function add()
    {
        try {
            // Check if this is a JSON request or form request
            $isJsonRequest = $this->request->getHeaderLine('Content-Type') === 'application/json';
            
            if ($isJsonRequest) {
                // Handle JSON request from AJAX
                $jsonData = json_decode($this->request->getBody(), true);
                
                $imageName = '';
                if (!empty($jsonData['image'])) {
                    // Image is already uploaded to Cloudinary by JavaScript
                    $imageName = $jsonData['image'];
                }

                $uidArray = $jsonData['uid'] ?? [];
                if (!is_array($uidArray)) {
                    $uidArray = empty($uidArray) ? [] : [$uidArray];
                }
                $uidArray = array_filter($uidArray, fn($u) => !empty(trim($u)));
                $uidArray = array_values($uidArray);

                $data = [
                    'uid' => $uidArray,
                    'quantity' => max(1, (int)($jsonData['quantity'] ?? 1)),
                    'code' => $jsonData['code'] ?? '',
                    'genre' => $jsonData['genre'] ?? '',
                    'title' => $jsonData['title'] ?? '',
                    'author' => $jsonData['author'] ?? '',
                    'illustrator' => $jsonData['illustrator'] ?? '',
                    'publisher' => $jsonData['publisher'] ?? '',
                    'series' => $jsonData['series'] ?? '',
                    'image' => $imageName,
                    'notes' => $jsonData['notes'] ?? '',
                    'shelf_position' => $jsonData['shelf_position'] ?? '',
                    'synopsis' => $jsonData['synopsis'] ?? '',
                    'is_in_class' => $jsonData['is_in_class'] ?? false,
                    'year' => (int)($jsonData['year'] ?? date('Y')),
                    'is_one_day_book' => $jsonData['is_one_day_book'] ?? false,
                    'available' => $jsonData['available'] ?? true,
                ];

                $result = $this->supabaseRequest('POST', 'books', $data);

                if (isset($result['error'])) {
                    log_message('error', 'Failed to add book: ' . print_r($result, true));
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'Gagal menambah buku: ' . ($result['response'] ?? 'Unknown error')
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Buku berhasil ditambahkan'
                ]);
            } else {
                // Handle form request (multipart/form-data) - for file uploads
                $file = $this->request->getFile('image');
                $imageName = '';
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    try {
                        $imageName = $this->uploadToCloudinary($file->getRealPath());
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to upload image to Cloudinary: ' . $e->getMessage());
                        return redirect()->to('/management-buku')->with('error', 'Gagal upload gambar ke Cloudinary: ' . $e->getMessage());
                    }
                }

                $uidArray = $this->request->getPost('uid') ?? [];
                $uidArray = array_filter($uidArray, fn($u) => !empty(trim($u)));
                $uidArray = array_values($uidArray);

                $data = [
                    'uid' => $uidArray,
                    'quantity' => max(1, (int)($this->request->getPost('quantity') ?? 1)),
                    'code' => $this->request->getPost('code'),
                    'genre' => $this->request->getPost('genre'),
                    'title' => $this->request->getPost('title'),
                    'author' => $this->request->getPost('author'),
                    'illustrator' => $this->request->getPost('illustrator'),
                    'publisher' => $this->request->getPost('publisher'),
                    'series' => $this->request->getPost('series'),
                    'image' => $imageName,
                    'notes' => $this->request->getPost('notes'),
                    'shelf_position' => $this->request->getPost('shelfPosition') ?? '',
                    'synopsis' => $this->request->getPost('synopsis'),
                    'is_in_class' => $this->request->getPost('isInClass') ? true : false,
                    'year' => (int)($this->request->getPost('year') ?? date('Y')),
                    'is_one_day_book' => $this->request->getPost('isOneDayBook') ? true : false,
                    'available' => true,
                ];

                $result = $this->supabaseRequest('POST', 'books', $data);

                if (isset($result['error'])) {
                    log_message('error', 'Failed to add book: ' . print_r($result, true));
                    return redirect()->to('/management-buku')->with('error', 'Gagal menambah buku: ' . ($result['response'] ?? 'Unknown error'));
                }

                return redirect()->to('/management-buku')->with('message', 'Buku berhasil ditambahkan');
            }
        } catch (\Exception $e) {
            log_message('error', 'Exception in add: ' . $e->getMessage());
            if ($isJsonRequest ?? false) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            return redirect()->to('/management-buku')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            // Get existing book
            $books = $this->supabaseRequest('GET', 'books', null, [
                'id' => 'eq.' . $id,
                'limit' => 1
            ]);

            if (isset($books['error']) || empty($books)) {
                log_message('error', 'Book not found for edit: ' . $id);
                return redirect()->to('/management-buku')->with('error', 'Buku tidak ditemukan');
            }

            $book = $books[0];

            $file = $this->request->getFile('image');
            $imageName = $book['image'] ?? '';
            if ($file && $file->isValid() && !$file->hasMoved()) {
                try {
                    $imageName = $this->uploadToCloudinary($file->getRealPath());
                } catch (\Exception $e) {
                    log_message('error', 'Failed to upload image to Cloudinary: ' . $e->getMessage());
                    return redirect()->to('/management-buku')->with('error', 'Gagal upload gambar ke Cloudinary: ' . $e->getMessage());
                }
            }

            $uidArray = $this->request->getPost('uid') ?? $book['uid'] ?? [];
            $uidArray = array_filter($uidArray, fn($u) => !empty(trim($u)));
            $uidArray = array_values($uidArray);

            $updateData = [
                'uid' => $uidArray,
                'quantity' => max(1, (int)($this->request->getPost('quantity') ?? 1)),
                'code' => $this->request->getPost('code'),
                'title' => $this->request->getPost('title'),
                'author' => $this->request->getPost('author'),
                'publisher' => $this->request->getPost('publisher'),
                'year' => (int)($this->request->getPost('year') ?? date('Y')),
                'genre' => $this->request->getPost('genre'),
                'illustrator' => $this->request->getPost('illustrator'),
                'series' => $this->request->getPost('series'),
                'notes' => $this->request->getPost('notes'),
                'synopsis' => $this->request->getPost('synopsis'),
                'is_one_day_book' => $this->request->getPost('isOneDayBook') ? true : false,
                'available' => $this->request->getPost('available') ? true : false,
                'image' => $imageName,
                // Tambahkan ISBN
                'isbn' => $this->request->getPost('isbn'),
            ];

            $result = $this->supabaseRequest('PATCH', 'books?id=eq.' . $id, $updateData);

            if (isset($result['error'])) {
                log_message('error', 'Failed to update book: ' . print_r($result, true));
                return redirect()->to('/management-buku')->with('error', 'Gagal mengupdate buku: ' . ($result['response'] ?? 'Unknown error'));
            }

            return redirect()->to('/management-buku')->with('message', 'Buku berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Exception in edit: ' . $e->getMessage());
            return redirect()->to('/management-buku')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $code = $this->request->getGetPost('code');
            
            if (empty($code)) {
                return redirect()->to('/management-buku')->with('error', 'Kode buku tidak valid');
            }
            
            // cari buku by code
            $books = $this->supabaseRequest('GET', 'books', null, [
                'code' => 'eq.' . trim($code),
                'limit' => 1
            ]);

            if (isset($books['error']) || empty($books)) {
                log_message('error', 'Book not found for delete: ' . $code);
                return redirect()->to('/management-buku')->with('error', 'Buku tidak ditemukan');
            }

            $bookId = $books[0]['id'];

            // Delete book
            $result = $this->supabaseRequest('DELETE', 'books?id=eq.' . $bookId);

            if (isset($result['error'])) {
                log_message('error', 'Failed to delete book: ' . print_r($result, true));
                return redirect()->to('/management-buku')->with('error', 'Gagal menghapus buku');
            }

            return redirect()->to('/management-buku')->with('message', 'Buku berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Exception in delete: ' . $e->getMessage());
            return redirect()->to('/management-buku')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importJson()
    {
        try {
            $file = $this->request->getFile('json_file');
            if (!$file || !$file->isValid()) {
                return redirect()->to('/management-buku')->with('error', 'File JSON tidak valid');
            }

            $jsonContent = file_get_contents($file->getTempName());
            $booksData = json_decode($jsonContent, true);
            
            if (!is_array($booksData)) {
                return redirect()->to('/management-buku')->with('error', 'Format JSON tidak valid. Harus berupa array.');
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->to('/management-buku')->with('error', 'JSON Error: ' . json_last_error_msg());
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Process in smaller batches
            $batchSize = 10;
            $batches = array_chunk($booksData, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                foreach ($batch as $index => $book) {
                    try {
                        // Validate required fields
                        if (empty($book['code']) || empty($book['title'])) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . ": Missing required fields (code or title)";
                            continue;
                        }

                        $uidArray = $book['uid'] ?? [];
                        $uidArray = is_array($uidArray) ? $uidArray : [$uidArray];
                        $uidArray = array_filter($uidArray, fn($u) => !empty(trim($u)));
                        $uidArray = array_values($uidArray);

                        $data = [
                            'uid' => $uidArray,
                            'quantity' => max(1, (int)($book['quantity'] ?? 1)),
                            'code' => trim($book['code']),
                            'genre' => $book['genre'] ?? '',
                            'title' => trim($book['title']),
                            'author' => $book['author'] ?? '',
                            'illustrator' => $book['illustrator'] ?? '',
                            'publisher' => $book['publisher'] ?? '',
                            'series' => $book['series'] ?? '',
                            'image' => $book['image'] ?? '',
                            'notes' => $book['notes'] ?? '',
                            'shelf_position' => $book['shelfPosition'] ?? $book['shelf_position'] ?? '',
                            'synopsis' => $book['synopsis'] ?? '',
                            'is_in_class' => isset($book['isInClass']) ? (bool)$book['isInClass'] : (isset($book['is_in_class']) ? (bool)$book['is_in_class'] : false),
                            'year' => (int)($book['year'] ?? date('Y')),
                            'is_one_day_book' => isset($book['isOneDayBook']) ? (bool)$book['isOneDayBook'] : (isset($book['is_one_day_book']) ? (bool)$book['is_one_day_book'] : false),
                            'available' => isset($book['available']) ? (bool)$book['available'] : true,
                        ];

                        $result = $this->supabaseRequest('POST', 'books', $data);

                        if (isset($result['error'])) {
                            $errorCount++;
                            $errors[] = "Row " . ($index + 1) . " (Code: {$book['code']}): " . ($result['response'] ?? 'Unknown error');
                            log_message('error', 'Failed to import book: ' . print_r($result, true));
                        } else {
                            $successCount++;
                        }
                        
                        // delay request (menghindari rate limit)
                        usleep(100000);
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                        log_message('error', 'Exception importing book: ' . $e->getMessage());
                    }
                }
                
                // delay
                if ($batchIndex < count($batches) - 1) {
                    sleep(1);
                }
            }

            $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
            if (!empty($errors) && $errorCount <= 10) {
                $message .= "\n\nError details:\n" . implode("\n", array_slice($errors, 0, 10));
            }
            
            if ($errorCount > 0) {
                log_message('error', 'Import errors: ' . print_r($errors, true));
            }
            
            return redirect()->to('/management-buku')->with('message', $message);
        } catch (\Exception $e) {
            log_message('error', 'Exception in importJson: ' . $e->getMessage());
            return redirect()->to('/management-buku')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}