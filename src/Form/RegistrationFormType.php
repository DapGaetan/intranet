<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Regex;

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
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 161]),
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
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 80]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\-\'\s]+$/',
                        'message' => 'Le Prénom ne peut contenir que des lettres, des espaces, des tirets ou des apostrophes.',
                    ]),
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
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 80]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\-\'\s]+$/',
                        'message' => 'Le Nom ne peut contenir que des lettres, des espaces, des tirets ou des apostrophes.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '180',
                    'placeholder' => 'prenom.nom@cc-osartis.com',
                ],
                'label' => 'Email :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Assert\Email(),
                    new Length(['min' => 2, 'max' => 180]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        'message' => 'L\'adresse email doit être valide et peut contenir des lettres, des chiffres, des points, des tirets, des underscores, et un @.',
                    ]),
                ],
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => '',
                    'minlength' => '2',
                    'maxlength' => '200',
                ],
                'label' => 'Lieux :',
                'label_attr' => [
                    'class' => ''
                ],
                'placeholder' => 'Sélectionnez un lieu de travail',
            ])
            
            ->add('position', ChoiceType::class, [
                'choices' => [
                    'Relais Petite Enfance "À Mini Pas"' => 'Relais Petite Enfance "À Mini Pas',
                    'Service Jeunesse ' => 'Service Jeunesse ',
                    'Actions pour les Personnes Âgées et/ou en Situation de Handicap (SPASAD)' => 'Actions pour les Personnes Âgées et/ou en Situation de Handicap (SPASAD)',               
                    'Transport à la Demande (TAD)' => 'Transport à la Demande (TAD)',
                    'Gestion des Déchets' => 'Gestion des Déchets',
                    'Culture' => 'Culture',
                    'Tourisme' => 'Tourisme',
                    'Service Informatique' => 'Service Informatique',
                ],
                'attr' => [
                    'class' => '',
                    'minlenght' => '4',
                    'maxlenght' => '180',
                    'placeholder' => 'Comptabilité',
                ],
                'label' => 'Service :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 180]),
                ],
            ])
            ->add('job', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '180',
                    'placeholder' => 'Chargé de communication',
                ],
                'label' => 'Travail Éxercer :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 180]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\-\'\s]+$/',
                        'message' => 'Le Métier ne peut contenir que des lettres, des espaces, des tirets ou des apostrophes.',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte que l\'on utilise mes données',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter que l\'ont utilises ces données',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '180',
                    'placeholder' => 'PetitChat123!!',
                ],
                'label' => 'Mot de passe :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous navez pas entrez de mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} charactères',
                        'max' => 255,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                        'message' => 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                    ]),
                ],
            ])
            
            ->add('plainPasswordConfirm', PasswordType::class, [
                'label' => 'Confirmez le mot de passe',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => '',
                    'minlenght' => '2',
                    'maxlenght' => '180',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer le mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} charactères',
                        'max' => 255,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                        'message' => 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                    ]),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $plainPassword = $form->get('plainPassword')->getData();
            $plainPasswordConfirm = $form->get('plainPasswordConfirm')->getData();

            if ($plainPassword !== $plainPasswordConfirm) {
                $form->get('plainPasswordConfirm')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
