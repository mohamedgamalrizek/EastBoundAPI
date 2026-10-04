<?php

namespace App\Enums;

enum TodoStatus: string
{
    case   PENDING     = 'pending';
    case   PROCESSING  = 'processing';
    case   COMPLETED   = 'completed';
}
