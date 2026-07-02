<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Traits\BookTrait;

ini_set("max_execution_time", 1000);
ini_set("memory_limit", "512M");

class BookManagementController extends Controller
{
    use BookTrait;

    private $supabaseUrl;
    private $supabaseKey;
    private $cloudinaryName = "dqx1ofl8j";
    private $cloudinaryPreset = "ml_default";
    private $cache;

    public function __construct()
    {
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_API_KEY");
        $this->cache = \Config\Services::cache();
    }

    private function supabaseRequest(
        $method,
        $endpoint,
        $data = null,
        $queryParams = []
    ) {
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
            log_message("error", "cURL Error: " . $error);
            return ["error" => $error];
        }

        if ($httpCode >= 400) {
            log_message("error", "HTTP Error " . $httpCode . ": " . $response);
            return [
                "error" => "HTTP Error " . $httpCode,
                "response" => $response,
            ];
        }

        return json_decode($response, true);
    }

    private function uploadToCloudinary($fileSource)
    {
        try {
            // Verify file exists
            if (!file_exists($fileSource)) {
                throw new \Exception("File does not exist: " . $fileSource);
            }

            $ch = curl_init();

            $timestamp = time();
            $randomStr = substr(
                str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"),
                0,
                7
            );
            $filename = "book_" . $timestamp . "_" . $randomStr;

            // Use CURLFile for proper multipart/form-data encoding
            $postData = [
                "file" => new \CURLFile($fileSource),
                "upload_preset" => $this->cloudinaryPreset,
                "public_id" => $filename,
                "folder" => "books",
            ];

            curl_setopt_array($ch, [
                CURLOPT_URL =>
                    "https://api.cloudinary.com/v1_1/" .
                    $this->cloudinaryName .
                    "/image/upload",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            log_message("info", "Uploading file to Cloudinary: " . $fileSource);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            log_message("info", "Cloudinary response code: " . $httpCode);
            log_message(
                "info",
                "Cloudinary response: " . substr($response, 0, 500)
            );

            if ($error) {
                log_message(
                    "error",
                    "cURL Error uploading to Cloudinary: " . $error
                );
                throw new \Exception("Cloudinary upload error: " . $error);
            }

            if ($httpCode >= 400) {
                log_message(
                    "error",
                    "Cloudinary HTTP Error " . $httpCode . ": " . $response
                );
                throw new \Exception(
                    "Cloudinary HTTP Error " .
                        $httpCode .
                        ": " .
                        substr($response, 0, 200)
                );
            }

            $data = json_decode($response, true);

            if (!isset($data["secure_url"])) {
                log_message(
                    "error",
                    "Cloudinary response missing secure_url: " . $response
                );
                throw new \Exception(
                    "Invalid Cloudinary response: " . $response
                );
            }

            log_message(
                "info",
                "Cloudinary upload successful: " . $data["secure_url"]
            );
            return $data["secure_url"];
        } catch (\Exception $e) {
            log_message(
                "error",
                "Exception in uploadToCloudinary: " . $e->getMessage()
            );
            throw $e;
        }
    }

    public function index()
    {
        $books = $this->fetchAllBooks([
            "order" => "created_at.desc",
        ]);

        return view("management_buku", [
            "books" => $books,
            "genres" => $this->getAllGenres(),
        ]);
    }

    // --- EXPORT TO CSV ---
    public function exportCsv()
    {
        // Fetch ALL books using pagination loop
        $books = $this->fetchAllBooks([
            "order" => "created_at.desc",
        ]);

        if (empty($books)) {
            return $this->response
                ->setStatusCode(500)
                ->setBody("Gagal mengambil data buku");
        }

        $filename = "books_export_" . date("Ymd_His") . ".csv";
        $fields = [
            "id",
            "code",
            "title",
            "author",
            "publisher",
            "year",
            "genre",
            "illustrator",
            "series",
            "isbn",
            "ddc_number",
            "quantity",
            "notes",
            "image",
            "synopsis",
            "uid",
            "available",
            "is_one_day_book",
            "shelf_position",
        ];

        // Set headers for download
        header("Content-Type: text/csv; charset=utf-8");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header("Pragma: no-cache");
        header("Expires: 0");

        // Open output stream
        $output = fopen("php://output", "w");

        // Add BOM for UTF-8
        fprintf($output, chr(0xef) . chr(0xbb) . chr(0xbf));

        // Write header
        fputcsv($output, $fields);

        // Write data
        foreach ($books as $book) {
            $row = [];
            foreach ($fields as $field) {
                $value = $book[$field] ?? "";

                // Handle array fields (uid)
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                // Handle boolean fields
                if (is_bool($value)) {
                    $value = $value ? "true" : "false";
                }

                $row[] = $value;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }

    public function edit($id)
    {
        try {
            $json = $this->request->getJSON(true);
            if (!$json) {
                return $this->response->setStatusCode(400)->setJSON([
                    "success" => false,
                    "message" => "No data received",
                ]);
            }

            if (
                empty($json["code"]) ||
                empty($json["title"]) ||
                empty($json["author"]) ||
                empty($json["genre"]) ||
                empty($json["uid"])
            ) {
                return $this->response->setStatusCode(400)->setJSON([
                    "success" => false,
                    "message" =>
                        "Kode buku, judul, penulis, genre, dan UID harus diisi",
                ]);
            }

            if (empty($json["quantity"]) || $json["quantity"] < 1) {
                return $this->response->setStatusCode(400)->setJSON([
                    "success" => false,
                    "message" => "Quantity harus minimal 1",
                ]);
            }

            $books = $this->supabaseRequest("GET", "books", null, [
                "id" => "eq." . $id,
                "limit" => 1,
            ]);

            if (isset($books["error"]) || empty($books)) {
                return $this->response->setStatusCode(404)->setJSON([
                    "success" => false,
                    "message" => "Buku tidak ditemukan",
                ]);
            }

            $book = $books[0];
            $imageName = $book["image"] ?? "";

            if (!empty($json["imageBase64"])) {
                try {
                    $imageData = base64_decode(
                        preg_replace(
                            "#^data:image/\w+;base64,#i",
                            "",
                            $json["imageBase64"]
                        )
                    );
                    $tempPath =
                        FCPATH . "writable/uploads/temp_" . uniqid() . ".jpg";

                    if (!is_dir(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }

                    file_put_contents($tempPath, $imageData);

                    $imageName = $this->uploadToCloudinary($tempPath);

                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                } catch (\Exception $e) {
                    log_message(
                        "error",
                        "Image upload failed: " . $e->getMessage()
                    );
                }
            }

            $uidArray = $json["uid"];
            if (is_string($uidArray) && strpos($uidArray, ",") !== false) {
                $uidArray = array_map("trim", explode(",", $uidArray));
            } elseif (is_string($uidArray)) {
                $uidArray = [trim($uidArray)];
            }
            $uidArray = array_filter($uidArray, fn($u) => !empty(trim($u)));
            $uidArray = array_values($uidArray);

            $updateData = [
                "uid" => $uidArray,
                "quantity" => max(1, (int) $json["quantity"]),
                "code" => $json["code"],
                "title" => $json["title"],
                "author" => $json["author"],
                "publisher" => $json["publisher"],
                "year" => (int) ($json["year"] ?? date("Y")),
                "genre" => $json["genre"],
                "illustrator" => $json["illustrator"],
                "series" => $json["series"],
                "notes" => $json["notes"] ?? "",
                "synopsis" => $json["synopsis"],
                "is_one_day_book" => (bool) $json["is_one_day_book"],
                "available" => (bool) $json["available"],
                "image" => $imageName,
                "isbn" => $json["isbn"],
            ];

            $result = $this->supabaseRequest(
                "PATCH",
                "books?id=eq." . $id,
                $updateData
            );

            if (isset($result["error"])) {
                log_message(
                    "error",
                    "Failed to update book: " . print_r($result, true)
                );
                return $this->response->setStatusCode(500)->setJSON([
                    "success" => false,
                    "message" =>
                        "Gagal mengupdate buku: " .
                        ($result["response"] ?? "Unknown error"),
                ]);
            }

            $this->invalidateBooksCache(["select" => "id,title,quantity,is_one_day_book"]);
            $this->invalidateBooksCache(["select" => "id,title"]);
            $this->invalidateBooksCache(["select" => "code"]);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);
            
            return $this->response->setJSON([
                "success" => true,
                "message" => "Buku berhasil diupdate",
                "data" => $result,
            ]);
        } catch (\Exception $e) {
            log_message("error", "Exception in edit: " . $e->getMessage());
            if ($isJsonRequest ?? false) {
                return $this->response->setStatusCode(500)->setJSON([
                    "success" => false,
                    "message" => "Terjadi kesalahan: " . $e->getMessage(),
                ]);
            }
            return redirect()
                ->to("/management-buku")
                ->with("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $code = $this->request->getGetPost("code");

            if (empty($code)) {
                return redirect()
                    ->to("/management-buku")
                    ->with("error", "Kode buku tidak valid");
            }

            // cari buku by code
            $books = $this->supabaseRequest("GET", "books", null, [
                "code" => "eq." . trim($code),
                "limit" => 1,
            ]);

            if (isset($books["error"]) || empty($books)) {
                log_message("error", "Book not found for delete: " . $code);
                return redirect()
                    ->to("/management-buku")
                    ->with("error", "Buku tidak ditemukan");
            }

            $bookId = $books[0]["id"];

            // Delete book
            $result = $this->supabaseRequest(
                "DELETE",
                "books?id=eq." . $bookId
            );

            if (isset($result["error"])) {
                log_message(
                    "error",
                    "Failed to delete book: " . print_r($result, true)
                );
                return redirect()
                    ->to("/management-buku")
                    ->with("error", "Gagal menghapus buku");
            }

            $this->invalidateBooksCache(["select" => "id,title,quantity,is_one_day_book"]);
            $this->invalidateBooksCache(["select" => "id,title"]);
            $this->invalidateBooksCache(["select" => "code"]);
            $this->invalidateBooksCache(["order" => "created_at.desc"]);

            return redirect()
                ->to("/management-buku")
                ->with("message", "Buku berhasil dihapus");
        } catch (\Exception $e) {
            log_message("error", "Exception in delete: " . $e->getMessage());
            return redirect()
                ->to("/management-buku")
                ->with("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function importJson()
    {
        try {
            $file = $this->request->getFile("json_file");
            if (!$file || !$file->isValid()) {
                return redirect()
                    ->to("/management-buku")
                    ->with("error", "File JSON tidak valid");
            }

            $jsonContent = file_get_contents($file->getTempName());
            $booksData = json_decode($jsonContent, true);

            if (!is_array($booksData)) {
                return redirect()
                    ->to("/management-buku")
                    ->with(
                        "error",
                        "Format JSON tidak valid. Harus berupa array."
                    );
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()
                    ->to("/management-buku")
                    ->with("error", "JSON Error: " . json_last_error_msg());
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            $batchSize = 10;
            $batches = array_chunk($booksData, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                foreach ($batch as $index => $book) {
                    try {
                        // Validate required fields
                        if (empty($book["code"]) || empty($book["title"])) {
                            $errorCount++;
                            $errors[] =
                                "Row " .
                                ($index + 1) .
                                ": Missing required fields (code or title)";
                            continue;
                        }

                        $uidArray = $book["uid"] ?? [];
                        $uidArray = is_array($uidArray)
                            ? $uidArray
                            : [$uidArray];
                        $uidArray = array_filter(
                            $uidArray,
                            fn($u) => !empty(trim($u))
                        );
                        $uidArray = array_values($uidArray);

                        $data = [
                            "uid" => $uidArray,
                            "quantity" => max(
                                1,
                                (int) ($book["quantity"] ?? 1)
                            ),
                            "code" => trim($book["code"]),
                            "genre" => $book["genre"] ?? "",
                            "title" => trim($book["title"]),
                            "author" => $book["author"] ?? "",
                            "illustrator" => $book["illustrator"] ?? "",
                            "publisher" => $book["publisher"] ?? "",
                            "series" => $book["series"] ?? "",
                            "isbn" => $book["isbn"] ?? "",
                            "ddc_number" =>
                                $book["ddc_number"] ??
                                ($book["ddcNumber"] ?? ""),
                            "image" => $book["image"] ?? "",
                            "notes" => $book["notes"] ?? "",
                            "shelf_position" =>
                                $book["shelfPosition"] ??
                                ($book["shelf_position"] ?? ""),
                            "synopsis" => $book["synopsis"] ?? "",
                            "is_in_class" => isset($book["isInClass"])
                                ? (bool) $book["isInClass"]
                                : (isset($book["is_in_class"])
                                    ? (bool) $book["is_in_class"]
                                    : false),
                            "year" => (int) ($book["year"] ?? date("Y")),
                            "is_one_day_book" => isset($book["isOneDayBook"])
                                ? (bool) $book["isOneDayBook"]
                                : (isset($book["is_one_day_book"])
                                    ? (bool) $book["is_one_day_book"]
                                    : false),
                            "available" => isset($book["available"])
                                ? (bool) $book["available"]
                                : true,
                        ];

                        $result = $this->supabaseRequest(
                            "POST",
                            "books",
                            $data
                        );

                        if (isset($result["error"])) {
                            $errorCount++;
                            $errors[] =
                                "Row " .
                                ($index + 1) .
                                " (Code: {$book["code"]}): " .
                                ($result["response"] ?? "Unknown error");
                            log_message(
                                "error",
                                "Failed to import book: " .
                                    print_r($result, true)
                            );
                        } else {
                            $successCount++;
                        }

                        // delay request (menghindari rate limit)
                        usleep(100000);
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] =
                            "Row " . ($index + 1) . ": " . $e->getMessage();
                        log_message(
                            "error",
                            "Exception importing book: " . $e->getMessage()
                        );
                    }
                }

                // delay
                if ($batchIndex < count($batches) - 1) {
                    sleep(1);
                }
            }

            $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
            if (!empty($errors) && $errorCount <= 10) {
                $message .=
                    "\n\nError details:\n" .
                    implode("\n", array_slice($errors, 0, 10));
            }

            if ($errorCount > 0) {
                log_message(
                    "error",
                    "Import errors: " . print_r($errors, true)
                );
            }

            return redirect()
                ->to("/management-buku")
                ->with("message", $message);
        } catch (\Exception $e) {
            log_message(
                "error",
                "Exception in importJson: " . $e->getMessage()
            );
            return redirect()
                ->to("/management-buku")
                ->with("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    /**
     * Get book borrowers and availability information
     * Called from management_buku.php to show detailed book info with current borrowers
     */
    public function getBookBorrowers()
    {
        try {
            $bookId =
                $this->request->getGet("book_id") ??
                $this->request->getPost("book_id");

            if (empty($bookId)) {
                return $this->response
                    ->setJSON(["error" => "book_id parameter is required"])
                    ->setStatusCode(400);
            }

            $cacheKey = "book_borrowers_" . $bookId;
            $cachedData = $this->cache->get($cacheKey);
            if ($cachedData !== null) {
                log_message("info", "Cache HIT for book borrowers: " . $bookId);
                return $this->response->setJSON($cachedData);
            }

            // Fetch book data
            $book = $this->supabaseRequest("GET", "books", null, [
                "id" => "eq." . $bookId,
                "limit" => 1,
            ]);

            if (empty($book) || isset($book["error"])) {
                return $this->response
                    ->setJSON(["error" => "Book not found"])
                    ->setStatusCode(404);
            }

            $bookData = $book[0];
            $availableQuantity = $bookData["quantity"];

            // Fetch active borrowers untuk buku ini
            $activeBorrowers = $this->supabaseRequest(
                "GET",
                "transactions",
                null,
                [
                    "book_id" => "eq." . $bookId,
                    "status" => "eq.active",
                    "type" => "eq.borrow",
                    "order" => "tanggal.desc",
                ]
            );

            // Fetch user details untuk setiap borrower
            $borrowersWithDetails = [];
            if (!isset($activeBorrowers["error"]) && !empty($activeBorrowers)) {
                foreach ($activeBorrowers as $tx) {
                    $userResult = $this->supabaseRequest("GET", "users", null, [
                        "id" => "eq." . $tx["user_id"],
                        "limit" => 1,
                    ]);
                    $classResult = $this->supabaseRequest(
                        "GET",
                        "classes",
                        null,
                        [
                            "id" => "eq." . ($userResult[0]["class_id"] ?? ""),
                            "limit" => 1,
                        ]
                    );

                    if (
                        !isset($userResult["error"]) &&
                        !empty($userResult) &&
                        !isset($classResult["error"]) &&
                        !empty($classResult)
                    ) {
                        $user = $userResult[0];
                        $user["class_id"] =
                            $classResult[0]["nama_kelas"] ?? "-";
                        // Calculate status (late or active)
                        $status = "AKTIF";
                        if (!empty($tx["due_date"])) {
                            $dueDate = new \DateTime($tx["due_date"]);
                            $today = new \DateTime();
                            if ($today > $dueDate) {
                                $status = "TERLAMBAT";
                            }
                        }

                        $borrowersWithDetails[] = [
                            "pic_name" => $user["nama"] ?? "-",
                            "pic_class" => $user["class_id"] ?? "-",
                            "borrow_date" => $tx["tanggal"] ?? "-",
                            "due_date" => $tx["due_date"] ?? "-",
                            "status" => $status,
                            "recorded_by" => $tx["pic_name"] ?? "-",
                        ];
                    }
                }
            }

            $totalQuantity = $availableQuantity + count($borrowersWithDetails);
            $isOutOfStock = $availableQuantity <= 0;
            $result = [
                "success" => true,
                "book_id" => $bookId,
                "total_quantity" => $totalQuantity,
                "borrowed_count" => count($borrowersWithDetails),
                "available_quantity" => $availableQuantity,
                "is_out_of_stock" => $isOutOfStock,
                "borrowers" => $borrowersWithDetails,
                "book_info" => [
                    "code" => $bookData["code"] ?? "-",
                    "title" => $bookData["title"] ?? "-",
                    "author" => $bookData["author"] ?? "-",
                    "illustrator" => $bookData["illustrator"] ?? "-",
                    "publisher" => $bookData["publisher"] ?? "-",
                    "year" => $bookData["year"] ?? "-",
                    "genre" => $bookData["genre"] ?? "-",
                    "series" => $bookData["series"] ?? "-",
                    "isbn" => $bookData["isbn"] ?? "-",
                    "ddc_number" => $bookData["ddc_number"] ?? "-",
                    "synopsis" => $bookData["synopsis"] ?? "-",
                    "is_one_day_book" => $bookData["is_one_day_book"] ?? false,
                    "shelf_position" => $bookData["shelf_position"] ?? "-",
                    "available" => $bookData["available"] ?? false,
                ],
            ];

            $this->cache->save($cacheKey, $result, 24 * 60 * 60);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message(
                "error",
                "Error in getBookBorrowers: " . $e->getMessage()
            );
            return $this->response
                ->setJSON([
                    "error" =>
                        "Error fetching borrower information: " .
                        $e->getMessage(),
                ])
                ->setStatusCode(500);
        }
    }
}
