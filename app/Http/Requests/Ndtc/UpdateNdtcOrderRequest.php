<?php
// app/Http/Requests/Ndtc/UpdateNdtcOrderRequest.php

namespace App\Http\Requests\Ndtc;

class UpdateNdtcOrderRequest extends StoreNdtcOrderRequest
{
    public function rules(): array
    {
        // Same rules as create
        // vehicle_id not needed on update
        $rules = parent::rules();
        unset($rules['vehicle_id']);
        return $rules;
    }
}
