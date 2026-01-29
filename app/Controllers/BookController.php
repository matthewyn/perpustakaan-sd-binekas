<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\GenreCache;

ini_set('max_execution_time', 1000);
ini_set('memory_limit', '512M');

class BookController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;
    private $perPage = 10;
    private $cache;
    private $genreCache;

    public function __construct()
    {
        $this->supabaseUrl = getenv('SUPABASE_URL');
        $this->supabaseKey = getenv('SUPABASE_API_KEY');
        $this->cache = \Config\Services::cache();
        $this->genreCache = new GenreCache();
        
        log_message('info', '=== BookController Initialized ===');
    }

    private function supabaseRequest($method, $endpoint, $data = null, $queryParams = [], $cacheKey = null, $cacheTTL = 300)
    {
        if ($cacheKey && $method === 'GET') {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                log_message('info', 'Cache HIT for: ' . $cacheKey);
                return $cached;
            }
        }

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
            CURLOPT_CONNECTTIMEOUT => 10,
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

        $decoded = json_decode($response, true);

        // Cache successful GET responses
        if ($cacheKey && $method === 'GET' && !isset($decoded['error'])) {
            $this->cache->save($cacheKey, $decoded, $cacheTTL);
            log_message('info', 'Cache SET for: ' . $cacheKey);
        }

        return $decoded;
    }

    private function fetchAllBooks($queryParams = [])
    {
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
                
                // If we got less than limit, we've reached the end
                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total books fetched: ' . count($allBooks));
        return $allBooks;
    }

    private function getGenres(array $books = []): array
    {
        // If books are provided, extract genres from them instead of making separate request
        if (!empty($books)) {
            $genres = array_unique(array_column($books, 'genre'));
            $genres = array_filter($genres);
            sort($genres);
            return $genres;
        }

        // Use genre cache for full genre list - 1 hour TTL
        return $this->genreCache->getGenres(function() {
            // Fetch ALL books to get complete genre list
            $allBooks = $this->fetchAllBooks(['select' => 'genre']);

            if (empty($allBooks)) {
                return [];
            }

            $genres = array_unique(array_column($allBooks, 'genre'));
            $genres = array_filter($genres); // Remove empty values
            sort($genres);
            return $genres;
        });
    }

    private function getAllGenres(): array
    {
        // Always fetch complete genre list from database cache - 1 hour TTL
        return $this->genreCache->getGenres(function() {
            // Fetch ALL books to get complete genre list
            $allBooks = $this->fetchAllBooks(['select' => 'genre']);

            if (empty($allBooks)) {
                return [];
            }

            $genres = array_unique(array_column($allBooks, 'genre'));
            $genres = array_filter($genres); // Remove empty values
            sort($genres);
            return $genres;
        });
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
        
        $allBooks = $this->fetchAllBooks(['select' => 'code']);
        
        $maxNumber = 0;
        $codesThisYear = [];
        
        if (is_array($allBooks)) {
            // Extract numbers from codes for current year
            foreach ($allBooks as $book) {
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

        // Build query for fetching books with pagination
        $queryParams = [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => $this->perPage,
            'offset' => ($page - 1) * $this->perPage
        ];

        // Search filter
        if (!empty($search)) {
            $searchFilter = "(title.ilike.*{$search}*,author.ilike.*{$search}*,code.ilike.*{$search}*,isbn.ilike.*{$search}*,series.ilike.*{$search}*)";
            $queryParams['or'] = $searchFilter;
        }

        // Genre filter
        if (!empty($selectedGenres) && is_array($selectedGenres)) {
            if (isset($queryParams['or'])) {
                $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
                $queryParams['and'] = "({$queryParams['or']}),{$genreFilter}";
                unset($queryParams['or']);
            } else {
                $queryParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $books = $this->supabaseRequest('GET', 'books', null, $queryParams);

        if (isset($books['error'])) {
            log_message('error', 'Failed to fetch books: ' . print_r($books, true));
            $books = [];
        }

        $countParams = ['select' => 'id'];
        
        if (!empty($search)) {
            $searchFilter = "(title.ilike.*{$search}*,author.ilike.*{$search}*,code.ilike.*{$search}*,isbn.ilike.*{$search}*,series.ilike.*{$search}*)";
            $countParams['or'] = $searchFilter;
        }

        if (!empty($selectedGenres) && is_array($selectedGenres)) {
            $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
            if (isset($countParams['or'])) {
                $countParams['and'] = "({$countParams['or']}),{$genreFilter}";
                unset($countParams['or']);
            } else {
                $countParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $allMatchingBooks = $this->fetchAllBooks($countParams);
        $totalBooks = count($allMatchingBooks);
        $totalPages = max(1, ceil($totalBooks / $this->perPage));

        $latestBooks = $this->supabaseRequest('GET', 'books', null, [
            'select' => '*',
            'order' => 'created_at.desc',
            'limit' => 5
        ], 'latest_books_5', 300);

        if (isset($latestBooks['error'])) {
            $latestBooks = [];
        }

        $allGenres = $this->getAllGenres();

        // Return view for normal page load
        return view('welcome_message', [
            'booksOnPage' => $books,
            'latestBooks' => $latestBooks,
            'genres' => $allGenres,
            'search' => $search,
            'selectedGenres' => $selectedGenres,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks
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

        // Search filter
        if (!empty($search)) {
            $searchFilter = "(title.ilike.*{$search}*,author.ilike.*{$search}*,code.ilike.*{$search}*,isbn.ilike.*{$search}*,series.ilike.*{$search}*)";
            $queryParams['or'] = $searchFilter;
        }

        // Genre filter
        if (!empty($selectedGenres) && is_array($selectedGenres)) {
            if (isset($queryParams['or'])) {
                $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
                $queryParams['and'] = "({$queryParams['or']}),{$genreFilter}";
                unset($queryParams['or']);
            } else {
                $queryParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $books = $this->supabaseRequest('GET', 'books', null, $queryParams);

        if (isset($books['error'])) {
            log_message('error', 'Failed to fetch books: ' . print_r($books, true));
            $books = [];
        }

        $countParams = ['select' => 'id'];
        
        if (!empty($search)) {
            $searchFilter = "(title.ilike.*{$search}*,author.ilike.*{$search}*,code.ilike.*{$search}*,isbn.ilike.*{$search}*,series.ilike.*{$search}*)";
            $countParams['or'] = $searchFilter;
        }

        if (!empty($selectedGenres) && is_array($selectedGenres)) {
            $genreFilter = 'genre.in.(' . implode(',', $selectedGenres) . ')';
            if (isset($countParams['or'])) {
                $countParams['and'] = "({$countParams['or']}),{$genreFilter}";
                unset($countParams['or']);
            } else {
                $countParams['genre'] = 'in.(' . implode(',', $selectedGenres) . ')';
            }
        }

        $allMatchingBooks = $this->fetchAllBooks($countParams);
        $totalBooks = count($allMatchingBooks);
        $totalPages = max(1, ceil($totalBooks / $this->perPage));

        $allGenres = $this->getAllGenres();

        return $this->response->setJSON([
            'html' => view('partials/book_list', [
                'booksOnPage' => $books,
                'page' => $page,
                'totalPages' => $totalPages,
                'totalBooks' => $totalBooks
            ], ['debug' => false]),
            'page' => $page,
            'totalPages' => $totalPages,
            'totalBooks' => $totalBooks,
            'genres' => $allGenres
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
        $books = $this->fetchAllBooks([
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        return $this->response->setJSON(['books' => $books]);
    }

    public function all_key()
    {
        $books = $this->fetchAllBooks([
            'select' => '*',
            'order' => 'created_at.desc'
        ]);

        foreach ($books as &$book) {
            $book['key'] = $book['id'];
        }

        return $this->response->setJSON(['books' => $books]);
    }

    public function searchBooks()
    {
        $search = $this->request->getGet('search');
        $limit = (int)$this->request->getGet('limit') ?? 20;
        
        if (!$search || strlen(trim($search)) < 1) {
            return $this->response->setJSON(['success' => false, 'books' => []]);
        }

        $search = trim($search);
        
        // Use only title and id fields to minimize egress
        $queryParams = [
            'select' => 'id,title',
            'title' => "ilike.*" . $search . "*",
            'order' => 'title.asc',
            'limit' => $limit
        ];

        $cacheKey = 'book_search_' . md5($search) . '_' . $limit;
        $books = $this->supabaseRequest('GET', 'books', null, $queryParams, $cacheKey, 300);

        if (is_array($books) && !isset($books['error'])) {
            return $this->response->setJSON(['success' => true, 'books' => $books]);
        }

        return $this->response->setJSON(['success' => false, 'books' => [], 'error' => 'Search failed']);
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