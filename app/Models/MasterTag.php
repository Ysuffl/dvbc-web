<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTag extends Model
{
    protected $fillable = ['group_name', 'name', 'font_size'];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_tags', 'tag_id', 'booking_id');
    }
}
