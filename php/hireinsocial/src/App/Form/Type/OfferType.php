<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Type\Offer\ChannelsType;
use App\Form\Type\Offer\CompanyType;
use App\Form\Type\Offer\ContactType;
use App\Form\Type\Offer\DescriptionType;
use App\Form\Type\Offer\LocationType;
use App\Form\Type\Offer\PositionType;
use App\Form\Type\Offer\SalaryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', CompanyType::class)
            ->add('position', PositionType::class)
            ->add('salary', SalaryType::class, ['required' => false])
            ->add('contract', ChoiceType::class, [
                'choices' => [
                    'B2B' => 'B2B',
                    'Umowa o Pracę' => 'Umowa o Pracę',
                    'Umowa Zlecenie' => 'Umowa Zlecenie',
                ],
            ])
            ->add('location', LocationType::class)
            ->add('description', DescriptionType::class)
            ->add('contact', ContactType::class)
            ->add('channels', ChannelsType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'new_offer',
        ]);
    }
}