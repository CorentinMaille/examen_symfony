<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\CartType;
use App\Repository\CartRepository;
use DateTime;
use Doctrine\ORM\Mapping\Id;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/cart')]
class CartController extends AbstractController
{
    /**
     * Permet à l'utilisateur de visualiser son panier
     * @param CartRepository $cartRepository
     * @return Response
     */
    #[Route('/', name: 'app_cart', methods: ['GET'])]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $carts = $cartRepository->getCurrentUserActiveCart($user->getId());
        return $this->render('cart/my_cart.html.twig', [
            'carts' => $carts,
            'user_full_name' => $user,
            'cart_id' => !empty($carts) ? $carts[0]['cart_id'] : null,
        ]);
    }

    /**
     * Permet à un compte Super Admin de visualiser l'ensemble des paniers non validés
     * @param CartRepository $cartRepository
     * @return Response
     */
    #[Route('/super_admin', name: 'app_cart_unvalidated', methods: ['GET'])]
    public function super_admin_view(CartRepository $cartRepository): Response
    {
        $carts = $cartRepository->getAllActiveCart();
        return $this->render('cart/index_super_admin.html.twig', [
            'carts' => $carts,
        ]);
    }

    /**
     * Permet l'achat fictif du panier client.
     */
    #[Route('/{id}', name: 'app_cart_validate', methods: ['POST'])]
    public function purchaseCart(Cart $cart, CartRepository $cartRepository, TranslatorInterface $translator): Response
    {
        try {
            $cart->setStatus(true);
            $cart->setPurchaseDate(new DateTime());
            $cartRepository->save($cart, true);
            $this->addFlash($translator->trans('flash.success'), $translator->trans('cart.flash.success.purchase'));
        } catch (Exception $e) {
            $this->addFlash($translator->trans('flash.warning'), $translator->trans('cart.flash.failure.purchase'));
        }
        return $this->redirectToRoute('app_cart', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Permet de visualiser les détails d'une commande en particulier
     * @param Cart $cart
     * @param CartRepository $cartRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/{id}', name: 'app_cart_show', methods: ['GET'])]
    public function show(Cart $cart, CartRepository $cartRepository, TranslatorInterface $translator): Response
    {
        $cart = $cartRepository->find(['id' => $cart->getId()]);
        return $this->render('cart/order.html.twig', [
            'cart' => $cart,
        ]);
    }
}
