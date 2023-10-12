<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry; //Přidáno pro manipulaci s entitami
use App\Entity\Product; //Přidáno pro práci s entitou "Product"
use App\Entity\ProductType; //Přidáno pro práci s entitou "ProductType"
use App\Form\ProductFormType; //Přidá definici formuláře pro entitu "Product" 
use Symfony\Component\HttpFoundation\Request; //Přidáno pro zpracování dat po provedení odeslání

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
            $type = $product->getProductType();
            foreach ($productTypes as $productType) 
            {
                /*
                dump($productType->getId());
                dump($type->getId());
                dump($product->getType()->getId());
                die();*/
                if ($type->getId() == $productType->getId()) 
                {
                    $product->setProductType($productType);
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

    #[Route('/product/new', name: 'product_new')] //cesta 
    public function new(Request $request,ManagerRegistry $doctrine): Response
    {

        $product = new Product(); //Tvorba nového objektu typu Product

 
        $form = $this->createForm(ProductFormType::class, $product); //Tvorba nového formuléře podle vzoru ProductFormType


        $form->handleRequest($request); //Předání dat z formuláře


        if ($form->isSubmitted() && $form->isValid()) // Kontrola validity a odeslání
        {
 
            $product = $form->getData();

            $em = $doctrine->getManager();

            $em->persist($product);
            $em->flush(); //Přidání data do databáze

            return $this->redirect('/'); //Přesměrování na Homepage
        }

        return $this->render('home/product_new.html.twig', [ //Stránka s daným formulářem
            'form' => $form->createView(),
        ]);
    }

}
