<?php

namespace App\Entity;

use App\Repository\InspectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Timezone;

#[ORM\Entity(repositoryClass: InspectorRepository::class)]
class Inspector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(enumType: Timezone::class)]
    private ?Timezone $timezone = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;


    /**
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'inspector')]
    private Collection $jobs;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTimezone(): ?Timezone
    {
        return $this->timezone;
    }

    public function setTimezone(Timezone $timezone): static
    {
        $this->timezone = $timezone;

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

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setInspector($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            if ($job->getInspector() === $this) {
                $job->setInspector(null);
            }
        }

        return $this;
    }
}
