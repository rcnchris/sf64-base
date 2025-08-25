<?php

namespace App\Form\Type;

use Symfony\Component\Form\{AbstractType, FormInterface, FormView};
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\{Options, OptionsResolver};
use function Symfony\Component\String\u;

final class DateRangeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Valeurs par défaut du champ
        $resolver
            ->setDefaults([
                'required' => false,
                'mapped' => false,
                'input_group' => true,
                'label' => 'Du - au',
            ])
            ->setAllowedTypes('input_group', ['null', 'bool'])
            ->setNormalizer('row_attr', function (Options $options): array {
                $inputGroup = $options->offsetGet('input_group');
                return $inputGroup === true ? ['class' => 'input-group mb-3'] : [];
            });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = [
            'class' => 'form-control',
            'data-controller' => 'daterange',
            'data-start' => array_key_exists('start', $options['extra_options']) ? $options['extra_options']['start'] : null,
            'data-end' => array_key_exists('start', $options['extra_options']) ? $options['extra_options']['end'] : null,
            'data-format' => array_key_exists('format', $options['extra_options']) ? $options['extra_options']['format'] : 'DD/MM/Y HH:mm',
            'data-locale' => u(\Locale::getDefault())->replace('_', '-')->lower(),
            'data-single' => array_key_exists('single', $options['extra_options']) ? $options['extra_options']['single'] : false,
            'data-time' => array_key_exists('time', $options['extra_options']) ? $options['extra_options']['time'] : true,
            'data-range' => array_key_exists('range', $options['extra_options']) ? $options['extra_options']['range'] : null,
        ];
        if ($options['label'] === false) {
            $view->vars['attr']['placeholder'] = 'Du - au';
        }
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
