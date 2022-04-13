<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/album')]
class AlbumController extends AbstractController
{
    #[Route('/', name: 'album', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): Response
    {
        return $this->render('album/index.html.twig', [
            'albums' => $albumRepository->findAll(),
        ]);
    }

    #[Route('/ajouter', name: 'album.new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlbumRepository $albumRepository, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $album = new Album();
        $album->setNbPlays(0);
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

            return $this->redirectToRoute('album', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('album/new.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'album.show', methods: ['GET'])]
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

            return $this->redirectToRoute('album', [], Response::HTTP_SEE_OTHER);
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

        return $this->redirectToRoute('album', [], Response::HTTP_SEE_OTHER);
    }
}
