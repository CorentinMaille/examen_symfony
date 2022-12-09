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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/product')]
class ProductController extends AbstractController
{
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, SluggerInterface $slugger, TranslatorInterface $translator): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $this->isGranted('ROLE_ADMIN')) {
            $imageFile = $form->get('photo')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $translator->trans('product_controller.flash_message.image_issue'));
                }

                $productRepository->save($product, true);
            }
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

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
