<?php

namespace App\Http\Middleware\Api\Application;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticateApplicationUser
{
    /**
     * Authenticate that the currently authenticated user is an administrator
     * and should be allowed to proceed through the application API.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if (!$user || !$user->isRootAdmin()) {
            throw new AccessDeniedHttpException('Dieses Konto hat keine Berechtigung, auf die API zuzugreifen.');
        }

        return $next($request);
    }
}
