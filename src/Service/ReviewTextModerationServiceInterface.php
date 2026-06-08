<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ReviewTextModerationResult;

interface ReviewTextModerationServiceInterface
{
    public function analyze(string $text): ReviewTextModerationResult;
}

