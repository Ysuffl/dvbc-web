<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLevel extends Model
{
    protected $fillable = ['name', 'min_spending', 'badge_color'];
}
