<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastTemplate extends Model
{
    protected $table = 'broadcast_templates';

    protected $fillable = [
        'name',
        'message',
        'variables',
        'type',
    ];

    protected $casts = [
        'variables' => 'array',
    ];
}
