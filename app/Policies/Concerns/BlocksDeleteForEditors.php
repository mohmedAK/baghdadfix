<?php
namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait BlocksDeleteForEditors
{
    public function delete(User $user, $model): bool
    {
        // editor can NEVER delete
        if ($user->role === UserRole::Editor) {
            return false;
        }

        return true; // allow others; Gate::before already allows admin
    }

    public function forceDelete(User $user, $model): bool
    {
        return false; // Only admins should force delete; Gate::before handles admin
    }

    public function deleteAny(User $user): bool
    {
        return $user->role !== UserRole::Editor;
    }

    public function restore(User $user, $model): bool
    {
        return $user->role !== UserRole::Editor;
    }
}
