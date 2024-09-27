<?php

namespace App\Controller;

use App\Data\collecteSearchData;
use App\Entity\ClothingPea;
use App\Entity\ClothingType;
use App\Entity\Collecte;
use App\Entity\CollecteDetaillesPea;
use App\Entity\Customer;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\CollecteFilterType;
use App\Form\CollecteType;
use App\Form\CustomerType;
use App\Repository\CollecteRepository;
use App\Security\Voter\CollecteVoter;
use App\Services\InvoiceService;
use App\Services\QrCodeGenerator;
use App\Services\ShipeService;
use App\Services\UniqueRefGenerator;
use App\Services\UserProvider;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
    public function index(CollecteRepository $collecteRepository, Request $request): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', 'Vous devez vous identifier pour acceder a la liste des collectes');
            return $this->redirectToRoute('app_login');
        }

        $reset = $request->query->get('reset', null);
        if($reset){
            return $this->redirectToRoute('collecte.index');
        }
        $collecteSearcheData = new collecteSearchData();

        // Verifier si l'utilisateur connecté est le propriétaire du shop
        if($user->getShop() != null){
            $collecteSearcheData->shop = $user->getShop();
        }

        // Verifier si l'utilisateur connecté est un collecteur & livreur, caissier ou un laveur (un employé)
        if($user->getJob() != null){
            $collecteSearcheData->shop = $user->getJob()->getShop();
        }

        $collecteSearcheData->page = $request->query->get('page', 1);
        $collecteFilterForm = $this->createForm(CollecteFilterType::class, $collecteSearcheData);
        $collecteFilterForm->handleRequest($request);

        $collectes = $collecteRepository->findByShop($collecteFilterForm->getData());

        return $this->render('collecte/index.html.twig', [
            'collectes' => $collectes,
            'filterForm' => $collecteFilterForm
        ]);
    }

    #[Route('/qrscan', name: 'collecte.qrscan', methods: ['GET', 'POST'])]
    public function qrscan(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('collecte/qrCodeScan.html.twig');
    }

    #[Route('/clothing', name: 'collecte.clothing', methods: ['GET', 'POST'])]
    public function clothing(Request $request, EntityManagerInterface $entityManager): Response
    {
        $datas = [
            [
                'name' => 'T-Shirt',
                'price' => 500
            ],
            [
                'name' => 'Pantalon',
                'price' => 500
            ],
            [
                'name' => 'Pull',
                'price' => 500
            ],
            [
                'name' => 'Short',
                'price' => 500
            ],
            [
                'name' => 'Robe',
                'price' => 1000
            ],
            [
                'name' => 'Veste',
                'price' => 1000
            ],
            [
                'name' => 'Jacket',
                'price' => 1000
            ],
            [
                'name' => 'Robe de soiree',
                'price' => 1500
            ],
            [
                'name' => 'Robe perle',
                'price' => 2000
            ]
        ];

        foreach ($datas as $key => $data) {
            $clothingType = new ClothingType();
            $clothingType->setName($data['name']);
            $clothingType->setPrice($data['price']);
            $entityManager->persist($clothingType);
            $entityManager->flush();
        }
        return $this->render('collecte/qrscan.html.twig');
    }

    /**
    * @param User|null $user
    */
    #[Route('/{tel}/{type}/new', name: 'collecte.new', methods: ['GET', 'POST'])]
    #[IsGranted(CollecteVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, InvoiceService $invoiceService, $tel, $type): Response
    {
        // Initialiser l'utilisateur courant
        $user = $this->getUser();

        // Verifier si l'utilisateur est connecté, si non initialiser le shop par defaut de la platform
        if(!($user instanceof User)) {
            $shop = $entityManager->getRepository(User::class)->findOneByTelefone('782044386')->getShop();
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

                    $url = $urlGenerator->generate('collecte.show', ['id' => $customerCollecte->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                    if($user){
                        $this->addFlash('error', 'Ce client a deja une collecte. '.$customerCollecte->getStatus().'.');
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

                $customer->setPhoneNumber($customerTelefone)
                    ->setCreatedAt(new DateTimeImmutable())
                ;
            }

            // si le client existe deja mais il effectue une nouvelle collecte dans un shop different de son shop actuel, on le met a jour
            if($customer->getShop() != $shop){
                $customer->setShop($shop);
            }

            // Initialiser le generateur de reference
            $refGenerator = new UniqueRefGenerator($entityManager);

            $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = substr(str_shuffle($caracteres), 0, 4);
            $collecteRef = $refGenerator->generateCollecteReference(4);

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
                    ->setPea($pea)
                    ->setStatus('En attente')
                ;

                $collecte->addCollecteDetaillesPea($collecteDetaillesPea)
                    ->setReference($collecteRef)
                ;
            }

            if($type === 'clothingType'){

                $totale = 0;
                foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                    $ref = $refGenerator->generateCollecteDetaillesReference(6);
                    $collecteD->setReference($ref)
                        ->setStatus('En attente')
                    ;

                    $collecte->addCollecteDetaille($collecteD);

                }
            }

            $collecte->setCollectedAt(new \DateTimeImmutable())
                ->setShop($shop)
                ->setCustomer($customer)
                ->setStatus('En attente')
                ->setSecret($code)
                ->setCollecteType($type)
                ->setReference($collecteRef)
            ;

            $invoice = $invoiceService->createInvoice($collecte);
            $collecte->setInvoice($invoice);
            
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
                ->add('totale', HiddenType::class)
                ->add('paymentChoice', ChoiceType::class, [
                    'choices' => [
                        'En ligne' => 'online',
                        'A la livraison' => 'on-deliver'],
                    'mapped' => false,
                    'expanded' => true, 'multiple' => false
                ])->getForm()
        ;

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $collecte->setPaymentChoice($form->get('paymentChoice')->getData());
            $collecte->setTotale($form->get('totale')->getData());

            $entityManager->flush();

            if($collecte->getCustomer()->getFullName() == null and $collecte->getCustomer()->getAdress() == null){
                return $this->redirectToRoute('collecte.customer', ['collecte' => $collecte->getId()]);
            }

            if($this->getUser()){

                if($collecte->getPaymentChoice() == 'online'){
                    return $this->redirectToRoute('payment.payout', ['ref' => $collecte->getReference()]);
                }

                $this->addFlash('success', 'La collecte a été crée avec succés.');
                return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()], Response::HTTP_SEE_OTHER);
            }

            if(!$this->getUser() and $form->get('paymentChoice')->getData() == "online"){
                return $this->redirectToRoute('payment.payout', ['ref' => $collecte->getReference()]);
            }

            $this->addFlash('success', 'Votre collecte a bien été enregistrée notre equipe vous contacteras dans les plus brefs delais.');
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        // Si la collecte est un clothingTypePea
        if($collecte->getCollecteType() === 'clothingType'){
            $collectePea = 'clothingType';
        }else{
            $collectePea = 'clothingPea';
        }

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $url = $baseurl.$this->generateUrl('collecte.show', ['id' => $collecte->getId()]);
        $qrCodeGenerator = new QrCodeGenerator();
        $qrCode = $qrCodeGenerator->generateQrCode($url);

        return $this->render('collecte/recap.html.twig', [
            'collecte' => $collecte,
            'collectePea' => $collectePea,
            'form' => $form, 
            'qrCode' => $qrCode
        ]);
    }

    #[IsGranted(CollecteVoter::CREATE)]
    #[Route('/{collecte}/customer', name: 'collecte.customer', methods: ['GET', 'POST'])]
    public function customer(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        if(!$collecte){
            // Regiriger vers la derniere page
            $this->addFlash('error', 'La collecte n\'existe pas, veuillez enregistrer une nouvelle collecte pour continuer');
            return $this->redirect($request->headers->get('referer'));
        }

        $customer = $collecte->getCustomer();

        $form = $this->createForm(CustomerType::class, $customer)
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
                if($collecte->getPaymentChoice() == 'online'){
                    return $this->redirectToRoute('payment.payout', ['ref' => $collecte->getReference()]);
                }
                return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()]);
            }

            if($collecte->getPaymentChoice() == 'online'){
                return $this->redirectToRoute('payment.payout', ['ref' => $collecte->getReference()]);
            }

            $this->addFlash('success', 'Votre collecte a bien été enregistrée notre equipe va vous contacter dans les plus brefs delais.');
            return $this->redirectToRoute('home');
        }

        return $this->render('collecte/newCustomer.html.twig', [
            'collecte' => $collecte,
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name: 'collecte.show', methods: ['GET', 'POST'])]
    public function show(Collecte $collecte, Request $request): Response
    {

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $url = $baseurl.$this->generateUrl('collecte.show', ['id' => $collecte->getId()]);
        $qrCodeGenerator = new QrCodeGenerator();
        $qrCode = $qrCodeGenerator->generateQrCode($url);

        if(!$this->getUser()){
            $form = $this->createFormBuilder()->add('code', PasswordType::class, [
                'label' => 'Votre code de comande',
                'attr' => [
                    'placeholder' => 'exemple: 4kd1'
                ],
                "mapped" => false
            ])->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $code = $form->get('code')->getData();
                if($code === $collecte->getSecret()){
                    return $this->render('collecte/suivi.html.twig', [
                        'collecte' => $collecte,
                        'collecteDetails' => $collecte->getCollecteDetailles(),
                        'qrCode' => $qrCode
                    ]);
                }else {
                    $this->addFlash('error', 'Code incorecte, veuillez réessayer');
                }
            }
            return $this->render('collecte/suivi.html.twig', [
                'form' => $form
            ]);
        }

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

        $type = $request->query->get('type');

        if($type === "clothingType"){
            $form = $this->createForm(CollecteType::class, $collecte);

            // Ajouter le client dans le formulaire
            $form->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'fullName',
                'data' => $customer,
                'label' => 'Client',
                'disabled' => true
            ]);
        }else{

             $form = $this->createFormBuilder($collecte->getCollecteDetaillesPeas()[0])->add('pea', NumberType::class, [
                'label' => 'Pois de votre collecte',
                'attr' => [
                    'placeholder' => 'Ex. 7'
                ],
                'data' => $collecte->getCollecteDetaillesPeas() ? $collecte->getCollecteDetaillesPeas()[0]->getPea() : "",
                'required' => true,
                'mapped' => false
            ])->getForm();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $refGenerator = new UniqueRefGenerator($entityManager);

            if($type === "clothingType"){
                $totale = 0;
                foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {

                    $ref = $refGenerator->generateOrderDetaillesReference(6);
                    if($collecteD->getReference() == null){
                        $collecteD->setReference($ref);
                    }
                    $totale += $collecteD->getClothingType()->getPrice();
                }

                $collecte->addCollecteDetaille($collecteD);
            }

            if($type === "clothingPea"){
                $collecteDP = $collecte->getCollecteDetaillesPeas()[0];
                $pea = $form->get('pea')->getData();
                $ref = $refGenerator->generateOrderDetaillesReference(6);
                
                $clothingPea = $entityManager->getRepository(ClothingPea::class)->createQueryBuilder('c')
                        ->where('c.pea <= :pea and c.peaMax >= :pea')
                        ->setParameter('pea', $pea)
                        ->getQuery()
                        ->getOneOrNullResult()
                ;
                $collecteDP->setClothingPea($clothingPea)
                        ->setPea($pea);
                $collecte->addCollecteDetaillesPea($collecteDP)
                    ->setReference($ref)
                    ->setTotale($clothingPea->getPriceMin())
                ;
            }

            $entityManager->flush();

            if($this->getUser()){
                $this->addFlash('success', 'La collecte a bien été modifiée');
                return $this->redirectToRoute('collecte.index');
            }
            if(!$this->getUser()){
                return $this->redirectToRoute('collecte.recap', ['id' => $collecte->getId()]);
            }
        }

        return $this->render('collecte/edit.html.twig', [
            'collecte' => $collecte,
            'customer' => $customer,
            'form' => $form,
            'type' => $type
        ]);
    }

    #[Route('/{id}/confirme', name: 'collecte.confirme', methods: ['POST', 'GET'])]
    #[IsGranted(CollecteVoter::CONFIRME, 'collecte')]
    public function confirme(Request $request, Collecte $collecte, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if(!($user instanceof User)){
            $this->addFlash('error', 'Vous devez vous identifier pour confirmer une collecte');
            return $this->redirectToRoute('app_login');
        }

        if(!$collecte){
            $this->addFlash('error', 'Une erreur c\'est produite, la collecte n\'existe pas.');
            return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
        }
        
        // Verifier si l'utilisateur connecté est le propriétaire du shop ou un collecteur (un employé)
        if($user->getShop() != null or $user->getJob() != null and $user->getJob()->getPoste() == 'collecteur'){

            $confirmedBy = $user->getShop() != null ? 'Le gérant '.$user->getFullName() : 'Le collecteur '.$user->getFullName();
            $collecte->setConfirmedBy($confirmedBy)
                ->setConfirmedAt(new \DateTimeImmutable());

            if($collecte->getPaymentChoice() == 'online'){
                $collecte->setStatus('En attente de paiement');
            }

            if($collecte->getPaymentChoice() == 'on-deliver'){
                $collecte->setStatus('En cours de traitement');

                if($collecte->getCollecteType() == "clothingType"){
                    foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                        $collecteD->setStatus('En attente de lavage');
                    }
                }

                if($collecte->getCollecteType() == "clothingPea"){
                    $collecteDP = $collecte->getCollecteDetaillesPeas()[0];
                    $collecteDP->setStatus('En attente de lavage');
                }

                $collecte->setStatus('En attente de lavage');
            }
                  
            $entityManager->flush();
            $this->addFlash('success', 'La collecte a bien été confirmée');
            return $this->redirectToRoute('collecte.index', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/{collecte}/wash', name: 'collecte.wash', methods:['GET'] )]
    public function clothingTypeWash(Request $request, Collecte $collecte, EntityManagerInterface $entityManager)
    {
        if($collecte->getCollecteType() == "clothingType"){

            foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                $collecteD->setStatus('En cours de lavage');
            }
        }

        if($collecte->getCollecteType() == "clothingPea"){
            $collecte->getCollecteDetaillesPeas()[0]->setStatus('En cours de lavage');
        }
        
        $collecte->setStatus('En cours de lavage');
        
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/{collecte}/ready', name: 'collecte.ready', methods:['GET'] )]
    public function readyToshipe(Request $request, Collecte $collecte, EntityManagerInterface $entityManager)
    {
        if($collecte->getCollecteType() == "clothingType"){

            foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                $collecteD->setStatus('Traitement terminé');
            }
        }

        if($collecte->getCollecteType() == "clothingPea"){
            $collecte->getCollecteDetaillesPeas()[0]->setStatus('Traitement terminé');
        }
        
        $collecte->setStatus('En attente de livraison');
        
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/{collecte}/shipping', name: 'collecte.shipping', methods:['GET'] )]
    public function shipping(Request $request, Collecte $collecte, EntityManagerInterface $entityManager, ShipeService $shipeService, UserProvider $userProvider)
    {
        $user = $userProvider->connectedUser($this->getUser());

        if($collecte->getCollecteType() == "clothingType"){

            foreach ($collecte->getCollecteDetailles() as $key => $collecteD) {
                $collecteD->setStatus('En cours de livraison');
            }
        }

        if($collecte->getCollecteType() == "clothingPea"){
            $collecte->getCollecteDetaillesPeas()[0]->setStatus('En cours de livraison');
        }
        
        $shipe = $shipeService->ship($collecte, "En cours", $user);
        
        $collecte->setStatus('En cours de livraison')
            ->setShipe($shipe)
        ;

        $customer = $collecte->getCustomer();
        
        $entityManager->persist($shipe);
        $entityManager->flush();

        return $this->redirectToRoute('customer.show', ['id' => $customer->getId()]);
    }

    #[Route('/{collecte}/shipped', name: 'collecte.shipped', methods:['GET'] )]
    public function shipped(Request $request, Collecte $collecte, EntityManagerInterface $entityManager, ShipeService $shipeService)
    {
        $secret = $request->query->get('secret');

        if(!$secret){
            $this->addFlash('error', "Vous devez saisire le code secret de la commande pour confirmer la livraison.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($secret and $secret != $collecte->getSecret()){
            $this->addFlash('error', "Le code que vous avez saisit est incorrecte.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($collecte->getStatus() != "En cours de livraison"){
            $this->addFlash('error', "Cet commande n'est pas prêt pour cette étape.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($collecte->getCollecteType() == "clothingType"){

            if($collecte->getPaymentChoice() == "online" and $collecte->getPayment() and $collecte->getShipe()){
                $collecte->setStatus('Terminé');
            }

            if($collecte->getPaymentChoice() == "on-deliver" and !$collecte->getPayment()){
                $collecte->setStatus('En attente de paiement');
            }
        }

        if($collecte->getCollecteType() == "clothingPea"){

            if($collecte->getPaymentChoice() == "online" and $collecte->getPayment()){
                $collecte->setStatus('Terminé');
            }

            if($collecte->getPaymentChoice() == "on-deliver" and !$collecte->getPayment()){
                $collecte->setStatus('En attente de paiement');
            }
        }

        $shipeService->ship($collecte, "Terminé", $this->getUser());
        
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/{collecte}/checkout-service', name: 'collecte.checkout', methods: ['POST', 'GET'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager, Collecte $collecte)
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour encaisser un paiement.");
            return $this->redirectToRoute('app_login');
        }

        if(!$collecte){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard.");
            return $this->redirect($request->headers->get('referer'));
        }

        $amount = $collecte->getTotale();
        $paymentMethode = $request->query->get('paymentMethode');

        if($user->getShop() or $user->getJob() and $user->getJob()->getPoste() == "caissier" or $user->getJob() and $user->getJob()->getPoste() == "collecteur"){
            if($collecte->getPayment() != null){
                
                $clean = true;
                foreach ($collecte->getCollecteDetailles() as $key => $cl) {
                    if($cl->getStatus() == "En attente"){
                        $clean = false;
                        break;
                    }
                }

                if(!$clean){
                    $collecte->setStatus('En attente de lavage');
                    $entityManager->flush();
                    return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()]);
                }

                if($collecte->getPaymentChoice() == "on-deliver" and $collecte->getShipe()){
                    $collecte->setStatus('Terminé');
                }

                $this->addFlash('warning', "Cette collecte as déjà été payé, veuillez verifier la reference et reéssayez !");
                return $this->redirect($request->headers->get('referer'));
            }

            $payment = new Payment();

            $payment->setPaimentMode($paymentMethode)
                ->setPaidAt(new DateTimeImmutable())
                ->setAmount($amount)
                ->setType("Service de pressing")
            ;

            if($user->getJob() and $user->getJob()->getPoste() == "caissier"){
                $payment->setConfirmation(true)
                    ->setCashedBy("Le caissier")
                    ->setStatus('Effectuée')
                ;
            }

            if($user->getShop()){
                $payment->setConfirmation(true)
                    ->setCashedBy("Le gérant")
                    ->setStatus('Effectuée')
                ;
            }

            if($user->getJob() and $user->getJob()->getPoste() == "collecteur"){
                $payment->setConfirmation(false)
                    ->setCashedBy("Le collecteur")
                    ->setStatus('En attente de confirmation')
                ;
            }

            if($collecte->getPaymentChoice() == "online"){
                $collecte->setStatus('En attente de lavage');
            }

            if($collecte->getPaymentChoice() == "on-deliver"){
                $collecte->setStatus('Terminé');
            }

            if($collecte->getPaymentChoice() == "online"){
                $collecte->setStatus('En attente de lavage');
            }

            if($collecte->getPaymentChoice() == "on-deliver"){
                $collecte->setStatus('Terminé');
            }

            $collecte->setPayedAt(new DateTimeImmutable())
                ->setPayment($payment)
            ;

            $entityManager->persist($collecte);
            $entityManager->flush();

            $this->addFlash('success', "Paiement effectué avec succés.");
        }

        return $this->redirectToRoute('collecte.show', ['id' => $collecte->getId()]);

    }

    #[Route('/{collecte}/{transaction}/online-checkout-service', name: 'collecte.onlineCheckout', methods: ['POST', 'GET'])]
    public function onlineCheckout(Request $request, EntityManagerInterface $entityManager, Collecte $collecte, string $transaction)
    {
        if(!$collecte){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($collecte->getStatus() != "En attente de paiement" && $collecte->getPayment() != null){
            $this->addFlash('error', "Une erreur c'est produit, la collecte a déja été payée.");
        }

        if($collecte->getPayment() != null){
            $this->addFlash('warning', "La collecte as déjà été payé, veuillez verifier la reference et reéssayez !");
            return $this->redirect($request->headers->get('referer'));
        }
        
        $paymentMethode = "online";

        $amount = $collecte->getTotale();

        $payment = new Payment();

        $payment->setPaimentMode($paymentMethode)
            ->setPaidAt(new DateTimeImmutable())
            ->setAmount($amount)
            ->setCashedBy("Le client")
            ->setStatus('Effectuée')
            ->setConfirmation(true)
            ->setTransactionId($transaction)
            ->setType("Service de pressing")
        ;

        if($collecte->getPaymentChoice() == "online"){
            $collecte->setStatus('En attente de lavage');
            
            if($collecte->getCollecteDetailles()){
                foreach ($collecte->getCollecteDetailles() as $key => $cd) {
                    $cd->setStatus('En attente de lavage');
                }
            }

            if($collecte->getCollecteDetaillesPeas()){
                foreach ($collecte->getCollecteDetaillesPeas() as $key => $cdp) {
                    $cdp->setStatus('En attente de lavage');
                }
            }
        }

        if($collecte->getPaymentChoice() == "on-deliver" and $collecte->getShipe()){
            $collecte->setStatus('Terminé');
        }else{
            $collecte->setStatus('En attente de livraison');
        }

        $collecte->setPayedAt(new DateTimeImmutable())
            ->setPayment($payment)
        ;

        $entityManager->flush();

        $this->addFlash('success', "Paiement effectué avec succés.");
        return $this->redirectToRoute('home');
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
