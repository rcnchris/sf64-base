<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{EmailType, TextareaType, TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'input-box form-ensurance-header-control'
                ],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'placeholder' => 'Pseudo',
                    'class' => 'input-box form-ensurance-header-control'
                ],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'Firstname',
                'attr' => [
                    'placeholder' => 'Firstname',
                    'class' => 'input-box form-ensurance-header-control'
                ],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'Lastname',
                'attr' => [
                    'placeholder' => 'Lastname',
                    'class' => 'input-box form-ensurance-header-control'
                ],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'attr' => [
                    'data-controller' => 'inputmask',
                    'data-type' => 'phone',
                    'class' => 'input-box form-ensurance-header-control'
                ],
                'label_html' => true,
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Description',
                    'class' => 'input-box form-ensurance-header-control'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
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
