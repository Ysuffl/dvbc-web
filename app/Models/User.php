<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users'; 
    public $timestamps = false; 

    protected $fillable = [
        'username', 'hashed_password', 'role', 'is_active',
    ];

    public function getAuthPasswordName()
    {
        return 'hashed_password';
    }

    public function getAuthPassword()
    {
        // Python's bcrypt generates $2b$ prefix hashes, but PHP's standard expects $2y$.
        // They are mathematically equivalent, so we can swap the prefix on the fly for Laravel's Auth.
        return str_replace('$2b$', '$2y$', $this->hashed_password);
    }
}
