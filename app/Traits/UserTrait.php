<?php

namespace App\Traits;

trait UserTrait
{
    /**
     * Fetch all users with pagination
     */
    private function fetchAllUsers($queryParams = [])
    {
        // Try cache first
        $cacheKey = "all_users_class_" . md5(json_encode($queryParams));
        $cachedUsers = $this->cache->get($cacheKey);

        if ($cachedUsers !== null) {
            log_message(
                "info",
                "Users fetched from cache: " . count($cachedUsers) . " users"
            );
            return $cachedUsers;
        }

        $allUsers = [];
        $limit = 1000;
        $offset = 0;
        $hasMore = true;

        log_message("info", "Starting fetchAllUsers with pagination");

        while ($hasMore) {
            $params = array_merge($queryParams, [
                "limit" => $limit,
                "offset" => $offset,
            ]);

            $users = $this->supabaseRequest("GET", "users", null, $params);

            if (isset($users["error"]) || !is_array($users)) {
                log_message(
                    "error",
                    "Error fetching users at offset " . $offset
                );
                break;
            }

            $count = count($users);
            log_message("info", "Fetched {$count} users at offset {$offset}");

            if ($count > 0) {
                $allUsers = array_merge($allUsers, $users);
                $offset += $limit;

                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message("info", "Total users fetched: " . count($allUsers));

        // Cache for 5 minutes
        $this->cache->save($cacheKey, $allUsers, 24 * 60 * 60); // Cache for 24 hours

        return $allUsers;
    }

    private function invalidateUserCache($queryParams = []): void
    {
        if (empty($queryParams)) {
            // No params = nuke all user cache keys
            // CodeIgniter's file cache doesn't support pattern deletion,
            // so we delete the known common keys or use cache()->clean()
            $this->cache->clean();
            log_message('info', 'Users cache cleared (all)');
            return;
        }

        $cacheKey = 'all_users_class_' . md5(json_encode($queryParams));
        $this->cache->delete($cacheKey);
        log_message('info', 'Users cache invalidated for key: ' . $cacheKey);
    }
}