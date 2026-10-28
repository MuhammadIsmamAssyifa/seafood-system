<?php

namespace App\Enums;

enum OrderBatchStatus: string
{
    case Waiting = 'waiting';
    case Cooking = 'cooking';
    case Ready = 'ready';
    case Cancelled = 'cancelled';
    case Sent = 'sent';
    case Completed = 'completed';
}
