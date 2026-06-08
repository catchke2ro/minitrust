<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\ReviewDto;
use App\Enum\Rating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'form.review.company_name.label',
                'required' => true,
            ])
            ->add('rating', EnumType::class, [
                'label' => 'form.review.rating.label',
                'class' => Rating::class,
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'star_rating',
            ])
            ->add('reviewText', TextareaType::class, [
                'label' => 'form.review.review_text.label',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'form.review.author_email.label',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewDto::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'review';
    }
}
