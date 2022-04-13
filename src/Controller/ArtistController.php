<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/artiste')]
class ArtistController extends AbstractController
{
    #[Route('/', name: 'artist', methods: ['GET'])]
    public function index(ArtistRepository $artistRepository): Response
    {
        return $this->render('artist/index.html.twig', [
            'artists' => $artistRepository->findAll(),
        ]);
    }

    #[Route('/ajouter', name: 'artist.new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtistRepository $artistRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artist = $form->getData();

            /** @var UploadedFile $artistCoverFile */
            $artistCoverFile = $form->get('cover')->getData();

            if ($artistCoverFile) {
                $fileName = $fileUploader->upload($artistCoverFile, $this->getParameter('images_artists'), $slugger->slug($artist->getName()));
                $artist->setCover($fileName);
            }

            $artistRepository->add($artist);

            return $this->redirectToRoute('artist', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/new.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'artist.show', methods: ['GET'])]
    public function show(Artist $artist): Response
    {
        return $this->render('artist/show.html.twig', [
            'artist' => $artist,
        ]);
    }

    #[Route('/modifier/{slug}', name: 'artist.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artist $artist, ArtistRepository $artistRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $artistCoverFile */
            $artistCoverFile = $form->get('cover')->getData();

            if ($artistCoverFile) {
                if ($artist->getCover()) {
                    $fileUploader->delete($this->getParameter('images_artists'), $artist->getCover());
                }
                $fileName = $fileUploader->upload($artistCoverFile, $this->getParameter('images_artists'), $slugger->slug($artist->getName()));
                $artist->setCover($fileName);
            }

            $artistRepository->add($artist);

            return $this->redirectToRoute('artist', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'artist.delete', methods: ['POST'])]
    public function delete(Request $request, Artist $artist, ArtistRepository $artistRepository, FileUploader $fileUploader): Response
    {
        if ($this->isCsrfTokenValid('delete' . $artist->getId(), $request->request->get('_token'))) {
            $fileUploader->delete($this->getParameter('images_artists'), $artist->getCover());
            $artistRepository->remove($artist);
        }

        return $this->redirectToRoute('artist', [], Response::HTTP_SEE_OTHER);
    }
}
