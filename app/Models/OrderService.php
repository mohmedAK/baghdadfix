<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderService extends Model
{
    use UUIDTrait, SoftDeletes;

    protected $table = 'order_services';
    protected $keyType = 'string';
    public $incrementing = false;



    protected $fillable = [
        // من الزبون
        'customer_id_fk',
        'service_id_fk',
        'state_id_fk',
        'area_id_fk',
        'gps_lat',
        'gps_lng',
        'description',
        'status',
        'submit',

        // تعيين الفني
        'technical_id_fk',
        'assigned_by_admin_id_fk',
        'assigned_at',
        'assignment_note',

        // التسعير الإداري
        'admin_initial_price',
        'admin_initial_by_id_fk',
        'admin_initial_at',
        'admin_initial_note',

        // (الجديدة) من الفني والزبون
        'technician_quote_price',
        'technician_quote_note',
        'technician_quote_at',
        'customer_decided_at',

        // سعر نهائي (اختياري)
        'final_price',
    ];

    public $timestamps = false; // عندك created_at/updated_at أعمدة يدوية

    protected $casts = [
        'assigned_at' => 'datetime',
        'admin_initial_price' => 'decimal:2',
        'admin_initial_at' => 'datetime',
        'final_price' => 'decimal:2',
        'gps_lat' => 'decimal:6',
        'gps_lng' => 'decimal:6',
        'submit' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => OrderStatus::class,

        'technician_quote_price' => 'decimal:2',
        'technician_quote_at'    => 'datetime',
        'customer_decided_at'    => 'datetime',
    ];

    // علاقات أساسية
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id_fk');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id_fk');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id_fk');
    }
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id_fk');
    }

    // علاقات إدارية
    public function technical()
    {
        return $this->belongsTo(User::class, 'technical_id_fk');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id_fk');
    }
    public function adminInitialBy()
    {
        return $this->belongsTo(User::class, 'admin_initial_by_id_fk');
    }

    // ميديا متعددة
    public function media()
    {
        return $this->hasMany(OrderServiceMedia::class, 'order_service_id_fk')->orderBy('sort_order');
    }
    public function images()
    {
        return $this->media()->where('type', 'image');
    }
    public function videos()
    {
        return $this->media()->where('type', 'video');
    }

    // تقييمات وكوبونات
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'order_service_id_fk');
    }
    public function usedCoupons()
    {
        return $this->hasMany(UsedCoupon::class, 'order_service_id_fk');
    }


    ///////////////////////////////////////////////////////////////////

    // علاقات






    public function rating()
    {
        return $this->hasOne(Rating::class, 'order_service_id_fk');
    }
}
