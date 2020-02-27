<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'subject',
        'description',
        'user_id',
        'created_at',
        'updated_at',
        'attachment_name'
    ];

    public function client()
    {
        return $this->belongsTo('App\Client', 'user_id');
    }
}
