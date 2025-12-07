<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SearchThrottle Filter
 * 
 * Rate limiting for public search endpoints to prevent abuse
 */
class SearchThrottle implements FilterInterface
{
    /**
     * Maximum requests per minute for search
     */
    private const MAX_REQUESTS = 30;
    
    /**
     * Time window in seconds
     */
    private const DECAY_SECONDS = 60;

    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = service('throttler');
        $ipAddress = (string) $request->getIPAddress();
        
        // Create unique key for search throttling
        $key = 'search-throttle-' . hash('sha256', $ipAddress ?: 'unknown');
        
        if (!$throttler->check($key, self::MAX_REQUESTS, self::DECAY_SECONDS)) {
            $waitTime = (int) ceil($throttler->getTokenTime($key));
            
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'error' => 'Too many requests',
                    'message' => "Terlalu banyak permintaan pencarian. Coba lagi dalam {$waitTime} detik.",
                    'retry_after' => $waitTime,
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}
