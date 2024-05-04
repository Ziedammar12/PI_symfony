<?php

namespace App\Form;

use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix_unitaire')
            ->add('quantite')
            ->add('montant_ht')
            ->add('tva')
            ->add('montant_ttc')
            ->add('date')
            ->add('type_devise')
            ->add('Ajouter', SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
