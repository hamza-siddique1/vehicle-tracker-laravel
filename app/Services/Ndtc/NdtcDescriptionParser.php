<?php

namespace App\Services\Ndtc;

class NdtcDescriptionParser
{
    private array $makeToNcic = [
        'MERCEDES-BENZ' => 'MERZ',
        'CHEVROLET'     => 'CHEV',
        'DODGE'         => 'DODG',
        'TOYOTA'        => 'TOYT',
        'CADILLAC'      => 'CADI',
        'HONDA'         => 'HOND',
        'JEEP'          => 'JEEP',
        'FORD'          => 'FORD',
        'GMC'           => 'GMC',
        'LINCOLN'       => 'LINC',
        'LAND ROVER'    => 'LNDR',
        'LEXUS'         => 'LEXS',
        'SUBARU'        => 'SUBA',
        'AUDI'          => 'AUDI',
        'BMW'           => 'BMW',
        'NISSAN'        => 'NISS',
        'HYUNDAI'       => 'HYUN',
        'KIA'           => 'KIA',
        'VOLKSWAGEN'    => 'VOLK',
        'VOLVO'         => 'VOLV',
        'BUICK'         => 'BUIC',
        'INFINITI'      => 'INFI',
        'MAZDA'         => 'MAZD',
        'MINI'          => 'MINI',
        'MITSUBISHI'    => 'MITS',
        'PONTIAC'       => 'PONT',
        'PORSCHE'       => 'PORS',
        'RAM'           => 'RRAM',
        'CHRYSLER'      => 'CHRY',
        'ACURA'         => 'ACUR',
        'TESLA'         => 'TESL',
        'GENESIS'       => 'GENE',
        'RIVIAN'        => 'RIVI',
        'LUCID'         => 'LUCI',
        'MASERATI'      => 'MASE',
        'ALFA ROMEO'    => 'ALFA',
        'ROLLS ROYCE'   => 'ROLL',
        'BENTLEY'       => 'BENT',
        'LAMBORGHINI'   => 'LAMB',
        'FERRARI'       => 'FERR',
    ];

    // Must be checked before single-word makes
    private array $multiWordMakes = [
        'MERCEDES-BENZ',
        'LAND ROVER',
        'ALFA ROMEO',
        'ROLLS ROYCE',
        'ASTON MARTIN',
    ];

    public function parse(?string $description): array
    {
        if (empty($description)) {
            return [
                'year'      => null,
                'make'      => null,
                'make_ncic' => null,
                'model'     => null,
            ];
        }

        $upper = strtoupper(trim($description));
        $parts = preg_split('/\s+/', $upper);

        // Year is always first token
        $year = $parts[0] ?? null;
        $rest = implode(' ', array_slice($parts, 1));

        $make  = null;
        $model = null;

        // Check multi-word makes first
        foreach ($this->multiWordMakes as $mw) {
            if (str_starts_with($rest, $mw)) {
                $make  = $mw;
                $model = trim(substr($rest, strlen($mw)));
                break;
            }
        }

        // Single word make
        if (!$make && count($parts) > 1) {
            $firstWord = $parts[1];
            if (isset($this->makeToNcic[$firstWord])) {
                $make  = $firstWord;
                $model = implode(' ', array_slice($parts, 2));
            }
        }

        return [
            'year'      => $year,
            'make'      => $make,
            'make_ncic' => $make ? ($this->makeToNcic[$make] ?? strtoupper(substr($make, 0, 4))) : null,
            'model'     => $model,
        ];
    }

    public function getNcicCode(string $make): ?string
    {
        $upper = strtoupper(trim($make));
        return $this->makeToNcic[$upper] ?? null;
    }
}
