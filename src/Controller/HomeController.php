<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry; //Přidáno pro manipulaci s entitami
use App\Entity\Product; //Přidáno pro práci s entitou "Product"
use App\Entity\ProductType; //Přidáno pro práci s entitou "ProductType"

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {

         // najít všechny produkty
         $products = $doctrine->getManager()->getRepository(Product::class)->findAll();

         $productTypes = $doctrine->getRepository(ProductType::class)->findAll();

         foreach ($products as $product) // Spojí ProductType s Product na základě cizího klíče v Product
         {
            $type = $product->getType();
            foreach ($productTypes as $productType) 
            {
                /*
                dump($productType->getId());
                dump($type->getId());
                dump($product->getType()->getId());
                die();*/
                if ($type->getId() == $productType->getId()) 
                {
                    $product->setType($productType);
                    break;
                }
            }
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products
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
