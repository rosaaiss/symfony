<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private $doctrine ;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine ;
    }

    public function index()
    {
        // on cherche le dernier article :
        // on récupère l'entity manager de Doctrine.
        $em = $this->doctrine->getManager();

        // puis on utilise le Repository de notre classe Article pour chercher le dernier en date.
        // Pour ce faire, on utilise la méthode findOneBy qui exite par défaut dans les Repository
        // de TOUTES les entités sans qu'on ai à l'écrire.
        // On lui donne comme paramètre un tableau de critère (qu'il traduira par des WHERE en SQL)
        // Ici, le premier tableau est vide (aucun critère sur les articles)
        // en deuxième argument on peut donner un tableau d'ordres, (qui lui sera traduit par des
        // clauses ORDER BY en SQL).
        // Dans notre cas, comme on veut le dernier article, on les range par date décroissante.
        // Comme on utilise findOneBy, on n'aura que le premier résultat, donc notre dernier article.
        $dernierArticle = $em->getRepository(Article::class)->findOneBy([], ["dateCreation" => "DESC"]);

        //enfin on retourne une réponse. Ici le cas le plus courant, une vue html.
        //C'est grace à la méthode d'AbstractController
        // $this->render()
        // qui prend un premier argument obligatoire : l'adresse de la vue relativement au dossier "templates".
        // En deuxième argument, on peut passer un tableau de type
        // [
        //     "cle1" => $valeur1,
        //     "cle2" => $valeur3,
        //     ...,
        // ]
        // où chaque clé sera le nom d'une variable utilisable dans
        // le template et chaque valeur sera ce que vaudra cette variable.
        // Ici on envoie au template notre dernier article.
        return $this->render("home/main.html.twig",
            [
                "dernierArticle" => $dernierArticle,
            ]);
    }
}