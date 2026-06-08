<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ReviewRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/companies')]
final class CompanyController extends AbstractController
{
    public function __construct(
        protected readonly ReviewRepositoryInterface $reviewRepository,
    ) {
    }

    #[Route('', name: 'company_list', methods: ['GET'])]
    public function list(): Response
    {
        $stats = $this->reviewRepository->getCompanyStats();

        return $this->render('company/list.html.twig', [
            'stats' => $stats,
        ]);
    }
}

