<?php

namespace App\Form\Type;

use App\Security\Antispam\ChallengeInterface;
use App\Security\Antispam\Puzzle\PuzzleChallenge;
use App\Validator\Challenge;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface, FormInterface, FormView};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly ChallengeInterface $challenge,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'label' => false,
            'help' => 'solve the puzzle',
            'constraints' => [
                new NotBlank(),
                new Challenge()
            ],
            'route' => 'security.captcha'
        ]);
        parent::configureOptions($resolver);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('challenge', HiddenType::class, ['attr' => ['class' => 'captcha-challenge']])
            ->add('answer', HiddenType::class, ['attr' => ['class' => 'captcha-answer']]);
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $key = $this->challenge->generateKey();
        $view->vars['attr'] = [
            'class' => 'rounded mx-auto d-block',
            'width' => PuzzleChallenge::WIDTH,
            'height' => PuzzleChallenge::HEIGHT,
            'piece-width' => PuzzleChallenge::PIECE_WIDTH,
            'piece-height' => PuzzleChallenge::PIECE_HEIGHT,
            'src' => $this->urlGenerator->generate($options['route'], ['challenge' => $key]),
            'data-controller' => 'captcha'
        ];
        $view->vars['challenge'] = $key;
        parent::buildView($view, $form, $options);
    }
}
