<?php

namespace App\Form;

use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DepartmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => '',
                    'minlength' => '5',
                    'maxlength' => '255',
                    'placeholder' => 'Exemple : 123 rue des adresses',
                ],
                'label' => 'Adresse :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 255]),
                ],
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => '',
                    'minlength' => '5',
                    'maxlength' => '150',
                    'placeholder' => 'Exemple : Vitry-en-Artois',
                ],
                'label' => 'Ville :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 150]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => '',
                    'minlength' => '5',
                    'maxlength' => '5',
                    'placeholder' => 'Exemple : 62490',
                ],
                'label' => 'Code Postale :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 5]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}
