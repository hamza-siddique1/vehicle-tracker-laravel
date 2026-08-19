<?php
// app/Models/NdtcRejectionHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdtcRejectionHistory extends Model
{
    protected $table = 'ndtc_rejection_histories';

    protected $fillable = [
        'ndtc_order_id',
        'submission_number',
        'ndtc_status',
        'rejection_reasons',
        'webhook_payload',
        'rejected_at',
    ];

    protected $casts = [
        'rejection_reasons' => 'array',
        'webhook_payload'   => 'array',
        'rejected_at'       => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(NdtcOrder::class, 'ndtc_order_id');
    }
}
