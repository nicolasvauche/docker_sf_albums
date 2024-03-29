<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CategoryRepository $categoryRepository, ArtistRepository $artistRepository, AlbumRepository $albumRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $suggestedCategories = new ArrayCollection();
        foreach ($categories as $category) {
            $suggestedCategory = new Category();
            $albums = $category->getAlbums()->toArray();
            foreach ($albums as $key => $album) {
                if ($album->getLastListened()) {
                    $lastDate = new \DateTime($album->getLastListened()->format('Y-m-d'));
                    $today = new \DateTime('now');
                    if ($lastDate->diff($today)->format('%a') < 30) {
                        unset($albums[$key]);
                    }
                }
            }
            shuffle($albums);
            $randomAlbum = array_slice($albums, 0, 1);
            $suggestedCategory
                ->setName($category->getName())
                ->setSlug($category->getSlug())
                ->addAlbum($randomAlbum[0]);
            $suggestedCategories->add($suggestedCategory);
        }

        $suggestedArtists = new ArrayCollection();
        foreach ($categories as $category) {
            $suggestedArtist = new Category();
            $albums = $category->getAlbums()->toArray();
            shuffle($albums);
            $randomAlbum = array_slice($albums, 0, 1);
            $suggestedArtist
                ->setName($category->getName())
                ->setSlug($category->getSlug())
                ->addAlbum($randomAlbum[0]);
            $suggestedArtists->add($suggestedArtist);
        }

        $albumsPlayed = $albumRepository->findByMostPlayed();
        $albumsPlayed = array_slice($albumsPlayed, 0, 8);

        $albumsNeverListened = $albumRepository->findByNeverListened();
        shuffle($albumsNeverListened);
        $albumsNeverListened = array_slice($albumsNeverListened, 0, 4);

        $albumsSeventies = $albumRepository->findByEra('197');
        shuffle($albumsSeventies);
        $albumsSeventies = array_slice($albumsSeventies, 0, 4);

        $albumsEighties = $albumRepository->findByEra('198');
        shuffle($albumsEighties);
        $albumsEighties = array_slice($albumsEighties, 0, 4);

        $albumsNineties = $albumRepository->findByEra('199');
        shuffle($albumsNineties);
        $albumsNineties = array_slice($albumsNineties, 0, 4);

        $albumsActual = $albumRepository->findByActual();
        shuffle($albumsActual);
        $albumsActual = array_slice($albumsActual, 0, 4);

        return $this->render('default/index.html.twig', [
            'suggestedCategories' => $suggestedCategories,
            'suggestedArtists' => $suggestedArtists,
            'categories' => $categoryRepository->findBy([], ['id' => 'ASC']),
            'artists' => $artistRepository->findBy([], ['name' => 'ASC']),
            'albumsPlayed' => $albumsPlayed,
            'albumsLastListened' => $albumRepository->findByLastListened(4),
            'albumsNeverListened' => $albumsNeverListened,
            'albumsSeventies' => $albumsSeventies,
            'albumsEighties' => $albumsEighties,
            'albumsNineties' => $albumsNineties,
            'albumsActual' => $albumsActual,
        ]);
    }
}
