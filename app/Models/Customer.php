<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    const UPDATED_AT = null; 

    protected $fillable = [
        'name', 'phone', 'category', 'created_at'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }
}
