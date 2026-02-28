<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CacheHeaders implements FilterInterface
{
    private string $assetVersion = 'v1.0.0';

    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $path = $request->getPath();
        $contentType = $response->getHeaderLine('Content-Type');

        if (preg_match('/\.woff2$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
            $response->setHeader('X-Content-Type-Options', 'nosniff');
        }
        elseif (preg_match('/\.(woff|ttf|eot)$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=15552000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 15552000));
        }
        elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path)) {
            if (preg_match('/[._-]v?\d+(\.\d+)*[._-]|[a-f0-9]{8,}|\?v=/i', $path)) {
                $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable');
            } else {
                $response->setHeader('Cache-Control', 'public, max-age=2592000, must-revalidate');
            }
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
            $this->setETag($response);
        }
        elseif (preg_match('/(bootstrap|style|home)\.css$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=5184000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 5184000));
            $this->setETag($response);
        }
        elseif (preg_match('/\.css$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=2592000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
            $this->setETag($response);
        }
        elseif (preg_match('/(bootstrap\.bundle|jquery-3)\..*\.js$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=5184000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 5184000));
            $this->setETag($response);
        }
        elseif (preg_match('/\.js$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=2592000, immutable');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
            $this->setETag($response);
        }
        elseif (preg_match('/^text\/html/i', $contentType) || preg_match('/\.(html)$/i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=300, must-revalidate');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
            $this->setETag($response);
            $this->setLastModified($response);
        }
        elseif (preg_match('/^application\/(json|xml)/i', $contentType) || preg_match('/\/api\//i', $path)) {
            $response->setHeader('Cache-Control', 'public, max-age=300, must-revalidate');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
            $this->setETag($response);
        }
        else {
            $response->setHeader('Cache-Control', 'public, max-age=3600, must-revalidate');
            $response->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
            $this->setETag($response);
        }

        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    private function setETag(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if (!empty($body)) {
            $etag = '"' . md5($body) . '"';
            $response->setHeader('ETag', $etag);
        }
    }

    private function setLastModified(ResponseInterface $response): void
    {
        $lastModified = gmdate('D, d M Y H:i:s \G\M\T', time());
        $response->setHeader('Last-Modified', $lastModified);
    }
}

