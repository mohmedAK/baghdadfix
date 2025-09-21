<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, UUIDTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'state',
        'area',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }


    public function scopeTechnicians($q)
    {
        return $q->where('role', UserRole::Technical);
    }
    public function scopeCustomers($q)
    {
        return $q->where('role', UserRole::Customer);
    }

    public function sendPasswordResetNotification($token): void
    {
        // عدّل 'admin' لو كان معرف اللوحة لديك مختلفًا
        $url = url(route('filament.admin.auth.password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        $notification = new ResetPasswordNotification($token);

        // في إصدارات Laravel الحديثة استخدم createUrlUsing (أنسب)،
        // لكن إن كان المتغير متاحًا لديك فسيعمل السطر التالي:
        $notification->url = $url;

        $this->notify($notification);
    }


    // علاقات مفيدة
    // public function otps()
    // {
    //     return $this->hasMany(Otp::class, 'user_id_fk');
    // }

    // طلبات أنشأها كمستخدم زبون
    public function customerOrders()
    {
        return $this->hasMany(OrderService::class, 'customer_id_fk');
    }

    // طلبات مُعيّن عليها كفنّي
    public function assignedOrders()
    {
        return $this->hasMany(OrderService::class, 'technical_id_fk');
    }

    // طلبات قام بتعيين فنّي لها كأدمن
    public function assignedAsAdmin()
    {
        return $this->hasMany(OrderService::class, 'assigned_by_admin_id_fk');
    }

    // تقييمات كتبها هذا المستخدم
    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rater_id_fk');
    }

    // تقييمات استلمها (لو كان فنّي)
    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'technical_id_fk');
    }
}
