<?php
namespace App\Enums;

enum PriceType: string
{
    case AdminInitial     = 'admin_initial';
    case TechnicianQuote  = 'technician_quote';
    case Final            = 'final';
}
