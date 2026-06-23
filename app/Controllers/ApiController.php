<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ApiController extends Controller
{
    // --- API Keys ---
    private $gemini_api_key;
    private $openai_api_key;
    private $supabaseUrl;
    private $supabaseKey;
    private $cache;

    // --- Logging ---
    private $debugLog = [];

    public function __construct()
    {
        // === SUPABASE CONFIG ===
        $this->supabaseUrl = getenv("SUPABASE_URL");
        $this->supabaseKey = getenv("SUPABASE_API_KEY");
        $this->cache = \Config\Services::cache();

        // =======================================================
        // 1. GEMINI API KEY LOADING
        // =======================================================
        $this->gemini_api_key = $_ENV["GEMINI_API_KEY"] ?? "";

        // Fallback: Load from .env file if not set
        if (empty($this->gemini_api_key)) {
            $envFile = ROOTPATH . ".env";
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                if (
                    preg_match(
                        "/GEMINI_API_KEY\s*=\s*(.+)/",
                        $envContent,
                        $matches
                    )
                ) {
                    $this->gemini_api_key = trim($matches[1]);
                }
            }
        }

        // Logging Gemini
        if (empty($this->gemini_api_key)) {
            log_message("error", " GEMINI_API_KEY not set in .env file");
            $this->clientLog(" GEMINI_API_KEY not set in .env file", "ERROR");
        } else {
            $keyLength = strlen($this->gemini_api_key);
            $keyPreview =
                substr($this->gemini_api_key, 0, 10) .
                "..." .
                substr($this->gemini_api_key, -4);
            log_message(
                "info",
                " Gemini API key loaded (length: {$keyLength}, preview: {$keyPreview})"
            );
            $this->clientLog(
                " Gemini API key loaded",
                "length: {$keyLength}, preview: {$keyPreview}"
            );
        }

        // =======================================================
        // 2. OPENAI API KEY LOADING
        // =======================================================
        $this->openai_api_key = $_ENV["OPENAI_API_KEY"] ?? "";

        // Fallback: Load from .env file if not set
        if (empty($this->openai_api_key)) {
            $envFile = ROOTPATH . ".env";
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                if (
                    preg_match(
                        "/OPENAI_API_KEY\s*=\s*(.+)/",
                        $envContent,
                        $matches
                    )
                ) {
                    $this->openai_api_key = trim($matches[1]);
                }
            }
        }

        // Logging OpenAI
        if (empty($this->openai_api_key)) {
            log_message("error", " OPENAI_API_KEY not set in .env file");
            $this->clientLog(" OPENAI_API_KEY not set in .env file", "ERROR");
        } else {
            $keyLength = strlen($this->openai_api_key);
            $keyPreview =
                substr($this->openai_api_key, 0, 10) .
                "..." .
                substr($this->openai_api_key, -4);
            log_message(
                "info",
                " OpenAI API key loaded (length: {$keyLength}, preview: {$keyPreview})"
            );
            $this->clientLog(
                " OpenAI API key loaded",
                "length: {$keyLength}, preview: {$keyPreview}"
            );
        }
    }

    // --- Helper Methods (Used by both) ---

    /**
     * Helper untuk mencatat log yang akan dikirim ke client (browser console)
     */
    private function clientLog($message, $data = null)
    {
        $entry = ["message" => $message];
        $entry["time"] = date("H:i:s");

        if ($data !== null) {
            $entry["data"] =
                is_array($data) || is_object($data)
                    ? json_encode(
                        $data,
                        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    )
                    : $data;
        }
        $this->debugLog[] = $entry;
        log_message(
            "debug",
            "CLIENT LOG: " .
                $message .
                ($data
                    ? " " .
                        (is_array($data) || is_object($data)
                            ? json_encode($data)
                            : $data)
                    : "")
        );
    }

    /**
     * JSON Schema for structured output (Used by Gemini).
     * Follows Gemini API schema format properly
     */
    private function getResponseSchema()
    {
        return (object) [
            "type" => "object",
            "properties" => (object) [
                "is_book" => (object) [
                    "type" => "boolean",
                    "description" =>
                        "True if the image is a book cover, false otherwise.",
                ],
                "title" => (object) [
                    "type" => "string",
                    "description" => "The full book title.",
                ],
                "author" => (object) [
                    "type" => "string",
                    "description" => "The main author of the book.",
                ],
                "illustrator" => (object) [
                    "type" => "string",
                    "description" => "The illustrator or artist, if mentioned.",
                ],
                "publisher" => (object) [
                    "type" => "string",
                    "description" => "The publisher name.",
                ],
                "series" => (object) [
                    "type" => "string",
                    "description" => "The book series, if applicable.",
                ],
                "isbn" => (object) [
                    "type" => "string",
                    "description" => "The 13-digit ISBN number.",
                ],
                "ddcNumber" => (object) [
                    "type" => "string",
                    "description" =>
                        "The Dewey Decimal Classification number. GENERATE if not found.",
                ],
                "category" => (object) [
                    "type" => "string",
                    "description" =>
                        "The genre or category of the book (in Indonesian).",
                ],
                "synopsis" => (object) [
                    "type" => "string",
                    "description" =>
                        "A brief synopsis of the book (2-3 sentences in Indonesian).",
                ],
                "quantity" => (object) [
                    "type" => "string",
                    "description" => 'Always "1" for a single book.',
                ],
            ],
            "required" => ["is_book", "title", "author", "category"],
        ];
    }

    /**
     * Fallback method to extract JSON data using regex (Used by both if JSON decoding fails)
     */
    private function extractJsonFallback($text)
    {
        $this->clientLog(" Attempting RegEx Fallback for JSON Parsing");
        $data = [
            "is_book" => true,
            "title" => "",
            "author" => "",
            "illustrator" => "",
            "publisher" => "",
            "series" => "",
            "category" => "",
            "isbn" => "NOT FOUND",
            "ddcNumber" => "",
            "quantity" => "1",
            "synopsis" => "",
        ];
        // Note: is_book is harder to reliably extract with simple regex, assume true for fallback
        if (preg_match('/"title"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["title"] = $matches[1];
        }
        if (preg_match('/"author"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["author"] = $matches[1];
        }
        if (preg_match('/"illustrator"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["illustrator"] = $matches[1];
        }
        if (preg_match('/"publisher"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["publisher"] = $matches[1];
        }
        if (preg_match('/"series"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["series"] = $matches[1];
        }
        if (preg_match('/"category"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["category"] = $matches[1];
        }
        if (preg_match('/"isbn"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["isbn"] = $matches[1];
        }
        if (preg_match('/"ddcNumber"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["ddcNumber"] = $matches[1];
        }
        if (preg_match('/"synopsis"\s*:\s*"([^"]*)"/', $text, $matches)) {
            $data["synopsis"] = $matches[1];
        }

        return !empty($data["title"]) ? $data : null;
    }

    // --- Gemini Specific Methods ---

    /**
     * Helper to get text content from Gemini's raw response
     */
    private function getGeminiContent($response)
    {
        return $response["candidates"][0]["content"]["parts"][0]["text"] ?? "";
    }

    /**
     * Logs the queries generated by the model's search tool (to debugLog).
     */
    private function logGroundingQueries($response)
    {
        $queries =
            $response["candidates"][0]["groundingMetadata"][
                "webSearchQueries"
            ] ?? [];
        if (!empty($queries)) {
            $this->clientLog(" Search Queries Used (Grounding):", $queries);
        } else {
            $this->clientLog(
                " Search Queries Used (Grounding): None generated by the model.",
                "INFO"
            );
        }
    }

    /**
     * Extracts grounding metadata for citations.
     */
    private function extractGroundingAnnotations($response)
    {
        $annotations = [];
        $candidate = $response["candidates"][0] ?? null;

        if (isset($candidate["groundingMetadata"]["groundingChunks"])) {
            $this->clientLog(
                " Found " .
                    count($candidate["groundingMetadata"]["groundingChunks"]) .
                    " grounding chunks/sources."
            );
            foreach (
                $candidate["groundingMetadata"]["groundingChunks"]
                as $chunk
            ) {
                $annotations[] = [
                    "type" => "Google Search",
                    "url" => $chunk["uri"] ?? null,
                    "title" =>
                        $chunk["title"] ??
                        ($chunk["uri"] ?? "Google Search Result"),
                ];
            }
        } else {
            $this->clientLog(" No grounding chunks/sources found in response.");
        }
        return $annotations;
    }

    /**
     * Make API call to Gemini 2.5 Flash
     * @return array The decoded API response.
     */
    private function callGemini(
        $prompt,
        $imageContent = null,
        $tools = null,
        $generationConfig = null
    ) {
        if (empty($this->gemini_api_key)) {
            $this->clientLog(" Gemini API Key Missing", "Cannot call Gemini");
            return ["error" => ["message" => "Gemini API key not configured"]];
        }

        $url =
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" .
            $this->gemini_api_key;
        $contents = [["role" => "user", "parts" => [["text" => $prompt]]]];

        // Add image content (base64 or data URL)
        if ($imageContent && isset($imageContent["image_url"]["url"])) {
            $base64Data = $imageContent["image_url"]["url"];
            $mimeType = "image/jpeg"; // Default fallback

            if (strpos($base64Data, "base64,") !== false) {
                list($header, $base64) = explode("base64,", $base64Data, 2);
                $base64Data = $base64;
                if (preg_match("/data:([^;]+)/", $header, $matches)) {
                    $mimeType = $matches[1];
                }
            } else {
                // Assuming pure base64 data was passed if no prefix found
            }

            // Image should be prepended
            array_unshift($contents[0]["parts"], [
                "inline_data" => [
                    "mime_type" => $mimeType,
                    "data" => $base64Data,
                ],
            ]);
        }

        $payload = ["contents" => $contents];
        // Add 'tools'
        if ($tools) {
            $payload["tools"] = $tools;
            $this->clientLog(" API Payload Tools Added:", $tools);
        }

        // Add 'generationConfig'
        if ($generationConfig) {
            $payload["generationConfig"] = $generationConfig;
            $this->clientLog(
                " API Payload Generation Config Added:",
                $generationConfig
            );
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120,
        ]);
        $this->clientLog(" Sending API Request to Gemini...");
        $this->clientLog(" Config:", [
            "responseMimeType" => "application/json",
            "temperature" => 0.0,
            "tools" => "googleSearch",
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            $this->clientLog(" Gemini cURL Error:", $error);
            return ["error" => ["message" => "Connection error: " . $error]];
        }

        $decoded = json_decode($response, true);
        $this->clientLog("⬇ API Response received.");

        // Log raw response for debugging (only if error)
        if ($response && !$decoded) {
            $this->clientLog(
                "⚠️ Response is not valid JSON:",
                substr($response, 0, 500)
            );
        }

        if (isset($decoded["error"])) {
            $this->clientLog(
                " Gemini API Error:",
                $decoded["error"]["message"] ?? "Unknown error"
            );
            $this->clientLog(
                "Full error details:",
                json_encode($decoded["error"])
            );
            return [
                "error" => [
                    "message" =>
                        $decoded["error"]["message"] ?? "Unknown error",
                ],
            ];
        }

        return $decoded;
    }

    // --- OpenAI Specific Methods (for Fallback) ---

    /**
     * Make API call to OpenAI
     */
    private function callOpenAI($data)
    {
        if (empty($this->openai_api_key)) {
            $this->clientLog(" OpenAI API Key Missing", "Cannot call OpenAI");
            return ["error" => ["message" => "OpenAI API key not configured"]];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->openai_api_key,
            ],
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);
        if ($error) {
            log_message("error", "cURL Error: " . $error);
            $this->clientLog(" OpenAI cURL Connection Error:", $error);
            return ["error" => ["message" => "Connection error: " . $error]];
        }

        $decoded = json_decode($response, true);
        if ($httpCode === 200) {
            $this->clientLog(" OpenAI API Call Success (HTTP 200)");
        } else {
            $errorMsg =
                $decoded["error"]["message"] ?? "HTTP Error " . $httpCode;
            log_message(
                "error",
                "OpenAI API Error (HTTP " . $httpCode . "): " . $errorMsg
            );
            $this->clientLog(
                " OpenAI API Error (HTTP " . $httpCode . "):",
                $errorMsg
            );
        }

        if ($httpCode !== 200) {
            return [
                "error" => ["message" => $errorMsg, "http_code" => $httpCode],
            ];
        }

        return $decoded;
    }

    /**
     * Fallback method using vision-only analysis (when web search in OpenAI fails)
     */
    private function fallbackVisionOnly($imageContent, $title, $author)
    {
        log_message(
            "info",
            " Using secondary OpenAI fallback (vision-only analysis)"
        );
        $this->clientLog(
            " Using secondary OpenAI fallback (vision-only analysis)"
        );

        $fallbackPayload = [
            "model" => "gpt-4o",
            "max_tokens" => 1500,
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "text",
                            "text" =>
                                "Analyze this book cover thoroughly. Extract ALL visible text including: - Title - Author - Illustrator - Publisher - Series name - ISBN - Any other text. Also determine: - Book category/genre (in Indonesian) - Appropriate DDC number - Write a brief synopsis (2-3 sentences in Bahasa Indonesia). Return ONLY valid JSON with NO markdown:" .
                                '{"title": "", "author": "", "illustrator": "", "publisher": "", "series": "", "isbn": "", "ddcNumber": "", "category": "", "quantity": "1", "synopsis": ""}',
                        ],
                        $imageContent,
                    ],
                ],
            ],
        ];
        $this->clientLog(
            " API Call (Fallback Vision) PAYLOAD:",
            $fallbackPayload
        );
        $fallbackResponse = $this->callOpenAI($fallbackPayload);

        if (isset($fallbackResponse["error"])) {
            $this->clientLog(
                " Secondary OpenAI fallback failed:",
                $fallbackResponse["error"]["message"]
            );
            throw new \Exception(
                "Secondary OpenAI analysis also failed: " .
                    $fallbackResponse["error"]["message"]
            );
        }

        $rawJson = $fallbackResponse["choices"][0]["message"]["content"] ?? "";

        $this->clientLog(" RAW API Response (Fallback) CONTENT:", $rawJson);
        $rawJson = trim($rawJson);
        $rawJson = preg_replace("/```json\s*/i", "", $rawJson);
        $rawJson = preg_replace("/\s*```/", "", $rawJson);
        if (preg_match("/\{.*\}/s", $rawJson, $matches)) {
            $rawJson = $matches[0];
        }

        $parsedData = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->clientLog(
                " Fallback JSON parse error:",
                json_last_error_msg()
            );
            $parsedData = $this->extractJsonFallback($rawJson);
            if (!$parsedData) {
                throw new \Exception(
                    "Failed to parse secondary fallback response"
                );
            }
        }

        $parsedData["is_book"] = true;
        $parsedData["quantity"] = $parsedData["quantity"] ?? "1";
        $parsedData["annotations"] = [
            [
                "type" => "Vision Only",
                "url" => null,
                "title" => "GPT-4o Vision",
            ],
        ]; // Mark as Vision only
        $parsedData["sources_count"] = 1;
        $parsedData["debug_logs"] = $this->debugLog;
        $parsedData["model_used"] = "CHATGPT";

        log_message(
            "info",
            " Secondary OpenAI analysis complete (vision only)"
        );
        $this->clientLog(" Secondary OpenAI analysis complete (vision only)");
        return $this->response->setJSON($parsedData);
    }

    /**
     * Fallback method to perform the full OpenAI Vision + Search flow.
     */
    private function fallbackToOpenAI($imageContent)
    {
        $this->clientLog("--- INITIATING OPENAI FALLBACK ---");

        // ============================================================
        // STEP 1 (OpenAI): EXTRACT BASIC INFO FROM VERIFIED BOOK IMAGE (GPT-4o)
        // ============================================================
        log_message(
            "info",
            " OpenAI Step 1: Analyzing book cover details with GPT-4o..."
        );
        $this->clientLog(
            " OpenAI Step 1: Analyzing book cover details with GPT-4o..."
        );

        $visionPrompt =
            'Analyze this image. Is it a book cover? Answer with ONLY "YES" or "NO" at the start. If YES, describe all visible text including: title, author, illustrator, publisher, series name, and any other text you can see.';
        $visionPayload = [
            "model" => "gpt-4o",
            "max_tokens" => 500,
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        ["type" => "text", "text" => $visionPrompt],
                        $imageContent,
                    ],
                ],
            ],
        ];
        $visionResponse = $this->callOpenAI($visionPayload);

        if (isset($visionResponse["error"])) {
            throw new \Exception(
                $visionResponse["error"]["message"] ??
                    "OpenAI API error in vision analysis"
            );
        }

        $basicInfo = $visionResponse["choices"][0]["message"]["content"] ?? "";
        $this->clientLog(
            " OpenAI Basic info extracted (Raw):",
            substr($basicInfo, 0, 500) . "..."
        );

        // Check if the image is a book cover
        if (stripos($basicInfo, "NO") === 0) {
            log_message(
                "info",
                " Image is not a book cover - OpenAI analysis stopped"
            );
            $this->clientLog(
                " Image is not a book cover - OpenAI analysis stopped"
            );
            return $this->response->setJSON([
                "is_book" => false,
                "synopsis" => "Gambar bukan sampul buku (via OpenAI)",
                "message" => "Image is not a book cover. Analysis stopped.",
                "title" => "BUKAN BUKU",
                "author" => "BUKAN BUKU",
                "illustrator" => "BUKAN BUKU",
                "publisher" => "BUKAN BUKU",
                "series" => "BUKAN BUKU",
                "category" => "BUKAN BUKU",
                "isbn" => "NOT FOUND",
                "ddcNumber" => "NOT FOUND",
                "quantity" => "1",
                "annotations" => [],
                "sources_count" => 0,
                "debug_logs" => $this->debugLog,
            ]);
        }

        // Extract title and author for search query (using simple regex)
        $title = "";
        $author = "";
        if (preg_match('/Title:\s*(.+?)(?:\n|$)/i', $basicInfo, $matches)) {
            $title = trim($matches[1]);
        }
        if (preg_match('/Author:\s*(.+?)(?:\n|$)/i', $basicInfo, $matches)) {
            $author = trim($matches[1]);
        }
        if (
            empty($title) &&
            preg_match('/title[:\s]+([^,\n]+)/i', $basicInfo, $matches)
        ) {
            $title = trim($matches[1]);
        }
        if (
            empty($author) &&
            preg_match('/author[:\s]+([^,\n]+)/i', $basicInfo, $matches)
        ) {
            $author = trim($matches[1]);
        }

        $this->clientLog("OpenAI Extracted Primary Info:", [
            "title" => $title,
            "author" => $author,
        ]);

        // ============================================================
        // STEP 2 (OpenAI): SEARCH WEB FOR COMPLETE BOOK INFORMATION (GPT-4o Search Preview)
        // ============================================================
        log_message(
            "info",
            " OpenAI Step 2: Searching web for complete book information..."
        );
        $this->clientLog(
            " OpenAI Step 2: Searching web for complete book information..."
        );

        $jsonPrompt =
            "Search for detailed information about the book from this message \"{$basicInfo}\" . Find:\n" .
            "1. Full title\n2. Author name\n3. Illustrator (if any)\n4. Publisher name\n5. Series name (if part of a series)\n" .
            "6. ISBN number\n7. DDC (Dewey Decimal Classification) number (GENERATE IF NOT FOUND ACCORDING TO DDC RULES)\n" .
            "8. Genre/Category\n9. Brief synopsis\n\n" .
            "Return ONLY a JSON object with these fields (use Indonesian for category and synopsis):\n" .
            "{\n" .
            '  "title": "", "author": "", "illustrator": "", "publisher": "", "series": "",' .
            '  "isbn": "", "ddcNumber": "", "category": "", "synopsis": ""' .
            "}\n\n";

        $searchPayload = [
            "model" => "gpt-4o-search-preview",
            "max_tokens" => 2000,
            "messages" => [["role" => "user", "content" => $jsonPrompt]],
        ];

        $searchResponse = $this->callOpenAI($searchPayload);

        if (isset($searchResponse["error"])) {
            log_message(
                "warning",
                " OpenAI Web search failed, falling back to vision-only mode. Error: " .
                    ($searchResponse["error"]["message"] ?? "Unknown Error")
            );
            $this->clientLog(
                " OpenAI Web search failed. Falling back to vision-only mode.",
                $searchResponse["error"]["message"] ?? "Unknown Error"
            );
            // Internal fallback to vision-only if search fails
            return $this->fallbackVisionOnly($imageContent, $title, $author);
        }

        // Extract response and annotations
        $searchResult =
            $searchResponse["choices"][0]["message"]["content"] ?? "";
        $annotations =
            $searchResponse["choices"][0]["message"]["annotations"] ?? [];

        $this->clientLog(
            "RAW OpenAI Response (Search) CONTENT:",
            $searchResult
        );

        // Clean and parse JSON
        $searchResult = trim($searchResult);
        $searchResult = preg_replace("/```json\s*/i", "", $searchResult);
        $searchResult = preg_replace("/\s*```/", "", $searchResult);
        $searchResult = preg_replace(
            '/[\x00-\x1F\x80-\xFF]/',
            "",
            $searchResult
        );
        if (preg_match("/\{.*\}/s", $searchResult, $matches)) {
            $searchResult = $matches[0];
        }

        $parsedData = json_decode($searchResult, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->clientLog(" JSON parse error:", json_last_error_msg());
            $fallbackData = $this->extractJsonFallback($searchResult);
            if ($fallbackData) {
                $parsedData = $fallbackData;
            } else {
                throw new \Exception(
                    "Failed to parse JSON response in OpenAI fallback"
                );
            }
        }

        // Final Data Assembly for OpenAI Search Success
        $parsedData["is_book"] = true;
        $parsedData["quantity"] = $parsedData["quantity"] ?? "1";
        // Convert OpenAI annotations structure to a consistent format if needed, or just use the raw output
        $parsedData["annotations"] = $annotations;
        $parsedData["sources_count"] = count($annotations);
        $parsedData["debug_logs"] = $this->debugLog;
        $parsedData["model_used"] = "CHATGPT";

        log_message("info", " Book analysis complete with OpenAI web search");
        $this->clientLog("--- FINISHED OPENAI FALLBACK ANALYSIS ---");

        return $this->response->setJSON($parsedData);
    }

    // --- Main Controller Method ---

    /**
     * Analyze book cover image using Gemini 2.5 Flash as primary, with OpenAI GPT-4o as fallback.
     */
    public function analyzeImage()
    {
        $imageContent = null;
        try {
            $method = strtolower($this->request->getMethod());

            log_message(
                "info",
                "--- STARTING SINGLE-CALL IMAGE ANALYSIS (PRIMARY: GEMINI) ---"
            );
            $this->clientLog(
                "--- STARTING SINGLE-CALL IMAGE ANALYSIS (PRIMARY: GEMINI) ---"
            );

            // --- Input Handling ---
            if ($method === "get") {
                $imageUrl = $this->request->getGet("image_url");
                if (empty($imageUrl)) {
                    return $this->response
                        ->setJSON([
                            "error" => "Parameter image_url is required",
                            "debug_logs" => $this->debugLog,
                        ])
                        ->setStatusCode(400);
                }
                $this->clientLog(
                    " Analyzing image from URL (GET)",
                    substr($imageUrl, 0, 100) . "..."
                );
                $imageContent = [
                    "type" => "image_url",
                    "image_url" => ["url" => $imageUrl],
                ];
            } elseif ($method === "post") {
                $json =
                    $this->request->getJSON(true) ?? $this->request->getPost();
                if (empty($json)) {
                    return $this->response
                        ->setJSON([
                            "error" => "Request body is required",
                            "debug_logs" => $this->debugLog,
                        ])
                        ->setStatusCode(400);
                }

                // Handle Base64
                if (isset($json["type"]) && $json["type"] === "base64") {
                    $imageData = $json["image_data"] ?? null;
                    if (empty($imageData)) {
                        return $this->response
                            ->setJSON([
                                "error" => "Parameter image_data is required",
                                "debug_logs" => $this->debugLog,
                            ])
                            ->setStatusCode(400);
                    }
                    $this->clientLog(
                        " Analyzing image from base64 data",
                        "length: " . strlen($imageData)
                    );
                    $url =
                        strpos($imageData, "data:") === 0
                            ? $imageData
                            : "data:image/jpeg;base64,{$imageData}";
                    $imageContent = [
                        "type" => "image_url",
                        "image_url" => ["url" => $url],
                    ];
                } else {
                    // Handle URL (POST JSON)
                    $imageUrl = $json["image_url"] ?? null;
                    if (empty($imageUrl)) {
                        return $this->response
                            ->setJSON([
                                "error" =>
                                    "Parameter image_url or image_data is required",
                                "debug_logs" => $this->debugLog,
                            ])
                            ->setStatusCode(400);
                    }
                    $this->clientLog(
                        " Analyzing image from URL (POST)",
                        substr($imageUrl, 0, 100) . "..."
                    );
                    $imageContent = [
                        "type" => "image_url",
                        "image_url" => ["url" => $imageUrl],
                    ];
                }
            }

            if ($imageContent === null) {
                return $this->response
                    ->setJSON([
                        "error" => "No image data provided",
                        "debug_logs" => $this->debugLog,
                    ])
                    ->setStatusCode(400);
            }
            $this->clientLog(" Image content prepared successfully");

            // ============================================================
            // PRIMARY: GEMINI VISION + SEARCH + JSON in ONE CALL
            // ============================================================
            log_message(
                "info",
                " Step 1: Performing combined Vision + Search analysis with Gemini..."
            );
            $prompt =
                "Analyze this image. If it is NOT a book cover, return ONLY this JSON: {\"is_book\": false, \"synopsis\": \"Gambar bukan sampul buku\", \"title\": \"BUKAN BUKU\"}. 
                If it IS a book cover, extract all visible text. Then, use Google Search to find detailed information about the book. Find:\n" .
                "1. Full title\n2. Author name\n3. Illustrator (if any)\n4. Publisher name\n5. Series name (if part of a series)\n" .
                "6. ISBN number\n7. DDC (Dewey Decimal Classification) number (GENERATE IF NOT FOUND ACCORDING TO DDC RULES)\n" .
                "8. Genre/Category\n9. Brief synopsis\n\n" .
                "Return ONLY a JSON object with the following fields (use Indonesian for category and synopsis, ensure ISBN and DDC are strings):\n" .
                "{\n" .
                '  "is_book": true, "title": "", "author": "", "illustrator": "", "publisher": "", "series": "",' .
                '  "isbn": "", "ddcNumber": "", "category": "", "synopsis": "", "quantity": "1"' .
                "}\n\n";

            // Define Tools (Google Search) - SIMPLIFIED
            $tools = [
                [
                    "googleSearch" => (object) [],
                ],
            ];

            // Define Generation Config
            // NOTE: responseMimeType/responseSchema removed - incompatible with googleSearch grounding.
            //       JSON output is enforced via prompt instructions instead.
            $generationConfig = [
                "temperature" => 0.0,
                "maxOutputTokens" => 2000,
            ];

            // Call Gemini
            $searchResponse = $this->callGemini(
                $prompt,
                $imageContent,
                $tools,
                $generationConfig
            );

            if (isset($searchResponse["error"])) {
                $errorMsg =
                    $searchResponse["error"]["message"] ??
                    "Unknown Gemini API error";
                log_message(
                    "error",
                    " Gemini Primary Call Failed: " . $errorMsg
                );

                // Check if the error is a quota/rate limit error (as requested)
                // Only fall back to OpenAI for quota/rate-limit errors
                if (
                    strpos($errorMsg, "Quota exceeded") !== false ||
                    strpos($errorMsg, "rate-limits") !== false ||
                    strpos($errorMsg, "RESOURCE_EXHAUSTED") !== false
                ) {
                    log_message(
                        "warning",
                        " Gemini Quota/Rate-limit hit. Initiating OpenAI fallback."
                    );
                    $this->clientLog(
                        " Gemini Quota/Rate-limit hit.",
                        "Initiating OpenAI fallback."
                    );
                    return $this->fallbackToOpenAI($imageContent);
                }

                // For all other errors (400 bad request, auth, etc.) throw so the catch block handles it
                $this->clientLog(
                    " Gemini error (not quota-related), will NOT fall back to OpenAI:",
                    $errorMsg
                );
                throw new \Exception("Gemini API error: " . $errorMsg);
            }

            // --- Gemini Success Logic ---
            $searchResult = $this->getGeminiContent($searchResponse);
            $annotations = $this->extractGroundingAnnotations($searchResponse);
            $this->logGroundingQueries($searchResponse);
            $this->clientLog(" RAW Gemini Response CONTENT:", $searchResult);

            // Clean and parse JSON
            $searchResult = trim($searchResult);
            $searchResult = preg_replace("/```json\s*/i", "", $searchResult);
            $searchResult = preg_replace("/\s*```/", "", $searchResult);
            $searchResult = preg_replace(
                '/[\x00-\x1F\x80-\xFF]/',
                "",
                $searchResult
            );
            if (preg_match("/\{.*\}/s", $searchResult, $matches)) {
                $searchResult = $matches[0];
            }

            $parsedData = json_decode($searchResult, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message(
                    "error",
                    " JSON parse error: " . json_last_error_msg()
                );
                $this->clientLog(" JSON parse error:", json_last_error_msg());
                $fallbackData = $this->extractJsonFallback($searchResult);
                if ($fallbackData) {
                    $parsedData = $fallbackData;
                } else {
                    throw new \Exception(
                        "Failed to parse JSON response and fallback failed"
                    );
                }
            }

            // Ensure mandatory fields are present for consistency
            $parsedData["is_book"] = $parsedData["is_book"] ?? true;
            $parsedData["quantity"] = $parsedData["quantity"] ?? "1";

            // Handle NOT A BOOK scenario
            if (
                isset($parsedData["is_book"]) &&
                $parsedData["is_book"] === false
            ) {
                log_message(
                    "info",
                    " Image is not a book cover - analysis stopped"
                );
                $this->clientLog(
                    " Image is not a book cover - analysis stopped"
                );
                $parsedData = [
                    "is_book" => false,
                    "synopsis" =>
                        $parsedData["synopsis"] ?? "Gambar bukan sampul buku",
                    "message" => "Image is not a book cover. Analysis stopped.",
                    "title" => $parsedData["title"] ?? "BUKAN BUKU",
                    "author" => "BUKAN BUKU",
                    "illustrator" => "BUKAN BUKU",
                    "publisher" => "BUKAN BUKU",
                    "series" => "BUKAN BUKU",
                    "category" => "BUKAN BUKU",
                    "isbn" => "NOT FOUND",
                    "ddcNumber" => "NOT FOUND",
                    "quantity" => "1",
                ];
            }

            // Final Data Assembly
            $parsedData["annotations"] = $annotations;
            $parsedData["sources_count"] = count($annotations);
            $parsedData["debug_logs"] = $this->debugLog;
            $parsedData["model_used"] = "GEMINI";

            log_message("info", " Book analysis complete with Gemini");
            $this->clientLog("--- FINISHED IMAGE ANALYSIS ---");

            return $this->response->setJSON($parsedData);
        } catch (\Exception $e) {
            log_message(
                "error",
                "❌ Error in primary Gemini analysis: " . $e->getMessage()
            );
            $this->clientLog(
                "❌ FATAL ERROR in Gemini primary:",
                $e->getMessage()
            );

            // --- SECONDARY FALLBACK (for any uncaught exception) ---
            try {
                log_message(
                    "info",
                    " Attempting secondary fallback to OpenAI due to uncaught exception."
                );
                $this->clientLog(" Attempting secondary fallback to OpenAI.");
                return $this->fallbackToOpenAI($imageContent);
            } catch (\Exception $e2) {
                // If OpenAI also fails, return the 500 error
                log_message(
                    "error",
                    " OpenAI fallback also failed: " . $e2->getMessage()
                );
                log_message(
                    "info",
                    "--- FINISHED IMAGE ANALYSIS WITH DOUBLE ERROR ---"
                );
                $this->clientLog(
                    " FATAL ERROR in OpenAI fallback:",
                    $e2->getMessage()
                );

                return $this->response
                    ->setJSON([
                        "error" => $e2->getMessage(),
                        "details" => "Both Gemini and OpenAI analysis failed",
                        "debug_logs" => $this->debugLog,
                    ])
                    ->setStatusCode(500);
            }
        }
    }

    /**
     * Generic Supabase API Request Handler
     */
    private function supabaseRequest(
        $method,
        $endpoint,
        $data = null,
        $queryParams = [],
        $cacheKey = null,
        $cacheTTL = 300
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

        // Cache successful GET responses
        if ($cacheKey && $method === "GET" && !isset($decoded["error"])) {
            $this->cache->save($cacheKey, $decoded, $cacheTTL);
            log_message("info", "Cache SET for: " . $cacheKey);
        }

        return $decoded;
    }
}
