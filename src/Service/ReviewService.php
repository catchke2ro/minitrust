<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewDto;
use App\Entity\Review;
use App\Repository\ReviewRepositoryInterface;
use Carbon\CarbonImmutable;

final readonly class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
    ) {
    }

    public function createReview(ReviewDto $dto): Review
    {
        $review = new Review();
        $this->populateReviewFromDto($review, $dto);

        $now = CarbonImmutable::now();
        $review->createdAt = $now;
        $review->updatedAt = $now;

        return $review;
    }

    public function save(Review $review, bool $flush = true): void
    {
        $entityManager = $this->reviewRepository->getEntityManager();
        $entityManager->persist($review);

        if ($flush) {
            $entityManager->flush();
        }
    }

    private function populateReviewFromDto(Review $review, ReviewDto $dto): void
    {
        if (null === $dto->companyName) {
            throw new \LogicException('Company name cannot be null');
        }
        if (null === $dto->rating) {
            throw new \LogicException('Rating cannot be null');
        }

        $review->companyName = $dto->companyName;
        $review->rating = $dto->rating;
        $review->reviewText = $dto->reviewText;
        $review->authorEmail = $dto->authorEmail;
    }
}
