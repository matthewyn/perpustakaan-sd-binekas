<?php
/**
 * COMPREHENSIVE TEST SCRIPT - PEMINJAMAN OTOMATIS RFID
 * Jalankan: php test_automate.php
 */

class RFIDTester {
    private $baseUrl = 'http://localhost:8080';
    private $endpoint = '/automateTransaction';
    private $results = [];
    private $passCount = 0;
    private $failCount = 0;

    public function test($scenarioName, $userUid, $bookUid, $expectedSuccess, $expectedMessage = null) {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "TEST: $scenarioName\n";
        echo "POST Data: user_uid=$userUid, uid=$bookUid\n";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'user_uid' => $userUid,
                'uid' => $bookUid,
                'csrf_token' => '' // Disabled for testing
            ]),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "❌ CURL Error: $error\n";
            $this->failCount++;
            return false;
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            echo "❌ Invalid JSON Response: $response\n";
            $this->failCount++;
            return false;
        }

        $actualSuccess = $data['success'] ?? false;
        $actualMessage = $data['message'] ?? '';
        
        echo "Response Code: $httpCode\n";
        echo "Success: " . ($actualSuccess ? 'true' : 'false') . "\n";
        echo "Message: $actualMessage\n";

        // Check hasil
        $passed = ($actualSuccess === $expectedSuccess);
        if ($expectedMessage && !strpos($actualMessage, $expectedMessage)) {
            $passed = false;
        }

        if ($passed) {
            echo "✅ PASS\n";
            $this->passCount++;
        } else {
            echo "❌ FAIL - Expected: success=$expectedSuccess";
            if ($expectedMessage) echo ", message contains: $expectedMessage";
            echo "\n";
            $this->failCount++;
        }

        return $passed;
    }

    public function runAllTests() {
        echo "\n\n";
        echo str_repeat("#", 80) . "\n";
        echo "# AUTOMATED TEST SUITE - PEMINJAMAN OTOMATIS RFID\n";
        echo "# Perpustakaan SD Binekas\n";
        echo str_repeat("#", 80) . "\n";

        // ==================== 1. HAPPY PATH ====================
        echo "\n\n### 1. HAPPY PATH SCENARIOS ###\n";

        $this->test(
            "1.1 Peminjaman Buku Normal (User 123, Buku B100)",
            "USER001",
            "B100",
            true,
            "Peminjaman berhasil"
        );

        $this->test(
            "1.2 Pengembalian Tepat Waktu (User 123, Buku B100)",
            "USER001",
            "B100",
            true,
            "Pengembalian berhasil"
        );

        // ==================== 2. ERROR - EMPTY INPUT ====================
        echo "\n\n### 2. ERROR SCENARIOS - INPUT VALIDATION ###\n";

        $this->test(
            "2.1 User UID Kosong",
            "",
            "B100",
            false,
            "UID user wajib diisi"
        );

        $this->test(
            "2.2 Buku UID Kosong",
            "USER001",
            "",
            false,
            "UID buku wajib diisi"
        );

        // ==================== 3. USER NOT FOUND ====================
        echo "\n\n### 3. UID NOT FOUND SCENARIOS ###\n";

        $this->test(
            "3.1 User UID Invalid (INVALID_USER_XXX)",
            "INVALID_USER_XXX",
            "B100",
            false,
            "UID user tidak ditemukan"
        );

        $this->test(
            "3.2 Buku UID Invalid (INVALID_BOOK_XXX)",
            "USER001",
            "INVALID_BOOK_XXX",
            false,
            "UID buku tidak ditemukan"
        );

        // ==================== 4. BUSINESS LOGIC ====================
        echo "\n\n### 4. BUSINESS LOGIC SCENARIOS ###\n";

        $this->test(
            "4.1 Buku Habis (User coba pinjam buku qty=0)",
            "USER001",
            "B_EMPTY",
            false,
            "Stok buku habis"
        );

        // Scenario: User dengan maxBorrow=1 pinjam 2 buku
        $this->test(
            "4.2a Peminjaman Pertama (maxBorrow=1)",
            "USER001",
            "B101",
            true,
            "Peminjaman berhasil"
        );

        $this->test(
            "4.2b Peminjaman Kedua (Exceed maxBorrow limit)",
            "USER001",
            "B102",
            false,
            "Batas maksimal peminjaman"
        );

        // ==================== 5. CRITICAL BUG FIX ====================
        echo "\n\n### 5. CRITICAL BUG FIX - OWNERSHIP VALIDATION ###\n";

        // Skenario: User123 pinjam B200, User312 coba pinjam B200 sama
        $this->test(
            "5.1 User 123 Pinjam B200",
            "USER001",
            "B200",
            true,
            "Peminjaman berhasil"
        );

        $this->test(
            "5.2 User 312 Coba Pinjam B200 (Sudah dipinjam USER001)",
            "USER002",
            "B200",
            false,
            "Buku sedang dipinjam oleh"
        );

        $this->test(
            "5.3 User 312 Coba Return B200 (Bukan miliknya)",
            "USER002",
            "B200",
            false,
            "tidak ditemukan"
        );

        // ==================== 6. CASE INSENSITIVITY ====================
        echo "\n\n### 6. CASE INSENSITIVITY & WHITESPACE ###\n";

        $this->test(
            "6.1 Scan Buku B301 (lowercase: b301)",
            "USER001",
            "b301",
            true,
            "Peminjaman berhasil"
        );

        $this->test(
            "6.2 Scan Dengan Extra Whitespace (  B301  )",
            "USER001",
            "  b301  ",
            true,
            "Pengembalian berhasil"
        );

        // ==================== 7. ONE DAY BOOK ====================
        echo "\n\n### 7. ONE DAY BOOK SCENARIOS ###\n";

        $this->test(
            "7.1 Peminjaman One-Day-Book",
            "USER001",
            "B_ONEDAY",
            true,
            "Peminjaman berhasil"
        );

        // ==================== 8. TRUST SCORE ====================
        echo "\n\n### 8. TRUST SCORE MECHANICS ###\n";

        $this->test(
            "8.1 Pengembalian Tepat Waktu (Trust score +1)",
            "USER001",
            "B_ONEDAY",
            true,
            "TEPAT WAKTU"
        );

        // SUMMARY
        echo "\n\n" . str_repeat("=", 80) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 80) . "\n";
        echo "✅ PASSED: {$this->passCount}\n";
        echo "❌ FAILED: {$this->failCount}\n";
        echo "📊 TOTAL:  " . ($this->passCount + $this->failCount) . "\n";
        echo str_repeat("=", 80) . "\n";

        if ($this->failCount === 0) {
            echo "\n🎉 ALL TESTS PASSED!\n\n";
        } else {
            echo "\n⚠️  SOME TESTS FAILED. Please review above.\n\n";
        }
    }
}

// Run tests
$tester = new RFIDTester();
$tester->runAllTests();
?>
