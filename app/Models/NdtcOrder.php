<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NdtcOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ndtc_orders';

    protected $fillable = [
        'vehicle_id',
        'created_by',
        'ndtc_order_id',
        'correlation_id',
        'vin',
        'vehicle_description',
        'transaction_type',
        'status',
        'ndtc_status',
        'transfer_date',
        'submission_count',
        'rejection_count',
        'finalized',
        'ready_to_finalize',
        'new_title_number',
        'approved_at',
        'rejected_at',
        'finalized_at',
        'cancelled_at',
        'order_payload',
        'rejection_reasons',
        'last_webhook',
    ];

    protected $casts = [
        'order_payload'     => 'array',
        'rejection_reasons' => 'array',
        'last_webhook'      => 'array',
        'finalized'         => 'boolean',
        'ready_to_finalize' => 'boolean',
        'transfer_date'     => 'date',
        'approved_at'       => 'datetime',
        'rejected_at'       => 'datetime',
        'finalized_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
    ];

    // ── STATUS CONSTANTS ──────────────────────────────────────
    const STATUS_DRAFT               = 'DRAFT';
    const STATUS_READY_FOR_DOCUMENTS = 'READY_FOR_DOCUMENTS';
    const STATUS_READY_TO_FINALIZE   = 'READY_TO_FINALIZE';
    const STATUS_PROCESSING          = 'PROCESSING';
    const STATUS_MANUAL_REVIEW       = 'MANUAL_REVIEW';
    const STATUS_ON_HOLD             = 'ON_HOLD';
    const STATUS_APPROVED            = 'APPROVED';
    const STATUS_REJECTED            = 'REJECTED';
    const STATUS_AGING               = 'AGING';
    const STATUS_CANCELLED           = 'CANCELLED';
    const STATUS_TITLE_TERMINATED    = 'TITLE_TERMINATED';

    // Terminal statuses — no further actions possible
    const TERMINAL_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_CANCELLED,
        self::STATUS_TITLE_TERMINATED,
    ];

    // ── RELATIONSHIPS ─────────────────────────────────────────
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(NdtcOrderDocument::class, 'ndtc_order_id');
    }

    public function webhookLogs()
    {
        return $this->hasMany(NdtcWebhookLog::class, 'order_id');
    }

    public function rejectionHistory()
    {
        return $this->hasMany(NdtcRejectionHistory::class, 'ndtc_order_id');
    }

    // ── SCOPES ───────────────────────────────────────────────
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', self::TERMINAL_STATUSES);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeAwaitingAction($query)
    {
        // Orders that need agent attention
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_READY_FOR_DOCUMENTS,
            self::STATUS_READY_TO_FINALIZE,
            self::STATUS_REJECTED,
            self::STATUS_AGING,
        ]);
    }

    // ── STATUS HELPERS ────────────────────────────────────────
    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeFinalized(): bool
    {
        return $this->ready_to_finalize && !$this->finalized;
    }

    public function canBeCancelled(): bool
    {
        return !$this->isTerminal() && !in_array($this->status, [
            self::STATUS_PROCESSING,
            self::STATUS_MANUAL_REVIEW,
            self::STATUS_ON_HOLD,
        ]);
    }

    public function canBeResubmitted(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
