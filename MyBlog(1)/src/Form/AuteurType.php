<?php

namespace App\Form;

use App\Entity\Auteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom')
            ->add('nom')
            ->add('bio')
            ->add('email')
            ->add('dateNaissance', DateType::class,
            [
                //mise en forme de la date :
                // https://symfony.com/doc/current/reference/forms/types/date.html#format
                // tous les formats possibles :
                // http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
                'format' => "dd-MM-yyyy",
                //ci-dessous, on définit l'ensemble des années affichée. Par défaut ce serait de -5ans à +5ans.
                //Pour une date de naissance, on préfère partir de l'année courante jusqu'à -120ans :
                'years' => range(
                    date_create("now")->format('Y'),
                    date_create("-120 years")->format('Y')
                ),
            ])
            ->add('sauver', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auteur::class,
        ]);
    }
}
