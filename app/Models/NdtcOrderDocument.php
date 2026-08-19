<?php
// app/Models/NdtcOrderDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NdtcOrderDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ndtc_order_id',
        'ndtc_document_id',
        'document_content',
        'file_display_name',
        'file_mime_type',
        'file_size_bytes',
        'status',
        'is_system_generated',
        'upload_attempts',
        'uploaded_at',
        'upload_error',
    ];

    protected $casts = [
        'is_system_generated' => 'boolean',
        'uploaded_at'         => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING    = 'PENDING';
    const STATUS_UPLOADING  = 'UPLOADING';
    const STATUS_UPLOADED   = 'UPLOADED';
    const STATUS_FAILED     = 'FAILED';
    const STATUS_REPLACED   = 'REPLACED';

    public function order()
    {
        return $this->belongsTo(NdtcOrder::class, 'ndtc_order_id');
    }

    public function isUploaded(): bool
    {
        return $this->status === self::STATUS_UPLOADED;
    }

    public function canBeReplaced(): bool
    {
        return !$this->is_system_generated;
    }
}
