<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Auth\LoginController as LC;

class Client extends Authenticatable
{
    use Notifiable;

    protected $guard = 'client';

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at'
    ];

    public static function authId()
    {
        return Auth::guard(LC::CLIENT_GUARDNAME)->id();
    }
}
