<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permission;

class CheckFinanceWriteAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Only apply to authenticated users with 'finance' role
        if ($user && $user->isFinance()) {
            // Only restrict write operations (POST, PUT, PATCH, DELETE)
            if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
                
                // Allow all routes under 'admin/finance' or starting with 'admin/finance'
                if ($request->is('admin/finance*')) {
                    return $next($request);
                }

                // For other routes, we try to determine if they belong to Finance group
                // In this system, we'll block by default unless explicitly allowed
                
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'Role Finance hanya memiliki akses Lihat (View Only) untuk modul ini.'
                    ], 403);
                }

                return back()->with('error', 'Role Finance hanya memiliki akses Lihat (View Only) untuk modul ini.');
            }
        }

        return $next($request);
    }
}
