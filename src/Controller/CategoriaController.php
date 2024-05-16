<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categorias')]
class CategoriaController extends AbstractController
{
    private CategoriaRepository $categoriaRepository;

    public function __construct(CategoriaRepository $categoriaRepository)
    {
        $this->categoriaRepository = $categoriaRepository;
    }

    #[Route('/', name: 'app_categoria')]
    public function getCategorias(): JsonResponse
    {
        $categorias = $this->categoriaRepository->findAll();

        $categoriasArray = [];
        foreach ($categorias as $categoria) {
            $categoriasArray[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'estado' => $categoria->getEstado(),
                'imagen' => $categoria->getImagen(),
            ];
        }

        return $this->json($categoriasArray);
    }
}