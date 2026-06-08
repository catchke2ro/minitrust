<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class ReviewTextSafety extends Constraint
{
    public string $offensiveMessage = 'validation.review.review_text.contains_offensive';

    public string $explicitMessage = 'validation.review.review_text.contains_explicit';

    public string $serviceUnavailableMessage = 'validation.review.review_text.moderation_unavailable';

    public function validatedBy(): string
    {
        return ReviewTextSafetyValidator::class;
    }
}
