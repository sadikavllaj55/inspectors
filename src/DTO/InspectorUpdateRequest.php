<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Enum\Timezone;

class InspectorUpdateRequest
{
    #[Assert\Length(max: 255, maxMessage: "Name cannot exceed 255 characters")]
    public ?string $name = null;

    #[Assert\Email(message: "Invalid email format")]
    public ?string $email = null;

    public ?string $timezone = null;

    #[Assert\Callback]
    public function validateTimezone(ExecutionContextInterface $context): void
    {
        if ($this->timezone === null) {
            return;
        }

        if (trim($this->timezone) === '') {
            $context->buildViolation('Timezone cannot be blank')
                ->atPath('timezone')
                ->addViolation();
            return;
        }

        $allowed = array_map(fn($tz) => $tz->value, Timezone::cases());

        if (!in_array($this->timezone, $allowed, true)) {
            $context->buildViolation('Invalid timezone')
                ->atPath('timezone')
                ->addViolation();
        }
    }
}
