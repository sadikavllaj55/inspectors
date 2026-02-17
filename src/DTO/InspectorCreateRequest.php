<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Enum\Timezone;

class InspectorCreateRequest
{
    #[Assert\NotBlank(message: "Name is required")]
    #[Assert\Length(max: 255, maxMessage: "Name cannot exceed 255 characters")]
    public ?string $name = null;

    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: "Invalid email format")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Timezone is required")]
    public ?string $timezone = null;

    public function validateTimezone(ExecutionContextInterface $context): void
    {
        if (empty($this->timezone)) {
            $context->buildViolation('Timezone cannot be empty')
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
