<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\ReviewDto;
use App\Entity\Review;
use App\Enum\Rating;
use App\Repository\ReviewRepositoryInterface;
use App\Service\ReviewService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ReviewServiceTest extends TestCase
{
    private ReviewService $service;

    protected function setUp(): void
    {
        $repositoryMock = $this->createStub(ReviewRepositoryInterface::class);
        $this->service = new ReviewService($repositoryMock);
    }

    /**
     * Test if createReview correctly maps all fields from the ReviewDto to the Review entity.
     */
    public function testCreateReviewMapsAllFieldsFromDto(): void
    {
        // Fixed time for testing timestamps
        $testNow = Carbon::create(2024, 6, 1, 12, 0, 0);
        Carbon::setTestNow($testNow);

        $dto = new ReviewDto();
        $dto->companyName = 'Test Company';
        $dto->rating = Rating::R4;
        $dto->reviewText = 'Great service and quality!';
        $dto->authorEmail = 'user@example.com';

        $review = $this->service->createReview($dto);

        self::assertInstanceOf(Review::class, $review);
        self::assertSame($dto->companyName, $review->companyName);
        self::assertSame($dto->rating, $review->rating);
        self::assertSame($dto->reviewText, $review->reviewText);
        self::assertSame($dto->authorEmail, $review->authorEmail);

        // Testing timestamps are set to the fixed time
        self::assertEquals($testNow, $review->createdAt->toDateTimeImmutable());
        self::assertEquals($testNow, $review->updatedAt->toDateTimeImmutable());
    }

    /**
     * Test if createReview throws a LogicException when the companyName is null, as it is a required field.
     */
    public function testCreateReviewThrowsWhenCompanyNameIsNull(): void
    {
        $dto = new ReviewDto();
        $dto->companyName = null;
        $dto->rating = Rating::R4; // Another not nullable field - must be st

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Company name cannot be null');

        $this->service->createReview($dto);
    }

    /**
     * Test if createReview throws a LogicException when the rating is null, as it is a required field.
     */
    public function testCreateReviewThrowsWhenRatingIsNull(): void
    {
        $dto = new ReviewDto();
        $dto->companyName = 'Some Co';
        $dto->rating = null;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Rating cannot be null');

        $this->service->createReview($dto);
    }

    /**
     * Test if createReview allows optional fields (reviewText and authorEmail) to be null without throwing exceptions.
     */
    public function testCreateReviewAllowsNullOptionalFields(): void
    {
        $dto = new ReviewDto();
        $dto->companyName = 'Minimal Co';
        $dto->rating = Rating::R1;

        $review = $this->service->createReview($dto);

        self::assertNull($review->reviewText);
        self::assertNull($review->authorEmail);
    }
}
