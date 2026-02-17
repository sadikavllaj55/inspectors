<?php

namespace App\Helper;

use App\Entity\Job;

class JobHelper
{
    public static function toArray(Job $job): array
    {
        return [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'status' => $job->getStatus()->value,
            'createdAt' => $job->getCreatedAt()->format('Y-m-d H:i:s'),
            'assessment' => $job->getAssessment(),
            'scheduledAt' => $job->getScheduledAt()?->format('Y-m-d H:i:s'),

            'inspector' => $job->getInspector() ? [
                'id' => $job->getInspector()->getId(),
                'name' => $job->getInspector()->getName(),
                'email' => $job->getInspector()->getEmail(),
                'timezone' => $job->getInspector()->getTimezone()->name,
            ] : null,
        ];
    }
}
