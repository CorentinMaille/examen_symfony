<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    /**
     * Permet de renvoyer vers la page d'accueil en prenant en compte la langue par défault
     */ 
    #[Route(['/'], name: 'redirectLanguage')]
    public function redirectLanguage(ProductRepository $productRepository, Request $request): Response {
        return $this->redirectToRoute('app_home');
    }

    /**
     * Récupération des données et affichage de la page d'accueil
     * Gestion de l'ajout d'un nouveau produit
     */
    #[Route(['{_locale}/home'], name: 'app_home')]
    public function index(ProductRepository $productRepository, Request $request, TranslatorInterface $translator): Response
    {
        $product = new Product();

        $createProductForm = $this->createForm(ProductType::class, $product);
        $createProductForm->handleRequest($request);

        if ($createProductForm->isSubmitted() && $createProductForm->isValid() && $this->isGranted('ROLE_ADMIN')) {
            $productRepository->save($product, true);
            $imageFile = $createProductForm->get('photo')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $product->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash($translator->trans('flash.danger'), $translator->trans('home_controller.flash_message.image_issue'));
                    $this->redirectToRoute('app_home');
                }
            }

            $productRepository->save($product, true);
            $this->addFlash($translator->trans('flash.success'), $translator->trans('home_controller.flash_message.created'));
        }

        $products = $productRepository->findAll();

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'createProductForm' => $createProductForm,
        ]);
    }
}
