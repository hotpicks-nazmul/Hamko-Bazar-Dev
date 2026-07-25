<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $apiKey = $request->bearerToken();
        
        // Also check for API key in header
        if (!$apiKey) {
            $apiKey = $request->header('X-API-Key');
        }
        
        // Also check for API key in query parameter
        if (!$apiKey) {
            $apiKey = $request->query('api_key');
        }
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error' => 'UNAUTHORIZED',
            ], 401);
        }
        
        // Find the API key in database
        $keyRecord = ApiKey::where('key', $apiKey)->first();
        
        if (!$keyRecord) {
            Log::warning('Invalid API key attempted', [
                'ip' => $request->ip(),
                'endpoint' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
                'error' => 'UNAUTHORIZED',
            ], 401);
        }
        
        // Check if key is active
        if (!$keyRecord->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'API key is inactive',
                'error' => 'INACTIVE_KEY',
            ], 401);
        }
        
        // Check if key is expired
        if ($keyRecord->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'API key has expired',
                'error' => 'EXPIRED_KEY',
            ], 401);
        }
        
        // Record the usage
        $keyRecord->recordUsage();
        
        // Log the request
        $this->logRequest($request, $keyRecord);
        
        // Add API key to request for later use
        $request->merge(['api_key_id' => $keyRecord->id]);
        $request->attributes->set('apiKey', $keyRecord);
        
        return $next($request);
    }
    
    /**
     * Log API request for analytics
     */
    private function logRequest(Request $request, ApiKey $apiKey): void
    {
        try {
            $startTime = microtime(true);
            
            // This will be called after the request is processed
            app()->terminating(function () use ($request, $apiKey, $startTime) {
                $responseTime = (microtime(true) - $startTime) * 1000;
                
                ApiKeyLog::create([
                    'api_key_id' => $apiKey->id,
                    'method' => $request->method(),
                    'endpoint' => $request->fullUrl(),
                    'status_code' => response()->getStatusCode(),
                    'response_time' => round($responseTime),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (\Exception $e) {
            // Don't fail the request if logging fails
            Log::error('API key logging failed: ' . $e->getMessage());
        }
    }
}
