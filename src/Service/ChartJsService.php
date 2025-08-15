<?php

namespace App\Service;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class ChartJsService
{
    private array $attributes = [
        'height' => 100,
    ];

    private array $options = [
        'plugins' => [
            'legend' => ['position' => 'top'],
        ],
    ];

    public function __construct(private readonly ChartBuilderInterface $chartBuilder) {}

    /**
     * Retourne un nouveau graphique
     * 
     * @param string $type Type du graphique (line, bar...)
     * @param ?array $attributes Attributs du graphique
     * @param ?array $options Options du graphique
     */
    public function make(string $type = 'bar', ?array $attributes = [], ?array $options = []): Chart
    {
        if (!in_array($type, $this->getTypes())) {
            $type = 'bar';
        }
        return $this->chartBuilder
            ->createChart($type)
            ->setAttributes(array_merge($this->attributes, $attributes))
            ->setOptions(array_merge($this->options, $options));
    }

    /**
     * Retourne la liste des types de graphiques
     */
    public function getTypes(): array
    {
        return [
            Chart::TYPE_BAR,
            Chart::TYPE_BUBBLE,
            Chart::TYPE_DOUGHNUT,
            Chart::TYPE_LINE,
            Chart::TYPE_PIE,
            Chart::TYPE_POLAR_AREA,
            Chart::TYPE_RADAR,
            Chart::TYPE_SCATTER,
        ];
    }
}
