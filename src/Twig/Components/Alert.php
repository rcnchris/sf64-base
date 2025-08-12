<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    const TYPES = [
        'primary' => 'info-circle',
        'secondary' => 'info-circle',
        'success' => 'success',
        'danger' => 'danger',
        'warning' => 'warning',
        'info' => 'info-circle-f',
        'light' => 'info-circle',
        'dark' => 'info-circle',
    ];

    public ?string $title = null;
    public ?string $content = null;
    public ?string $type = null;
    public ?string $icon = null;
    public int $mt = 2;
    public int $mb = 2;
    public bool $dismissible = false;
    public bool $centered = true;
    public bool $bold = false;
    public bool $shadow = false;

    public function getType(): string
    {
        if (!array_key_exists($this->type, self::TYPES)) {
            return 'light';
        }
        return $this->type;
    }

    public function getIcon(): string 
    {
        return self::TYPES[$this->type];
    }

    public function getClass(): string
    {
        return sprintf(
            'alert alert-%s %s %s %s mt-%d mb-%d',
            $this->getType(),
            $this->dismissible ? 'alert-dismissible fade show' : '',
            $this->centered ? 'text-center' : '',
            $this->shadow ? 'shadow' : '',
            $this->mt,
            $this->mb,
        );
    }
}
