<?php

namespace App\Form;

use App\Entity\CulturalEventTicket;
use App\Entity\Department;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CulturalEventTicketFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '3',
                    'maxlength' => '120',
                    'placeholder' => 'saison 2024-2025',
                ],
                'label' => 'Sujet de la demande :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 120]),
                ],
            ])
            ->add('logo', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Logo d\'Osartis marquion :',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                    ])
                ],
            ])
            ->add('season', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Logo de la saison actuel',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                    ])
                ],
            ])
            ->add('series', ChoiceType::class, [
                'choices' => [
                    'PLEIN TARIF' => 'Série A : PLEIN TARIF',
                    'TARIF RÉDUIT' => 'Série A : TARIF RÉDUIT',
                ],
                'required' => true,
                'attr' => ['class' => 'custom-select', 'list' => 'series-list'],
            ])
            ->add('placing', ChoiceType::class, [
                'choices' => [
                    'Placement libre' => 'Placement libre',
                    'Placement numéroté' => 'Placement numéroté',
                ],
                'required' => true,
                'attr' => ['class' => 'custom-select', 'list' => 'placing-list'],
            ])
            ->add('siret', ChoiceType::class, [
                'choices' => [
                    '20004404800011' => '20004404800011',
                ],
                'required' => true,
                'attr' => [
                    'maxlength' => 18,
                    'minlength' => 14,
                    'pattern' => '\d+',
                    'placeholder' => 'exemple : 012 345 678 910 12'
                ],
                'constraints' => [
                    new Length([
                        'min' => 14,
                        'max' => 18,
                        'minMessage' => 'Le numéro de siret doit faire minimum {{ limit }} chiffres',
                        'maxMessage' => 'Le numéro de siret doit faire maximum {{ limit }} chiffres',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le numéro de Siret doit contenir uniquement des chiffres.',
                    ]),
                ],
            ])            
            ->add('licence', ChoiceType::class, [
                'choices' => [
                    'PLATESV-R-2021-012094 SGC - ARRAS' => 'PLATESV-R-2021-012094 SGC - ARRAS',
                ],
                'required' => true,
                'attr' => [
                    'maxlength' => 12,
                    'minlength' => 60,
                    'placeholder' => 'exemple : PLATESV-R-2020-011235 SGC - LILLE'
                ],
                'constraints' => [
                    new Length([
                        'min' => 12,
                        'max' => 60,
                        'minMessage' => 'Le numéro de siret doit faire exactement {{ limit }} chiffres',
                        'maxMessage' => 'Le numéro de siret doit faire exactement {{ limit }} chiffres',
                    ]),
                ],
            ])     
            ->add('background', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Illustration de fond des tickets',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                    ])
                ],
            ])
            ->add('created_by', HiddenType::class, [
                'data' => $options['user'],
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CulturalEventTicket::class,
            'user' => null,
        ]);
    }
}
