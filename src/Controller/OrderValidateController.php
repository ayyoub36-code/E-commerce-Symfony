<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Classe\MailJet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderValidateController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['stripeSessionId' => $stripeSessionId]);

        // verifier si ya bien une commande et que l user co et bien celui qui a passé la commande
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // modifier le statut = 1 => payée
        if ($order->getState() == 0) {
            $order->setState(1);
            $this->entityManager->flush();

            // vider la session cart 
            $cart->remove();

            // envoyer un email au client pour confirmer la commande 
            // pour modifier le template mail le creer sur mailJet et envoyer un var Id Template => send($id)
            $mail = new MailJet();
            $content = "Bonjour " . $order->getUser()->getFirstname() . "<br/>Merci pour votre commande sur La Boutique Marocaine.<br/><br/>Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita voluptates, distinctio numquam sunt delectus minima iste alias fugit aut pariatur optio inventore.";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur La Boutique Marocaine est bien validée', $content);
        }


        return $this->render('order_validate/index.html.twig', [
            'order' => $order,
        ]);
    }
}
