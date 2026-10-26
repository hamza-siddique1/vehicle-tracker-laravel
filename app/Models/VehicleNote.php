<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class VehicleNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function () {
            Artisan::call('cache:clear');
        });

        static::updated(function () {
            Artisan::call('cache:clear');
        });

        static::deleted(function () {
            Artisan::call('cache:clear');
        });
    }
}
