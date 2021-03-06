<?php

declare(strict_types=1);

/*
 * This file is part of the itoffers.online project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Offers\Form\Type;

use App\Offers\Form\Type\Offer\ChannelsType;
use App\Offers\Form\Type\Offer\CompanyType;
use App\Offers\Form\Type\Offer\ContactType;
use App\Offers\Form\Type\Offer\DescriptionType;
use App\Offers\Form\Type\Offer\LocationType;
use App\Offers\Form\Type\Offer\PositionType;
use App\Offers\Form\Type\Offer\SalaryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

final class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('locale', LocaleType::class, [
                'required' => true,
                'constraints' => [new NotBlank()],
                'preferred_choices' => ['en_US', 'pl_PL'],
            ])
            ->add('company', CompanyType::class)
            ->add('position', PositionType::class)
            ->add('salary', SalaryType::class, ['required' => false])
            ->add('contract', ChoiceType::class, [
                'choices' => [
                    'Contract (B2B)' => 'Contract (B2B)',
                    'Employment contract' => 'Employment contract',
                    'Part-time' => 'Part-time',
                    'Internship' => 'Internship',
                    'Volunteer' => 'Volunteer',
                ],
            ])
            ->add('location', LocationType::class)
            ->add('description', DescriptionType::class)
            ->add('contact', ContactType::class)
            ->add('channels', ChannelsType::class)
            ->add('offer_pdf', FileType::class, [
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['application/pdf', 'application/x-pdf'],
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class)
            ->add('reset', ResetType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'offer',
        ]);
    }
}
