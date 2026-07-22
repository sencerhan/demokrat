<?php

namespace App\Enums;

enum TalepStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
