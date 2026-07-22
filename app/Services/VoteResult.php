<?php

namespace App\Services;

enum VoteResult: string
{
    case Cast = 'cast';
    case ToggledOff = 'toggled_off';
    case Switched = 'switched';
}
