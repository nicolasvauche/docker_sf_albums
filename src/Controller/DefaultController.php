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
            shuffle($albums);
            $randomAlbum = array_slice($albums, 0, 1);
            $suggestedCategory
                ->setName($category->getName())
                ->setSlug($category->getSlug())
                ->addAlbum($randomAlbum[0]);
            $suggestedCategories->add($suggestedCategory);
        }

        $albumsNeverListened = $albumRepository->findByNeverListened();
        shuffle($albumsNeverListened);
        $albumsNeverListened = array_slice($albumsNeverListened, 0, 8);

        return $this->render('default/index.html.twig', [
            'suggestedCategories' => $suggestedCategories,
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
            'artists' => $artistRepository->findBy([], ['name' => 'ASC']),
            'albumsPlayed' => $albumRepository->findByMostPlayed(),
            'albumsLastListened' => $albumRepository->findByLastListened(),
            'albumsNeverListened' => $albumsNeverListened,
        ]);
    }
}
