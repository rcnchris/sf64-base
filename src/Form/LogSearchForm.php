<?php

namespace App\Form;

use App\Entity\{Log, User};
use App\Form\Type\DateRangeType;
use App\Model\LogSearchModel;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextType};
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LogSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'label' => 'Message',
                'attr' => ['placeholder' => 'Message'],
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add('users', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'autocomplete' => true,
                'multiple' => true,
                'label' => false,
                'attr' => ['placeholder' => 'Users'],
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')->innerJoin('u.logs', 'l');
                }
            ])
            ->add('levels', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'choices' => array_flip(Log::LEVELS),
                'choice_label' => function ($level, $name) {
                    return sprintf('%s - %s', $level, $name);
                },
                'multiple' => true,
                'autocomplete' => true,
                'attr' => ['placeholder' => 'Levels']
            ])
            ->add('daterange', DateRangeType::class, [
                'mapped' => true,
                'extra_options' => ['range' => 'search_past'],
                'input_group' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LogSearchModel::class,
            'method' => 'get',
            'csrf_protection' => false,
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
