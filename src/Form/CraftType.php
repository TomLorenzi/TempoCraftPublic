<?php

namespace App\Form;

use App\Entity\Craft;
use App\Entity\Card;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CraftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cards', EntityType::class, [
                'class' => Card::class,
                'required' => true,
                'multiple' => true,
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'choice_attr' => function($choice, $key, $value) {
                    if (!$choice instanceof Card) {
                        return [];
                    }

                    return [
                        'data-marker-text' => '<img src="' . $choice->getImage() .'"></img>'
                    ];
                },
            ])
            ->add('item')
            ->add('submitBtn', SubmitType::class, [
                'label' => 'Proposer le craft',
                'attr' => [
                    'class' => 'btn btn-success mt-2'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Craft::class,
        ]);
    }
}
