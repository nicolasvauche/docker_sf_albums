<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CategoryRepository $categoryRepository, ArtistRepository $artistRepository, AlbumRepository $albumRepository): Response
    {
        $albums = $albumRepository->findAll();
        shuffle($albums);
        $suggestedAlbum = array_slice($albums, 0, 4);
        $suggestedAlbum = $suggestedAlbum;

        $albumsNeverListened = $albumRepository->findByNeverListened();
        shuffle($albumsNeverListened);
        $albumsNeverListened = array_slice($albumsNeverListened, 0, 8);

        return $this->render('default/index.html.twig', [
            'suggestedAlbum' => $suggestedAlbum,
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
            'artists' => $artistRepository->findBy([], ['name' => 'ASC']),
            'albumsPlayed' => $albumRepository->findByMostPlayed(),
            'albumsLastListened' => $albumRepository->findByLastListened(),
            'albumsNeverListened' => $albumsNeverListened,
        ]);
    }
}
