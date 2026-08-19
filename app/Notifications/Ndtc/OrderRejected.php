<?php
// app/Notifications/Ndtc/OrderRejected.php

namespace App\Notifications\Ndtc;

use App\Models\NdtcOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRejected extends Notification
{
    use Queueable;

    public function __construct(
        private NdtcOrder $order,
        private array $rejections,
    ) {}

    public function via(): array
    {
        return ['mail', 'database'];
    }

    public function toMail(): MailMessage
    {
        $reason = $this->rejections[0]['reasons'][0]
               ?? 'No specific reason provided';

        return (new MailMessage)
            ->subject('Action Required — Title Order Rejected — ' . $this->order->vin)
            ->error()
            ->line('Your NDTC title order has been rejected by the DMV.')
            ->line('VIN: ' . $this->order->vin)
            ->line('Reason: ' . $reason)
            ->line('Please review the rejection, correct the issue, and resubmit the same order.')
            ->action('View & Fix Order', route('ndtc.orders.show', $this->order));
    }

    public function toArray(): array
    {
        return [
            'type'       => 'order_rejected',
            'order_id'   => $this->order->id,
            'vin'        => $this->order->vin,
            'rejections' => $this->rejections,
        ];
    }
}
