<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        activity('auth')
            ->causedBy($this->causer($event->user))
            ->withProperties(['ip' => request()->ip(), 'guard' => $event->guard])
            ->log('login');
    }

    public function handleLogout(Logout $event): void
    {
        activity('auth')
            ->causedBy($this->causer($event->user))
            ->withProperties(['ip' => request()->ip(), 'guard' => $event->guard])
            ->log('logout');
    }

    public function handleFailed(Failed $event): void
    {
        activity('auth')
            ->causedBy($this->causer($event->user))
            ->withProperties([
                'ip' => request()->ip(),
                'guard' => $event->guard,
                'attempted_email' => $event->credentials['email'] ?? null,
            ])
            ->log('failed_login');
    }

    public function handleRegistered(Registered $event): void
    {
        activity('auth')
            ->causedBy($this->causer($event->user))
            ->withProperties(['ip' => request()->ip()])
            ->log('registered');
    }

    private function causer(?Authenticatable $user): ?Model
    {
        return $user instanceof Model ? $user : null;
    }
}
