<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = [
        'name', 'description', 'floor_number', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Semua meja dalam area ini */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'area_fk_id');
    }
}
