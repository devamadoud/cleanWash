<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Services\CartService;
use App\Services\UploadImageService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product.index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/add', name: 'product.add', methods: ['GET', 'POST'])]
    public function add(Request $request, UploadImageService $uploadImageService, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        // Créer le formulaire
        $productAddForm = $this->createForm(ProductType::class, $product);
        $productAddForm->handleRequest($request);

        if($productAddForm->isSubmitted() && $productAddForm->isValid()) {
            $imageFile = $productAddForm->get('productImage')->getData();
            $prixAchat = $productAddForm->get('purchasePrice')->getData();
            $prixVente = $productAddForm->get('price')->getData();

            // On verifie que le prix d'achat est supérieur au prix de vente
            if($prixAchat >= $prixVente) {
                $this->addFlash('error', 'Le prix d\'achat doit être supérieur au prix de vente');
                return $this->render('product/add.html.twig', [
                    'productAddForm' => $productAddForm,
                    'product' => $product,
                ]);
            }

            // Verifier si une image a été selectionnée
            if(!$imageFile) {
                $this->addFlash('error', 'Veuillez sélectionner une image de votre produit');
                return $this->render('product/add.html.twig', [
                    'productAddForm' => $productAddForm,
                    'product' => $product,
                ]);
            }

            $response = $uploadImageService->uploadImage($imageFile, 'products/');
            if($response['error'] !== '0'){
                $this->addFlash('error', $response['error']);
                return $this->render('product/add.html.twig', [
                    'productAddForm' => $productAddForm,
                    'product' => $product,
                ]);
            }

            $imageName = $response['imageName'];

            $product->setProductImage($imageName)
                ->setPublished(true);
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a bien été ajouté');
            return $this->redirectToRoute('product.index');
        }

        return $this->render('product/add.html.twig', [
            'productAddForm' => $productAddForm,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/publish', name: 'product.publish')]
    public function publish(Product $product, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        if(!$product->isPublished()) {
            $product->setPublished(true);
        }else {
            $product->setPublished(false);
        }

        $entityManager->flush($product);

        return $this->redirectToRoute('product.index');
    }

    #[Route('/{id}', name: 'product.show')]
    public function show(Product $product, ProductRepository $productRepository, Request $request, CartService $cartService): Response
    {   
        // Recuperer le numéro de page
        $page = $request->query->get('page', 1);
        // Trouver les produits similaires grace a la categorie
        $products = $productRepository->findByCategory($product->getCategory()[0], $page);

        $form = $this->createFormBuilder()
            ->add('quantity', NumberType::class, [
                'label' => false,
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'max' => $product->getQuantityStocke(),
                    'aria-describedby' => "helper-text-explanation"
                ]
            ])->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $quantity = $form->get('quantity')->getData();
            $cartService->add($product->getId(), $quantity);

            $this->addFlash('success', 'Le produit a bien ete ajouté au panier');
            return $this->redirectToRoute('cart.index');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products,
            'form' => $form
        ]);
    }

    #[Route('/dashboard/{id}', name: 'product.owner.show', methods: ['GET', 'POST'])]
    public function owner(Product $product): Response
    {
        return $this->render('product/owner.show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/addToCart', name: 'product.addToCart', methods: ['POST'])]
    public function addToCart(Product $product, CartService $cartService, Request $request): Response
    {
        // Recuperer la quantite saisie par l'utilisateur
        $quantity = $request->request->get('quantity');
        
        // Ajouter le produit au panier
        if($quantity and $quantity > 0) {

            $resopnse = $cartService->add($product->getId(), $quantity);

            if($resopnse->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                $referer = $request->headers->get('referer');
                $this->addFlash('error', 'Le produit n\'a pas pu etre ajouté au panier le stock est insuffisant');
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('cart.index');
        }

        // Si la quantité est inferieure ou egale a 0
        if(!$quantity or $quantity <= 0) {
            $this->addFlash('warning', 'La quantité doit etre superieure a 0');
            return $this->redirectToRoute('product.show', ['id' => $product->getId()]);
        }

        return $this->redirectToRoute('product.show', ['id' => $product->getId()]);
    }

    #[Route('/{id}/edit', name: 'product.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository, EntityManagerInterface $entityManager, UploadImageService $uploadImageService): Response
    {
        $productImage = $product->getProductImage();

        if(!$product){
            $this->addFlash('error', 'Le produit que vous essayez d\'éditer n\'existe pas');
            return $this->redirectToRoute('product.index');
        }
        
        $productEditForm = $this->createForm(ProductType::class, $product);
        $productEditForm->handleRequest($request);

        if($productEditForm->isSubmitted() && $productEditForm->isValid()) {
            $imageFile = $productEditForm->get('productImage')->getData();

            // Verifier si une image a été selectionnée
            if($imageFile) {
                $uploadImageService->deleteImage($productImage, 'products/');
                $response = $uploadImageService->uploadImage($imageFile, 'products/');
                if($response['error'] !== '0'){
                    $this->addFlash('error', $response['error']);
                    return $this->render('product/add.html.twig', [
                        'productAddForm' => $productEditForm,
                        'product' => $product,
                    ]);
                }
                $imageName = $response['imageName'];
                $product->setProductImage($imageName);
            }

            $entityManager->flush($product);

            return $this->redirectToRoute('product.owner.show', ['id' => $product->getId()]);
        }

        return $this->render('product/add.html.twig', [
            'productAddForm' => $productEditForm->createView(),
            'product' => $product,
        ]);
    }
}