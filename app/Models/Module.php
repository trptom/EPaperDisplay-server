<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = null; // composite primary

    protected $table = 'modules';

    protected $fillable = [
        'display_id',
        'position',
        'type',
        'x',
        'y',
        'width',
        'height',
        'border',
        'data',
    ];

    protected $casts = [
        'type' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'border' => 'integer',
    ];

    public function display()
    {
        return $this->belongsTo(Display::class, 'display_id', 'id');
    }
}
