<?php

namespace App\Controller;

use App\Entity\ClothingType;
use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'service')]
    public function index(): Response
    {
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing(EntityManagerInterface $entityManager): Response
    {
        $clothingTypes = $entityManager->getRepository(ClothingType::class)->findAll();
        return $this->render('service/pricing.html.twig', [
            'clothingTypes' => $clothingTypes,
        ]);
    }

    #[Route('/propos', name: 'propos')]
    public function propos(): Response
    {
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // Recuperer le numéro de téléfone saisie par l'utilisateur
            $telefone = $form->get('phone')->getData();

            // Initialiser la date d'aujourd'hui
            $now = new \DateTimeImmutable();

            // Reinitialiser la date a 00:00
            $now->format('Y-m-d');

            // Recuperer la date d'hier a partir de la date d'aujourd'hui
            $yesterday = $now->modify('-1 day');
            
            // Initialiser la repository de Contact grace au manager de doctrine
            // Recuperer un message envoye hier correspondant au numéro de telephone entree par l'utilisateur
            $lasteMessage = $entityManager->getRepository(Contact::class)->findOneBy(['phone' => $telefone, 'sendedAt' => $yesterday]);

            // Verifier si l'utilisateur n'as pas envoyé de message dans les dernieures 24 heures
            if($lasteMessage){
                $this->addFlash('error', 'Vous nous avez déjà envoyé un message dans les dernieures 24 heures, merci de patienter notre service vous contactera dans les prochaines 24 heures.');
                return $this->redirectToRoute('contact');
            }

            $contact->setSendedAt(new \DateTimeImmutable())
                ->setViewed(false);
            $entityManager->persist($contact);
            $entityManager->flush();
            $this->addFlash('success', 'Votre message a bien été envoyé, nous vous contacterons dans les prochaines 24 heures, merci !');
            return $this->redirectToRoute('contact');
        }
        return $this->render('service/contact.html.twig', [
            'form' => $form,
        ]);
    }
}
