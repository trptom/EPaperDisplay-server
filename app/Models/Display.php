<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Display extends Model
{
    use HasFactory;

    protected $table = 'displays';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'model',
        'width',
        'height',
        'ip_filter',
        'displayed',
    ];

    protected $casts = [
        'ip_filter' => 'boolean',
        'model' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'displayed' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class, 'display_id', 'id');
    }

    public function allowedIps()
    {
        return $this->hasMany(AllowedIp::class, 'display_id', 'id');
    }
}
