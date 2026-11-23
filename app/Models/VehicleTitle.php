<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTitle extends Model
{
    protected $fillable = [
        'vehicle_id', 'title_number', 'received_date', 'reassignment_number',
        'sent_to_auction_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'received_date'        => 'date',
        'sent_to_auction_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
