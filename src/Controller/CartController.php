<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use DateTime;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index_user', methods: ['GET'])]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $carts = $cartRepository->getActiveCart($user->getId());
        return $this->render('cart/index_user.html.twig', [
            'carts' => $carts,
            'user_full_name' => $user,
            'cart_id' => !empty($carts) ? $carts[0]['cart_id'] : null,
        ]);
    }

    // #[Route('/new', name: 'app_cart_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, CartRepository $cartRepository): Response
    // {
    //     $cart = new Cart();
    //     $form = $this->createForm(CartType::class, $cart);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $cartRepository->save($cart, true);

    //         return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('cart/new.html.twig', [
    //         'cart' => $cart,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_cart_show', methods: ['POST'])]
    public function show(Cart $cart, CartRepository $cartRepository, TranslatorInterface $translator): Response
    {
        $cart->setStatus(true);
        $cart->setPurchaseDate(new DateTime());
        $cartRepository->save($cart, true);
        $this->addFlash($translator->trans('flash.success'), $translator->trans('cart.flash.success.purchase'));
        return $this->redirectToRoute('app_cart_index_user', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_cart_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cart $cart, CartRepository $cartRepository): Response
    {
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartRepository->save($cart, true);

            return $this->redirectToRoute('app_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/edit.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_delete', methods: ['POST'])]
    public function delete(Request $request, Cart $cart, CartRepository $cartRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            $cartRepository->remove($cart, true);
        }

        return $this->redirectToRoute('app_cart_index_user', [], Response::HTTP_SEE_OTHER);
    }
}
