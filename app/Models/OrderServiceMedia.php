<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;


class OrderServiceMedia extends Model
{
    use UUIDTrait, SoftDeletes;

    protected $table = 'order_service_media';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_service_id_fk', 'type', 'file_path', 'disk', 'mime',
        'size', 'sort_order', 'uploaded_by_user_id_fk', 'duration_seconds',
    ];

    protected static function booted(): void
    {
        // لا تفعل شيئًا في Soft Delete
        static::deleting(function (self $model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return; // soft delete
            }

            // لو ما عندك SoftDeletes لأي سبب، هذا الفرع سيعمل عند delete العادي:
            if (! in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->path) {
                    Storage::disk($model->disk ?? 'public')->delete($model->path);
                }
            }
        });

        // احذف الملف فقط عند Force Delete
        static::forceDeleted(function (self $model) {
            if ($model->path) {
                Storage::disk($model->disk ?? 'public')->delete($model->path);
            }
        });

        // عند تحديث المسار: احذف القديم
        static::updating(function (self $model) {
            if ($model->isDirty('path')) {
                $old = $model->getOriginal('path');
                if ($old && $old !== $model->path) {
                    Storage::disk($model->disk ?? 'public')->delete($old);
                }
            }
        });
    }

    public function order(){ return $this->belongsTo(OrderService::class, 'order_service_id_fk'); }
    public function uploader(){ return $this->belongsTo(User::class, 'uploaded_by_user_id_fk'); }
}
