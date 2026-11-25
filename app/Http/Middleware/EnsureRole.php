<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class EnsureRole {
    public function handle(Request $request, Closure $next, string ...$roles) {
        $user = $request->user();
        // Peran dicocokkan persis: administrator tidak otomatis diloloskan.
        if (! $user || ! $user->punyaRole(...$roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        return $next($request);
    }
}
