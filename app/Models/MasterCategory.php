<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCategory extends Model
{
    protected $fillable = ['name', 'icon', 'bg_color', 'text_color'];
}
