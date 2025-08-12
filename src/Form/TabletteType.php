<?php

namespace App\Form;

use App\Entity\Tablette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TabletteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('icon')
            ->add('color', ColorType::class)
            ->add('description')
            ->add('parent', EntityType::class, [
                'class' => Tablette::class,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tablette::class,
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
