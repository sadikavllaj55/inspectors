<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\JobStatus;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: JobStatus::class)]
    private JobStatus $status = JobStatus::AVAILABLE;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $assessment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Inspector::class)]
    private ?Inspector $inspector = null;

    public function __construct()
    {
        $this->status = JobStatus::AVAILABLE;
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }

    public function setStatus(?JobStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getAssessment(): ?string
    {
        return $this->assessment;
    }

    public function setAssessment(?string $assessment): static
    {
        $this->assessment = $assessment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getInspector(): ?Inspector
    {
        return $this->inspector;
    }

    public function setInspector(?Inspector $inspector): static
    {
        $this->inspector = $inspector;

        return $this;
    }

    public function assignTo(Inspector $inspector, \DateTimeImmutable $scheduledAt): void
    {
        if ($this->status !== JobStatus::AVAILABLE) {
            throw new \DomainException('Only available jobs can be assigned.');
        }

        $this->inspector = $inspector;
        $this->scheduledAt = $scheduledAt;
        $this->status = JobStatus::ASSIGNED;
    }

    public function complete(string $assessment): void
    {
        if ($this->status !== JobStatus::ASSIGNED) {
            throw new \DomainException('Only assigned jobs can be completed.');
        }

        $this->assessment = $assessment;
        $this->completedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->status = JobStatus::COMPLETED;
    }

}
