<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
        // The password update is not mandatory
        $editAccountForm = $this->createForm(UserType::class, $user)
            ->remove('password')
            ->add('plainPassword', PasswordType::class, ['required' => false]);

        $editAccountForm->handleRequest($request);

        // The user edit his profile
        if ($editAccountForm->isSubmitted() && $editAccountForm->isValid()) {
            $newPassword = $editAccountForm->get('plainPassword')?->getData() ?? null;
            if ($newPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
            }
            $userRepository->save($user, 1);
            $this->addFlash($translator->trans('flash.success'), $translator->trans('account.flash_message.edited'));
        }

        // Retrieves orders placed by the user
        $orders = $cartRepository->findBy([
            'user' => $user,
            'status' => true
        ]);
        // Compute orders total price
        $orders = OrderUtils::computeTotalPriceMany($orders);

        return $this->render('user/account.html.twig', [
            'user' => $this->getUser(),
            'editAccountForm' => $editAccountForm->createView(),
            'orders' => $orders,
        ]);
    }
}
