<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Impersonate;

    public $rate = 89;

    public $default_chat_id = '-1001456845228';

    public function canImpersonate()
    {
        // For example
        return $this->role == 'admin';
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'parent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function metas()
    {
        return $this->hasMany(RunList::class);
    }

    public function scopeFilters($query, $request)
    {
        if (isset($request['status']) && $request['status'] != -100) {
            $search = $request['status'];
            $query->where('status', $search);
        }

        if (isset($request['daterange'])) {
            $dateRange = explode(' - ', $request['daterange']);
            $query->whereBetween('orders.created_at', [Carbon::parse($dateRange[0])->format('Y-m-d'), Carbon::parse($dateRange[1])->format('Y-m-d')]);
        }

        if (isset($request['user']) && $request['user'] != -100) {
            $query->where('email', '=', $request['user']);
        }
    }

    public const USER_ROLES = [
        'admin' => 'Admin',
        'vehicle_manager' => 'Vehicle Manager',
        'yard_manager' => 'Yard Manager',
        'ndtc_manager' => 'NDTC Manager',
        'viewer' => 'Viewer',
    ];
}