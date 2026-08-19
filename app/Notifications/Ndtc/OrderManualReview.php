<?php
// app/Notifications/Ndtc/OrderManualReview.php

namespace App\Notifications\Ndtc;

use App\Models\NdtcOrder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderManualReview extends Notification
{
    public function __construct(private NdtcOrder $order) {}

    public function via(): array { return ['database']; }

    // No email for manual review — it's informational only
    public function toArray(): array
    {
        return [
            'type'     => 'manual_review',
            'order_id' => $this->order->id,
            'vin'      => $this->order->vin,
            'message'  => 'Order is under manual review by the DMV. No action required.',
        ];
    }
}
