<?php
namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TicketFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'Open',
                    'En cour de traitement' => 'In Progress',
                    'Fermer' => 'Closed',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Bas' => 'low',
                    'Moyen' => 'medium',
                    'Haut' => 'high',
                    'Urgent' => 'very hight',
                ],
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            // ->add('user', EntityType::class, [
            //    'class' => User::class,
            //    'choice_label' => 'id',
            // ])
            // ->add('assigned_to', EntityType::class, [
            //    'class' => User::class,
            //    'choice_label' => 'id',
            //    'multiple' => true,
            //    'expanded' => false,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
