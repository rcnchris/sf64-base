<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    public string $type = 'light';
    public ?string $title = null;
    public ?string $content = null;
    public ?string $icon = null;
    public int $mt = 2;
    public int $mb = 2;
    public bool $dismissible = false;
    public bool $centered = true;
    public bool $bold = false;
    public bool $shadow = false;

    public function getClass(): string
    {
        return sprintf(
            'alert alert-%s %s %s %s mt-%d mb-%d',
            $this->type,
            $this->dismissible ? 'alert-dismissible fade show' : '',
            $this->centered ? 'text-center' : '',
            $this->shadow ? 'shadow' : '',
            $this->mt,
            $this->mb,
        );
    }
}
