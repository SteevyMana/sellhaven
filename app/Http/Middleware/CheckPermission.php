<?php
// app/Http/Middleware/CheckPermission.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        $hasPermission = $user
            && $user->role
            && $user->role->permissions()->where('name', $permission)->exists();

        if (! $hasPermission) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}