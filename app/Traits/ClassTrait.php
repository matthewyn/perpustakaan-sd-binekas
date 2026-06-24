<?php

namespace App\Traits;

trait ClassTrait
{
    /**
     * Fetch all classes with pagination
     */
    private function fetchAllClasses($queryParams = [])
    {
        // Try cache first
        $cacheKey = "all_classes_" . md5(json_encode($queryParams));
        $cachedClasses = $this->cache->get($cacheKey);

        if ($cachedClasses !== null) {
            log_message(
                "info",
                "Classes fetched from cache: " .
                    count($cachedClasses) .
                    " classes"
            );
            return $cachedClasses;
        }

        $allClasses = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message("info", "Starting fetchAllClasses with pagination");

        while ($hasMore) {
            $params = array_merge($queryParams, [
                "limit" => $limit,
                "offset" => $offset,
            ]);

            $classes = $this->supabaseRequest("GET", "classes", null, $params);

            if (isset($classes["error"]) || !is_array($classes)) {
                log_message(
                    "error",
                    "Error fetching classes at offset " . $offset
                );
                break;
            }

            $count = count($classes);
            log_message("info", "Fetched {$count} classes at offset {$offset}");

            if ($count > 0) {
                $allClasses = array_merge($allClasses, $classes);
                $offset += $limit;

                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message("info", "Total classes fetched: " . count($allClasses));

        // Cache for 24 hours
        $this->cache->save($cacheKey, $allClasses, 24 * 60 * 60);

        return $allClasses;
    }
}