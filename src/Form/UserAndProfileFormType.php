<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\User;
use App\Entity\UserProfile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Validator\Constraints\File;

class UserAndProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs pour l'entité User
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '2',
                    'maxlength' => '50',
                    'placeholder' => 'prenom.nom',
                ],
                'label' => 'Nom d\'utilisateur :',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 161]),
                ],
            ])
            ->add('first_name', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '2',
                    'maxlength' => '50',
                    'placeholder' => 'Prenom',
                ],
                'label' => 'Prénom :',
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
                    'minlength' => '2',
                    'maxlength' => '50',
                    'placeholder' => 'Nom',
                ],
                'label' => 'Nom :',
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
                    'minlength' => '2',
                    'maxlength' => '180',
                    'placeholder' => 'prenom.nom@cc-osartis.com',
                ],
                'label' => 'Email :',
                'constraints' => [
                    new NotBlank(),
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
                'placeholder' => 'Sélectionnez un lieu de travail',
            ])
            ->add('position', ChoiceType::class, [
                'choices' => [
                    'Relais Petite Enfance "À Mini Pas"' => 'Relais Petite Enfance "À Mini Pas"',
                    'Service Jeunesse ' => 'Service Jeunesse',
                    'Actions pour les Personnes Âgées et/ou en Situation de Handicap (SPASAD)' => 'Actions pour les Personnes Âgées et/ou en Situation de Handicap (SPASAD)',
                    'Transport à la Demande (TAD)' => 'Transport à la Demande (TAD)',
                    'Gestion des Déchets' => 'Gestion des Déchets',
                    'Culture' => 'Culture',
                    'Tourisme' => 'Tourisme',
                    'Service Informatique' => 'Service Informatique',
                ],
                'attr' => [
                    'class' => '',
                    'minlength' => '4',
                    'maxlength' => '180',
                    'placeholder' => 'Comptabilité',
                ],
                'label' => 'Service :',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 180]),
                ],
            ])
            ->add('job', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '2',
                    'maxlength' => '180',
                    'placeholder' => 'Chargé de communication',
                ],
                'label' => 'Travail Éxercer :',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 180]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\-\'\s]+$/',
                        'message' => 'Le Métier ne peut contenir que des lettres, des espaces, des tirets ou des apostrophes.',
                    ]),
                ],
            ]);

        // Sous-formulaire pour UserProfile
            $builder
            ->add('profile', FormType::class, [
                'label' => false,
                'mapped' => true,
                'data_class' => UserProfile::class,
                'required' => false,
            ])
            ->get('profile')
            ->add('avatar', FileType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Photo de profil',
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier d\'image valide (JPEG, PNG, GIF uniquement).',
                        'maxSizeMessage' => 'La taille maximale autorisée pour l\'image est de 2 Mo.',
                    ])
                ],
            ])
            ->add('phoneFixed', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone fixe professionnel',
                'attr' => [
                    'placeholder' => 'exemple : 0321600600'
                ],
            ])
            ->add('phoneMobile', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone mobile professionnel',
                'attr' => [
                    'placeholder' => 'exemple : 0679058225'
                ],
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'label' => 'Biographie',
                'attr' => [
                    'placeholder' => 'exemple : Encore une magnifiue journée pasée a vos cotés'
                ],
            ])
            ->add('linkedinUrl', TextType::class, [
                'required' => false,
                'label' => 'Lien du profil LinkedIn',
            ])
            ->add('style', ChoiceType::class, [
                'choices' => [
                    'Light' => 'light',
                    'Dark' => 'dark',
                    'Classic' => 'classic',
                    'Modern' => 'modern',
                ],
                'placeholder' => 'Choisissez un thème',
                'required' => false,
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Lieux de travail',
            ]);

        // Champ pour le mot de passe administrateur
        $builder->add('adminPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Votre mot de passe :',
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre mot de passe pour valider les modifications.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // C'est l'entité User qui sera passée au formulaire
        ]);
    }
}