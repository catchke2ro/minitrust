<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewDto;
use App\Entity\Review;

interface ReviewServiceInterface
{
    /**
     * Creates a new review from DTO data.
     */
    public function createReview(ReviewDto $dto): Review;

    /**
     * Persists the review to the database.
     */
    public function save(Review $review, bool $flush = true): void;
}
