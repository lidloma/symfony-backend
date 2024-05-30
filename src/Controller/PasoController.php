<?php

namespace App\Controller;

use App\Entity\Paso;
use App\Repository\RecetaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pasos')]
class PasoController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private RecetaRepository $recetaRepository;

    public function __construct(EntityManagerInterface $entityManager, RecetaRepository $recetaRepository)
    {
        $this->entityManager = $entityManager;
        $this->recetaRepository = $recetaRepository;
    }

    #[Route('/crear', name: 'crear_paso', methods: ['POST'])]
    public function crearPaso(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['numero']) || !isset($data['descripcion']) || !isset($data['receta_id'])) {
            return $this->json(['error' => 'Datos del paso incompletos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $receta = $this->recetaRepository->find($data['receta_id']);
        if (!$receta) {
            return $this->json(['error' => 'Receta no encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $paso = new Paso();
        $paso->setNumero($data['numero']);
        $paso->setDescripcion($data['descripcion']);
        $paso->setReceta($receta);

        if (isset($data['imagen'])) {
            $imagenBase64 = base64_decode($data['imagen']);
            $paso->setImagen($imagenBase64);
        }

        $this->entityManager->persist($paso);
        $this->entityManager->flush();

        return $this->json(['mensaje' => 'Paso creado correctamente', 'paso' => $paso], JsonResponse::HTTP_CREATED);
    }
}
