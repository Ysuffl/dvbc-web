<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTagGroup extends Model
{
    protected $fillable = ['name'];

    public function tags()
    {
        return $this->hasMany(MasterTag::class);
    }
}
