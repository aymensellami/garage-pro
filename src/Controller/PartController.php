<?php
namespace App\Controller;

use App\Entity\Part;
use App\Form\PartType;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock')]
#[IsGranted('ROLE_MECHANIC')]
class PartController extends AbstractController
{
    #[Route('/', name: 'app_part_index', methods: ['GET'])]
    public function index(PartRepository $repo): Response
    {
        return $this->render('part/index.html.twig', [
            'parts' => $repo->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_part_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $part = new Part();
        $form = $this->createForm(PartType::class, $part);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($part);
            $em->flush();
            $this->addFlash('success', 'Pièce enregistrée.');
            return $this->redirectToRoute('app_part_index');
        }

        return $this->render('part/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'app_part_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Part $part, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PartType::class, $part);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Pièce mise à jour.');
            return $this->redirectToRoute('app_part_index');
        }
        return $this->render('part/edit.html.twig', ['part' => $part, 'form' => $form->createView()]);
    }
}
