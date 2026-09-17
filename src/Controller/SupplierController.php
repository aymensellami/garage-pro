<?php
namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierType;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier')]
#[IsGranted('ROLE_ADMIN')]
class SupplierController extends AbstractController
{
    #[Route('/', name: 'app_supplier_index', methods: ['GET'])]
    public function index(SupplierRepository $repo): Response
    {
        return $this->render('supplier/index.html.twig', ['suppliers' => $repo->findAll()]);
    }

    #[Route('/new', name: 'app_supplier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($supplier);
            $em->flush();
            $this->addFlash('success', 'Fournisseur enregistré.');
            return $this->redirectToRoute('app_supplier_index');
        }
        return $this->render('supplier/new.html.twig', ['form' => $form->createView()]);
    }
}
