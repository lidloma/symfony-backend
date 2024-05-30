<?php

namespace App\Controller;

use App\Entity\Ingrediente;
use App\Repository\IngredienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ingredientes')]

class IngredienteController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private IngredienteRepository $ingredienteRepository;

    public function __construct(EntityManagerInterface $entityManager, IngredienteRepository $ingredienteRepository)
    {
        $this->entityManager = $entityManager;
        $this->ingredienteRepository = $ingredienteRepository;
    }
    #[Route('/', name: 'app_ingredientes')]
    public function getIngredientes(): JsonResponse
    {
        $ingredientes = $this->ingredienteRepository->findAll();

        $ingredientesArray = [];
        foreach ($ingredientes as $ingrediente) {
            $ingredientesArray[] = [
                'id' => $ingrediente->getId(),
                'nombre' => $ingrediente->getNombre(),
                'cantidad' => $ingrediente->getCantidad(),
                'unidad' => $ingrediente->getUnidad(),
            ];
        }

        return $this->json($ingredientesArray);
    }

   

    #[Route('/crear', name: 'crear_ingrediente', methods: ['POST'])]
    public function crearIngrediente(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar los datos recibidos
        if (!$data || !isset($data['nombre']) || !isset($data['cantidad']) || !isset($data['unidad'])) {
            return $this->json(['error' => 'Datos de ingrediente incompletos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Crear una nueva instancia de Ingrediente
        $ingrediente = new Ingrediente();
        $ingrediente->setNombre($data['nombre']);
        $ingrediente->setCantidad($data['cantidad']);
        $ingrediente->setUnidad($data['unidad']);

        // Persistir el ingrediente en la base de datos
        $this->entityManager->persist($ingrediente);
        $this->entityManager->flush();

        return $this->json(['mensaje' => 'Ingrediente creado correctamente', 'ingrediente' => $ingrediente]);
    }
}
    
