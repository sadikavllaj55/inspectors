<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class JobCompleteRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public ?string $assessment = null;
}
