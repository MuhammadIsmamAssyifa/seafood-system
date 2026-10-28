<?php

namespace App\Enums;

enum AdjustmentStatus: string
{
    case PendingCustomer = 'pending_customer';

    case Approved = 'approved';

    case Rejected = 'rejected';
}
