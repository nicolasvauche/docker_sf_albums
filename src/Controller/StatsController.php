<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stats')]
class StatsController extends AbstractController
{
    #[Route('/', name: 'stats')]
    public function index(CategoryRepository $categoryRepository, ArtistRepository $artistRepository, AlbumRepository $albumRepository): Response
    {
        return $this->render('stats/index.html.twig', [
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
            'artists' => $artistRepository->findBy([], ['name' => 'ASC']),
            'albums' => $albumRepository->findBy([], ['title' => 'ASC']),
            'albumsPlayed' => $albumRepository->findByMostPlayed(),
            'albumsLastListened' => $albumRepository->findByLastListened(),
            'albumsNeverListened' => $albumRepository->findByNeverListened(),
        ]);
    }
}
