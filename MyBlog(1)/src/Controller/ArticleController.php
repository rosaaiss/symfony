<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Auteur;
use App\Entity\Utilisateur;
use App\Form\ArticleType;
use App\Form\AuteurType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends AbstractController
{
    private $doctrine ;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine ;
    }

    public function showAll()
    {
        // Il faut avoir le répository d'une Classe d'entité pour pouvoir passer des requêtes en BDD portant sur
        // cette entité
        $articleRepo = $this->doctrine->getRepository(Article::class) ;

        // Ici, il y a une fonction que nous avons écrite dans ArticleRepository, pour obtenir
        //les articles classés par date descendante (du plus récent au plus ancien),
        // avec une jointure sur les auteurs, pour éviter que chaque auteur génère une nouvelle requête à l'affichage
        $allArticles = $articleRepo->findAllArticlesWithAuteurs()  ;

        return $this->render(
            'article/showAll.html.twig',
            [
                "allArticles" => $allArticles ,
            ]
        ) ;
    }

    public function show($idArticle)
    {
        $articleRepo = $this->doctrine->getRepository(Article::class) ;

        // on cherche l'article qui a le bon id grace cette fois à la methode find() disponible
        // automatiquement dans le Repository de toute entité.
        $article = $articleRepo->find($idArticle) ;

        return $this->render(
            'article/show.html.twig',
            [
                "article" => $article ,
            ]
        ) ;
    }

    public function create(Request $request)
    {
        // Vérification de l'autorisation : cette page n'est accessible qu'à un utilisateur connecté.
        // S'il n'est pas connecté, il sera redirigé vers la page de login, et s'il se connecte il
        // sera automatiquement redirigé vers cette page (creation d'article)
        // Tout ça en une ligne. Merci Symfony.
        $this->denyAccessUnlessGranted('ROLE_USER');

        //on instancie un article, pour avoir un objet vide, que le formulaire ou le reste du code pourra
        //remplir.
        $article = new Article() ;

        // L'utilisateur connecté est celui qui sera le créteur de l'article, on y accède avec
        // $this->getUser() dans n'importe quel controlleur.
        //Sur notre blog, les auteurs sont virtuels ! L'auteur et le créateur sont
        //donc des personnes bien distinctes.
        //Seul lui aura droit d'affacer l'article (voir methode remove($idArticle) plus bas.
        $article->setCreateur($this->getUser()) ;

        //on crée un formulaire de type ArticleType, et on le lie à notre objet article.
        $form = $this->createForm(ArticleType::class, $article) ;
        //on transmet la requète au formulaire, pour qu'il aie accès aux donnée POST qui en font partie.
        //Attention, on oublie souvent cette ligne.
        $form->handleRequest($request) ;

        //Si le formulaire est soumis et valide :
        if($form->isSubmitted() && $form->isValid())
        {
            //la date n'est pas demandée dans le formulaire :
            //C'est ici qu'on la met à jour avec le temps exact du serveur.
            $article->setDateCreation(date_create("now")) ;

            //on enregistre notre objet $article en base de donné grace à l'entity manager
            // de Doctrine.
            $em = $this->doctrine->getManager() ;
            // persist permet de créer comme de mettre à jour l'entité (INSERT INTO et UPDATE en mysql)
            $em->persist($article);

            //les requêtes préparée plus haut sont effectivement executé dans la base de donné quand on
            //appelle la methode flush() de l'EntityManager
            $em->flush() ;

            // Puis redirection vers la page " tous les articles "
            return $this->redirectToRoute("myblog_article_showall") ;
        }


        return $this->renderForm(
            'article/create.html.twig',
            [
                "form" => $form,
            ]
        ) ;
    }

    public function update($idArticle, Request $request)
    {

        //idem methode précédente
        $this->denyAccessUnlessGranted('ROLE_USER');

        //La démarche pour créer et utiliser le formulaire sont exactement identique à la méthode create().
        //La seule différence, c'est qu'on lie le formulaire à un article, récupéré par son id, et non un article vide.
        $articleRepo = $this->doctrine->getRepository(Article::class) ;
        $article = $articleRepo->find($idArticle) ;

        if($article === null)
        {
            //Si on n'a trouvé aucun article avec cet id, impossible de le mettre à jour.
            //On renvoie alors à la liste de tous les articles
            return $this->redirectToRoute("myblog_article_showall") ;
        }

        if($article->getCreateur() !== $this->getUser())
        {
            //Si l'utilisateur connecté n'est pas celui qui a écrit l'aricle on renvoie une
            // AccessDeniedException, exception d'accès refusé.
            throw $this->createAccessDeniedException("t'as pas le droit, c'est pas ton article") ;
        }

        $form = $this->createForm(ArticleType::class, $article) ;
        $form->handleRequest($request) ;

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->doctrine->getManager() ;
            $article->setDateEdition(date_create("now")) ;

            $em->persist($article);
            $em->flush() ;

            return $this->redirectToRoute("myblog_article_showall") ;
        }

        return $this->renderForm(
            'article/create.html.twig',
            [
                "form" => $form,
            ]
        ) ;
    }

    public function remove($idArticle)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $articleRepo = $this->doctrine->getRepository(Article::class) ;
        $article = $articleRepo->find($idArticle) ;

        if($article === null)
        {
            return $this->redirectToRoute("myblog_article_showall") ;
        }

        if($article->getCreateur() !== $this->getUser())
        {
            //Si l'utilisateur connecté n'est pas celui qui a écrit l'aricle on renvoie une
            // AccessDeniedException, exception d'accès refusé.
            throw $this->createAccessDeniedException("t'as pas le droit, c'est pas ton article") ;
        }

        $em = $this->doctrine->getManager() ;

        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute("myblog_article_showall") ;
    }


}