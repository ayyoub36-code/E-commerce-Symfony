<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use DateTimeImmutable;
use App\Classe\MailJet;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-pass-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        // verifier si l user est co !
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // verifier l envoie du mail 
        if ($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);

            if ($user) {

                // 1 : enregistrer dans la BD la requette de reset passWord avec user, token, createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new DateTimeImmutable());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // 2 : envoyer un email a user avec un lien lui permettant de mettre a jour son mot de pass
                $mail = new MailJet();
                $url = $this->generateUrl('update_password', ['token' => $reset_password->getToken()]);

                $content = "Bonjour " . $user->getFirstname() . ",<br/> Vous avez demandé à réinitialiser votre mot de passe sur la Boutique Marocaine.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='" . $url . "'>mettre à jour votre mot de passe<a/>.";
                $mail->send($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname(), "Réinitialiser votre mot de passe", $content);
                $this->addFlash('notice', 'Vous allez recevoir dans quelques secondes un mail avec la procedure pour réinitialiser votre mot de passe');
            } else {
                $this->addFlash('notice', 'Votre adresse email est incorrect !');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-pass-oublie/{token}", name="update_password")
     */

    public function update(Request $request, UserPasswordHasherInterface $encoder, $token)
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);

        // verif si ya bien une entrée avec ce token 
        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }
        // verifier si le createdAt == now - 3h !
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de réinitialisation à expiré veuillez la renouveller !');
            return $this->redirectToRoute('reset_password');
        } else {

            // rendre une vue avec mot de passe et confirmation 
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                // encodage du mot de passe et flush
                $new_password = $form->get('new_password')->getData();
                $user = $reset_password->getUser();
                $password = $encoder->hashPassword($user, $new_password);
                $user->setPassword($password);
                $this->entityManager->flush();

                // redirect to connexion page 
                $this->addFlash('notice', 'Votre mot de passe a été mis à jour avec succes.');
                return $this->redirectToRoute('app_login');
            }
            return $this->render('reset_password/update.html.twig', ['form' => $form->createView()]);
        }
    }
}
