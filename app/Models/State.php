<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class State extends Model
{
    use UUIDTrait,SoftDeletes;

    protected $table = 'states';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class, 'state_id_fk');
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class, 'state_id_fk');
    }
}
