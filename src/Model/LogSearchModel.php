<?php

namespace App\Model;

final class LogSearchModel
{
    private ?string $message = null;
    private array $users = [];
    private array $levels = [];
    private ?string $daterange = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function getLevels(): array
    {
        return $this->levels;
    }

    public function setLevels(array $levels): void
    {
        $this->levels = $levels;
    }

    public function getDaterange(): ?string
    {
        return $this->daterange;
    }

    public function setDaterange(?string $daterange): void
    {
        $this->daterange = $daterange;
    }
}
