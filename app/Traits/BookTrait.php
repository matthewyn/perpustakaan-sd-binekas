<?php

namespace App\Traits;

trait BookTrait
{
    /**
     * Fetch all books with pagination
     */
    private function fetchAllBooks($queryParams = [])
    {
        // Try cache first
        $cacheKey = 'all_books_class_' . md5(json_encode($queryParams));
        $cachedBooks = $this->cache->get($cacheKey);

        if ($cachedBooks !== null) {
            log_message('info', 'Books fetched from cache: ' . count($cachedBooks) . ' books');
            return $cachedBooks;
        }

        $allBooks = [];
        $limit = 1000;
        $offset = 0;

        log_message("info", "Starting fetchAllBooks with pagination");

        while (true) {
            $params = array_merge($queryParams, [
                "limit" => $limit,
                "offset" => $offset,
            ]);
            $books = $this->supabaseRequest("GET", "books", null, $params);

            if (isset($books["error"]) || !is_array($books)) {
                log_message(
                    "error",
                    "Error fetching books at offset " . $offset
                );
                break;
            }

            $count = count($books);
            log_message("info", "Fetched {$count} books at offset {$offset}");

            if ($count === 0) {
                break;
            }

            $allBooks = array_merge($allBooks, $books);
            $offset += $limit;

            if ($count < $limit) {
                break;
            }
        }

        $this->cache->save($cacheKey, $allBooks, 24 * 60 * 60); // Cache for 24 hours

        log_message("info", "Total books fetched: " . count($allBooks));
        return $allBooks;
    }

    private function invalidateBooksCache($queryParams = []): void
    {
        if (empty($queryParams)) {
            // No params = nuke all book cache keys
            // CodeIgniter's file cache doesn't support pattern deletion,
            // so we delete the known common keys or use cache()->clean()
            $this->cache->clean();
            log_message('info', 'Books cache cleared (all)');
            return;
        }

        $cacheKey = 'all_books_class_' . md5(json_encode($queryParams));
        $this->cache->delete($cacheKey);
        log_message('info', 'Books cache invalidated for key: ' . $cacheKey);
    }

    /**
     * Get the full genre list, cached for 24 h.
     */
    private function getAllGenres(): array
    {
        $cacheKey = "all_genres_v1";
        $genres = $this->cache->get($cacheKey);

        if (!is_array($genres) || empty($genres)) {
            $raw = $this->supabaseRequest("GET", "distinct_genres");

            if (!is_array($raw) || isset($raw["error"])) {
                return is_array($genres) ? $genres : [];
            }

            $freshGenres = array_values(
                array_unique(array_filter(array_column($raw, "genre")))
            );
            sort($freshGenres);

            if (!empty($freshGenres)) {
                $genres = $freshGenres;
                $this->cache->save($cacheKey, $genres, 24 * 60 * 60);
            }
        }

        return is_array($genres) ? $genres : [];
    }
}
