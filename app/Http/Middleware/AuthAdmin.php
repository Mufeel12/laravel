<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;

class AuthAdmin
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string[] ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws Forbidden403Exception
     */
     public function handle($request, Closure $next, ...$guards)
    {
        $user = User::where('id', $request->user()->id)->where('super_admin', true)->first();
        if ($request->cookie('BCAccessToken')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('BCAccessToken'));
        }

        if ($request->has('BCAccessToken')) {
            setcookie('BCAccessToken', $request->input('BCAccessToken'));
            $request->headers->set('Authorization', 'Bearer ' . $request->input('BCAccessToken'));
        }

        $this->authenticate($guards, $user);

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param array $guards
     * @param $user
     * @return void
     *
     * @throws AuthenticationException
     */
    protected function authenticate(array $guards, $user)
    {
        if (empty($guards) && $user) {
            return $this->auth->authenticate();
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated.', $guards);
    }
}
