<?php

namespace App\Libraries;

use Config\Cache;

class GenreCache
{
    private $cache;
    private $cacheName = 'book_genres';
    private $cacheTimeout = 3600;

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    public function getGenres($refreshCallback)
    {
        $cached = $this->cache->get($this->cacheName);
        
        if ($cached !== null) {
            return $cached;
        }

        $genres = call_user_func($refreshCallback);
        
        if (!empty($genres)) {
            $this->cache->save($this->cacheName, $genres, $this->cacheTimeout);
        }

        return $genres;
    }

    public function invalidate()
    {
        $this->cache->delete($this->cacheName);
    }

    public function setTimeout($timeout)
    {
        $this->cacheTimeout = $timeout;
        return $this;
    }
}
