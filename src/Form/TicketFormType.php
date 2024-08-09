<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TicketFormType extends AbstractType
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $statusChoices = ['Ouvert' => 'Open'];

        if ($isEdit) {
            $statusChoices['Fermé'] = 'Closed';
        }

        $isEdit = $options['is_edit'];
        $user = $this->security->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        $statusChoices = ['Ouvert' => 'Open'];

        if ($isAdmin) {
            $statusChoices['Fermé'] = 'Closed';
        }

        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '5',
                    'maxlength' => '150',
                    'placeholder' => 'Mon écran est HS',
                ],
                'label' => 'Sujet de la demande :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 150]),
                ],
            ])
            ->add('description', TextType::class, [
                'attr' => [
                    'class' => '',
                    'minlength' => '5',
                    'maxlength' => '550',
                    'placeholder' => 'L\'écran ne s\'allume plus...',
                ],
                'label' => 'Description de la demande :',
                'label_attr' => [
                    'class' => ''
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 550]),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => $statusChoices,
                'label' => 'Statut du ticket :',
                'label_attr' => [
                    'class' => ''
                ],
                'disabled' => !$isAdmin && $isEdit,
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Bas' => 'low',
                    'Moyen' => 'medium',
                    'Haut' => 'high',
                    'Urgent' => 'very high',
                ],
                'label' => 'Indicateur d\'impact sur mon travail :',
                'label_attr' => [
                    'class' => ''
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'is_edit' => false,
        ]);
    }
}