<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vin',
        'location',
        'description',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    protected $dates = [
        'created_at',
    ];


    public const STATUSES = [
        'AWAITING BID APPROVAL',
        'READY FOR AUCTION',
        'WAITING FOR ORIGINAL TITLE',
        'WAITING FOR TRANSFERABLE TITLE',
        'In Transit',
        'Title Rejected',
        'Intake',
        'SOLD',
        'PARTS CAR'
    ];

    public static function getStatuses()
    {
        return self::STATUSES;
    }

    public function getInvoiceDateAttribute($date)
    {
        $date = Carbon::parse($date);

        return $date->format('Y-m-d');
    }

    public function metas()
    {
        return $this->hasMany(VehicleMetas::class);
    }

    public function invoice_amount()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'invoice_amount');
    }

    public function location()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'location');
    }

    public function date_pickup()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'pickup_date');
    }

    public function auction_stk()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'auction_stk');
    }

    public function auction_date()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'auction_date');
    }

    public function sale_price()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'sale_price');
    }

    public function days_in_yard()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'days_in_yard');
    }


    public static function countVehicles($status)
    {
        $sold_vehicles = Vehicle::whereHas('metas', function ($query) use ($status) {
            $query->where('meta_key', 'status')
                ->where('meta_value', $status);
        })->count();

        return $sold_vehicles;
    }


    protected static function applyInTransitConditions($query)
    {
        return $query->whereNotIn('location', ['NY - NEWBURGH', 'NJ - PATERSON', 'NEWBURGH', 'PATERSON'])
            ->where(function ($query) {
                $query->whereDoesntHave('metas', function ($subQuery) {
                    $subQuery->where('meta_key', 'status');
                })
                ->orWhereHas('metas', function ($subQuery) {
                    $subQuery->where('meta_key', 'status')
                             ->whereIn('meta_value', [self::STATUSES[4], '']);
                });
            });
    }

    public static function countInTransitVehicles()
    {
        $vehicles = self::applyInTransitConditions(Vehicle::query())->count();
        return $vehicles;
    }

    public static function countAllVehicles()
    {
        $query = "
        SELECT COUNT(*) AS aggregate
        FROM vehicles
        WHERE deleted_at IS NULL
            AND NOT EXISTS (
                SELECT 1
                FROM vehicle_metas
                WHERE vehicles.id = vehicle_metas.vehicle_id
                    AND vehicle_metas.meta_key = 'status'
                    AND vehicle_metas.meta_value = 'SOLD'
            )
    ";

        $result = DB::select($query);
        return $result[0]->aggregate;
    }

    public function number_of_runs()
    {
        return $this->hasOne(VehicleMetas::class)
            ->where('meta_key', 'number_of_runs');
    }

    public function scopeFilters($query, $request)
    {
        $query = $this->mainFilters($query, $request);

        $query = $this->allVehicles($query);

    }

    public function scopeSold($query, $request)
    {
        $query = $this->mainFilters($query, $request);

        $query = $this->soldVehicles($query);

    }

    public function mainFilters($query, $request)
    {

        if (isset($request['status']) && $request['status'] != -100) {

            $search = $request['status'];
            if ($search == 'In Transit') {
                self::applyInTransitConditions($query);
            } else {
                $query->whereHas('metas', function ($q1) use ($search) {
                    $q1->where('meta_value', 'LIKE', "%$search%");
                });
            }

        }

        if (isset($request['claim_number']) && $request['claim_number'] != '') {

            $claim_number = $request['claim_number'];
            $query->whereHas('metas', function ($q1) use ($claim_number) {
                $q1->where('meta_value', "$claim_number");
            });

        }

        if (isset($request['left_location'])) {
            $dateRange = explode(' - ', $request['left_location']);
            $query->whereBetween('left_location', [Carbon::parse($dateRange[0])->format('Y-m-d'), Carbon::parse($dateRange[1])->format('Y-m-d')]);
        }

        if (isset($request['location']) && $request['location'] != -100) {
            $query->where('location', $request['location']);
        }

        if (isset($request['date_paid'])) {
            $dateRange = explode(' - ', $request['date_paid']);
            $query->whereBetween('date_paid', [Carbon::parse($dateRange[0])->format('Y-m-d'), Carbon::parse($dateRange[1])->format('Y-m-d')]);
        }

        // $query->orderBy('id', 'desc');

        return $query;
    }

    public function allVehicles($query)
    {

        $query->whereDoesntHave('metas', function ($q1) {
            $q1->where('meta_key', 'status')
                ->where('meta_value', 'SOLD');
        });

        return $query;
    }

    public function soldVehicles($query)
    {

        $query->whereHas('metas', function ($q1) {
            $q1->where('meta_key', 'status')
                ->where('meta_value', 'SOLD');
        });

        return $query;
    }

    public function scopeSort($query, $request)
    {
        if (!isset($request['sort'])) {
            return $query->orderBy('id', 'desc');
        }

        $sort = $request['sort'];
        $order = $request['order'];

        return $query->orderBy($sort, $order);
    }

    protected static function booted()
    {
        // Trim and strip spaces from VIN before saving
        static::saving(function ($vehicle) {
            $vehicle->vin = preg_replace('/\s+/', '', trim($vehicle->vin));
        });


        static::created(function () {
            Artisan::call('cache:clear');
        });

        static::updated(function () {
            Artisan::call('cache:clear');
        });

        static::deleted(function ($vehicle) {
            Artisan::call('cache:clear');
            $vehicle->metas()->forceDelete();
        });
    }
}
