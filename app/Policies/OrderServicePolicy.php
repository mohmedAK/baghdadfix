<?php
// app/Policies/OrderServicePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\OrderService;
use App\Policies\Concerns\BlocksDeleteForEditors;

class OrderServicePolicy
{
    use BlocksDeleteForEditors;

    public function viewAny(User $user): bool
    {
        return in_array($user->role->value, ['admin','editor','technical','customer']);
    }

    public function view(User $user, OrderService $order): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role !== \App\Enums\UserRole::Customer; // example rule
    }

    public function update(User $user, OrderService $order): bool
    {
        return $user->role !== \App\Enums\UserRole::Customer; // example rule
    }
}
