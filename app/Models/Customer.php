<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $table = 'customers';
    const UPDATED_AT = null; 

    protected $fillable = [
        'name', 'phone', 'age', 'gender', 'nat', 'total_spending', 'master_level_id', 'created_at', 'last_visit', 'total_visits'
    ];

    public function masterLevel()
    {
        return $this->belongsTo(MasterLevel::class, 'master_level_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function topTags($limit = 3)
    {
        return MasterTag::join('booking_tags', 'master_tags.id', '=', 'booking_tags.tag_id')
            ->join('bookings', 'booking_tags.booking_id', '=', 'bookings.id')
            ->where('bookings.customer_id', $this->id)
            ->select('master_tags.*', DB::raw('count(*) as count'))
            ->groupBy('master_tags.id')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
