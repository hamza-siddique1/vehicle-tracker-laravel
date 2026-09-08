<?php
// app/Http/Controllers/Dev/NdtcWebhookSimulatorController.php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NdtcWebhookSimulatorController extends Controller
{
    /**
     * Event => [status, orderStatus, includeDocuments, includeRejection, includeNewTitle]
     * Mirrors the §6.5 table from the NDTC Technical Guide.
     */
    private array $eventDefinitions = [
        'READY_FOR_DOCUMENTS' => ['status' => 'DRAFT',   'orderStatus' => 'DRAFT',   'documents' => false],
        'READY_TO_FINALIZE'   => ['status' => 'DRAFT',   'orderStatus' => 'DRAFT',   'documents' => true],
        'PROCESSING'          => ['status' => 'PROCESSING', 'orderStatus' => 'PROCESSING', 'documents' => true],
        'MANUAL_REVIEW'       => ['status' => 'MANUAL_REVIEW_REQUIRED', 'orderStatus' => 'MANUAL_REVIEW_REQUIRED', 'documents' => true],
        'ON_HOLD'             => ['status' => 'PROCESSING', 'orderStatus' => 'PROCESSING', 'documents' => true],
        'ORDER_APPROVED'      => ['status' => 'MANUALLY_APPROVED', 'orderStatus' => 'MANUALLY_APPROVED', 'documents' => true, 'newTitle' => true],
        'ORDER_REJECTED'      => ['status' => 'MANUALLY_REJECTED', 'orderStatus' => 'MANUALLY_REJECTED', 'documents' => true, 'rejection' => true],
        'AGING_ORDER'         => ['status' => 'DRAFT', 'orderStatus' => 'DRAFT', 'documents' => false],
        'ORDER_CANCELED'      => ['status' => 'CANCELLED', 'orderStatus' => 'CANCELLED', 'documents' => false],
        'TITLE_TERMINATED'    => ['status' => 'TITLE_TERMINATED', 'orderStatus' => 'TITLE_TERMINATED', 'documents' => true],
    ];

    public function show()
    {
        return view('dev.ndtc-webhook-simulator', [
            'events' => array_keys($this->eventDefinitions),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'string'],
            'event'    => ['required', 'string', 'in:' . implode(',', array_keys($this->eventDefinitions))],
        ]);

        $orderId = $request->input('order_id');
        $event   = $request->input('event');
        $def     = $this->eventDefinitions[$event];

        $payload = [
            'orderId'       => $orderId,
            'clientId'      => 'kaj-sit',
            'correlationId' => 'SIMULATED-' . now()->format('YmdHis'),
            'status'        => $def['status'],
            'event'         => $event,
            'order'         => [
                'id'              => $orderId,
                'transactionType' => 'TNL',
                'transferDate'    => '2024-01-18T00:00:00.000Z',
                'orderStatus'     => $def['orderStatus'],
                'stakeholders'    => [
                    'disposingEntities' => [[
                        'type'            => 'company',
                        'name'            => 'copart',
                        'phone'           => null,
                        'physicalAddress' => [
                            'addressLine1' => '123 Auction Drive',
                            'addressLine2' => null,
                            'city'         => 'Linden',
                            'stateCode'    => 'NJ',
                            'zipCode'      => '07036',
                            'county'       => null,
                        ],
                    ]],
                    'acquiringEntity' => [
                        'type'            => 'company',
                        'name'            => 'AutoRetail NJ LLC',
                        'phone'           => null,
                        'physicalAddress' => [
                            'addressLine1' => '890 Main Street',
                            'addressLine2' => null,
                            'city'         => 'Newark',
                            'stateCode'    => 'NJ',
                            'zipCode'      => '07102',
                            'county'       => null,
                        ],
                    ],
                    'titleWorkEntity' => [
                        'type'           => 'company',
                        'name'           => 'KAJ INC',
                        'representative' => [
                            'firstName'            => 'ADMIN',
                            'lastName'             => 'ADMIN',
                            'email'                => 'hamzasiddique836@gmail.com',
                            'phone'                => ['number' => '555-555-5555', 'usageType' => 'MOBILE'],
                            'relationshipToEntity' => 'AGENT',
                        ],
                        'phone'           => null,
                        'physicalAddress' => [
                            'addressLine1' => '3 ARCH STREET',
                            'addressLine2' => null,
                            'city'         => 'PATERSON',
                            'stateCode'    => 'NJ',
                            'zipCode'      => '07522',
                            'county'       => '',
                        ],
                    ],
                ],
                'evidenceDetail' => [
                    'existingTitle' => [
                        'titleType'        => 'PAPER',
                        'issuingStateCode' => 'AL',
                        'titleNumber'      => 'NY123456789',
                        'controlNumber'    => null,
                        'titleBrands'      => [],
                        'liens'            => [],
                    ],
                    'vehicleDetails' => [
                        'vin'          => $orderId, // matches your example's pattern where VIN mirrors a placeholder
                        'year'         => '2023',
                        'make'         => 'FORD',
                        'model'        => 'F350 SUPER DUTY',
                        'bodyStyle'    => '4W',
                        'odometer'     => [
                            'condition' => 'ACTUAL',
                            'reading'   => ['reading' => 4200, 'date' => '2024-01-18T00:00:00.000Z', 'unit' => 'MI'],
                        ],
                        'vehicleClass' => 'CARS_AND_TRUCKS',
                        'weight'       => ['weight' => 4500, 'unit' => 'LBS'],
                        'fuelType'     => 'GAS',
                    ],
                    'attachedDocuments' => $def['documents'] ? [
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'fileType' => 'application/pdf',
                            'fileDisplayName' => 'Nonresident Business Title Assignment',
                            'byteSize' => 1913364,
                            'hash' => 'simulated-hash',
                            'documentContent' => 'CLEARINGHOUSE_TITLE_APPLICATION',
                        ],
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'fileType' => 'image/jpeg',
                            'fileDisplayName' => 'Front of Title',
                            'byteSize' => 59752,
                            'hash' => 'simulated-hash',
                            'documentContent' => 'TITLE_FRONT',
                        ],
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'fileType' => 'image/jpeg',
                            'fileDisplayName' => 'Back of Title',
                            'byteSize' => 54421,
                            'hash' => 'simulated-hash',
                            'documentContent' => 'TITLE_BACK',
                        ],
                    ] : [],
                ],
                'rejections' => null,
            ],
        ];

        // Optional: attach rejection reasons for ORDER_REJECTED
        if (!empty($def['rejection'])) {
            $payload['order']['rejections'] = [[
                'element' => 'STATE',
                'code'    => 'STATE',
                'reasons' => ['Simulated rejection: Evidence Illegible.'],
            ]];
        }

        // Optional: attach newTitle for ORDER_APPROVED
        if (!empty($def['newTitle'])) {
            $payload['order']['evidenceDetail']['newTitle'] = [
                'titleFormat' => 'PAPER',
                'titleNumber' => 'SIM' . rand(100000, 999999),
                'active'      => true,
                'issueDate'   => now()->toIso8601String(),
                'brands'      => [],
                'digitalTitleUrl' => null,
                'ownerId'     => (string) \Illuminate\Support\Str::uuid(),
            ];
        }
        $jsonBody  = json_encode($payload);

        $webhookRequest = Request::create(route('ndtc.webhook'), 'POST', [], [], [], [
            'HTTP_X-Signature-256' => 123, // Simulated signature for testing
            'CONTENT_TYPE' => 'application/json',
        ], $jsonBody);

        app()->handle($webhookRequest);

       return back()->with('success', "Simulated webhook for event '{$event}' sent to order '{$orderId}'.");
    }
}
