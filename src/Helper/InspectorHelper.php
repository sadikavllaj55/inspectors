<?php

namespace App\Helper;
use App\Entity\Inspector;

class InspectorHelper
{
    public static function toArray(Inspector $inspector): array
    {
        return [
            'id' => $inspector->getId(),
            'name' => $inspector->getName(),
            'email' => $inspector->getEmail(),
            'timezone' => $inspector->getTimezone()->value
        ];
    }
}
