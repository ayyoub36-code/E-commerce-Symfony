<?php

namespace App\Controller;

use App\Classe\Search;
use App\Entity\Product;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/Nos-Produits", name="products")
     */
    public function index(Request $request)
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        // formulaire de recherche
        $search = new Search;
        $message = null;
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search);
        }
        if (count($products) == null) {
            $message = 'Désolé on a pas trouvé de résultat pour votre recherche ';
        }

        return $this->render(
            'product/index.html.twig',
            [
                'products' => $products,
                'message' => $message,
                'recherche' => $search->string,
                'form' => $form->createView()
            ]
        );
    }


    /**
     * @Route("/Produit/{slug}", name="product")
     */
    public function show($slug): Response
    {

        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        $best_products = $this->entityManager->getRepository(Product::class)->findBy(['isBest' => 1]);

        if (!$product) {
            return $this->redirectToRoute('products');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $best_products
        ]);
    }
}
