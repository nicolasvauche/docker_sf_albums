<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
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

    #[Route('/ecoutes', name: 'album.played', methods: ['GET'])]
    public function played(AlbumRepository $albumRepository): Response
    {
        return $this->render('stats/albums/played.html.twig', [
            'albums' => $albumRepository->findByLastListened(),
        ]);
    }

    #[Route('/plus-ecoutes', name: 'album.mostplayed', methods: ['GET'])]
    public function mostplayed(AlbumRepository $albumRepository): Response
    {
        return $this->render('stats/albums/mostplayed.html.twig', [
            'albums' => $albumRepository->findByMostPlayed(),
        ]);
    }

    #[Route('/non-ecoutes', name: 'album.notplayed', methods: ['GET'])]
    public function notplayed(AlbumRepository $albumRepository): Response
    {
        return $this->render('stats/albums/notplayed.html.twig', [
            'albums' => $albumRepository->findByNeverListened(),
        ]);
    }

    #[Route('/liste', name: 'album.list')]
    public function list(AlbumRepository $albumRepository): Response
    {
        $albums = $albumRepository->findAll();
        usort($albums, function ($a, $b) {
            return $a->getArtist()->getName() > $b->getArtist()->getName() ? 1 : -1;
        });

        return $this->render('stats/albums/list.html.twig', [
            'albums' => $albums,
        ]);
    }
}
