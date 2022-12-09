<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Product1Type;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('{_locale}/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, SluggerInterface $slugger): Response
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
                    $this->addFlash('danger', 'An error occurred with the given product image');
                }

                $productRepository->save($product, true);
            }
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET', 'POST'])]
    public function show(Product $product, ProductRepository $productRepository, Request $request): Response
    {
       $productEditForm = $this->createForm(ProductType::class, $product);
       $productEditForm->handleRequest($request);
       if ($productEditForm->isSubmitted() && $productEditForm->isValid() && $this->isGranted('ROLE_ADMIN')) {
            $productRepository->save($product, true);
            $this->addFlash('success', 'The product has been edited');
       }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'productEditForm' => $productEditForm->createView(),
        ]);
    }

    #[Route('delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $productRepository->remove($product, true);
            $this->addFlash('success', 'The product has been deleted');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
