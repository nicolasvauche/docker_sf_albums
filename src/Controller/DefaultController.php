<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CategoryRepository $categoryRepository, ArtistRepository $artistRepository): Response
    {
        return $this->render('default/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'artists' => $artistRepository->findAll(),
        ]);
    }
}
