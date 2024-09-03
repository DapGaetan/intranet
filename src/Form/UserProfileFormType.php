<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\UserProfile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['placeholder' => 'Sélectionnez une image'],
                'label' => 'Photo de profil',
            ])
            ->add('phone_fixed', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone fixe professionnel',
                'attr' => [
                    'placeholder' => 'exemple : 0679058225'
            
            ],
            ])
            ->add('phone_mobile', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone mobile profesionnel',
                'attr' => [
                    'placeholder' => 'exemple : 0321600600'
            
            ],
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'label' => 'Biographie',
                'attr' => [
                    'placeholder' => 'exemple : Encore une magnifiue journée pasée a vos cotés'

            ],
            ])
            ->add('linkedin_url', TextType::class, [
                'required' => false,
                'label' => 'Lien du profil LinkedIn',
                'attr' => [
                    'placeholder' => 'exemple : https://fr.linkedin.com/in/nom-prenom'
            ],
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
            ->add('address', HiddenType::class, [
                'mapped' => false,
            ]);
    
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $userProfile = $event->getData();
    
            if (!$userProfile instanceof UserProfile) {
                return;
            }
    
            $currentDepartment = $userProfile->getDepartment();

            $form->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Lieux de travail',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d');
                },
                'data' => $currentDepartment,
            ]);

            if ($currentDepartment) {
                $form->get('address')->setData($currentDepartment->getName());
            }
        });
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
