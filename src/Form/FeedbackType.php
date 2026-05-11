<?php
// src/Form/FeedbackType.php
namespace App\Form;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, ['label' => 'Votre avis'])
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => ['⭐ 1' => 1, '⭐⭐ 2' => 2, '⭐⭐⭐ 3' => 3, '⭐⭐⭐⭐ 4' => 4, '⭐⭐⭐⭐⭐ 5' => 5],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Feedback::class]);
    }
}