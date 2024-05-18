<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // recuperer le mot de passe de confirmation
            $confirmedPassword = $form->get('confirmPassword')->getData();

            // recuperer le plainPassword de l'utilisateur
            $plainPassword = $form->get('plainPassword')->getData();

            // verifier si le mot de passe et le mot de passe de confirmation sont identiques
            if ($plainPassword !== $confirmedPassword) {
                $this->addFlash('error', 'Les mots de passe ne sont pas identiques.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // recuperer le userType de l'utilisateur
            $userType = $form->get('userType')->getData();

            // Assigner un role correspondant au type de l'utilisateur
            switch ($userType) {
                case 'gerant': $user->setRoles(['ROLE_OWNER']);
                break;
                
                case 'employe': $user->setRoles(['ROLE_EMPLOYEE']);
                break;
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
