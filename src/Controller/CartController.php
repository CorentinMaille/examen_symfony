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
     * Permet à n'importe quel utilisateur de visualiser son panier en cours
     */
    #[Route('/', name: 'app_cart_index_user', methods: ['GET'])]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $carts = $cartRepository->getCurrentUserActiveCart($user->getId());
        return $this->render('cart/index_user.html.twig', [
            'carts' => $carts,
            'user_full_name' => $user,
            'cart_id' => !empty($carts) ? $carts[0]['cart_id'] : null,
        ]);
    }

    /**
     * Permet au super admin de pouvoir voir toutes les commandes non validées en cours sur le site
     */
    #[Route('/super_admin', name: 'app_cart_super_admin', methods: ['GET'])]
    public function super_admin_view(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $carts = $cartRepository->getAllActiveCart();
        return $this->render('cart/index_super_admin.html.twig', [
            'carts' => $carts,
        ]);
    }

    /**
     * Permet l'achat fictif du panier client.
     */
    #[Route('/{id}', name: 'app_cart_purchase', methods: ['POST'])]
    public function purchaseCart(Cart $cart, CartRepository $cartRepository, TranslatorInterface $translator): Response
    {
        try {
            $cart->setStatus(true);
            $cart->setPurchaseDate(new DateTime());
            $cartRepository->save($cart, true);
            $this->addFlash($translator->trans('flash.success'), $translator->trans('cart_content_controller.flash_message.success.purchase'));
        } catch (Exception $e){
            $this->addFlash($translator->trans('flash.warning'), $translator->trans('cart_content_controller.flash_message.failure.purchase'));
        }
        return $this->redirectToRoute('app_cart_index_user', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Permet de voir les détails d'une commande sur une page spécifique
     */
    #[Route('/{id}', name: 'app_cart_show', methods: ['GET'])]
    public function show(Cart $cart, CartRepository $cartRepository, TranslatorInterface $translator): Response
    {
        $cart = $cartRepository->find(['id' => $cart->getId()]);
        return $this->render('cart/one_cart_view.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * Permet de supprimer une commande
     */
    #[Route('/delete/{id}', name: 'app_cart_delete', methods: ['POST'])]
    public function delete(Request $request, Cart $cart, CartRepository $cartRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            $cartRepository->remove($cart, true);
        }

        return $this->redirectToRoute('app_cart_index_user', [], Response::HTTP_SEE_OTHER);
    }
}
