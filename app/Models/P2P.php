<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class P2P extends Model
{
    protected $table = 'p2p';

    protected $fillable = [
        'uuid',
        'market_id',
        'owner_id',
        'buyer_id',
        'buyer_accept_trade',
        'owner_send_trade',
        'status',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    protected $casts = [
        'info' => 'json'
    ];
}
