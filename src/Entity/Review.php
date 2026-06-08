<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Rating;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'review')]
#[ORM\Index(name: 'idx_review_company_name', columns: ['company_name'])]
#[ORM\Index(name: 'idx_review_rating', columns: ['rating'])]
#[ORM\Index(name: 'idx_review_created_at', columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public private(set) int $id;

    #[ORM\Column(length: 255, nullable: false)]
    public string $companyName;

    #[ORM\Column(type: Types::SMALLINT, nullable: false, enumType: Rating::class, options: ['unsigned' => true])]
    public Rating $rating;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    public ?string $reviewText = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $authorEmail = null;

    #[ORM\Column(
        type: 'carbon_immutable',
        precision: 0,
        options: ['default' => 'CURRENT_TIMESTAMP'],
    )]
    public CarbonImmutable $createdAt;

    #[ORM\Column(
        type: 'carbon_immutable',
        precision: 0,
        options: ['default' => 'CURRENT_TIMESTAMP'],
    )]
    public CarbonImmutable $updatedAt;
}
