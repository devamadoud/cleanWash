<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/customers')]
class CustomerController extends AbstractController
{
    #[Route('/', name: 'customer.index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): Response
    {

        $user = $this->getUser();
        if(!($user instanceof User)){
            // Restreindre l'accés et redirectionner vers la page d'accueil
            return $this->redirectToRoute('home');
        }

        // Verifier si l'utilisateur connecté est le propriétaire du shop
        if($user->getShop() != null){
            $customers = $user->getShop()->getCustomers();
        }

        // Verifier si l'utilisateur connecté est un collecteur (un employé)
        if($user->getJob() != null and $user->getJob()->getPoste() == 'collecteur'){
            $customers = $user->getJob()->getShop()->getCustomers();
        }

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/new', name: 'customer.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $isEmploye = false;
        if($user instanceof User){
            // Verifier si un utilisateur est connecté et si son poste n'est pas gerant ou collecteur
            if($user->getShop() == null and $user->getJob()->getPoste() != 'collecteur'){
                $this->addFlash('error', 'Vous devez être collecteur pour ajouter un nouveau client.');
                return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
            }

            $isEmploye = true;
        }

        $telefone = $request->getSession()->get('telephone');
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['phoneNumber' => $telefone]);

        if(!$customer){
            $customer = new Customer();
            if($telefone != null){
                $customer->setPhoneNumber($telefone);
            }
        }


        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();

            // Si c'est un employé qui a ajoute un nouveau client, notifier l'utilisateur
            if($isEmploye){
                $this->addFlash('success', 'Le client a bien été ajouté.');
                return $this->redirectToRoute('customer.index', [], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash('success', "Vous venez d'enregistrer vos informations. notre service vous contactera dans les plus brefs délais.");
                return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/addcollecte', name: 'customer.collecte', methods: ['GET', 'POST'])]
    public function addcollecte(Request $request, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('telefone', TelType::class, [
                'required' => true,
                'label' => 'N° de téléphone',
            ])->add('waiteForAgentChoice', ChoiceType::class, [
                'required' => true,
                'label' => 'Vous pouvez le faire ?',
                'label_attr' => ['class' => 'text-gray-500 font-semibold text-md mb-2'],
                'choices' => [
                    'J\'attend le collecteur' => 'waitForAgent',
                    'Je peux le faire' => 'canDoIt',
                ],
                'data' => 'waitForAgent',
                'empty_data' => 'waitForAgent',
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'flex justify-center'
                ]
            ])->add('collecteChoice', ChoiceType::class, [
                'label_attr' => ['class' => 'text-gray-500 font-semibold text-md mb-2'],
                'choices' => [
                    'Par type de vêtement/nombre' => 'clothingType',
                    'Par kg' => 'clothingPea',
                ],
                'placeholder' => null,
                'data' => 'none',
                'empty_data' => 'clothingType',
                'label' => 'Type de collecte',
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'flex justify-center'
                ],
                'required' => false,
            ])->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $telefone = $form->get('telefone')->getData();
            $waitForAgent = $form->get('waiteForAgentChoice')->getData();

            $url = $urlGenerator->generate('customer.new', [], UrlGeneratorInterface::ABSOLUTE_URL);
            if($waitForAgent === 'canDoIt'){
                // Rediriger l'utilisateur vert la page de collecte avec l'id du client en parametre get
                $url = $urlGenerator->generate('collecte.new', ['tel' => $telefone, 'type' => $form->get('collecteChoice')->getData()]);
            }else{
                $url = $urlGenerator->generate('customer.new', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $request->getSession()->set('telephone', $telefone);
            }


            return $this->redirect($url);
        }
        return $this->render('customer/collecte.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'customer.show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'customer.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('customer.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'customer.delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('customer.index', [], Response::HTTP_SEE_OTHER);
    }
}
