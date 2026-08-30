<?php
// app/Http/Requests/Ndtc/StoreNdtcOrderRequest.php

namespace App\Http\Requests\Ndtc;

use Illuminate\Foundation\Http\FormRequest;

class StoreNdtcOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'          => ['required', 'exists:vehicles,id'],
            'vin'                 => ['required', 'string', 'regex:/^[A-Za-z0-9]+$/'],
            'year'                => ['required', 'digits:4', 'integer', 'min:1980'],
            'make'                => ['required', 'string', 'max:10'],
            'model'               => ['required', 'string', 'max:100'],
            'body_style'          => ['required', 'string', 'max:5'],
            'fuel_type'           => ['required', 'string'],
            'weight'              => ['required', 'integer', 'min:100', 'max:99999'],
            'vehicle_class'       => ['required', 'string'],
            'title_number'        => ['required', 'string', 'max:50'],
            'issuing_state'       => ['required', 'string', 'size:2'],
            'title_type'          => ['required', 'in:PAPER,ELECTRONIC'],
            'title_brands'        => ['nullable', 'array'],
            'title_brands.*'      => ['string'],
            'odometer_reading'    => ['nullable', 'integer', 'min:0'],
            'odometer_condition'  => ['required', 'in:ACTUAL,NOT_ACTUAL,EXEMPT,EXCEEDS_MECHANICAL_LIMIT,NO_ODOMETER'],
            'odometer_date'       => ['required', 'date'],
            'disposing_name'      => ['required', 'string', 'max:150'],
            'disposing_address1'  => ['required', 'string', 'max:100'],
            'disposing_address2'  => ['nullable', 'string', 'max:100'],
            'disposing_city'      => ['required', 'string', 'max:100'],
            'disposing_state'     => ['required', 'string', 'size:2'],
            'disposing_zip'       => ['required', 'string', 'max:10'],
            'disposing_county'    => ['nullable', 'string', 'max:100'],
            'transfer_date'       => ['required', 'date'],
            'transaction_type'    => ['required', 'in:TNL,TWL'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title_number.required'       => 'Title number is required — check the physical title.',
            'issuing_state.required'      => 'Issuing state is required.',
            'disposing_address1.required' => 'Auction address is required.',
            'disposing_city.required'     => 'Auction city is required.',
            'disposing_zip.required'      => 'Auction ZIP code is required.',
            'weight.required'             => 'Vehicle weight is required — check NHTSA if unsure.',
            'body_style.required'         => 'Body style is required.',
            'odometer_date.required'      => 'Odometer date is required.',
        ];
    }
}
