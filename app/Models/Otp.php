<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
class Otp extends Model
{
    use UUIDTrait;

    protected $table = 'otp';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // عندك created_at فقط، بدون updated_at

    protected $fillable = ['user_id_fk', 'code', 'expire_at', ];

    protected $casts = [
        'expire_at' => 'datetime',
        'consumed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_fk');
    }
}
