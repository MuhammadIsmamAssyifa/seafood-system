<?php

namespace App\Enums;

enum OrderItemChangeStatus: string
{
    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';
}
