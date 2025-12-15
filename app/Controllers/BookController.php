<?php

namespace App\Controllers;

use CodeIgniter\Controller;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class BookController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $perPage = 10;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        
        log_message('info', '=== BookController Initialized ===');
    }

    private function supabaseRequest($method, $endpoint, $data = null, $queryParams = [])
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message('error', 'Supabase credentials not configured');
            return ['error' => 'Supabase credentials not configured'];
        }

        $url = rtrim($this->supabaseUrl, '/') . '/rest/v1/' . $endpoint;
        
        // Add query parameters
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

        log_message('info', 'Supabase Request: ' . $method . ' ' . $url);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
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

        if ($error) {
            log_message('error', 'Supabase CURL Error: ' . $error);
            return ['error' => $error];
        }

        if ($httpCode >= 400) {
            log_message('error', 'Supabase HTTP Error ' . $httpCode . ': ' . $response);
            return [
                'error' => 'HTTP Error ' . $httpCode,
                'response' => $response
            ];
        }

        return json_decode($response, true);
    }

    public function getGenres(): array
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

    private function generateKodeSekolah(): string
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $monthRoman = $romanMonths[$currentMonth];
        
        // Query all books to find codes for the current year
        $result = $this->supabaseRequest('GET', 'books', null, [
            'select' => 'code'
        ]);
        
        $maxNumber = 0;
        $codesThisYear = [];
        
        if (!isset($result['error']) && is_array($result)) {
            // Extract numbers from codes for current year
            foreach ($result as $book) {
                if (!empty($book['code'])) {
                    // Parse code format: {nomor}/YCB-CB/{bulan}/{tahun}
                    $parts = explode('/', $book['code']);
                    if (count($parts) >= 4) {
                        $codeYear = (int)$parts[3];  // Get year from code
                        $codeNumber = (int)$parts[0];  // Get number from code
                        
                        if ($codeYear == $currentYear) {
                            $codesThisYear[] = $codeNumber;
                            $maxNumber = max($maxNumber, $codeNumber);
                        }
                    }
                }
            }
        }
        
        log_message('info', 'Generated Kode Debug - Year: ' . $currentYear . ', Max Number: ' . $maxNumber . ', Codes This Year: ' . json_encode($codesThisYear));
        
        $newNumber = $maxNumber + 1;
        return sprintf('%03d/YCB-CB/%s/%s', $newNumber, $monthRoman, $currentYear);
    }

    public function getNextKodeSekolah()
    {
        try {
            $kodeSekolah = $this->generateKodeSekolah();
            
            return $this->response->setJSON([
                'success' => true,
                'kode_sekolah' => $kodeSekolah
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $selectedGenres = $this->request->getGet('genres') ?? [];
        $page = max(1, (int)($this->request->getGet('page') ?? 1));

        // Build query for fetching books
        $queryParams = [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $this->perPage,
            'offset' => ($page - 1) * $this->perPage
        ];

        // Add search filter
        if ($search) {
            $queryParams['or'] = "(title.ilike.*{$search}*,author.ilike.*{$search}*,genre.ilike.*{$search}*)";
        }

        // Add genre filter
        if (!empty($selectedGenres)) {
            $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
            if (isset($queryParams['or'])) {
                $queryParams['and'] = "({$queryParams['or']}),{$genreFilter}";
                unset($queryParams['or']);
            } else {
                $queryParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $books = $this->supabaseRequest('GET', 'books', null, $queryParams);
        
        if (isset($books['error'])) {
            $books = [];
        }

        // Get total count for pagination - fetch only IDs to count accurately
        $countParams = ['select' => 'id'];
        if ($search) {
            $countParams['or'] = "(title.ilike.*{$search}*,author.ilike.*{$search}*,genre.ilike.*{$search}*)";
        }
        if (!empty($selectedGenres)) {
            $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
            if (isset($countParams['or'])) {
                $countParams['and'] = "({$countParams['or']}),{$genreFilter}";
                unset($countParams['or']);
            } else {
                $countParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $countResult = $this->supabaseRequest('GET', 'books', null, $countParams);
        $totalBooks = is_array($countResult) && !isset($countResult['error']) ? count($countResult) : 0;
        $totalPages = max(1, ceil($totalBooks / $this->perPage));

        // Get latest 3 books
        $latestBooks = $this->supabaseRequest('GET', 'books', null, [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => 3
        ]);

        $latestBooks = isset($latestBooks['error']) ? [] : $latestBooks;
        $bookTitles = array_column($books, 'title');

        return view('welcome_message', [
            'booksOnPage' => $books,
            'genres' => $this->getGenres(),
            'selectedGenres' => $selectedGenres,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'books' => $books,
            'allBooks' => $books,
            'latestBooks' => $latestBooks,
            'bookTitles' => $bookTitles,
        ]);
    }

    public function filter()
    {
        $search = $this->request->getGet('search') ?? '';
        $selectedGenres = $this->request->getGet('genres') ?? [];
        $page = max(1, (int)($this->request->getGet('page') ?? 1));

        $queryParams = [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $this->perPage,
            'offset' => ($page - 1) * $this->perPage
        ];

        if ($search) {
            $queryParams['or'] = "(title.ilike.*{$search}*,author.ilike.*{$search}*,genre.ilike.*{$search}*)";
        }

        if (!empty($selectedGenres)) {
            $queryParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
        }

        $books = $this->supabaseRequest('GET', 'books', null, $queryParams);
        
        if (isset($books['error'])) {
            $books = [];
        }

        // Get total count for pagination
        $countParams = ['select' => 'id'];
        if ($search) {
            $countParams['or'] = "(title.ilike.*{$search}*,author.ilike.*{$search}*,genre.ilike.*{$search}*)";
        }
        if (!empty($selectedGenres)) {
            $countParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
        }

        $countResult = $this->supabaseRequest('GET', 'books', null, $countParams);
        $totalBooks = is_array($countResult) && !isset($countResult['error']) ? count($countResult) : 0;
        $totalPages = max(1, ceil($totalBooks / $this->perPage));

        return view('partials/book_list', [
            'booksOnPage' => $books,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks
        ]);
    }

    public function add()
    {
        try {
            $json = $this->request->getJSON(true);
            
            if (!$json) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No data received'
                ]);
            }

            // Process UID - convert to PostgreSQL array format
            $uidArray = [];
            if (!empty($json['rfid_uid'])) {
                if (is_string($json['rfid_uid']) && strpos($json['rfid_uid'], ',') !== false) {
                    $uidArray = array_map('trim', explode(',', $json['rfid_uid']));
                } else if (is_string($json['rfid_uid'])) {
                    $uidArray = [trim($json['rfid_uid'])];
                } else if (is_array($json['rfid_uid'])) {
                    $uidArray = $json['rfid_uid'];
                }
            }

            $bookData = [
                'uid' => $uidArray,
                'code' => $json['kode_sekolah'] ?? '',
                'title' => $json['judul'] ?? '',
                'author' => $json['pengarang'] ?? '',
                'illustrator' => $json['illustrator'] ?? '',
                'publisher' => $json['publisher'] ?? '',
                'series' => $json['series'] ?? '',
                'genre' => $json['kategori'] ?? '',
                'isbn' => $json['isbn'] ?? '',
                'ddc_number' => $json['ddcNumber'] ?? '',
                'image' => $json['gambar'] ?? '',
                'quantity' => (int)($json['quantity'] ?? 1),
                'synopsis' => $json['sinopsis'] ?? '',
                'year' => (int)(date('Y')),
                'available' => true
            ];

            if (empty($bookData['title'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Judul harus diisi'
                ]);
            }

            $result = $this->supabaseRequest('POST', 'books', $bookData);
            
            if (isset($result['error'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menambahkan buku: ' . ($result['error'] ?? 'Unknown error')
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error adding book: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function edit()
    {
        $originalTitle = $this->request->getPost('originalTitle');
        $title = $this->request->getPost('title');
        $author = $this->request->getPost('author');
        $illustrator = $this->request->getPost('illustrator');
        $publisher = $this->request->getPost('publisher');
        $series = $this->request->getPost('series');
        $genre = $this->request->getPost('genre');
        $quantity = $this->request->getPost('quantity');
        $notes = $this->request->getPost('notes');
        $image = $this->request->getFile('image');

        // Find book by original title
        $books = $this->supabaseRequest('GET', 'books', null, [
            'title' => 'eq.' . $originalTitle,
            'limit' => 1
        ]);

        if (isset($books['error']) || empty($books)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Book not found'
            ]);
        }

        $book = $books[0];
        $bookId = $book['id'];

        $imageName = $book['image'] ?? '';
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $image->move(FCPATH . 'uploads', $imageName);
        }

        $updateData = [
            'title' => $title,
            'author' => $author,
            'illustrator' => $illustrator,
            'publisher' => $publisher,
            'series' => $series,
            'genre' => $genre,
            'quantity' => (int)$quantity,
            'notes' => $notes,
            'image' => $imageName
        ];

        $result = $this->supabaseRequest('PATCH', 'books?id=eq.' . $bookId, $updateData);

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update book'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Book updated successfully'
        ]);
    }

    public function detail()
    {
        $title = $this->request->getGet('title');

        $books = $this->supabaseRequest('GET', 'books', null, [
            'title' => 'eq.' . $title,
            'limit' => 1
        ]);

        if (isset($books['error']) || empty($books)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Book not found");
        }

        return view('detail_buku', [
            'book' => $books[0]
        ]);
    }

    public function all()
    {
        $books = $this->supabaseRequest('GET', 'books', null, [
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        if (isset($books['error'])) {
            $books = [];
        }

        return $this->response->setJSON(['books' => $books]);
    }

    public function all_key()
    {
        $books = $this->supabaseRequest('GET', 'books', null, [
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        if (isset($books['error'])) {
            $books = [];
        }

        // Add 'key' as alias for 'id' for backward compatibility
        foreach ($books as &$book) {
            $book['key'] = $book['id'];
        }

        return $this->response->setJSON(['books' => $books]);
    }

    public function uploadImage()
    {
        $file = $this->request->getFile('gambar');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false]);
        }
        $fileName = $file->getRandomName();
        $file->move(FCPATH . 'uploads', $fileName);
        $imageUrl = base_url('uploads/' . $fileName);
        return $this->response->setJSON(['success' => true, 'imageUrl' => $imageUrl]);
    }
}