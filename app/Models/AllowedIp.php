<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedIp extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = null;

    protected $table = 'allowed_ips';

    public $timestamps = false;

    protected $fillable = [
        'display_id',
        'ip',
    ];

    public function display()
    {
        return $this->belongsTo(Display::class, 'display_id', 'id');
    }
}
