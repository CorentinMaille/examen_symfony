<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/user')]
class UserController extends AbstractController
{

    /**
     * Affiche la vue contenant la liste des utilisateurs qui se sont inscrits aujourd'hui,
     * par ordre d'inscription la plus récente à la plus ancienne
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/list', name: 'app_user_today_registered', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/registered_today.html.twig', [
            'users' => $userRepository->getRegisterUsersOnThisDay(),
        ]);
    }

    /**
     * Affiche la vue d'édition du profil de l'utilisateur connecté
     * @param UserRepository $userRepository
     * @param CartRepository $cartRepository
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param TranslatorInterface $translator
     * @return Response
     */
    #[Route('/', name: 'app_user_account', methods: ['GET', 'POST'])]
    public function account(
        UserRepository $userRepository,
        CartRepository $cartRepository,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();
        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }

        $editAccountForm = $this->createForm(UserType::class, $user);
        $editAccountForm->handleRequest($request);
        // The user edit his profile
        if ($editAccountForm->isSubmitted() && $editAccountForm->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $editAccountForm->get('password')->getData()
                )
            );
            $userRepository->save($user, 1);
            $this->addFlash('success', $translator->trans('account.flash_message.edited'));
        }

        // Get user passed orders
        $orders = $cartRepository->findBy([
            'user' => $user,
            'status' => true
        ]);

        // compute orders total price
        foreach ($orders as $order) {
            $totalPrice = 0;
            foreach ($order->getCartContents() as $orderLine) {
                $totalPrice += $orderLine->getQuantity() * $orderLine->getProduct()->getPrice();
            }
            $order->totalPrice = $totalPrice;
        }

        return $this->render('user/account.html.twig', [
            'user' => $this->getUser(),
            'editAccountForm' => $editAccountForm->createView(),
            'orders' => $orders,
        ]);
    }
}
