<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\GenreCache;
use App\Traits\BookTrait;

ini_set("max_execution_time", 1000);
ini_set("memory_limit", "512M");

class BookController extends Controller
{
    use BookTrait;
    private $supabaseUrl;
    private $supabaseKey;
    private $perPage = 10;
    private $cache;
    private $genreCache;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_SERVICE_ROLE_KEY") ?: getenv("SUPABASE_API_KEY");
        $this->cache = \Config\Services::cache();
        $this->genreCache = new GenreCache();

        log_message("info", "=== BookController Initialized ===");
    }

    // =========================================================================
    // Supabase HTTP helpers
    // =========================================================================

    private function supabaseRequest(
        $method,
        $endpoint,
        $data = null,
        $queryParams = [],
        $cacheKey = null,
        $cacheTTL = 24 * 60 * 60
    ) {
        if ($cacheKey && $method === "GET") {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                log_message("info", "Cache HIT for: " . $cacheKey);
                return $cached;
            }
        }

        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message("error", "Supabase credentials not configured");
            return ["error" => "Supabase credentials not configured"];
        }

        $url = rtrim($this->supabaseUrl, "/") . "/rest/v1/" . $endpoint;

        if (!empty($queryParams)) {
            $url .= "?" . http_build_query($queryParams);
        }

        $headers = [
            "apikey: " . $this->supabaseKey,
            "Authorization: Bearer " . $this->supabaseKey,
            "Content-Type: application/json",
            "Accept: application/json",
            "Prefer: return=representation",
        ];

        log_message("info", "Supabase Request: " . $method . " " . $url);

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
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 60,
            CURLOPT_TCP_KEEPINTVL => 30,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_FRESH_CONNECT => false,
        ]);

        if ($data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            log_message("info", "Request Body: " . $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        log_message("info", "Response Code: " . $httpCode);

        if ($error) {
            log_message("error", "Supabase CURL Error: " . $error);
            return ["error" => $error];
        }

        if ($httpCode >= 400) {
            log_message(
                "error",
                "Supabase HTTP Error " . $httpCode . ": " . $response
            );
            return [
                "error" => "HTTP Error " . $httpCode,
                "response" => $response,
            ];
        }

        $decoded = json_decode($response, true);

        if ($cacheKey && $method === "GET" && !isset($decoded["error"])) {
            $this->cache->save($cacheKey, $decoded, $cacheTTL);
            log_message("info", "Cache SET for: " . $cacheKey);
        }

        return $decoded;
    }

    private function getCountFromSupabase($endpoint, $queryParams = []): int
    {
        if (empty($this->supabaseUrl) || empty($this->supabaseKey)) {
            log_message("error", "Supabase credentials not configured");
            return 0;
        }

        $countParams = array_merge($queryParams, [
            "select" => "id",
            "limit" => 1,
        ]);

        $url =
            rtrim($this->supabaseUrl, "/") .
            "/rest/v1/" .
            $endpoint .
            "?" .
            http_build_query($countParams);

        $headers = [
            "apikey: " . $this->supabaseKey,
            "Authorization: Bearer " . $this->supabaseKey,
            "Prefer: count=exact",
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerText = substr($response, 0, $headerSize);

        // Content-Range: 0-9/100  →  total = 100
        if (
            ($httpCode === 200 || $httpCode === 206) &&
            preg_match(
                "/content-range:\s*\d+-\d+\/(\d+)/i",
                $headerText,
                $matches
            )
        ) {
            log_message(
                "info",
                "Count retrieved for: " . $endpoint . " = " . $matches[1]
            );
            return (int) $matches[1];
        }

        log_message(
            "info",
            "Count request failed for: " .
                $endpoint .
                " (HTTP: " .
                $httpCode .
                ")"
        );
        return 0;
    }

    /**
     * Build Supabase query params from search/genre filters.
     * Returns params ready to pass to supabaseRequest or getCountFromSupabase.
     */
    private function buildBookQueryParams(
        string $search,
        array $selectedGenres,
        bool $withPagination = false,
        int $page = 1,
        array $extraParams = []
    ): array {
        $params = [
            "select" => "*",
            "order" => "created_at.desc",
            "is_test_data" => "eq.false",
        ];

        if ($withPagination) {
            $params["limit"] = $this->perPage;
            $params["offset"] = ($page - 1) * $this->perPage;
        }

        if (!empty($search)) {
            $params[
                "or"
            ] = "(title.ilike.*{$search}*,code.ilike.*{$search}*,isbn.ilike.*{$search}*,publisher.ilike.*{$search}*,series.ilike.*{$search}*)";
        }

        if (!empty($selectedGenres)) {
            $params["genres"] = "cs.{" . implode(",", array_map("trim", $selectedGenres)) . "}";
        }

        // Extra params (e.g. select override) are merged last so they can override defaults
        return array_merge($params, $extraParams);
    }

    /**
     * Fetch a paginated page of books plus total count in one call pair.
     *
     * @return array{books: array, totalBooks: int, totalPages: int}
     */
    private function fetchBooksPage(
        string $search,
        array $selectedGenres,
        int $page,
        array $selectParams = []
    ): array {
        $queryParams = $this->buildBookQueryParams(
            $search,
            $selectedGenres,
            true,
            $page,
            $selectParams
        );
        $books = $this->supabaseRequest("GET", "books_view", null, $queryParams);

        if (isset($books["error"])) {
            log_message(
                "error",
                "Failed to fetch books: " . print_r($books, true)
            );
            $books = [];
        }
        $books = $this->normalizeBookRows($books);

        $countParams = $queryParams;
        unset(
            $countParams["limit"],
            $countParams["offset"],
            $countParams["select"]
        );

        $totalBooks = $this->getCountFromSupabase("books_view", $countParams);
        $totalPages = max(1, (int) ceil($totalBooks / $this->perPage));

        return compact("books", "totalBooks", "totalPages");
    }

    // =========================================================================
    // Public actions
    // =========================================================================

    public function index()
    {
        $search = $this->request->getGet("search") ?? "";
        $selectedGenres = $this->request->getGet("genres") ?? [];
        $page = max(1, (int) ($this->request->getGet("page") ?? 1));

        [
            "books" => $books,
            "totalBooks" => $totalBooks,
            "totalPages" => $totalPages,
        ] = $this->fetchBooksPage($search, $selectedGenres, $page, [
            "select" => "id,title,authors,genres,image,year",
        ]);

        $latestBooks = $this->supabaseRequest(
            "GET",
            "books_view",
            null,
            [
                "select" => "title,synopsis,image",
                "order" => "created_at.desc",
                "limit" => 5,
                "is_test_data" => "eq.false",
            ],
            "latest_books_5"
        );

        return view("homepage", [
            "booksOnPage" => $books,
            "latestBooks" => $latestBooks ?? [],
            "genres" => $this->getAllGenres(),
            "search" => $search,
            "selectedGenres" => $selectedGenres,
            "page" => $page,
            "totalPages" => $totalPages,
            "totalBooks" => $totalBooks,
        ]);
    }

    public function filter()
    {
        $search = $this->request->getGet("search") ?? "";
        $selectedGenres = $this->request->getGet("genres") ?? [];
        $page = max(1, (int) ($this->request->getGet("page") ?? 1));

        [
            "books" => $books,
            "totalBooks" => $totalBooks,
            "totalPages" => $totalPages,
        ] = $this->fetchBooksPage($search, $selectedGenres, $page, [
            "select" => "id,title,authors,genres,image,year",
        ]);

        return $this->response->setJSON([
            "booksOnPage" => $books,
            "page" => $page,
            "totalPages" => $totalPages,
            "totalBooks" => $totalBooks,
            "genres" => $this->getAllGenres(),
        ]);
    }

    public function getNextKodeSekolah()
    {
        try {
            return $this->response->setJSON([
                "success" => true,
                "kode_sekolah" => $this->generateKodeSekolah(),
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Error: " . $e->getMessage(),
            ]);
        }
    }

    public function add()
    {
        try {
            $json = $this->request->getJSON(true);

            if (!$json) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "No data received",
                ]);
            }

            $bookData = [
                "uid" => $json["rfid_uid"] ?? "",
                "code" => $json["kode_sekolah"] ?? "",
                "title" => $json["judul"] ?? "",
                "author" => $json["pengarang"] ?? "",
                "illustrator" => $json["illustrator"] ?? "",
                "publisher" => $json["publisher"] ?? "",
                "series" => $json["series"] ?? "",
                "genre" => $json["kategori"] ?? "",
                "isbn" => $json["isbn"] ?? "",
                "ddc_number" => $json["ddcNumber"] ?? "",
                "image" => $json["gambar"] ?? "",
                "quantity" => (int) ($json["quantity"] ?? 1),
                "synopsis" => $json["sinopsis"] ?? "",
                "year" => (int) date("Y"),
            ];

            $result = $this->createNormalizedBook($bookData);

            if (isset($result["error"])) {
                return $this->response->setJSON([
                    "success" => false,
                    "message" => "Gagal menambahkan buku: " . $result["error"],
                ]);
            }

            $this->cache->delete("latest_books_5");
            $this->invalidateBooksCache(['select' => 'id,title,quantity,is_one_day_book']);
            $this->invalidateBooksCache(['select' => 'id,title']);
            $this->invalidateBooksCache(['select' => 'code']);
            $this->invalidateBooksCache([]);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);

            return $this->response->setJSON([
                "success" => true,
                "message" => "Buku berhasil ditambahkan",
                "data" => $result,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Error adding book: " . $e->getMessage());
            return $this->response->setJSON([
                "success" => false,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ]);
        }
    }

    public function edit()
    {
        $originalTitle = $this->request->getPost("originalTitle");

        $books = $this->supabaseRequest("GET", "books_view", null, [
            "title" => "eq." . $originalTitle,
            "is_test_data" => "eq.false",
            "limit" => 1,
        ]);

        if (isset($books["error"]) || empty($books)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Book not found",
            ]);
        }

        $book = $books[0];
        $bookId = $book["id"];

        $image = $this->request->getFile("image");
        $imageName = $book["image"] ?? "";
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $image->move(FCPATH . "uploads", $imageName);
        }

        $updateData = [
            "title" => $this->request->getPost("title"),
            "author" => $this->request->getPost("author"),
            "illustrator" => $this->request->getPost("illustrator"),
            "publisher" => $this->request->getPost("publisher"),
            "series" => $this->request->getPost("series"),
            "genre" => $this->request->getPost("genre"),
            "quantity" => (int) $this->request->getPost("quantity"),
            "notes" => $this->request->getPost("notes"),
            "image" => $imageName,
        ];

        $result = $this->updateNormalizedBook((int) $bookId, $updateData);

        if (isset($result["error"])) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Failed to update book",
            ]);
        }

        return $this->response->setJSON([
            "success" => true,
            "message" => "Book updated successfully",
        ]);
    }

    public function detail()
    {
        $title = $this->request->getGet("title");

        $books = $this->supabaseRequest("GET", "books_view", null, [
            "title" => "eq." . $title,
            "is_test_data" => "eq.false",
            "limit" => 1,
        ]);

        if (isset($books["error"]) || empty($books)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Book not found"
            );
        }

        $book = $this->normalizeBookRow($books[0]);
        $bookId = $book["id"];

        $activeBorrowers = $this->supabaseRequest("GET", "transactions", null, [
            "book_id" => "eq." . $bookId,
            "status" => "eq.active",
            "type" => "eq.borrow",
            "order" => "created_at.desc",
        ]);

        $borrowersWithDetails = [];
        if (!isset($activeBorrowers["error"]) && !empty($activeBorrowers)) {
            foreach ($activeBorrowers as $tx) {
                $userResult = $this->supabaseRequest("GET", "users_view", null, [
                    "id" => "eq." . $tx["user_id"],
                    "limit" => 1,
                ]);

                if (!isset($userResult["error"]) && !empty($userResult)) {
                    $borrowersWithDetails[] = [
                        "transaction" => $tx,
                        "user" => $userResult[0],
                    ];
                }
            }
        }

        return view("detail_buku", [
            "book" => $book,
            "borrowers" => $borrowersWithDetails,
        ]);
    }

    public function searchBooks()
    {
        $search = trim($this->request->getGet("search") ?? "");
        $limit = max(1, (int) ($this->request->getGet("limit") ?? 20));

        if ($search === "") {
            return $this->response->setJSON([
                "success" => false,
                "books" => [],
            ]);
        }

        $queryParams = [
            "select" => "id,title",
            "title" => "ilike.*{$search}*",
            "is_test_data" => "eq.false",
            "order" => "title.asc",
            "limit" => $limit,
        ];

        $books = $this->supabaseRequest("GET", "books_view", null, $queryParams);

        if (is_array($books) && !isset($books["error"])) {
            return $this->response->setJSON([
                "success" => true,
                "books" => $books,
            ]);
        }

        return $this->response->setJSON([
            "success" => false,
            "books" => [],
            "error" => "Search failed",
        ]);
    }

    public function uploadImage()
    {
        $file = $this->request->getFile("gambar");
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(["success" => false]);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . "uploads", $fileName);

        return $this->response->setJSON([
            "success" => true,
            "imageUrl" => base_url("uploads/" . $fileName),
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function generateKodeSekolah(): string
    {
        $currentYear = date("Y");
        $currentMonth = (int) date("n");

        $romanMonths = [
            1 => "I",
            2 => "II",
            3 => "III",
            4 => "IV",
            5 => "V",
            6 => "VI",
            7 => "VII",
            8 => "VIII",
            9 => "IX",
            10 => "X",
            11 => "XI",
            12 => "XII",
        ];

        $allBooks = $this->fetchAllBooks(["select" => "code"]);
        $maxNumber = 0;

        foreach ((array) $allBooks as $book) {
            if (empty($book["code"])) {
                continue;
            }
            $parts = explode("/", $book["code"]);
            if (count($parts) >= 4 && (int) $parts[3] === (int) $currentYear) {
                $maxNumber = max($maxNumber, (int) $parts[0]);
            }
        }

        log_message(
            "info",
            "generateKodeSekolah - year: {$currentYear}, next: " .
                ($maxNumber + 1)
        );

        return sprintf(
            "%03d/YCB-CB/%s/%s",
            $maxNumber + 1,
            $romanMonths[$currentMonth],
            $currentYear
        );
    }

    private function getGenres(array $books = []): array
    {
        if (!empty($books)) {
            $genres = array_unique(array_filter(array_column($books, "genre")));
            sort($genres);
            return array_values($genres);
        }

        return $this->getAllGenres();
    }
}
