<?php
// app/Notifications/Ndtc/OrderApproved.php

namespace App\Notifications\Ndtc;

use App\Models\NdtcOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderApproved extends Notification
{
    use Queueable;

    public function __construct(private NdtcOrder $order) {}

    public function via(): array
    {
        return ['mail', 'database'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Title Order Approved — ' . $this->order->vin)
            ->line('Your NDTC title order has been approved.')
            ->line('VIN: ' . $this->order->vin)
            ->line('New title number: ' . $this->order->new_title_number)
            ->action('View Order', route('ndtc.orders.show', $this->order));
    }

    public function toArray(): array
    {
        return [
            'type'             => 'order_approved',
            'order_id'         => $this->order->id,
            'vin'              => $this->order->vin,
            'new_title_number' => $this->order->new_title_number,
        ];
    }
}
