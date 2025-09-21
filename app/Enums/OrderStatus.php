<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Created                   = 'created';
    case AdminEstimated            = 'admin_estimated';
    case Assigned                  = 'assigned';
    case Inspecting                = 'inspecting';
    case QuotePending              = 'quote_pending';
    case AwaitingCustomerApproval  = 'awaiting_customer_approval';
    case Approved                  = 'approved';
    case Rejected                  = 'rejected';
    case InProgress                = 'in_progress';
    case Completed                 = 'completed';
    case Canceled                  = 'canceled';

    public static function options(): array
    {
        return array_combine(
            array_map(fn($c) => $c->value, self::cases()),
            array_map(fn($c) => ucwords(str_replace('_', ' ', $c->value)), self::cases())
        );
    }

    public static function color(self|string $value): string
    {
        $v = $value instanceof self ? $value->value : $value;

        return match ($v) {
            self::Created->value, self::QuotePending->value => 'gray',
            self::AdminEstimated->value                     => 'warning',
            self::Assigned->value, self::Inspecting->value  => 'info',
            self::AwaitingCustomerApproval->value           => 'purple',
            self::Approved->value                           => 'success',
            self::Rejected->value, self::Canceled->value    => 'danger',
            self::InProgress->value                         => 'primary',
            self::Completed->value                          => 'success',
            default                                         => 'gray',
        };
    }
}
