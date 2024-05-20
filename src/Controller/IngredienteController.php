<?php

namespace App\Controller;

use App\Repository\IngredienteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ingredientes')]

class IngredienteController extends AbstractController
{
    private IngredienteRepository $ingredienteRepository;

    public function __construct(IngredienteRepository $ingredienteRepository)
    {
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
    
}
