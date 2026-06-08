<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\CompanyStatsDto;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

interface ReviewRepositoryInterface extends ServiceEntityRepositoryInterface
{
    /**
     * Find other reviews for the same company (matched by slugified company name), excluding the given review.
     *
     * @return Review[]
     */
    public function findReviewsByCompanyName(string $companyName, ?int $exceptId = null): array;

    /**
     * Return company stats: review count and average rating, ordered by average rating descending.
     *
     * @return CompanyStatsDto[]
     */
    public function getCompanyStats(): array;
}
