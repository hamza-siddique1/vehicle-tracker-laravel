<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdtcDisposingEntity extends Model
{
    protected $fillable = [
        'name', 'address_line_1', 'address_line_2', 'city', 'state',
        'zip', 'county', 'phone_number', 'phone_type', 'created_by',
    ];
}
