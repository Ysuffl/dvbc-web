<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $table = 'tables';
    public $timestamps = true;

    protected $fillable = [
        'code', 'x_pos', 'y_pos', 'shape', 'status',
        'area_id',          // legacy string (masih dipakai FastAPI)
        'area_fk_id',       // FK ke areas table
        'min_spending',
        'capacity',
        'hold_until',
        'hold_by_customer_id',
    ];

    protected $casts = [
        'hold_until'   => 'datetime',
        'min_spending' => 'decimal:2',
        'capacity'     => 'integer',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_fk_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'table_id');
    }

    public function holdByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'hold_by_customer_id');
    }
}
