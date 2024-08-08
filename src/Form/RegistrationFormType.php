<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '50',
                    'placeholder' => 'prenom.nom',
                ],
                'label' => 'Nom d\'utilisateur :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 161])
                ],
            ])
            ->add('first_name', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '50',
                    'placeholder' => 'Prenom',
                ],
                'label' => 'Prénom :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 80])
                ],
            ])
            ->add('last_name', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '50',
                    'placeholder' => 'Nom',
                ],
                'label' => 'Nom :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 80])
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '180',
                    'placeholder' => 'prenom.nom@cc-osartis.com',
                ],
                'label' => 'Email',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(['min' => 2, 'max' => 180])
                ],
            ])
            ->add('department')
            ->add('position')
            ->add('job')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Accepter lutilisation de mes donner',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous navez pas entrez de mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} charactères',
                        // max length allowed by Symfony for security reasons
                        'max' => 255,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
