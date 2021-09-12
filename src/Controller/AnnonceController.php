<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @Route("/annonce")
 */
class AnnonceController extends AbstractController
{
    /**
     * @Route("/", name="annonce_index", methods={"GET"})
     */
    public function index(AnnonceRepository $annonceRepository): Response
    {

        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonceRepository->findAll(),
        ] );

    }

    /**
     * @Route("/search", name="search" , methods={"GET","POST"})
     * @param Request $request
     * @param AnnonceRepository $annonceRepository
     * @return Response
     */

    public function Search(Request $request, AnnonceRepository  $annonceRepository)
    {
        $annonce = $annonceRepository->findAll();$data = $request->get('search');
        $annonces =[];
        if ($request->isMethod('POST') &&$data = $request->get('search') ) {

            $annonces = $annonceRepository->findBy(array('nom' => $data));
        }

            if ($request->isMethod('POST') && $data = $request->get('recherche')) {
                $annonces = $annonceRepository->getAnnonce($data);

            }

        return $this->render('recherche.html.twig', ['annonces' => $annonces ]);
    }


    /**
     * @Route("/afficher/{name}", name="afficher" , methods={"GET","POST"})

     * @return Response
     */
    public function display(Request $request, AnnonceRepository  $annonceRepository ,string $name ): Response
    {
        $annonces = new Annonce();

        $data = $request->get('immobilier');

        $annonces = $annonceRepository->getAnnonce($name);


        return $this->render('annonce/index.html.twig', ['annonces' => $annonces]);
    }



    /**
     * @Route("/new", name="annonce_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $annonce = new Annonce();

        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            $fileImage = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_directory'), $fileImage);
            $annonce->setImage($fileImage);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('annonce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="annonce_show", methods={"GET"})
     */
    public function show(Annonce $annonce): Response
    {
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="annonce_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Annonce $annonce): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('annonce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="annonce_delete", methods={"POST"})
     */
    public function delete(Request $request, Annonce $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('annonce_index', [], Response::HTTP_SEE_OTHER);
    }


}
