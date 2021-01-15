<?php

namespace App\Form;

use App\Entity\Brand;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BrandType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, $this->getConfiguration('Nom', 'Entrer un nom de marque'))
            ->add('logo', TextType::class, $this->getConfiguration('Logo', 'Veuillez entrer une URL valide vers une image', [
                'required' => false,
            ]))
            ->add('creationDate', DateType::class, $this->getConfiguration('Date de création', '', [
                'widget' => 'single_text'
            ]))
            ->add('nationality', TextType::class, $this->getConfiguration('Nationalité', 'Entrer une nationalité'))
            ->add('slogan', TextType::class, $this->getConfiguration('Slogan', 'Entrer un slogan', [
                'required' => false,
            ]))
            ->add('website', TextType::class, $this->getConfiguration('Site web', 'Veuillez entrer une URL valide vers le site web de la marque', [
                'required' => false,
            ]))
            ->add('submit', SubmitType::class, $this->getConfiguration('Ajouter',''))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Brand::class,
        ]);
    }
}
