<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CacheHeaders implements FilterInterface
{
    /**
     * Asset version hash for cache busting
     * Change this when deploying new versions of assets
     */
    private string $assetVersion = 'v1.0.0';

    public function before(RequestInterface $request, $arguments = null)
    {
        // Do nothing
    }

    /**
     * Implement efficient cache lifetimes for LCP/FCP optimization
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $path = $request->getPath();
        $contentType = $response->getHeaderLine('Content-Type');

        // ========== CRITICAL RENDERING PATH - Aggressive Caching ==========
        
        // Fonts (WOFF2) - Cache for 1 year, these rarely change
        if (preg_match('/\.woff2$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable'); // 1 year
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
            $response->setHeader('X-Content-Type-Options', 'nosniff');
        }
        // Web fonts (TTF, WOFF, EOT)
        elseif (preg_match('/\.(woff|ttf|eot)$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=15552000, immutable'); // 180 days
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 15552000));
        }
        // Images - Aggressive caching with versions
        elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path)) {
            // Check if image is versioned (contains hash/version)
            if (preg_match('/[._-]v?\d+(\.\d+)*[._-]|[a-f0-9]{8,}|\?v=/i', $path)) {
                // Versioned images - can cache forever
                $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable'); // 1 year
            } else {
                // Unversioned images - shorter cache to allow updates
                $response->setHeader('Cache-Control', 'public, max-age=2592000, must-revalidate'); // 30 days
            }
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
            $this->setETag($response);
        }
        // Critical CSS - Cache for 60 days (above-the-fold styles)
        elseif (preg_match('/(bootstrap|style|welcome-page)\.css$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=5184000, immutable'); // 60 days
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 5184000));
            $this->setETag($response);
        }
        // Non-critical CSS - Cache for 30 days
        elseif (preg_match('/\.css$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=2592000, immutable'); // 30 days
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
            $this->setETag($response);
        }
        // Critical JavaScript (Bootstrap) - Cache for 60 days
        elseif (preg_match('/(bootstrap\.bundle|jquery-3)\..*\.js$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=5184000, immutable'); // 60 days
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 5184000));
            $this->setETag($response);
        }
        // Other JavaScript - Cache for 30 days
        elseif (preg_match('/\.js$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=2592000, immutable'); // 30 days
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
            $this->setETag($response);
        }
        // HTML pages - Short cache with strong validation for freshness
        elseif (preg_match('/^text\/html/i', $contentType) || preg_match('/\.(html)$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=300, must-revalidate'); // 5 minutes
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
            $this->setETag($response);
            $this->setLastModified($response);
        }
        // API responses - Cache with revalidation
        elseif (preg_match('/^application\/(json|xml)/i', $contentType) || preg_match('/\/api\//i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=300, must-revalidate'); // 5 minutes
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
            $this->setETag($response);
        }
        // Default - Conservative caching with validation
        else {
            $response->setHeader('Cache-Control', 'public, max-age=3600, must-revalidate'); // 1 hour
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
            $this->setETag($response);
        }

        // ========== SECURITY HEADERS ==========
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Set ETag header for conditional requests
     */
    private function setETag(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if (!empty($body)) {
            $etag = '"' . md5($body) . '"';
            $response->setHeader('ETag', $etag);
        }
    }

    /**
     * Set Last-Modified header for conditional requests
     */
    private function setLastModified(ResponseInterface $response): void
    {
        $lastModified = gmdate('D, d M Y H:i:s \G\M\T', time());
        $response->setHeader('Last-Modified', $lastModified);
    }
}

