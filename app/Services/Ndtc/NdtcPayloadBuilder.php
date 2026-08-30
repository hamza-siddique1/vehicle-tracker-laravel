<?php
// app/Services/Ndtc/NdtcPayloadBuilder.php

namespace App\Services\Ndtc;

use Illuminate\Http\Request;

class NdtcPayloadBuilder
{
    public function fromRequest(Request $request, $correlationId): array
    {
        return [
            // ── ACQUIRING ENTITY ──────────────────────────────
            'acquiringEntity' => [
                'id' => [
                    'nrbNumber' => config('ndtc.nrb_number'),
                ],
                'name' => config('ndtc.acquiring_name'),
                'physicalAddress' => [
                    'addressLine1' => config('ndtc.acquiring_address1'),
                    'city'         => config('ndtc.acquiring_city'),
                    'stateCode'    => config('ndtc.acquiring_state'),
                    'zipCode'      => config('ndtc.acquiring_zip'),
                ],
            ],

            // ── TITLE WORK ENTITY ─────────────────────────────
            'titleWorkEntity' => [
                'id' => [
                    'nrbNumber' => config('ndtc.nrb_number'),
                ],
                'representative' => [
                    'firstName'            => 'David',
                    'lastName'             => 'Jone',
                    'email'                => 'david_jone@gmail.com',
                    'phone'                => [
                        'number'    => '555-555-5555',
                        'usageType' => 'MOBILE',
                    ],
                    'relationshipToEntity' => 'AGENT',
                ],
            ],

            // ── REQUESTED TITLE ───────────────────────────────
            'requestedTitle' => [
                'titleType' => 'PAPER',
            ],

            // ── EVIDENCE ──────────────────────────────────────
            'evidence' => [
                'existingTitle' => [
                    'titleType'       => $request->input('title_type', 'PAPER'),
                    'issuingStateCode'=> $request->input('issuing_state'),
                    'titleNumber'     => $request->input('title_number'),
                    'titleBrands'     => $request->input('title_brands', []),
                    'liens'           => [],
                ],

                'vehicle' => [
                    'vin'          => strtoupper($request->input('vin')),
                    'year'         => $request->input('year'),
                    'make'         => $request->input('make'),
                    'model'        => $request->input('model'),
                    'bodyStyle'    => $request->input('body_style'),
                    'vehicleClass' => $request->input('vehicle_class', 'CARS_AND_TRUCKS'),
                    'fuelType'     => $request->input('fuel_type', 'GAS'),
                    'weight'       => [
                        'weight' => (int) $request->input('weight'),
                        'unit'   => 'LBS',
                    ],
                    'odometer'     => $this->buildOdometer($request),
                ],

                'disposingEntities' => [
                    [
                        'type' => 'company',
                        'name' => $request->input('disposing_name'),
                        'physicalAddress' => [
                            'addressLine1' => $request->input('disposing_address1'),
                            'addressLine2' => $request->input('disposing_address2'),
                            'city'         => $request->input('disposing_city'),
                            'stateCode'    => $request->input('disposing_state'),
                            'zipCode'      => $request->input('disposing_zip'),
                            'county'       => $request->input('disposing_county'),
                        ],
                        'phone' => $request->filled('disposing_phone') ? [
                            'number'    => $request->input('disposing_phone'),
                            'usageType' => 'MOBILE',
                        ] : null,
                    ],
                ],

                'transferDate' => $this->formatDate($request->input('transfer_date')),
            ],

            // ── VERTICAL ──────────────────────────────────────
            'vertical' => [
                'type' => $request->input('vertical', 'NATIONAL_RETAILER'),
            ],

            // ── CLIENT INTEGRATION ────────────────────────────
            'clientIntegration' => [
                'correlationId' => $correlationId,
            ],
        ];
    }

    private function buildOdometer(Request $request): array
    {
        $condition = $request->input('odometer_condition', 'ACTUAL');

        if (in_array($condition, ['NO_ODOMETER', 'EXEMPT'])) {
            return [
                'condition' => $condition,
                'reading'   => null,
            ];
        }

        return [
            'condition' => $condition,
            'reading'   => [
                'reading' => (int) $request->input('odometer_reading'),
                'date'    => $this->formatDate($request->input('odometer_date')),
                'unit'    => 'MI',
            ],
        ];
    }

    private function formatDate(?string $date): ?string
    {
        if (empty($date)) return null;

        try {
            return \Carbon\Carbon::parse($date)->toIso8601String();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getFirstName(): string
    {
        $user = auth()->user();
        if (isset($user->first_name)) return $user->first_name;
        return explode(' ', $user->name)[0];
    }

    private function getLastName(): string
    {
        $user = auth()->user();
        if (isset($user->last_name)) return $user->last_name;
        $parts = explode(' ', $user->name);
        return $parts[1] ?? $user->name;
    }
}
