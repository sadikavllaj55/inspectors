<?php

namespace App\Helper;

use App\Enum\Timezone;
use InvalidArgumentException;

class EnumHelper
{
    public static function timezoneFromString(string $tz): Timezone
    {
        foreach (Timezone::cases() as $case) {
            if ($case->value === $tz) {
                return $case;
            }
        }

        throw new InvalidArgumentException('Invalid timezone value: ' . $tz);
    }
}
