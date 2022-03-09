<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Form\AuteurType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


//Les commentaires sur le controlleur en général sont dans ArticleController.
//Ceux qui sont ici souligne seulement les différences.
class AuteurController extends AbstractController
{
    private $doctrine ;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine ;
    }
    
    public function showAll()
    {
        $auteurRepo = $this->doctrine->getRepository(Auteur::class) ;

        //On utilise ici une des méthode prête à l'emploi de tous les répository : findBy
        // le premier tableau passé en argument contient (ou pas) des critères, le deuxième des clause d'ordre,
        // similaire à un 'ORDER BY' en mysql.
        // https://symfony.com/doc/current/doctrine.html#fetching-objects-from-the-database
        $allAuteurs = $auteurRepo->findBy(
            [
            ],
            [
                "nom" => "ASC",
                "prenom" => "ASC",
            ]
        ) ;

        return $this->render(
            'auteur/showAll.html.twig',
            [
                "allAuteurs" => $allAuteurs ,
            ]
        ) ;
    }
    
    public function show($idAuteur)
    {
        $auteurRepo = $this->doctrine->getRepository(Auteur::class) ;

        $auteur = $auteurRepo->find($idAuteur) ;

        return $this->render(
            'auteur/show.html.twig',
            [
                "auteur" => $auteur ,
            ]
        ) ;
    }

    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $auteur = new Auteur() ;
        $form = $this->createForm(AuteurType::class, $auteur) ;

        $form->handleRequest($request) ;

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager() ;

            $em->persist($auteur);
            $em->flush() ;

            return $this->redirectToRoute("myblog_auteur_showall") ;
        }

        return $this->renderForm(
            'auteur/create.html.twig',
            [
                "form" => $form,
            ]
        ) ;
    }
    
    public function update($idAuteur, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $auteurRepo = $this->doctrine->getRepository(Auteur::class) ;
        $auteur = $auteurRepo->find($idAuteur) ;

        if($auteur === null)
        {
            return $this->redirectToRoute("myblog_auteur_showall") ;
        }

        $form = $this->createForm(AuteurType::class, $auteur) ;
        $form->handleRequest($request) ;

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager() ;

            $em->persist($auteur);
            $em->flush() ;

            return $this->redirectToRoute("myblog_auteur_showall") ;
        }

        return $this->renderForm(
            'auteur/create.html.twig',
            [
                "form" => $form,
            ]
        ) ;
    }
    
    public function remove($idAuteur)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $auteurRepo = $this->doctrine->getRepository(Auteur::class) ;
        $auteur = $auteurRepo->find($idAuteur) ;

        if($auteur === null)
        {
            return $this->redirectToRoute("myblog_auteur_showall") ;
        }

        $em = $this->doctrine->getManager() ;

        $em->remove($auteur);
        $em->flush();

        return $this->redirectToRoute("myblog_auteur_showall") ;
    }
}