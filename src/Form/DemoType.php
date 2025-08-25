<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Type\DateRangeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DemoType extends AbstractType
{
    public function __construct(
        #[Autowire('%app.timezone%')]
        private readonly string $timezone
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $start = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));
        $end = $start->modify('+1 hours');
        $builder
            ->add('daterange', DateRangeType::class, [
                'mapped' => true,
                'extra_options' => [
                    'start' => $start->format('d/m/Y H:i'),
                    'end' => $end->format('d/m/Y H:i'),
                ]
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'multiple' => true,
                'label' => 'Sélectionner un ou plusieurs utilisateurs'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }

    /**
     * Permet de supprimer le nom du formulaire dans les inputs et paramètres de l'uri
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
