<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;


class ProduitController extends AbstractController
{
    #[Route('/index', name: 'app_produit')]
    public function index(ManagerRegistry $manager, Request $request): Response
    {
        $em = $manager->getManager();

        $prod = new Produit();

        $form = $this->createForm(ProduitType::class, $prod);
        $form->handleRequest($request);
        if (($form->isSubmitted() && $form->isValid()))
        {
            $em->persist($prod);
            $em->flush();
            


            return $this->redirectToRoute('add_Prod');
        
    }
    return $this->render('produit/index.html.twig', ['form' => $form->createView(),]);
}
    #[Route('/showProduit', name: 'show_Produit')]
    public function showProduit(ProduitRepository $prodrepository) : Response
    {
        $produits = $prodrepository->findAll();
        return $this->render('produit/show.html.twig', [
            'produits' => $produits,
        ]);
    }
   
    
    #[Route('/produit/{id}', name: 'Produit_details')]
    public function ProduitDetails($id)
    {
        $prod = null;
        
        foreach ($this->produits as $prodData) {
            if ($prodData['id'] == $id) {
                $prod = $prodData;
            };
        };
        return $this->render('produit/show.html.twig', [
            'produit' => $prod,
            'id' => $id
        ]);
    }
   
    #[Route('/addProd', name: 'add_Prod')]
    public function addProd(ManagerRegistry $manager, Request $request, SluggerInterface $slugger): Response
    {
        $em = $manager->getManager();

        $prod = new Produit();

        $form = $this->createForm(ProduitType::class, $prod);
        $form->handleRequest($request);
        if (($form->isSubmitted() && $form->isValid()))
        {
            /** @var UploadedFile $photoFile */
        $photoFile = $form->get('photo')->getData();

        if ($photoFile) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('photos_directory'),
                    $newFilename
                );
            } catch (FileException $e) { }

            $prod->setPhoto($photoFile);
        }
            $em->persist($prod);
            $em->flush();

            return $this->redirectToRoute('add_Prod');
        }
        return $this->render('produit/Add.html.twig', [
            'produit' => $prod,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/list', name: 'list_Produit')]
     public function listProduit(ProduitRepository $prodrepository): Response
     {
        $produits = $prodrepository->findAll();

         return $this->render('produit\list.html.twig', [
             'produits' => $produits,
             
         ]);
     }

    #[Route('/editproduit/{id}', name: 'produit_edit')]
    public function editproduit(Request $request, ManagerRegistry $manager, $id, ProduitRepository $prodrepository): Response
    {
        $em = $manager->getManager();

        $prod = $prodrepository->find($id);
        $form = $this->createForm(ProduitType::class, $prod);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->persist($prod);
            $em->flush();
            return $this->redirectToRoute('list_Produit');
        }
        return $this->renderForm('produit/edit.html.twig', ['form' => $form]);

    }

    #[Route('/deleteproduit/{id}', name: 'Produit_delete')]
    public function deleteProd(Request $request, $id, ManagerRegistry $manager, ProduitRepository $prodrepository): Response
   {
    $em = $manager->getManager();
    $prod = $prodrepository->find($id);

    if ($prod !== null) {
        $em->remove($prod);
        $em->flush();
        
        return $this->redirectToRoute('list_Produit');
    } else {
        $errorMessage = 'Produit not found.';
        
        return $this->render('produit/list.html.twig', [
            'errorMessage' => $errorMessage,
        ]);
    }
   } 

#[Route('/RechercheDQL', name:'Search')]
    function RechercheDQL(ProduitRepository $repo,Request $request){
        $min=$request->get('min');
        $max=$request->get('max');
        $prod=$repo->SearchproduitDQL($min,$max);
        return $this->render('produit/listProduit.html.twig', [
            'produits' => $prod,
        ]);
    }

    #[Route('/DeleteDQL', name:'DD')]
    function DeleteDQL(AuthorRepository $repo){
        $repo->DeleteAuthor();
        return $this->redirectToRoute('list_Produit');
    }

    
    #[Route('/produit/lis', name: 'app_produit_list_ordered', methods: ['GET'])]
    public function listProduitOrderByCategorie(ProduitRepository $prodRepository): Response
    {
        return $this->render('produit/orderedList.html.twig', [
            'produits' => $prodRepository->showAllProduitsOrderBycategorie(),
        ]);
    }
    
    #[Route('/test-statistique', name: 'test_statistique')] 
    public function testStatistique(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        $statistiques = $this->calculateStatistics($produits);

        dump($statistiques);

        return new Response('Test de statistique réussi ! Vérifiez la console pour voir les résultats.');
    }
    #[Route('/api/produit/statistiques', name: 'api_produit_statistiques')]
    public function produitStatistiques(ProduitRepository $produitRepository): JsonResponse
    {
        $statistics = $produitRepository->getSalesStatistics();

        return $this->json($statistics);
    }
       
    private function calculateStatistics($produits)
    {
        $statistics = [];
    
        foreach ($produits as $produit) {
            $name = $produit->getName();
            $statistics[$name] = isset($statistics[$name]) ? $statistics[$name] + 1 : 1;
        }
    
        return $statistics;
    }
    

}


