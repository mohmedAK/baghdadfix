<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class Rating extends Model
{
    use UUIDTrait,SoftDeletes;

    protected $table = 'ratings';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // عندك created_at فقط

    protected $fillable = [
        'order_service_id_fk', 'rater_id_fk', 'technical_id_fk',
        'rate', 'comment', 'created_at',
    ];

    protected $casts = [
        'rate' => 'integer',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(OrderService::class, 'order_service_id_fk');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id_fk');
    }

    public function technical()
    {
        return $this->belongsTo(User::class, 'technical_id_fk');
    }
}
