<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Otp extends Model
{
   use HasFactory,SoftDeletes;
    protected $table = 'otps';
    protected $fillable = ['to_phone', 'message', 'status', 'request_id'];


}
