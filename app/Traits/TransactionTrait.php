<?php

namespace App\Traits;

trait TransactionTrait
{
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

    /**
     * Fetch all transactions with pagination
     */
    private function fetchAllTransactions($queryParams = [])
    {
        $allTransactions = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message('info', 'Starting fetchAllTransactions with pagination');

        while ($hasMore) {
            $params = array_merge($queryParams, [
                'limit' => $limit,
                'offset' => $offset
            ]);

            $transactions = $this->supabaseRequest('GET', 'transactions', null, $params);

            if (isset($transactions['error']) || !is_array($transactions)) {
                log_message('error', 'Error fetching transactions at offset ' . $offset);
                break;
            }

            $count = count($transactions);
            log_message('info', "Fetched {$count} transactions at offset {$offset}");

            if ($count > 0) {
                $allTransactions = array_merge($allTransactions, $transactions);
                $offset += $limit;
                
                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message('info', 'Total transactions fetched: ' . count($allTransactions));
        return $allTransactions;
    }

    private function calculateTrustScore($userId)
    {
        $maxCompletedTransactions = 60;
        $completedTransactions = $this->fetchAllTransactions([
            "user_id" => "eq." . $userId,
            "type" => "eq.return",
            "status" => "eq.completed",
            "is_finished_semester" => "is.false",
            "select" => "completed_at,due_date,book_condition",
        ]);

        $lateTransactions = array_filter($completedTransactions, function ($trx) {
            return strtotime($trx['completed_at']) > strtotime($trx['due_date']);
        });

        $onTimeTransactions = array_filter($completedTransactions, function ($trx) {
            return strtotime($trx['completed_at']) <= strtotime($trx['due_date']);
        });

        $damagedBookTransactions = array_filter($completedTransactions, function ($trx) {
            return isset($trx['book_condition']) && in_array(strtolower($trx['book_condition']), ['rusak', 'hilang']);
        });

        $totalCompleted = count($completedTransactions);
        $totalLate = count($lateTransactions);
        $totalOnTime = count($onTimeTransactions);
        $totalDamaged = count($damagedBookTransactions);

        $L = 0 + 200 * (min($totalCompleted, $maxCompletedTransactions) / $maxCompletedTransactions);

        $f1 = 1 - $totalLate / $totalCompleted;
        $f2 = $totalOnTime / $totalCompleted;
        $f3 = 1 - $totalDamaged / $totalCompleted;

        $trustScore = $L * (0.45 * $f1 + 0.35 * $f2 + 0.2 * $f3);
        log_message("info", "Trust score components for user {$userId}: L={$L}, f1={$f1}, f2={$f2}, f3={$f3}");
        log_message("info", "Calculated trust score for user {$userId}: " . $trustScore);
        return $trustScore;
    }
}