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

    // Document content types accepted by POST /api/v1/orders/{orderId}/documents
    const CONTENT_TYPES = [
        'BILL_OF_SALE', 'CERTIFICATE_OF_COMPLETION', 'DECLARATION_PAGE', 'DOING_BUSINESS_AS',
        'INSURANCE_SETTLEMENT', 'LETTER_OF_CORRECTION', 'LIEN_RELEASE', 'LOAN_AGREEMENT',
        'MANUFACTURER_CERTIFICATE', 'MVR_REPORT', 'NMVTIS_REPORT', 'ODOMETER_DISCLOSURE',
        'ONE_AND_THE_SAME', 'OTHER_EVIDENCE', 'POWER_OF_ATTORNEY',
        'POWER_OF_ATTORNEY_WITH_ODOMETER_DISCLOSURE', 'REQUEST_FOR_MISSING_EVIDENCE',
        'SECURE_ELECTRONIC_POWER_OF_ATTORNEY', 'SECURITY_AGREEMENT', 'STATEMENT_OF_IDENTITY',
        'STATEMENT_OF_MISSING_EVIDENCE', 'STATEMENT_OF_NO_TITLE', 'STATEMENT_OF_REPOSSESSION',
        'TITLE_BACK', 'TITLE_FRONT', 'TITLE_FRONT_AND_BACK', 'TITLE_REASSIGNMENT', 'VIN_VERIFICATION',
    ];

    // Maximum documents per order for each content type (NDTC guide 11.8); others are unlimited
    const MAX_PER_ORDER = [
        'TITLE_FRONT'                                => 1,
        'TITLE_BACK'                                 => 1,
        'TITLE_FRONT_AND_BACK'                       => 1,
        'SECURE_ELECTRONIC_POWER_OF_ATTORNEY'        => 1,
        'POWER_OF_ATTORNEY'                          => 3,
        'POWER_OF_ATTORNEY_WITH_ODOMETER_DISCLOSURE' => 3,
        'LIEN_RELEASE'                               => 3,
        'ODOMETER_DISCLOSURE'                        => 3,
        'MVR_REPORT'                                 => 1,
        'NMVTIS_REPORT'                              => 1,
        'MANUFACTURER_CERTIFICATE'                   => 1,
        'VIN_VERIFICATION'                           => 1,
    ];

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
