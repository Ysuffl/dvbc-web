<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    public $timestamps = false;

    protected $fillable = [
        'table_id', 'customer_id', 'customer_name', 'customer_category', 
        'phone', 'pax', 'start_time', 'end_time', 'billed_at', 
        'status', 'notes', 'cancel_reason', 'billed_price'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'billed_at' => 'datetime',
    ];

    public function tableModel()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function tags()
    {
        return $this->belongsToMany(MasterTag::class, 'booking_tags', 'booking_id', 'tag_id');
    }
}
