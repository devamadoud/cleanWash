<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordResetType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{

    #[Route('/{user}', name: 'profile.index', methods: ['GET', 'POST'])]
    public function index(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $connectedUser = $this->getUser();

        if($connectedUser instanceof User and $user != $connectedUser){
            return $this->redirectToRoute("app_login");
        }

        $resetPasswordForm = $this->createFormBuilder()
            ->add('telefone', TelType::class, ['label' => 'Votre numéro de téléfone', 'data' => $user->getTelefone(), 'required' => false, 'attr' => ['placeholder' => '7xxxxxxxx'], 'disabled' => true])
            ->add('setPassword', PasswordResetType::class, ['label' => false, 'required' => true])->getForm()
        ;
        $resetPasswordForm->handleRequest($request);

        if($resetPasswordForm->isSubmitted() and $resetPasswordForm->isValid()){
            
            $previousPassword = $resetPasswordForm->get('setPassword')['previous']->getData();
            $new = $resetPasswordForm->get('setPassword')['new']->getData();
            $confirm = $resetPasswordForm->get('setPassword')['confirm']->getData();

            if(password_verify($previousPassword, $user->getPassword()) and $new == $confirm){

                if($new != $confirm){
                    $this->addFlash('error', 'Les mots de passe ne sont pas identiques.');
                    return $this->render('profile/index.html.twig', [
                        'user' => $user,
                        'resetPasswordForm' => $resetPasswordForm->createView(),
                    ]);
                }
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user, $new
                    )
                );
                $this->addFlash('success', 'Mot de passe mis à jour avec succes');
            } else {
                $this->addFlash('error', 'Mot de passe incorrect');
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'resetPasswordForm' => $resetPasswordForm->createView(),
        ]);
    }
}
