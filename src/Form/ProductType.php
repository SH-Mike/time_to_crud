<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Product;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, $this->getConfiguration('Nom', 'Entrer un nom de produit'))
            ->add('creationDate', DateType::class, $this->getConfiguration('Date de création', '', [
                'widget' => 'single_text'
            ]))
            ->add('price', NumberType::class, $this->getConfiguration('Prix', 'Laisser vide pour 0', [
                'attr' => [
                    'min' => '0',
                    'max' => '9999999'
                ],
                'required' => false
            ]))
            ->add('description', TextType::class, $this->getConfiguration('Description', 'Entrer une description de produit'))
            ->add('brand', EntityType::class, $this->getConfiguration('Marque', '', [
                //Class corresponding to EntityType
                'class' => Brand::class,
                //What will be displayed in the list
                'choice_label' => 'name',
                'multiple' => false,
            ]))
            ->add('submit', SubmitType::class, $this->getConfiguration('Envoyer', ''));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
