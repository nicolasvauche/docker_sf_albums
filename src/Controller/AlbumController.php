<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/album')]
class AlbumController extends AbstractController
{
    #[Route('/{categorySlug}', name: 'album', defaults: ['categorySlug' => null], methods: ['GET'])]
    public function index(AlbumRepository $albumRepository, CategoryRepository $categoryRepository, $categorySlug): Response
    {
        if ($categorySlug) {
            $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
            if ($category) {
                $albums = $category->getAlbums();
            }
        } else {
            $category = null;
            $albums = $albumRepository->findBy([], ['title' => 'asc']);
        }
        return $this->render('album/index.html.twig', [
            'albums' => $albums,
            'category' => $category,
        ]);
    }

    #[Route('/ajouter/{slug}', name: 'album.new', defaults: ['slug' => null], methods: ['GET', 'POST'])]
    public function new(Request $request, AlbumRepository $albumRepository, ArtistRepository $artistRepository, SluggerInterface $slugger, FileUploader $fileUploader, $slug): Response
    {
        $album = new Album();
        $album->setNbPlays(0);
        if ($slug) {
            $album->setArtist($artistRepository->findOneBy(['slug' => $slug]));
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album = $form->getData();

            /** @var UploadedFile $albumCoverFile */
            $albumCoverFile = $form->get('cover')->getData();

            if ($albumCoverFile) {
                $fileName = $fileUploader->upload($albumCoverFile, $this->getParameter('images_albums'), $slugger->slug($album->getTitle()));
                $album->setCover($fileName);
            }

            $albumRepository->add($album);

            return $this->redirectToRoute('album.show', ['slug' => $album->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('album/new.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/details/{slug}', name: 'album.show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }

    #[Route('/modifier/{slug}', name: 'album.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album, AlbumRepository $albumRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $albumCoverFile */
            $albumCoverFile = $form->get('cover')->getData();

            if ($albumCoverFile) {
                if ($album->getCover()) {
                    $fileUploader->delete($this->getParameter('images_albums'), $album->getCover());
                }
                $fileName = $fileUploader->upload($albumCoverFile, $this->getParameter('images_albums'), $slugger->slug($album->getTitle()));
                $album->setCover($fileName);
            }

            $albumRepository->add($album);

            return $this->redirectToRoute('album.show', ['slug' => $album->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('album/edit.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'album.delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, AlbumRepository $albumRepository, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete' . $album->getId(), $request->request->get('_token'))) {
            $fileUploader->delete($this->getParameter('images_albums'), $album->getCover());
            $albumRepository->remove($album);
        }

        return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/ecouter/{id}', name: 'album.play', methods: ['GET'])]
    public function play(Request $request, Album $album, AlbumRepository $albumRepository): Response
    {
        if ($request->isXmlHttpRequest()) {
            $album->setNbPlays($album->getNbPlays() + 1)
                ->setLastListened(new \DateTime());
            $albumRepository->add($album);
            return new JsonResponse(['nbPlays' => $album->getNbPlays(), 'lastListened' => $album->getLastListened()->format('d/m/Y')], 200);
        } else {
            return new JsonResponse(['message' => 'Accès non autorisé'], 405);
        }
    }
}
