<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class Coupon extends Model
{
    use UUIDTrait,SoftDeletes;

    protected $table = 'coupons';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['code', 'discount', 'is_active', 'starts_at', 'ends_at'];

    protected $casts = [
        'discount' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(UsedCoupon::class, 'coupon_id_fk');
    }
}
