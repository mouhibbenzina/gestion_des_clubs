<?php

namespace App\Form;

use App\Entity\Club;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEvenement')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('lieu')
            ->add('description')
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('club', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'name',
                'choices' => $options['club_choices'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'club_choices' => null,
        ]);
    }
}
