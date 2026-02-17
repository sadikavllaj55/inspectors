<?php

namespace App\Enum;

enum JobStatus: string
{
    case AVAILABLE = 'available';
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
}
