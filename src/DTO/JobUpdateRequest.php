<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Enum\JobStatus;

class JobUpdateRequest
{
    #[Assert\Length(max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 1000)]
    public ?string $description = null;

    public ?string $status = null;

    public function validateStatus(ExecutionContextInterface $context): void
    {
        if ($this->status === null) {
            return;
        }

        $allowed = array_map(fn($s) => $s->value, JobStatus::cases());

        if (!in_array($this->status, $allowed, true)) {
            $context->buildViolation('Invalid job status')
                ->atPath('status')
                ->addViolation();
        }
    }
}
