<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\Rating;
use App\Validator\Constraint\ReviewTextSafety;
use Symfony\Component\Validator\Constraints as Assert;

final class ReviewDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public ?string $companyName = null;

    #[Assert\NotBlank(message: 'validation.review.rating.not_blank')]
    public ?Rating $rating = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[ReviewTextSafety]
    public ?string $reviewText = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $authorEmail = null;
}
