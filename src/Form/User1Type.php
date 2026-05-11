<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
            'choices' => [
            'Etudiant'    => 'ROLE_ETUDIANT',
            'Responsable' => 'ROLE_RESPONSABLE',
            'President'   => 'ROLE_PRESIDENT',
            'Admin'       => 'ROLE_ADMIN',
             ],
            'multiple' => true,
            'expanded' => true,
            'label'    => 'Role'
             ])

            ->add('password')
            ->add('nom')
            ->add('prenom')
            ->add('matricule')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
