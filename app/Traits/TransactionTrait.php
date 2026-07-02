<?php

namespace App\Traits;

trait TransactionTrait
{
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
            "type" => "eq.borrow",
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
        log_message("info", "Calculated trust score for user {$userId}: " . $trustScore);
        return $trustScore;
    }
}