<?php

namespace App\Controller;

use App\Entity\ClothingPea;
use App\Entity\Collecte;
use App\Entity\CollecteDetailles;
use App\Entity\CollecteDetaillesPea;
use App\Entity\Customer;
use App\Entity\Shop;
use App\Entity\User;
use App\Form\CollecteDetaillesPeaType;
use App\Form\CollecteDetailsType;
use App\Form\CollecteType;
use App\Form\CustomerType;
use App\Repository\CollecteRepository;
use App\Security\Voter\CollecteVoter;
use App\Services\QrCodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/collectes')]
class CollecteController extends AbstractController
{
    #[Route('/', name: 'collecte.index', methods: ['GET'])]
    #[IsGranted(CollecteVoter::LIST)]
    public function index(CollecteRepository $collecteRepository): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', 'Vous devez vous identifier pour acceder a la liste des collectes');
            return $this->redirectToRoute('home');
        }

        // Verifier si l'utilisateur connecté est le propriétaire du shop
        if($user->getShop() != null){
            $collectes = $user->getShop()->getCollectes();
        }

        // Verifier si l'utilisateur connecté est un collecteur & livreur, caissier ou un laveur (un employé)
        if($user->getJob() != null){
            $collectes = $user->getJob()->getShop()->getCollectes();
        }
        return $this->render('collecte/index.html.twig', [
            'collectes' => $collectes,
        ]);
    }

    #[Route('/qrscan', name: 'collecte.qrscan', methods: ['GET', 'POST'])]
    public function qrscan(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('collecte/qrCodeScan.html.twig');
    }

    /**
    * @param User|null $user
    */
    #[Route('/{tel}/{type}/new', name: 'collecte.new', methods: ['GET', 'POST'])]
    #[IsGranted(CollecteVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, $tel, $type): Response
    {
        // Initialiser l'utilisateur courant
        $user = $this->getUser();

        // Verifier si l'utilisateur est connecté, si non initialiser le shop par defaut de la platform
        if(!($user instanceof User)) {
            $shop = $entityManager->getRepository(User::class)->findOneByTelefone('771051360')->getShop();
        }

        // Verifier si l'utilisateur connecté est le propriétaire du shop, initialiser le shop
        if($user instanceof User and $user->getShop() != null) {
            $shop = $user->getShop();
        }

        // Verifier si l'utilisateur connecté est un collecteur (un employé) initialiser le shop
        if($user instanceof User and $user->getJob() != null) {
            $shop = $user->getJob()->getShop();
        }

        // Si tel existe dans l'url, l'utiliser pour rechercher et initialiser le client, s'il n'existe pas, creer un nouveau client
        if($tel){
            $customer = $entityManager->getRepository(Customer::class)->findOneByPhoneNumber($tel);
        }

        if($customer and $customer->getCollectes()->count() > 0){
            foreach ($customer->getCollectes() as $key => $customerCollecte) {
                if($customerCollecte->getStatus() == "En attente" || $customerCollecte->getStatus() == "En cours"){
                    if($user){
                        $this->addFlash('error', 'Ce client a deja une collecte '.$customerCollecte->getStatus().'');
                        return $this->redirectToRoute('home');
                    }
                    $this->addFlash('error', 'Vous avez deja une collecte '.$customerCollecte->getStatus().', merci de patienter notre service vous contactera dans les plus bref delais.');
                    return $this->redirectToRoute('home');
                }
            }
        }

        // Initialiser la collecte
        $collecte = new Collecte();
        $form = [];

        // Initialier le formulaire
        if($type === 'clothingType'){
            $form = $this->createForm(CollecteType::class, $collecte);
        }else {
            $form = $this->createFormBuilder($collecte)->add('pea', NumberType::class, [
                'label' => 'Pois de votre collecte',
                'attr' => [
                    'placeholder' => 'Ex. 7'
                ],
                'required' => true,
                'mapped' => false
            ])->getForm();
        }

        // Si l'utilisateur n'est pas connecté et que le client existe on initialise le shop du client
        if(!($user instanceof User)){
            if($customer){
                $shop = $customer->getShop();
            }
        }

        $form->handleRequest($request);
        // Verifier si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {

            // Si l'utilisateur n'existe pas dans la base de données on l'ajoute
            if(!$customer){

                $customer = new Customer();

                // Récupérer les informations du nouveau client
                $customerTelefone = $tel;

                $customer->setPhoneNumber($customerTelefone);
            }

            // si le client existe deja mais il effectue une nouvelle collecte dans un shop different de son shop actuel, on le met a jour
            if($customer->getShop() != $shop){
                $customer->setShop($shop);
            }

            $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = substr(str_shuffle($caracteres), 0, 4);

            if($type === 'clothingPea'){
                $collecteDetaillesPea = new CollecteDetaillesPea();
                $pea = $form->get('pea')->getData();
                $clothingPea = $entityManager->getRepository(ClothingPea::class)->createQueryBuilder('c')
                        ->where('c.pea <= :pea and c.peaMax >= :pea')
                        ->setParameter('pea', $pea)
                        ->getQuery()
                        ->getOneOrNullResult()
                ;
                $collecteDetaillesPea->setClothingPea($clothingPea)
                        ->setPea($pea);

                $collecte->addCollecteDetaillesPea($collecteDetaillesPea);
            }

            if($type === 'clothingType'){
                
                foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                    $collecte->addCollecteDetaille($collecteD);
                }
            }

            $collecte->setCollectedAt(new \DateTimeImmutable())
                ->setShop($shop)
                ->setCustomer($customer)
                ->setStatus('En attente')
                ->setSecret($code)
                ->setCollecteType($type)
            ;

            $entityManager->persist($collecte);
            $entityManager->flush();

            return $this->redirectToRoute("collecte.recap", ['id' => $collecte->getId() ], Response::HTTP_SEE_OTHER);
            
        }

        return $this->render('collecte/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
            'type' => $type
        ]);
    }

    #[Route('/{id}/recap', name: 'collecte.recap', methods: ['GET', 'POST'])]
    #[IsGranted(CollecteVoter::CREATE)]
    public function recap(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        if(!$collecte){
            // Regiriger vers la derniere page
            $this->addFlash('error', 'La collecte n\'existe pas, veuillez enregistrer une nouvelle collecte pour continuer');
            return $this->redirectToRoute('customer.collecte');
        }

        $form = $this->createFormBuilder($collecte)
                ->add('paymentChoice', ChoiceType::class, [
            'choices' => [
                'At collection' => 'pay-on-collect',
                'At delivery' => 'pay-on-deliver'],
                'mapped' => false,
            'expanded' => true, 'multiple' => false])->getForm()
        ;

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $collecte->setPaymentChoice($form->get('paymentChoice')->getData());

            $entityManager->flush();

            if($collecte->getCustomer()->getFullName() == null and $collecte->getCustomer()->getAdress() == null){
                return $this->redirectToRoute('collecte.customer', ['id' => $collecte->getId()]);
            }

            if($this->getUser()){
                $this->addFlash('success', 'Le client a bien été ajoutée.');
                return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('success', 'Votre collecte a bien été enregistrée notre equipe va vous contacter dans les plus brefs delais.');
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        // Si la collecte est un clothingTypePea
        if($collecte->getCollecteType() === 'clothingType'){
            $collectePea = 'clothingType';
        }else{
            $collectePea = 'clothingPea';
        }


        return $this->render('collecte/recap.html.twig', [
            'collecte' => $collecte,
            'collectePea' => $collectePea,
            'form' => $form
        ]);
    }

    #[Route('/{id}/customer', name: 'collecte.customer', methods: ['GET', 'POST'])]
    #[IsGranted(CollecteVoter::CREATE)]
    public function customer(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        if(!$collecte){
            // Regiriger vers la derniere page
            $this->addFlash('error', 'La collecte n\'existe pas, veuillez enregistrer une nouvelle collecte pour continuer');
            return $this->redirect($request->headers->get('referer'));
        }

        $customer = $collecte->getCustomer();
        $tel = $customer->getPhoneNumber();

        $form = $this->createForm(CustomerType::
            class, $customer)
            ->add('fullName', null, ['label' => 'Name'])
            ->add('phoneNumber', null, ['label' => 'Phone'])
            ->add('adress', null, ['label' => 'Address'])
            ->add('coordLng', TextType::class,
                ['label' => false, 'attr' => ['placeholder' => 'Longitude']])
            ->add('coordLat', TextType::class,
                ['label' => false, 'attr' => ['placeholder' => 'Latitude']])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            if($this->getUser()){
                $this->addFlash('success', 'Le client a bien été ajoutée.');
                return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('success', 'Votre collecte a bien été enregistrée notre equipe va vous contacter dans les plus brefs delais.');
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collecte/newCustomer.html.twig', [
            'collecte' => $collecte,
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name: 'collecte.show', methods: ['GET'])]
    #[IsGranted(CollecteVoter::VIEW, 'collecte')]
    public function show(Collecte $collecte, Request $request): Response
    {
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $url = $baseurl.$this->generateUrl('collecte.show', ['id' => $collecte->getId()]);
        $qrCodeGenerator = new QrCodeGenerator();
        $qrCode = $qrCodeGenerator->generateQrCode($url);

        return $this->render('collecte/show.html.twig', [
            'collecte' => $collecte,
            'collecteDetails' => $collecte->getCollecteDetailles(),
            'qrCode' => $qrCode
        ]);
    }

    #[Route('/{id}/edit', name: 'collecte.edit', methods: ['GET', 'POST'])]
    #[IsGranted(CollecteVoter::EDIT, 'collecte')]
    public function edit(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {

        $customer = $collecte->getCustomer();

        $form = $this->createForm(CollecteType::class, $collecte);

        // Ajouter le client dans le formulaire
            $form->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'fullName',
                'data' => $customer,
                'label' => 'Client',
                'disabled' => true
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if($this->getUser()){
                $this->addFlash('success', 'La collecte a bien été modifiée');
                return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
            }
            if(!$this->getUser()){
                return $this->redirectToRoute('collecte.recap', ['id' => $collecte->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('collecte/edit.html.twig', [
            'collecte' => $collecte,
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/confirme', name: 'collecte.confirme', methods: ['POST', 'GET'])]
    #[IsGranted(CollecteVoter::CONFIRME, 'collecte')]
    public function confirme(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if(!($user instanceof User)){
            $this->addFlash('error', 'Vous devez vous identifier pour confirmer une collecte');
            return $this->redirectToRoute('home');
        }

        if(!$collecte){
            $this->addFlash('error', 'Une erreur c\'est produite, la collecte n\'existe pas.');
            return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
        }
        
        // Verifier si l'utilisateur connecté est le propriétaire du shop ou un collecteur (un employé)
        if($user->getShop() != null or $user->getJob() != null and $user->getJob()->getPoste() == 'collecteur'){
            $confirmedBy = $user->getShop() != null ? 'Le patron '.$user->getFullName() : 'Le collecteur '.$user->getFullName();
            $collecte->setConfirmedBy($confirmedBy)
                ->setStatus('En cours')
                ->setConfirmedAt(new \DateTimeImmutable())
            ;
            $entityManager->flush();
            $this->addFlash('success', 'La collecte a bien été confirmée');
            return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/{id}', name: 'collecte.delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collecte->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($collecte);
            $entityManager->flush();
        }

        return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
    }
}
