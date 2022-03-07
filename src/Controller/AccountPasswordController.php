<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/compte/modifier-mon-mot-de-passe", name="account_password")
     */
    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {

        $success = null;
        $fail = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $old_pass = $form->get('old_password')->getData();

            if ($encoder->isPasswordValid($user, $old_pass)) {
                $new_pass = $form->get('new_password')->getData();
                $password = $encoder->hashPassword($user, $new_pass);

                $user->setPassword($password);
                $this->entityManager->flush();
                $success = "Votre mot de passe a bien été mis à jour.";
            } else {
                $fail = "Votre mot de passe actuel n'est pas le bon !";
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'success' => $success,
            'fail' => $fail
        ]);
    }
}
