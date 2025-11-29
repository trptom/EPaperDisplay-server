<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    // Use only created_at; disable updated_at
    public $timestamps = true;
    const UPDATED_AT = null;

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
        'data' => 'array',
    ];

    public function display()
    {
        return $this->belongsTo(Display::class, 'display_id', 'id');
    }
}
