<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    protected $table = 'market';

    protected $fillable = [
        'owner_id',
        'assetid',
        'price',
        'info',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    protected $casts = [
        'info' => 'json'
    ];
}
