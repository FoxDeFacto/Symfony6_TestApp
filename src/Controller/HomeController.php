<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/info', name: 'app_home')] //Cesta s názvem -> '/info'  // Název pro cestu kontroler 'app_home'
    public function info(): Response //Název metody
    {
        return $this->render('home/info.html.twig', [ //Cesta jakopu stránku má kontroler vykreslit
            'controller_name' => 'HomeController',
            'variable_test' => 'Value_test', //Název proměné a její hodnota
        ]);
    }
}
