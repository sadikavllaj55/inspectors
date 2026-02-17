<?php

namespace App\Helper;

class DtoHelper
{
    public static function fill(object $dto, ?array $data): void
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Request body must be a valid JSON object');
        }
        $allowed = array_keys(get_object_vars($dto));

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                throw new \InvalidArgumentException("Unknown field: $key");
            }
            $dto->$key = $value;
        }
    }
}
