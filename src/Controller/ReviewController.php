<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ReviewDto;
use App\Form\ReviewType;
use App\Repository\ReviewRepositoryInterface;
use App\Service\ReviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/')]
final class ReviewController extends AbstractController
{
    public function __construct(
        protected readonly ReviewServiceInterface $reviewService,
        protected readonly ReviewRepositoryInterface $reviewRepository,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('', name: 'review_list', methods: ['GET'])]
    public function list(): Response
    {
        $reviews = $this->reviewRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('review/list.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/create', name: 'review_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $dto = new ReviewDto();
        $form = $this->createForm(ReviewType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $review = $this->reviewService->createReview($dto);
                $this->reviewService->save($review);

                $this->addFlash('success', $this->translator->trans('flash.review.saved'));

                return $this->redirectToRoute('review_show', ['id' => $review->id]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('flash.review.save_error', ['%error%' => $e->getMessage()]));
            }
        }

        return $this->render('review/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'review_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $review = $this->reviewRepository->find($id);

        if (!$review) {
            throw $this->createNotFoundException($this->translator->trans('flash.review.not_found'));
        }

        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }
}
