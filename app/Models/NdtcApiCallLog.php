<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdtcApiCallLog extends Model
{
    protected $fillable = [
        'ndtc_order_id', 'method', 'endpoint',
        'request_payload', 'response_status', 'response_body',
        'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(NdtcOrder::class, 'ndtc_order_id', 'ndtc_order_id');
    }

    public function isSuccessful(): bool
    {
        return $this->response_status !== null && $this->response_status < 300;
    }
}
