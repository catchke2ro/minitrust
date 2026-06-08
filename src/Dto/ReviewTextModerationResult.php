<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class ReviewTextModerationResult
{
    public function __construct(
        public bool $containsOffensive,
        public bool $containsExplicit,
        public ?string $reason = null,
    ) {
    }
}
