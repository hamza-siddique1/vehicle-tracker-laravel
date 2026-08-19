<?php
// app/Models/NdtcWebhookLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdtcWebhookLog extends Model
{
    protected $fillable = [
        'order_id',
        'ndtc_order_id',
        'event',
        'ndtc_status',
        'payload',
        'signature',
        'signature_verified',
        'processed',
        'process_error',
        'process_attempts',
        'processed_at',
        'received_at',
    ];

    protected $casts = [
        'payload'            => 'array',
        'signature_verified' => 'boolean',
        'processed'          => 'boolean',
        'received_at'        => 'datetime',
        'processed_at'       => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(NdtcOrder::class, 'order_id');
    }
}
