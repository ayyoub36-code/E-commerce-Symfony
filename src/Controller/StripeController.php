<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-checkout-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $entityManager, $reference): Response
    {
        // declarer le tab stripe 
        $products_to_stripe = [];

        $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }

        foreach ($order->getOrderDetails()->getValues() as $product) {

            $product_object = $entityManager->getRepository(Product::class)->findOneBy(['name' => $product->getProduct()]);

            //  tableau que j'envoie a stripe 
            $products_to_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => ['http://127.0.0.1:8000' . "/uploads/" . $product_object->getIllustration()],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        //  tableau pour le transporteur  que j'envoie a stripe 
        $products_to_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => ['http://127.0.0.1:8000'],
                ],
            ],
            'quantity' => 1,
        ];

        // utiliser API Stripe 
        Stripe::setApiKey('sk_test_51KY7K7IszouRGcJsHYPqslnv5Xn6qTAP37TB1U31mPYpSjWQ0AIvTZvew6Nr3CoP4gEPmTsWU8k7fH7gc1l18xGH00ZRQZAtY8');

        $session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $products_to_stripe
            ],
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://127.0.0.1:8000/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($session->id);
        $entityManager->flush();

        $response = new JsonResponse(['id' => $session->id]);

        return $response;
    }
}
