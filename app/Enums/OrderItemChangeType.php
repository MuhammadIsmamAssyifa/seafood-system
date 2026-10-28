<?php

namespace App\Enums;

enum OrderItemChangeType: string
{
    case QuantityChange = 'quantity_change';

    case Cancel = 'cancel';

    case Replace = 'replace';
}
