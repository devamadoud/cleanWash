<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\User;
use App\Form\EmployesType;
use App\Repository\EmployeRepository;
use App\Security\Voter\EmployeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employes')]
class EmployesController extends AbstractController
{
    #[Route('/', name: 'employes.index')]
    #[IsGranted(EmployeVoter::LIST)]
    public function index()
    {
        $gerant = $this->getUser();

        if ($gerant instanceof User and $gerant->getShop() != null){
            $employes = $gerant->getShop()->getEmployes();
        }
        
        return $this->render('employes/index.html.twig', [
            'employes' => $employes
        ]);
    }
    #[Route('/new', name: 'employes.new')]
    #[IsGranted(EmployeVoter::CREATE)]
    public function new(EntityManagerInterface $em, Request $request): Response
    {   
        $employe_user = [];
        $userRepo = $em->getRepository(User::class);
        $employeRepo = $em->getRepository(Employe::class);
        $form = $this->createForm(EmployesType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid()){
            $telefone = $form->get('phoneNumber')->getData();
            $employeField = $form->get('employe')->getData();
            $gerant = $this->getUser();

            if(!($gerant instanceof User)){
                $this->addFlash('error', 'Une erreur c\'est produit veuillez reéssayer plus tard, si le probleme persiste veuillez nous contacter !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }

            $shop = $gerant->getShop();
            if(!$shop){
                $this->addFlash('error', 'Vous êtes pas Gérant vous ne pouvez pas avoir d\'employé. si vous pensez que c\'est une erreur veuillez nous contacter !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }
            

            if($gerant->getUserType() !== 'gerant'){
                $this->addFlash('error', 'Vous êtes pas Gérant vous ne pouvez pas avoir d\'employé. si vous pensez que c\'est une erreur veuillez nous contacter !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }

            $employe_user = $userRepo->findOneByTelefone($telefone);
            $isEmployed = $employeRepo->findOneByUser($employe_user);
            if($isEmployed and $isEmployed->getRevokedAt() === null){
                $this->addFlash('error', 'Cet utilisateur(ice) est déjà employée par une boutique de la plateform, ou il n\'as pas été révoké par son patron !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }
            if(!$employe_user){
                $this->addFlash('error', 'Aucun(e) utilisateur(ice) trouvé. veuillez vérifier le numéro de téléfone ou demander a votre employé de s\'inscrire sur la plateforme.');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }

            if(!($employe_user instanceof User)){
                $this->addFlash('error', 'Une erreur c\'est produit veuillez reéssayer plus tard, "L\'utilisateur n\'est pas un potentiel employé" si le probleme persiste veuillez nous contacter !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }
            if($employe_user->getUserType() !== 'employe'){
                $this->addFlash('error', 'Cette utilisateur ne c\'est pas inscrit en tant que potentiel employée !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }

            if($employeField === null){
                $this->addFlash('success', 'L\'employé idéal de votre entreprise !');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }
            
            if($employeField == $employe_user->getId()){
                $emPoste = $form->get('emPoste')->getData();
                $employe = $employe_user->getJob();

                if(!$employe){
                    $employe = new Employe;
                    $employe_user->setJob($employe);
                }

                $employe->setShop($shop)
                    ->setPoste($emPoste)
                    ->setEmployedAt(new \DateTimeImmutable())
                    ->setRevokedAt(null)
                    ->setActive(true)
                ;
                $em->persist($employe);
                $em->flush();

                $this->addFlash('success', 'Vous avez ajouté un employé au poste de : '.$emPoste.'');
                return $this->render('employes/new.html.twig', [
                    'form' => $form,
                    'employe_user' => $employe_user
                ]);
            }else{
                $this->addFlash('error', "Une erreur c'est produit! l'utilisateur que vous soumis n'est pas celuis selectionée ! Veuillez contacter le support !");
            }
            
        }
        return $this->render('employes/new.html.twig', [
            'form' => $form,
            'employe_user' => $employe_user
        ]);
    }

    /**
    * Suspendre un employe de la boutique (Il ne pourras plus travailler jusqu'à nouvelle ordre)
    */
    #[Route('/{id}/suspend', name: 'employe.suspend', methods: ['GET', 'POST'])]
    #[IsGranted(EmployeVoter::REVOKE, 'employe')]
    public function suspend(Employe $employe, Request $request, EntityManagerInterface $em): Response
    {
        $employe->setActive(false);
        
        $em->persist($employe);
        $em->flush();
        $this->addFlash('success', "Vous avez suspendu votre {$employe->getPoste()}, Il n'auras plus accès à votre boutique jusqu'a nouvelle ordre !");

        return $this->redirectToRoute('employes.index');
    }

    /**
    * Reactiver un employe après l'avoir suspendu (Il pourra travailler a nouveau)
    */
    #[Route('/{id}/reactivate', name: 'employe.reactivate', methods: ['GET', 'POST'])]
    #[IsGranted(EmployeVoter::REVOKE, 'employe')]
    public function reactivate(Employe $employe, Request $request, EntityManagerInterface $em): Response
    {
        $employe->setActive(true);
        $employe->setRevokedAt(null);
        
        $em->persist($employe);
        $em->flush();

        $this->addFlash('success', "Vous avez reactivé votre {$employe->getPoste()}, Il pourra travailler a nouveau");

        return $this->redirectToRoute('employes.index');
    }

    /**
    * Revoker un employe de la boutique (Renvoyer un employe de la boutique de son employeur)
    */
    #[Route('/{id}/revoke', name: 'employe.revoke', methods: ['GET', 'POST'])]
    #[IsGranted(EmployeVoter::REVOKE, 'employe')]
    public function revoke(Employe $employe, Request $request, EntityManagerInterface $em): Response
    {
        $employe->setRevokedAt(new \DateTimeImmutable())
            ->setActive(false)
            ->setShop(null)
        ;

        $em->persist($employe);
        $em->flush();

        $this->addFlash('success', "Vous avez renvoyé votre {$employe->getPoste()}, Il n'auras plus accées a votre boutique a travert la plateforme");
        $this->addFlash('infos', "Vous pouvez toujours l'ajouter une nouvelle fois en cliquant sur le bouton 'Embaucher', ou embaucher un nouveau employé de la même manière !");

        return $this->redirectToRoute('employes.index');
    }
}
