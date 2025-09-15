<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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

     protected static function booted(): void
    {
        // غيّر اسم القرص إذا كنت تستخدم قرصاً مخصصاً (مثلاً 'services')
        $disk = 'public';

        // لا تفعل شيئاً عند Soft Delete
        static::deleting(function (ServiceCategory $model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                // Soft delete: لا نحذف الملف
                return;
            }
        });

        // احذف الملف فقط عند Force Delete (مع SoftDeletes)
        static::forceDeleted(function (ServiceCategory $model) use ($disk) {
            if ($model->image) {
                Storage::disk($disk)->delete($model->image);
            }
        });

        // إن كان الموديل لا يستخدم SoftDeletes: احذف الملف بعد الحذف العادي
        static::deleted(function (ServiceCategory $model) use ($disk) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->image) {
                    Storage::disk($disk)->delete($model->image);
                }
            }
        });

        // عند استبدال الصورة في التعديل: احذف القديمة فقط إن تغيّرت القيمة
        static::updating(function (ServiceCategory $model) use ($disk) {
            if ($model->isDirty('image')) {
                $old = $model->getOriginal('image');
                if ($old && $old !== $model->image) {
                    Storage::disk($disk)->delete($old);
                }
            }
        });
    }
    public function services()
    {
        return $this->hasMany(Service::class, 'service_category_id_fk');
    }
}
