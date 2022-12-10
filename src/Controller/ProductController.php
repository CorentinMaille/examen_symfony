<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Form\CartContentType;
use App\Form\ProductType;
use App\Repository\CartContentRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/product')]
class ProductController extends AbstractController
{
    /**
     * Affiche la vue de la fiche technique d'un produit
     * Possibilité d'ajouter le produit au panier avec la quantité sélectionnée
     * Si Admin, possibilité d'éditer le produit & de le supprimer
     * Si ajout d'un produit au panier vérifie si un panier existe déjà pour l'utilisateur sinon en créé un
     * @param Product $product
     * @param ProductRepository $productRepository
     * @param CartContentRepository $cartContentRepository
     * @param CartRepository $cartRepository
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/{id}', name: 'app_product_sheet', methods: ['GET', 'POST'])]
    public function show(
        Product $product,
        ProductRepository $productRepository,
        CartContentRepository $cartContentRepository,
        CartRepository $cartRepository,
        Request $request,
        TranslatorInterface $translator
    ): Response {
       $productEditForm = $this->createForm(ProductType::class, $product);
       $productEditForm->handleRequest($request);
       if ($productEditForm->isSubmitted() && $productEditForm->isValid() && $this->isGranted('ROLE_ADMIN')) {

        $photoFile = $productEditForm->get('photo')->getData();
        if ($photoFile) {
            $newFilename = uniqid().'.'.$photoFile->guessExtension();

            try {
                $photoFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('warning', $translator->trans('form_type.product.photo.constraint'));
                return $this->redirectToRoute('app_home');
            }

            $product->afterDeletePhoto();
            $product->setPhoto($newFilename);
        }

            $productRepository->save($product, true);
            $this->addFlash('success', $translator->trans('product_controller.flash_message.edited'));
       }

       $addToCartForm = $this->createForm(CartContentType::class);
       $addToCartForm->handleRequest($request);
        if ($addToCartForm->isSubmitted() && $addToCartForm->isValid() && $this->isGranted('ROLE_USER')) {

            // Check if a cart exists for this user
            $cart = $cartRepository->findOneBy([
                'user' => $this->getUser(),
                'status' => false
            ]);

            // If not create a new one
            if (is_null($cart)) {
                $cart = new Cart();
                $cart->setUser($this->getUser());
                $cart->setStatus(false);
                $cartRepository->save($cart);
            }

            // Add the product to the user's cart
            $cartContent = $addToCartForm->getData();

            if ($product->getStock() < $cartContent->getQuantity() || $cartContent->getQuantity() < 0) {
                $this->addFlash('danger', $translator->trans('product_controller.flash_message.stock_below_quantity'));
                return $this->redirectToRoute('app_product_sheet', ['id' => $product->getId()]);
            }

            $cartContent->setCart($cart);
            $cartContent->setProduct($product);
            $cartContentRepository->save($cartContent, true);

            // remove product stock
            $product->setStock($product->getStock() - $cartContent->getQuantity());
            $productRepository->save($product, 1);

            // Refresh form
            $addToCartForm = $this->createForm(CartContentType::class);

            $this->addFlash('success', $translator->trans('product_controller.flash_message.added'));
        }

        return $this->render('product/sheet.html.twig', [
            'product' => $product,
            'productEditForm' => $productEditForm->createView(),
            'addToCartForm' => $addToCartForm->createView(),
        ]);
    }

    /**
     * Éxécute la suppression d'un produit
     * @param Product $product Le produit à supprimer
     * @param ProductRepository $productRepository
     * @param TranslatorInterface $translator
     * @return Response Redirection à la page d'accueil
     */
    #[Route('delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Product $product, ProductRepository $productRepository, TranslatorInterface $translator): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $productRepository->remove($product, true);
            $this->addFlash('success', $translator->trans('product_controller.flash_message.deleted'));
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
