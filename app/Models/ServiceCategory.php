<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class ServiceCategory extends Model
{
    use UUIDTrait,SoftDeletes;

    protected $table = 'service_categories';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'image', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'service_category_id_fk');
    }
}
