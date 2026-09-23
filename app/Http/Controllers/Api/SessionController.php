<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(User $user)
    {
        return $user->tokens()
            ->select('id', 'name', 'ip_address', 'user_agent', 'last_used_at', 'created_at')
            ->latest('last_used_at')
            ->get();
    }

    // Cierra una sesión específica de ese usuario.
    public function destroy(Request $request, User $user, int $tokenId)
    {
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            abort(404);
        }

        $token->delete();

        return response()->json(null, 204);
    }

    // Cierra TODAS las sesiones de ese usuario (excepto la que hace la petición, si es el mismo usuario).
    public function destroyAll(Request $request, User $user)
    {
        $currentTokenId = $request->user()->currentAccessToken()->id ?? null;

        $user->tokens()
            ->when($request->user()->id === $user->id, fn ($q) => $q->where('id', '!=', $currentTokenId))
            ->delete();

        return response()->json(null, 204);
    }
}