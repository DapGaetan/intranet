<?php

namespace App\Form;

use App\Entity\CulturalEventTicket;
use App\Entity\Department;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class CulturalEventTicketFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Logo Osartis :',
            ])
            ->add('season', FileType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Logo de la saison actuel',
            ])
            ->add('series', ChoiceType::class, [
                'choices' => [
                    'PLEIN TARIF' => 'Série A : PLEIN TARIF',
                    'TARIF RÉDUIT' => 'Série A : TARIF RÉDUIT',
                ],
                'placeholder' => 'Choisir ou entrer une série',
                'required' => true,
                'attr' => ['class' => 'custom-select', 'list' => 'series-list'],
            ])
            ->add('placing', ChoiceType::class, [
                'choices' => [
                    'Placement libre' => 'Placement libre',
                    'Placement numéroté' => 'Placement numéroté',
                ],
                'placeholder' => 'Choisir ou entrer un placement',
                'required' => true,
                'attr' => ['class' => 'custom-select', 'list' => 'placing-list'],
            ])
            ->add('siret', ChoiceType::class, [
                'choices' => [
                    '200 044 048 000 11' => '200 044 048 000 11',
                ],
                'placeholder' => 'Choisir un numéro Siret ou entrer manuellement',
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
                        'minMessage' => 'Le numéro de siret doit faire exactement {{ limit }} chiffres',
                        'maxMessage' => 'Le numéro de siret doit faire exactement {{ limit }} chiffres',
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
                'placeholder' => 'Choisir un numéro Siret ou entrer manuellement',
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
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('updated_at', null, [
                'widget' => 'single_text',
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'id',
            ])
            ->add('created_by', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ]);
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CulturalEventTicket::class,
        ]);
    }
}
