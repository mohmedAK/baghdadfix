<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class UsedCoupon extends Model
{
    use UUIDTrait,SoftDeletes;

    protected $table = 'used_coupons';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'customer_id_fk', 'coupon_id_fk', 'order_service_id_fk', 'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id_fk');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id_fk');
    }

    public function order()
    {
        return $this->belongsTo(OrderService::class, 'order_service_id_fk');
    }
}
