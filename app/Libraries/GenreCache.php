<?php

namespace App\Libraries;

use Config\Cache;

class GenreCache
{
    private $cache;
    private $cacheName = 'book_genres';
    private $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get genres from cache or fetch if expired
     */
    public function getGenres($refreshCallback)
    {
        $cached = $this->cache->get($this->cacheName);
        
        if ($cached !== null) {
            return $cached;
        }

        // Cache miss - call refresh function
        $genres = call_user_func($refreshCallback);
        
        if (!empty($genres)) {
            $this->cache->save($this->cacheName, $genres, $this->cacheTimeout);
        }

        return $genres;
    }

    /**
     * Invalidate genre cache
     */
    public function invalidate()
    {
        $this->cache->delete($this->cacheName);
    }

    /**
     * Set cache timeout
     */
    public function setTimeout($timeout)
    {
        $this->cacheTimeout = $timeout;
        return $this;
    }
}
