<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\UserProfile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Photo de profile',
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a department',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone profesionnel (fixe et/ou portable)',
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'label' => 'Biographie',
            ])
            ->add('linkedin_url', TextType::class, [
                'required' => false,
                'label' => 'Lien du profile LinkedIn',
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
