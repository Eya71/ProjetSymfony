<?php

namespace App\Form;

use App\Entity\DealRequest;
use App\Entity\Review;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_deal', EntityType::class, [
                'class' => DealRequest::class,
                'choices' => $options['reviewable_deals'],
                'choice_label' => function (DealRequest $deal) {
                    return 'Deal #' . $deal->getId() . ' - ' . $deal->getPrixPropose() . ' TND';
                },
                'placeholder' => 'Sélectionner le deal concerné',
                'label' => 'Deal concerné',
                'required' => true,
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Votre commentaire',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Partagez votre avis...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'reviewable_deals' => [],
        ]);
    }
}