<?php

namespace App\Controller;

use App\Entity\User;
use App\Classe\MailJet;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $success = null;
        $fail = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            // verif si le user existe déja dans la base searchByMail
            $search_mail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if (!$search_mail) {
                // hasher le pass 
                $password = $encoder->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                // entityManager qui gère les requettes 
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // envoie du mail a user enregistré 
                $mail = new MailJet();
                $content = "Bonjour " . $user->getFirstname() . "<br/>Bienvenue sur la première boutique dédiée au made in Maroc.<br/><br/>Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita voluptates, distinctio numquam sunt delectus minima iste alias fugit aut pariatur optio inventore.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur La Boutique Marocaine', $content);

                // notif success
                $success = "Votre inscription s'est bien déroulée. Vous pouvez dès à présent vous connecter à votre compte. ";
            } else {
                // notif fail 
                $fail = "L'email que vous avez renseigné existe déja !";
            }
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'fail' => $fail
        ]);
    }
}
