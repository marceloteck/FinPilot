<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirstUserAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $firstUser = User::query()->orderBy('id')->first();

        if (!$user || !$firstUser || $user->id !== $firstUser->id) {
            abort(403, 'Acesso restrito ao administrador principal.');
        }

        return $next($request);
    }
}
