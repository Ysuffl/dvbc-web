<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTag extends Model
{
    protected $fillable = ['master_tag_group_id', 'name', 'abbreviation'];

    public function group()
    {
        return $this->belongsTo(MasterTagGroup::class, 'master_tag_group_id');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_tags', 'tag_id', 'booking_id');
    }
}
