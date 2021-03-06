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

use ITOffers\Offers\Application\Query\Offer\OfferFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OfferFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->setMethod('GET');

        $builder
            ->add('remote', CheckboxType::class, [
                'required' => false,
            ])
            ->add('with_salary', CheckboxType::class, [
                'required' => false,
            ])
            ->add('sort_by', ChoiceType::class, [
                'choices' => [
                    '' => null,
                    'Salary ascending' =>  OfferFilter::SORT_SALARY_ASC,
                    'Salary descending' => OfferFilter::SORT_SALARY_DESC,
                    'Added at ascending' => OfferFilter::SORT_CREATED_AT_ASC,
                    'Added at descending' => OfferFilter::SORT_CREATED_AT_DESC,
                ],
                'choice_value' => fn (string $value = null) => $value,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
