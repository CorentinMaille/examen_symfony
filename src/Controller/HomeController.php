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

class HomeController extends AbstractController
{
    #[Route(['/', 'home'], name: 'app_home')]
    public function index(ProductRepository $productRepository, Request $request): Response
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
                    $this->addFlash('danger', 'An error occurred with the given product image');
                    $this->redirectToRoute('app_home');
                }
            }

            $productRepository->save($product, true);
            $this->addFlash('success', 'The product has been created');
        }

        $products = $productRepository->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'createProductForm' => $createProductForm,
        ]);
    }
}
