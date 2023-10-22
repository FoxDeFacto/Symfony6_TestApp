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
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Dompdf\Dompdf;



class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {

        return $this->render('home/index.html.twig', [ //Cesta jakopu stránku má kontroler vykreslit
            'controller_name' => 'HomeController',
            'variable_test' => 'Value_test', //Název proměné a její hodnota
        ]);
    }

    /*
    #[Route('/overview', name: 'app_overview', methods: ['GET'])] //Cesta s názvem -> '/info'  // Název pro cestu kontroler 'app_home'
    public function overview(ManagerRegistry $doctrine): Response //Název metody
    {

        // najít všechny produkty
        $products = $doctrine->getManager()->getRepository(Product::class)->findAll();

        $productTypes = $doctrine->getRepository(ProductType::class)->findAll();

        foreach ($products as $product) // Spojí ProductType s Product na základě cizího klíče v Product
        {
           $type = $product->getProductType();
           foreach ($productTypes as $productType) 
           {
               if ($type->getId() == $productType->getId()) 
               {
                   $product->setProductType($productType);
                   break;
               }
           }
       }
       
       return $this->render('home/overview.html.twig', [
           'controller_name' => 'HomeController',
           'products' => $products
       ]);
    }*/

    #[Route('/overview/{page}', name: 'app_overview', defaults: ["page" => 1])]
    public function overview(ManagerRegistry $doctrine, int $page): Response
    {
        $em = $doctrine->getManager();
        $queryBuilder = $em->getRepository(Product::class)->createQueryBuilder('p');
        
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);  // Nastaví max limit na stránku
        $pagerfanta->setCurrentPage($page);  // Aktuální stránka
        //var_dump($pagerfanta);
        
        return $this->render('home/overview.html.twig', [
            'controller_name' => 'HomeController',
            'pager' => $pagerfanta
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

            $em = $doctrine->getManager(); // Objekt pro práci s entitami

            $em->persist($product); //Přidání do fronty vykonání v databázi - teoreticky zbytečné pro tento případ
            $em->flush(); //Přidání data do databáze

            return $this->redirect('/'); //Přesměrování na Homepage
        }

        return $this->render('home/product_new.html.twig', [ //Stránka s daným formulářem
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, $id): Response //Přidá parametr id pro zjištění záznamu
    {
        $product = $doctrine->getManager()->getRepository(Product::class)->find($id); //Najde na základě id záznam
        if (!$product) {
            throw $this->createNotFoundException('No product found for id ' . $id); //Error pokud není záznam nalezen
        }

        $form = $this->createForm(ProductFormType::class, $product);  //Tvorba nového formuléře podle vzoru ProductFormType
        
        $form->handleRequest($request);  //Předání dat z formuláře
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $doctrine->getManager(); // Objekt pro práci s entitami
            
            $em->flush(); // Provedení změn v databázi
            
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/product_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function delete(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $product = $doctrine->getManager()->getRepository(Product::class)->find($id); //Najde na základě id záznam
        
        if (!$product) 
        {
            throw $this->createNotFoundException('No product found for id ' . $id); //Error pokud není záznam nalezen
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) 
        {
                $em = $doctrine->getManager();
                $em->remove($product); //Odebere záznam
                $em->flush(); // Provedení změn v databázi

                return $this->render('home/index.html.twig', [ //Cesta jakopu stránku má kontroler vykreslit
                    'controller_name' => 'HomeController',
                    'variable_test' => 'Value_test', //Název proměné a její hodnota
                ]);
        }
        

        return $this->render('home/product_delete.html.twig', [
            'product' => $product,
        ]);
    }


    #[Route('/product/read/{id}', name: 'product_read')]
    public function read(ManagerRegistry $doctrine, $id): Response
    {
        $product = $doctrine->getManager()->getRepository(Product::class)->find($id); //Najde na základě id záznam

        $productTypes = $doctrine->getRepository(ProductType::class)->findAll();

        $type = $product->getProductType();
        foreach ($productTypes as $productType) 
        {
            if ($type->getId() == $productType->getId()) 
            {
                $product->setProductType($productType);
                break;
            }
        }

        dump($product);
        
        if (!$product) 
        {
            throw $this->createNotFoundException('No product found for id ' . $id); //Error pokud není záznam nalezen
        }

        return $this->render('home/product_detail.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/print', name: 'app_print')]
    public function printPDF(ManagerRegistry $doctrine): Response
    {

        $products = $doctrine->getManager()->getRepository(Product::class)->findLimited(1000); //Najde všechny záznamy entity

        $productTypes = $doctrine->getRepository(ProductType::class)->findAll();

        foreach ($products as $product) // Spojí ProductType s Product na základě cizího klíče v Product
        {
           $type = $product->getProductType();
           foreach ($productTypes as $productType) 
           {
               if ($type->getId() == $productType->getId()) 
               {
                   $product->setProductType($productType);
                   break;
               }
           }
       }

        $html = $this->renderView('/home/pdf_print.html.twig', ['products' => $products]); //Pošle záznamy pro vykreslení na html

        $dompdf = new Dompdf(); 
        $dompdf->loadHtml($html); //Nahraje data pro generování pdf
        $dompdf->render(); //Vykreslí podle dat
        $pdfOutput = $dompdf->output(); // Hotová stránka

        return new Response($pdfOutput, Response::HTTP_OK, ['Content-Type' => 'application/pdf']); //Předání dat a nastavení typu
    }

}
