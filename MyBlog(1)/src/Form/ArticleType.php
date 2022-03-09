<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Auteur;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    //code essentiellement généré par php bin/console make:form
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu')
            ->add('auteur', EntityType::class,
                [
                    //Le nom de l'entité qu'on veut renseigner. Ici, l'auteur de l'article.
                    'class' => Auteur::class,
                    //Ici, on fait une requête pour trouver les auteurs de notre blog et les classer par ordre
                    //alphabétique de nom puis prénom.
                    'query_builder' => function (EntityRepository $er)
                    {
                        return $er->createQueryBuilder('a')
                            ->orderBy('a.nom, a.prenom', 'ASC')
                            ;
                    },
                    // ce qui apparaitra dans la select permettant de choisir l'auteur de l'article.
                    // on utilise la methode getFullName() (voir dans Auteur.php)
                    // celle ci affichera  prénom + nom
                    // ex: Optimus Prime
                    'choice_label' => "fullName",
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}





/*
 *
 * ->add('auteur', EntityType::class,
            [
                //Le nom de l'entité qu'on veut renseigner. Ici, l'auteur de l'article.
                'class' => Auteur::class,
                //Ici, on fait une requête pour trouver les auteurs de notre blog et les classer par ordre
                //alphabétique de nom puis prénom.
                'query_builder' => function (EntityRepository $er)
                {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nom, a.prenom', 'ASC')
                        ;
                },
                // ce qui apparaitra dans la select permettant de choisir l'auteur de l'article.
                // on utilise la methode getFullName() (voir dans Auteur.php)
                // celle ci affichera Titre + prénom + nom
                // ex: Dr Anthelme Nobody
                'choice_label' => 'fullName',

            ])
 */
