<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/categorie')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['id' => 'asc']),
        ]);
    }

    #[Route('/ajouter', name: 'category.new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            /** @var UploadedFile $categoryCoverFile */
            $categoryCoverFile = $form->get('cover')->getData();

            if ($categoryCoverFile) {
                $fileName = $fileUploader->upload($categoryCoverFile, $this->getParameter('images_categories'), $slugger->slug($category->getName()));
                $category->setCover($fileName);
            }

            $categoryRepository->add($category);

            return $this->redirectToRoute('category.show', ['slug' => $category->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'category.show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/modifier/{slug}', name: 'category.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $categoryCoverFile */
            $categoryCoverFile = $form->get('cover')->getData();

            if ($categoryCoverFile) {
                if ($category->getCover()) {
                    $fileUploader->delete($this->getParameter('images_categories'), $category->getCover());
                }
                $fileName = $fileUploader->upload($categoryCoverFile, $this->getParameter('images_categories'), $slugger->slug($category->getName()));
                $category->setCover($fileName);
            }

            $categoryRepository->add($category);

            return $this->redirectToRoute('category.show', ['slug' => $category->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'category.delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, CategoryRepository $categoryRepository, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $fileUploader->delete($this->getParameter('images_categories'), $category->getCover());
            $categoryRepository->remove($category);
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }
}
