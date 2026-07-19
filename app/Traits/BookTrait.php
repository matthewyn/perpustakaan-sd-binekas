<?php

namespace App\Traits;

trait BookTrait
{
    private function normalizeBookRow(array $book): array
    {
        foreach (["authors" => "author", "illustrators" => "illustrator", "genres" => "genre"] as $source => $target) {
            if (isset($book[$source]) && is_array($book[$source])) {
                $book[$target] = implode(", ", array_filter($book[$source]));
            }
        }

        $book["author"] = $book["author"] ?? "";
        $book["illustrator"] = $book["illustrator"] ?? "";
        $book["genre"] = $book["genre"] ?? "";
        $book["publisher"] = $book["publisher"] ?? "";
        $book["series"] = $book["series"] ?? "";
        $book["uid"] = $book["uid"] ?? [];

        return $book;
    }

    private function normalizeBookRows(array $books): array
    {
        return array_map(fn($book) => is_array($book) ? $this->normalizeBookRow($book) : $book, $books);
    }

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
            $books = $this->supabaseRequest("GET", "books_view", null, $params);

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

            $allBooks = array_merge($allBooks, $this->normalizeBookRows($books));
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
            $raw = $this->supabaseRequest("GET", "genres", null, [
                "select" => "name",
                "order" => "name.asc",
            ]);

            if (!is_array($raw) || isset($raw["error"])) {
                $raw = $this->supabaseRequest("GET", "distinct_genres");
                if (!is_array($raw) || isset($raw["error"])) {
                    return is_array($genres) ? $genres : [];
                }
            }

            $freshGenres = array_values(
                array_unique(array_filter(array_column($raw, isset($raw[0]["name"]) ? "name" : "genre")))
            );
            sort($freshGenres);

            if (!empty($freshGenres)) {
                $genres = $freshGenres;
                $this->cache->save($cacheKey, $genres, 24 * 60 * 60);
            }
        }

        return is_array($genres) ? $genres : [];
    }

    private function splitBookNames($value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[,;]+/', (string) $value);
        }

        return array_values(array_unique(array_filter(array_map("trim", $parts ?? []))));
    }

    private function getBookCopyUidsFromSource(array $source): array
    {
        return $this->splitBookNames($source["uid"] ?? ($source["rfid_uid"] ?? ""));
    }

    private function getOrCreateNameId(string $table, string $name): ?int
    {
        $name = trim($name);
        if ($name === "") {
            return null;
        }

        $existing = $this->supabaseRequest("GET", $table, null, [
            "name" => "eq." . $name,
            "select" => "id",
            "limit" => 1,
        ]);

        if (!isset($existing["error"]) && !empty($existing[0]["id"])) {
            return (int) $existing[0]["id"];
        }

        $created = $this->supabaseRequest("POST", $table, ["name" => $name]);
        if (isset($created["error"]) || empty($created[0]["id"])) {
            return null;
        }

        return (int) $created[0]["id"];
    }

    private function buildBookBaseData(array $source): array
    {
        $data = [
            "code" => trim($source["code"] ?? ($source["kode_sekolah"] ?? "")),
            "title" => trim($source["title"] ?? ($source["judul"] ?? "")),
            "year" => (int) ($source["year"] ?? date("Y")),
            "isbn" => $source["isbn"] ?? "",
            "ddc_number" => $source["ddc_number"] ?? ($source["ddcNumber"] ?? ""),
            "image" => $source["image"] ?? ($source["gambar"] ?? ""),
            "synopsis" => $source["synopsis"] ?? ($source["sinopsis"] ?? ""),
            "notes" => $source["notes"] ?? "",
            "shelf_position" => $source["shelf_position"] ?? ($source["shelfPosition"] ?? ""),
            "quantity" => max(1, (int) ($source["quantity"] ?? 1), count($this->getBookCopyUidsFromSource($source))),
            "is_one_day_book" => (bool) ($source["is_one_day_book"] ?? ($source["isOneDayBook"] ?? false)),
            "is_in_class" => (bool) ($source["is_in_class"] ?? ($source["isInClass"] ?? false)),
        ];

        $publisherId = $this->getOrCreateNameId("publishers", $source["publisher"] ?? "");
        $seriesId = $this->getOrCreateNameId("book_series", $source["series"] ?? "");

        if ($publisherId !== null) {
            $data["publisher_id"] = $publisherId;
        }

        if ($seriesId !== null) {
            $data["series_id"] = $seriesId;
        }

        return $data;
    }

    private function syncBookPeople(int $bookId, $names, string $role): void
    {
        $this->supabaseRequest("DELETE", "book_authors?book_id=eq." . $bookId . "&role=eq." . $role);

        foreach ($this->splitBookNames($names) as $name) {
            $authorId = $this->getOrCreateNameId("authors", $name);
            if ($authorId === null) {
                continue;
            }

            $this->supabaseRequest("POST", "book_authors", [
                "book_id" => $bookId,
                "author_id" => $authorId,
                "role" => $role,
            ]);
        }
    }

    private function syncBookGenres(int $bookId, $genres): void
    {
        $this->supabaseRequest("DELETE", "book_genres?book_id=eq." . $bookId);

        foreach ($this->splitBookNames($genres) as $name) {
            $genreId = $this->getOrCreateNameId("genres", $name);
            if ($genreId === null) {
                continue;
            }

            $this->supabaseRequest("POST", "book_genres", [
                "book_id" => $bookId,
                "genre_id" => $genreId,
            ]);
        }
    }

    private function syncBookMetadata(int $bookId, array $source): void
    {
        $this->syncBookPeople($bookId, $source["author"] ?? ($source["pengarang"] ?? ""), "penulis");
        $this->syncBookPeople($bookId, $source["illustrator"] ?? "", "ilustrator");
        $this->syncBookGenres($bookId, $source["genre"] ?? ($source["kategori"] ?? ""));
    }

    private function syncBookCopies(int $bookId, array $source)
    {
        if (!array_key_exists("uid", $source) && !array_key_exists("rfid_uid", $source)) {
            return null;
        }

        $uids = $this->getBookCopyUidsFromSource($source);
        $existing = $this->supabaseRequest("GET", "book_copies", null, [
            "book_id" => "eq." . $bookId,
            "select" => "id,uid",
        ]);

        if (isset($existing["error"])) {
            return $existing;
        }

        $existingByUid = [];
        foreach ($existing as $copy) {
            $existingByUid[$copy["uid"]] = $copy;
        }

        foreach ($existing as $copy) {
            if (!in_array($copy["uid"], $uids, true)) {
                $result = $this->supabaseRequest("PATCH", "book_copies?id=eq." . $copy["id"], [
                    "is_active" => false,
                    "updated_at" => date("Y-m-d H:i:s"),
                ]);
                if (isset($result["error"])) {
                    return $result;
                }
            }
        }

        foreach ($uids as $uid) {
            if (isset($existingByUid[$uid])) {
                $result = $this->supabaseRequest("PATCH", "book_copies?id=eq." . $existingByUid[$uid]["id"], [
                    "is_active" => true,
                    "updated_at" => date("Y-m-d H:i:s"),
                ]);
            } else {
                $result = $this->supabaseRequest("POST", "book_copies", [
                    "book_id" => $bookId,
                    "uid" => $uid,
                    "is_active" => true,
                ]);
            }

            if (isset($result["error"])) {
                return $result;
            }
        }

        return null;
    }

    private function createNormalizedBook(array $source)
    {
        $created = $this->supabaseRequest("POST", "books", $this->buildBookBaseData($source));
        if (isset($created["error"]) || empty($created[0]["id"])) {
            return $created;
        }

        $this->syncBookMetadata((int) $created[0]["id"], $source);
        $copyResult = $this->syncBookCopies((int) $created[0]["id"], $source);
        if (is_array($copyResult) && isset($copyResult["error"])) {
            return $copyResult;
        }
        return $created;
    }

    private function updateNormalizedBook(int $bookId, array $source)
    {
        $updated = $this->supabaseRequest("PATCH", "books?id=eq." . $bookId, $this->buildBookBaseData($source));
        if (isset($updated["error"])) {
            return $updated;
        }

        $this->syncBookMetadata($bookId, $source);
        $copyResult = $this->syncBookCopies($bookId, $source);
        if (is_array($copyResult) && isset($copyResult["error"])) {
            return $copyResult;
        }
        return $updated;
    }
}
