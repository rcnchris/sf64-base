<?php

namespace App\Validator;

use App\Security\Antispam\ChallengeInterface;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};

/**
 * @codeCoverageIgnore
 */
class ChallengeValidator extends ConstraintValidator
{
    public function __construct(private readonly ChallengeInterface $challenge) {}

    /**
     * @param array{challenge: string, answer: string} $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var Challenge $constraint */
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->challenge->verify($value['challenge'], $value['answer'] ?? '')) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
