<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\CompanyStatsDto;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @implements ReviewRepositoryInterface
 */
class ReviewRepository extends ServiceEntityRepository implements ReviewRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findReviewsByCompanyName(string $companyName, ?int $exceptId = null): array
    {
        // Create the company name without spaces and special characters for comparison
        $companyName = preg_replace('/[^a-z0-9]/i', '', $companyName);

        // Find reviews with the same company name, ignoring spaces and special characters
        $qb = $this->createQueryBuilder('r')
            ->where("REGEXP_REPLACE(r.companyName, '[^[:alnum:]]', '') = :companyName")
            ->setParameter('companyName', $companyName)
            ->orderBy('r.createdAt', 'DESC');

        if (null !== $exceptId) {
            // Exclude the review with the specified ID
            $qb->andWhere('r.id != :id')->setParameter('id', $exceptId);
        }

        return $qb->getQuery()->getResult();
    }

    public function getCompanyStats(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select(
                'COUNT(r.id) AS reviewCount',
                'AVG(r.rating) AS averageRating',
                "REGEXP_REPLACE(r.companyName, '[^[:alnum:]]', '') AS companyName"
            )
            ->groupBy('companyName')
            ->orderBy('averageRating', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): CompanyStatsDto => new CompanyStatsDto(
                companyName: $row['companyName'],
                reviewCount: (int) $row['reviewCount'],
                averageRating: (float) $row['averageRating'],
            ),
            $rows
        );
    }
}
