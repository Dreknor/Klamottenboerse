<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineSaleQueue extends Model
{
    protected $table = 'offline_sale_queue';

    protected $fillable = [
        'user_id',
        'device_id',
        'payload',
        'status',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
