<?php

namespace App\Form;

use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
                'attr' => ['placeholder' => 'Email'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => ['placeholder' => 'Pseudo'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'Firstname',
                'attr' => ['placeholder' => 'Firstname'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'Lastname',
                'attr' => ['placeholder' => 'Lastname'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'attr' => [
                    'data-controller' => 'inputmask',
                    'data-type' => 'phone',
                ],
                'label_html' => true,
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('description', CKEditorType::class, [
                'required' => false,
                'label' => false,
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
