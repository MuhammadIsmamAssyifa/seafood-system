<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
