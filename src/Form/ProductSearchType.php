<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductSearchType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search_category', ChoiceType::class, $this->getConfiguration('Type de recherche', 'Que recherchez-vous ?', [
                'choices' => [
                    'Nom' => 0,
                    'Description' => 1,
                ]
            ]))
            ->add('search_text', TextType::class, $this->getConfiguration('Recherche textuelle', 'Tapez votre recherche'))
            ->add('submit', SubmitType::class, $this->getConfiguration('Rechercher', ''));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
