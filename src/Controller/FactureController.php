<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\FactureRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Dompdf\Dompdf;
use Dompdf\Options;


class FactureController extends AbstractController
{
    #[Route('/index', name: 'app_facture')]
    public function index(ManagerRegistry $manager, Request $request): Response
    {
        $em = $manager->getManager();

        $fact = new Facture();

        $form = $this->createForm(FactureType::class, $fact);
        $form->handleRequest($request);
        if (($form->isSubmitted() && $form->isValid()))
        {
            $em->persist($fact);
            $em->flush();
            

            return $this->redirectToRoute('add_fact');
        
    }
    return $this->render('facture/index.html.twig', ['form' => $form->createView(),]);
}
    #[Route('/showFacture/{name}', name: 'show_Facture')]
       public function showFacture($name)
       {
             return $this->render('facture/showFacture.html.twig', [
            'name' => $name,
             ]);
        }
   
    
    #[Route('/facture/{id}', name: 'Facture_details')]
    public function FactureDetails($id)
    {
        $fact = null;
        
        foreach ($this->factures as $factData) {
            if ($factData['id'] == $id) {
                $fact = $factData;
            };
        };
        return $this->render('facture/show.html.twig', [
            'facture' => $fact,
            'id' => $id
        ]);
    }
   
    #[Route('/addFact', name: 'add_Fact')]
    public function addFact(ManagerRegistry $manager, Request $request): Response
    {
        $em = $manager->getManager();

        $fact = new Facture();

        $form = $this->createForm(FactureType::class, $fact);
        $form->handleRequest($request);
        if (($form->isSubmitted() && $form->isValid()))
        {
            $em->persist($fact);
            $em->flush();

            return $this->redirectToRoute('add_Fact');
        }
        return $this->renderForm('facture/Add.html.twig', ['form' => $form]);
    }

    #[Route('/listfact', name: 'list_Facture')]
    public function listFacture(FactureRepository $facturepository): Response
    {
        $factures = $facturepository->findAll(); 
        
        return $this->render('facture/list.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/editfacture/{id}', name: 'facture_edit')]
    public function editfacture(Request $request, ManagerRegistry $manager, $id, FactureRepository $factrepository): Response
    {
        $em = $manager->getManager();

        $fact = $factrepository->find($id);
        $form = $this->createForm(FactureType::class, $fact);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->persist($fact);
            $em->flush();
            return $this->redirectToRoute('list_Facture');
        }
        return $this->renderForm('facture/edit.html.twig', ['form' => $form]);

    }
    #[Route('/deleteFacture/{id}', name: 'Facture_delete')]
      public function deleteFact(Request $request, $id, ManagerRegistry $manager, FactureRepository $facturepository): Response
       {
          $em = $manager->getManager();
          $fact = $facturepository->find($id);
    
        if ($fact === null) {
             throw $this->createNotFoundException('Facture not found');
        }
    
        $em->remove($fact);
        $em->flush();

        return $this->render('facture/list.html.twig');
    }
    
    #[Route('/RechercheDQL', name:'Search')]
    function RechercheDQL(FactureRepository $repo,Request $request){
        $min=$request->get('min');
        $max=$request->get('max');
        $fact=$repo->SearchfactureDQL($min,$max);
        return $this->render('facture/list.html.twig', [
            'factures' => $fact,
        ]);
    }

    #[Route('/DeleteDQL', name:'DD')]
    function DeleteDQL(AuthorRepository $repo){
        $repo->DeleteAuthor();
        return $this->redirectToRoute('list_Facture');
    }

    
    #[Route('/facture/list', name: 'app_facture_list_ordered', methods: ['GET'])]
    public function listFactureOrderByDate(FactureRepository $factRepository): Response
    {
        return $this->render('facture/orderedList.html.twig', [
            'factures' => $factRepository->showAllFacturesOrderByDate(),
        ]);
    }

    #[Route('/facture/{id}/pdf', name: 'facture_pdf')]
    public function facturePdf(int $id, FactureRepository $factureRepository): Response
    {
        $facture = $factureRepository->find($id);

        if (!$facture) {
            throw $this->createNotFoundException('La facture n\'existe pas.');
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);


        $dompdf = new Dompdf($options);

        $html = $this->renderView('facture/pdf.html.twig', [
            'facture' => $facture,
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $output = $dompdf->output();

        return new Response($output, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/statistiques/produit-plus-frequent', name: 'produit_plus_frequent')]
    public function produitPlusFrequent(FactureRepository $factureRepository): Response
    {
        $factures = $factureRepository->findAll();

        $occurrencesProduits = [];

        foreach ($factures as $facture) {
            $produitsFacture = $facture->getProduits();
            foreach ($produitsFacture as $produit) {
                $nomProduit = $produit->getName();
                if (!isset($occurrencesProduits[$nomProduit])) {
                    $occurrencesProduits[$nomProduit] = 1;
                } else {
                    $occurrencesProduits[$nomProduit]++;
                }
            }
        }

        $produitPlusFrequent = '';
        $occurrencesMax = 0;
        foreach ($occurrencesProduits as $nomProduit => $occurrences) {
            if ($occurrences > $occurrencesMax) {
                $produitPlusFrequent = $nomProduit;
                $occurrencesMax = $occurrences;
            }
        }

        return $this->render('statistiques/produit_plus_frequent.html.twig', [
            'produit_plus_frequent' => $produitPlusFrequent,
            'occurrences_max' => $occurrencesMax,
        ]);
    }
}
   
    

