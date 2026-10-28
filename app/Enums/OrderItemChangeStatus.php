<?php

namespace App\Enums;

enum OrderItemChangeStatus: string
{
    case PendingCustomer = 'pending_customer';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
