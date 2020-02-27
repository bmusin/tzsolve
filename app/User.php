<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getManagerEmail() : string
    {
        $config_path = resource_path(config('constants.config_json'));
        $json = json_decode(file_get_contents($config_path, false));
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->notify_and_redirect_view(
                'Configuration file is corrupted.'
            );
        }
        return $json->email;
    }

    public static function updateManagerEmail(string $email) : string
    {
        $config_path = resource_path(config('constants.config_json'));
        $json = json_decode(file_get_contents($config_path, false));
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Configuration file is corrupted.';
        } else {
            $json->email = $email;
            $h = fopen($config_path, 'w');
            fwrite($h, json_encode($json));
            fclose($h);
            return "Manager's e-mail was updated.";
        }
    }
}
