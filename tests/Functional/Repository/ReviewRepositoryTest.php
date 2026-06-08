<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Dto\CompanyStatsDto;
use App\Entity\Review;
use App\Enum\Rating;
use App\Repository\ReviewRepositoryInterface;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private ReviewRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(ReviewRepositoryInterface::class);

        // Delete all reviews before each test to ensure a clean state.
        $this->em->createQuery('DELETE FROM App\Entity\Review r')->execute();
        $this->em->clear();
    }

    protected function tearDown(): void
    {
        // Delete all reviews after each test to ensure a clean state for the next test.
        $this->em->createQuery('DELETE FROM App\Entity\Review r')->execute();
        $this->em->clear();
        parent::tearDown();
    }

    /**
     * Test that the average rating is calculated correctly when there is only one review for a company.
     */
    public function testAverageRatingWithSingleReview(): void
    {
        $this->createAndSaveReview('Acme Corp', Rating::R4);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(1, $stats);
        self::assertEqualsWithDelta(4.0, $stats[0]->averageRating, 0.001);
    }

    /**
     * Test that the average rating is calculated correctly when there are multiple reviews for a company.
     */
    public function testAverageRatingWithMultipleReviews(): void
    {
        $this->createAndSaveReview('Acme Corp', Rating::R5);
        $this->createAndSaveReview('Acme Corp', Rating::R3);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(1, $stats);
        self::assertEqualsWithDelta(4.0, $stats[0]->averageRating, 0.001);
    }

    /**
     * Test that the review count is correct when there are multiple reviews for a company.
     */
    public function testReviewCountIsCorrect(): void
    {
        $this->createAndSaveReview('Beta Ltd', Rating::R2);
        $this->createAndSaveReview('Beta Ltd', Rating::R4);
        $this->createAndSaveReview('Beta Ltd', Rating::R3);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(1, $stats);
        self::assertSame(3, $stats[0]->reviewCount);
        self::assertEqualsWithDelta(3.0, $stats[0]->averageRating, 0.001);
    }

    /**
     * Test that the company name is correctly returned in the DTO.
     */
    public function testGetCompanyStatsReturnsDtoObjects(): void
    {
        $this->createAndSaveReview('TestCo', Rating::R5);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(1, $stats);
        self::assertInstanceOf(CompanyStatsDto::class, $stats[0]);
        self::assertIsFloat($stats[0]->averageRating);
        self::assertIsInt($stats[0]->reviewCount);
        self::assertIsString($stats[0]->companyName);
    }

    /**
     * Test that the companies are ordered by average rating in descending order.
     */
    public function testCompaniesAreOrderedByAverageRatingDescending(): void
    {
        // Szándékosan fordított sorrendben szúrjuk be, hogy a sorrend ne véletlenszerű legyen
        $this->createAndSaveReview('Gamma', Rating::R1);
        $this->createAndSaveReview('Beta', Rating::R3);
        $this->createAndSaveReview('Alpha', Rating::R5);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(3, $stats);
        self::assertEqualsWithDelta(5.0, $stats[0]->averageRating, 0.001, 'Első helyen a legmagasabb átlagú cégnek kell állnia');
        self::assertEqualsWithDelta(3.0, $stats[1]->averageRating, 0.001);
        self::assertEqualsWithDelta(1.0, $stats[2]->averageRating, 0.001, 'Utolsó helyen a legalacsonyabb átlagú cégnek kell állnia');
    }

    /**
     * Test that the ordering of companies is based on the aggregated average rating when there are multiple reviews per company.
     */
    public function testOrderingIsBasedOnAggregatedAverage(): void
    {
        $this->createAndSaveReview('MidCo', Rating::R4);
        $this->createAndSaveReview('MidCo', Rating::R2);
        $this->createAndSaveReview('LowCo', Rating::R1);
        $this->createAndSaveReview('LowCo', Rating::R2);
        $this->createAndSaveReview('TopCo', Rating::R5);
        $this->createAndSaveReview('TopCo', Rating::R5);

        $stats = $this->repository->getCompanyStats();

        self::assertCount(3, $stats);
        self::assertEqualsWithDelta(5.0, $stats[0]->averageRating, 0.001, '"TopCo" legyen az első');
        self::assertEqualsWithDelta(3.0, $stats[1]->averageRating, 0.001, '"MidCo" legyen a második');
        self::assertEqualsWithDelta(1.5, $stats[2]->averageRating, 0.001, '"LowCo" legyen az utolsó');
    }

    /**
     * Test that the getCompanyStats method returns an empty array when there are no reviews in the database.
     */
    public function testGetCompanyStatsReturnsEmptyArrayWhenNoReviews(): void
    {
        $stats = $this->repository->getCompanyStats();

        self::assertIsArray($stats);
        self::assertEmpty($stats);
    }

    private function createAndSaveReview(string $companyName, Rating $rating): void
    {
        $review = new Review();
        $review->companyName = $companyName;
        $review->rating = $rating;
        $review->createdAt = CarbonImmutable::now();
        $review->updatedAt = CarbonImmutable::now();

        $this->em->persist($review);
        $this->em->flush();
    }
}
