<?php

namespace App\Enums;

enum TalepSortMode: string
{
    case Random = 'random';
    case MostVotes = 'most_votes';
    case MostSupported = 'most_supported';
    case LeastSupported = 'least_supported';
}
