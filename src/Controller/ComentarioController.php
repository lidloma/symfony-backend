<?php

namespace App\Controller;

use App\Entity\Comentario;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Receta;
use Proxies\__CG__\App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/comentarios')]
class ComentarioController extends AbstractController
{
    #[Route('/crear', name: 'app_comentario_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $descripcion = $body['descripcion'];
        $puntuacion = $body['puntuacion'];
        $complejidad = $body['complejidad'];
        $usuarioId = $body['usuario_id'];
        $recetaId = $body['receta_id'];

        // Buscar el usuario y la receta asociados
        $usuarioRepository = $entityManager->getRepository(Usuario::class);
        $recetaRepository = $entityManager->getRepository(Receta::class);
        
        $usuario = $usuarioRepository->find($usuarioId);
        $receta = $recetaRepository->find($recetaId);

        if (!$usuario || !$receta) {
            return new JsonResponse(['message' => 'Usuario o Receta no encontrados'], Response::HTTP_BAD_REQUEST);
        }

        // Crear una nueva instancia de comentario
        $comentario = new Comentario();
        $comentario->setDescripcion($descripcion);
        $comentario->setPuntuacion($puntuacion);
        $comentario->setComplejidad($complejidad);
        $comentario->setUsuario($usuario);
        $comentario->setReceta($receta);

        // Guardar el comentario en la base de datos
        $entityManager->persist($comentario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Comentario creado con éxito'], Response::HTTP_CREATED);
    }

}
