<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait IntervalFieldTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startAt')]
    private ?\DateTimeImmutable $endAt = null;

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;
        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;
        return $this;
    }

    public function getIntervalStart(): \DateInterval
    {
        return $this->getStartAt()->diff($this->getEndAt());
    }

    public function getIntervalEnd(): \DateInterval
    {
        return $this->getEndAt()->diff($this->getStartAt());
    }

    public function isCurrent(): bool
    {
        $now = new \DateTimeImmutable();
        return ($now > $this->getStartAt() && $now < $this->getEndAt());
    }

    public function isPast(string $field = 'endAt'): bool
    {
        $method = sprintf('get%s', ucfirst($field));
        return $this->{$method}()->diff(new \DateTimeImmutable())->invert === 0;
    }

    public function isFuture(string $field = 'startAt'): bool
    {
        $method = sprintf('get%s', ucfirst($field));
        return $this->{$method}()->diff(new \DateTimeImmutable())->invert === 1;
    }

    public function getPeriode(?string $interval = 'P1D'): \DatePeriod
    {
        return new \DatePeriod($this->startAt, new \DateInterval($interval), $this->endAt);
    }
}
