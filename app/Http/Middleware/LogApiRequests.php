<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        ApiLog::create([
            'user_id' => auth()->id(),
            'service' => $request->path(),
            'request_body' => json_encode($request->all()),
            'response_code' => $response->status(),
            'response_body' => $response->content(),
            'ip_address' => $request->ip()
        ]);

        return $response;
    }
}
