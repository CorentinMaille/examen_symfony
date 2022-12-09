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

    #[Route('/list', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->getRegisterUsersOnThisDay(),
        ]);
    }

    /**
     * Affiche la vue d'édition du profil de l'utilisateur connecté
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return Response
     */
    #[Route('/', name: 'app_user_account', methods: ['GET', 'POST'])]
    public function account(UserRepository $userRepository, CartRepository $cartRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response {
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
            $userRepository->save($user);
            $this->addFlash('success', 'Your account has been edited');
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

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
