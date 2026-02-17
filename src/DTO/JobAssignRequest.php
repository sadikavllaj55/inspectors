<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class JobAssignRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public ?int $inspector_id = null;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: \DateTimeInterface::ATOM)]
    public ?string $scheduled_at = null;
}
