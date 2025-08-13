<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, EmailType, PasswordType, TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{IsTrue, Length, NotBlank};

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('pseudo', TextType::class)
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label_attr' => ['class' => 'checkbox-switch'],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Password',
                'toggle' => true,
                'hidden_label' => 'Masquer',
                'visible_label' => 'Afficher',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
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
