<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
class Service extends Model
{
    use UUIDTrait;

    protected $table = 'services';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'image', 'service_category_id_fk', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id_fk');
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class, 'service_id_fk');
    }
}
