<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class AdminMiddleware
{
    private function checkIfUserIsAdmin($user)
    {
        return DB::table('model_has_roles')->where('role_id', 1)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->exists();
    }

    public function handle($request, Closure $next)
    {
        if (!backpack_auth()) {
            return redirect()->to('/');
        }
        if (! $this->checkIfUserIsAdmin(backpack_user())) {
            return redirect()->to('/login');
        }

        return $next($request);
    }
}
