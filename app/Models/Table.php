<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';
    public $timestamps = false; // FastAPI Table model has no timestamps
    
    protected $fillable = [
        'code', 'x_pos', 'y_pos', 'shape', 'status', 'area_id', 'hold_until', 'hold_by_customer_id'
    ];

    protected $casts = [
        'hold_until' => 'datetime'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'table_id');
    }

    public function holdByCustomer()
    {
        return $this->belongsTo(Customer::class, 'hold_by_customer_id');
    }
}
