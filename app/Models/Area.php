<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
class Area extends Model
{
    use UUIDTrait;

    protected $table = 'areas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['state_id_fk', 'name', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id_fk');
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class, 'area_id_fk');
    }
}
