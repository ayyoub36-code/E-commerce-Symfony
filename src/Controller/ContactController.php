<?php

namespace App\Controller;

use App\Classe\MailJet;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="contact")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Merci de nous avoir contacter. Notre équipe va vous répondre dans les plus brefs délais.');
            // dd($form->getData());
            // envoie de mail ou creer une entité dans la bd pour stocker les demandes de contact 
            $mail = new MailJet();
            $content = "Bonjour " . $form->get('prenom')->getData() . "<br/>Merci de nous avoir contacter. <br/> On revient vers vous dans les meilleurs délais.<br/><br/>Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita voluptates, distinctio numquam sunt delectus minima iste alias fugit aut pariatur optio inventore.";
            $mail->send($form->get('email')->getData(), $form->get('nom')->getData(), 'Nous avons bien reçu votre demande', $content);
            // return $this->redirectToRoute('home');
        }

        return $this->render('contact/index.html.twig', ['form' => $form->createView()]);
    }
}
