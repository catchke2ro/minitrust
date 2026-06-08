<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Service\ReviewTextModerationServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ReviewTextSafetyValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ReviewTextModerationServiceInterface $moderationService,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReviewTextSafety) {
            throw new UnexpectedTypeException($constraint, ReviewTextSafety::class);
        }

        if (null === $value || '' === trim((string) $value)) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            $result = $this->moderationService->analyze($value);
        } catch (\Throwable) {
            $this->context
                ->buildViolation($constraint->serviceUnavailableMessage)
                ->addViolation();

            return;
        }

        if ($result->containsOffensive) {
            $this->context
                ->buildViolation($constraint->offensiveMessage)
                ->setParameter('%reason%', $result->reason ?? '')
                ->addViolation();
        }

        if ($result->containsExplicit) {
            $this->context
                ->buildViolation($constraint->explicitMessage)
                ->setParameter('%reason%', $result->reason ?? '')
                ->addViolation();
        }
    }
}
