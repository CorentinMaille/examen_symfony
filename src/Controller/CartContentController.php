<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Form\CartContentType;
use App\Repository\CartContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/cart/content')]
class CartContentController extends AbstractController
{
    /**
     * Permet à l'utilisateur d'enlever un produit de son panier,
     * Puis redirige l'utilisateur sur son panier
     * @param Request $request
     * @param CartContent $cartContent
     * @param CartContentRepository $cartContentRepository
     * @return Response
     */
    #[Route('/{id}', name: 'app_cart_remove_product', methods: ['POST'])]
    public function delete(Request $request, CartContent $cartContent, CartContentRepository $cartContentRepository, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cartContent->getId(), $request->request->get('_token'))) {
            $cartContentRepository->remove($cartContent, true);
            $this->addFlash($translator->trans('flash.success'), $translator->trans('cart_content_controller.flash_message.success.deleted'));
        } else {
            $this->addFlash($translator->trans('flash.warning'), $translator->trans('cart_content_controller.flash_message.failure.deleted'));
        }

        return $this->redirectToRoute('app_cart', [], Response::HTTP_SEE_OTHER);
    }
}
