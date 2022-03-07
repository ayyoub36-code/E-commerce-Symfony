<?php

namespace App\Controller;

use App\Classe\MailJet;
use App\Entity\Header;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        // requette pour chercher les best products 
        $products = $this->entityManager->getRepository(Product::class)->findBy(['isBest' => 1]);
        $headers = $this->entityManager->getRepository(Header::class)->findAll();

        return $this->render(
            'home/index.html.twig',
            [
                'products' => $products,
                'headers' => $headers
            ]
        );
    }
}
