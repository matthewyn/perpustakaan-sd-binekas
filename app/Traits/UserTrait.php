<?php

namespace App\Traits;

trait UserTrait
{
    private function normalizeUserQueryParams(array $queryParams): array
    {
        if (!isset($queryParams["is_active"])) {
            $queryParams["is_active"] = "eq.true";
        }

        if (isset($queryParams["select"])) {
            $queryParams["select"] = str_replace("maxBorrow", "max_borrow", $queryParams["select"]);
            $queryParams["select"] = trim($queryParams["select"], ",");
        }

        return $queryParams;
    }

    private function normalizeUserRow(array $user): array
    {
        if (isset($user["max_borrow"]) && !isset($user["maxBorrow"])) {
            $user["maxBorrow"] = $user["max_borrow"];
        }

        $user["uid"] = $user["uid"] ?? null;
        return $user;
    }

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
            $params = array_merge($this->normalizeUserQueryParams($queryParams), [
                "limit" => $limit,
                "offset" => $offset,
            ]);

            $users = $this->supabaseRequest("GET", "users_view", null, $params);

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
                $allUsers = array_merge(
                    $allUsers,
                    array_map(fn($user) => is_array($user) ? $this->normalizeUserRow($user) : $user, $users)
                );
                $offset += $limit;

                if ($count < $limit) {
                    $hasMore = false;
                }
            } else {
                $hasMore = false;
            }
        }

        log_message("info", "Total users fetched: " . count($allUsers));

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

    private function createNormalizedUser(array $userData, ?array $studentData = null, ?array $teacherData = null)
    {
        $result = $this->supabaseRequest("POST", "users", $userData);
        if (isset($result["error"]) || empty($result[0]["id"])) {
            return $result;
        }

        $userId = (int) $result[0]["id"];
        if (($userData["role"] ?? null) === "murid" && $studentData !== null) {
            $studentData["user_id"] = $userId;
            $studentResult = $this->supabaseRequest("POST", "students", $studentData);
            if (isset($studentResult["error"])) {
                return $studentResult;
            }
        } elseif (in_array($userData["role"] ?? null, ["guru", "admin"], true) && $teacherData !== null) {
            $teacherData["user_id"] = $userId;
            $teacherResult = $this->supabaseRequest("POST", "teachers", $teacherData);
            if (isset($teacherResult["error"])) {
                return $teacherResult;
            }
        }

        return $result;
    }

    private function updateNormalizedStudent(int $userId, array $userData, array $studentData)
    {
        $result = $this->supabaseRequest("PATCH", "users?id=eq." . $userId, $userData);
        if (isset($result["error"])) {
            return $result;
        }

        $existing = $this->supabaseRequest("GET", "students", null, [
            "user_id" => "eq." . $userId,
            "select" => "user_id",
            "limit" => 1,
        ]);

        if (!isset($existing["error"]) && !empty($existing)) {
            return $this->supabaseRequest("PATCH", "students?user_id=eq." . $userId, $studentData);
        }

        $studentData["user_id"] = $userId;
        return $this->supabaseRequest("POST", "students", $studentData);
    }

    private function updateNormalizedTeacher(int $userId, array $userData, array $teacherData)
    {
        $result = $this->supabaseRequest("PATCH", "users?id=eq." . $userId, $userData);
        if (isset($result["error"])) {
            return $result;
        }

        $existing = $this->supabaseRequest("GET", "teachers", null, [
            "user_id" => "eq." . $userId,
            "select" => "user_id",
            "limit" => 1,
        ]);

        if (!isset($existing["error"]) && !empty($existing)) {
            return $this->supabaseRequest("PATCH", "teachers?user_id=eq." . $userId, $teacherData);
        }

        $teacherData["user_id"] = $userId;
        return $this->supabaseRequest("POST", "teachers", $teacherData);
    }
}
