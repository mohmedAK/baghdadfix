<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
class OrderService extends Model
{
    use UUIDTrait;

    protected $table = 'order_services';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id_fk', 'service_id_fk',
        'technical_id_fk', 'assigned_by_admin_id_fk', 'assigned_at', 'assignment_note',
        'state_id_fk', 'area_id_fk', 'gps_lat', 'gps_lng',
        'admin_initial_price', 'admin_initial_by_id_fk', 'admin_initial_at', 'admin_initial_note',
        'final_price',
        'description', 'status', 'submit', 'image', 'video',
        'created_at', 'updated_at',
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
    ];

    // علاقات
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id_fk');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technical_id_fk');
    }

    public function assignedByAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id_fk');
    }

    public function adminInitialBy()
    {
        return $this->belongsTo(User::class, 'admin_initial_by_id_fk');
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

    public function rating()
    {
        return $this->hasOne(Rating::class, 'order_service_id_fk');
    }
}
