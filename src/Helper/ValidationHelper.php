<?php

namespace App\Helper;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationHelper
{
    public static function formatErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $errors;
    }
}
