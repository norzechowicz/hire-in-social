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

namespace App\Offers\Form\Type\Offer;

use App\Offers\Validator\Constraints\NotContainsEmoji;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('seniorityLevel', HiddenType::class, [
                'empty_data' => 0,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length(['min' => 3, 'max' => 255]),
                    new NotContainsEmoji(),
                    new Callback([
                        'callback' => function ($object, ExecutionContextInterface $context, $payload) {
                            $forbiddenWords = ['senior', 'junior', 'intern', 'mid', 'expert', 'ninja'];

                            foreach ($forbiddenWords as $forbiddenWord) {
                                if (\mb_strpos(\mb_strtolower($object), $forbiddenWord) !== false) {
                                    $context->buildViolation('Please don\'t put seniority level "' . \ucfirst($forbiddenWord) . '" into job title, there is separated option for it.')
                                        ->addViolation();
                                }
                            }
                        },
                    ]),
                ],
            ])
        ;
    }
}
