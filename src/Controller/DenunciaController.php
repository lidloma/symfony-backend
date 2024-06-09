<?php

namespace App\Controller;

use App\Entity\Denuncia;
use App\Entity\Receta;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DenunciaController extends AbstractController
{
    #[Route('/api/denunciar', name: 'api_denunciar_receta', methods: ['POST'])]
    public function denunciar(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['motivo']) || empty($data['fecha']) || empty($data['usuario_id']) || empty($data['receta_id'])) {
            return new JsonResponse(['error' => 'Datos incompletos'], Response::HTTP_BAD_REQUEST);
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($data['usuario_id']);
        $receta = $entityManager->getRepository(Receta::class)->find($data['receta_id']);

        if (!$usuario || !$receta) {
            return new JsonResponse(['error' => 'Usuario o Receta no encontrados'], Response::HTTP_NOT_FOUND);
        }

        $denuncia = new Denuncia();
        $denuncia->setMotivo($data['motivo']);
        $denuncia->setFecha(new \DateTime($data['fecha']));
        $denuncia->setUsuario($usuario);
        $denuncia->setReceta($receta);

        $entityManager->persist($denuncia);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Denuncia creada exitosamente'], Response::HTTP_CREATED);
    }

    #[Route('/api/denuncias', name: 'api_listar_denuncias', methods: ['GET'])]
    public function listarDenuncias(EntityManagerInterface $entityManager): JsonResponse
    {
        $denuncias = $entityManager->getRepository(Denuncia::class)->findAll();
        $data = [];

        foreach ($denuncias as $denuncia) {
            $data[] = [
                'id' => $denuncia->getId(),
                'motivo' => $denuncia->getMotivo(),
                'fecha' => $denuncia->getFecha()->format('Y-m-d'),
                'usuario_id' => $denuncia->getUsuario()->getId(),
                'receta_id' => $denuncia->getReceta()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

}
