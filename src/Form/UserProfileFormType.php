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
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone professionnel (fixe et/ou portable)',
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'label' => 'Biographie',
            ])
            ->add('linkedin_url', TextType::class, [
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
                'placeholder' => false,
                'label' => 'Lieux de travail',
                'query_builder' => function (EntityRepository $er) use ($currentDepartment) {
                    $qb = $er->createQueryBuilder('d');
                    if ($currentDepartment) {
                        $qb->where('d.id != :currentDepartmentId')
                           ->setParameter('currentDepartmentId', $currentDepartment->getId());
                    }
                    return $qb;
                },
                'data' => $currentDepartment,
            ]);
        });
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}    
