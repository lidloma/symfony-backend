<?php

namespace App\Controller;

use App\Entity\Comentario;
use App\Repository\ComentarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Receta;
use Proxies\__CG__\App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/comentarios')]
class ComentarioController extends AbstractController {
    #[Route('/crear', name: 'app_comentario_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $descripcion = $body['descripcion'] ?? null;
        $puntuacion = $body['puntuacion'] ?? null;
        $complejidad = $body['complejidad'] ?? null;
        $usuarioId = $body['usuario_id'];
        $recetaId = $body['receta_id'];
        $comentarioId = $body['comentario_id'] ?? null; // Campo opcional para respuestas

        // Buscar el usuario y la receta asociados
        $usuarioRepository = $entityManager->getRepository(Usuario::class);
        $recetaRepository = $entityManager->getRepository(Receta::class);
        $comentarioRepository = $entityManager->getRepository(Comentario::class);

        $usuario = $usuarioRepository->find($usuarioId);
        $receta = $recetaRepository->find($recetaId);
        $comentarioPadre = $comentarioId ? $comentarioRepository->find($comentarioId) : null;

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

        if ($comentarioPadre) {
            $comentario->setComentario($comentarioPadre);
        }

        // Guardar el comentario en la base de datos
        $entityManager->persist($comentario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Comentario creado con éxito'], Response::HTTP_CREATED);
    }
    #[Route('/receta/{recetaId}', name: 'get_comentarios_by_receta', methods: ['GET'])]
    public function getComentariosByReceta(int $recetaId, ComentarioRepository $comentarioRepository): JsonResponse
    {
        $comentarios = $comentarioRepository->findBy(['receta' => $recetaId]);
    
        if (!$comentarios) {
            return new JsonResponse(['error' => 'No comments found for this recipe'], 404);
        }
    
        $data = [];
        foreach ($comentarios as $comentario) {
            $respuestas = [];
            foreach ($comentario->getComentarios() as $respuesta) {
                $respuestas[] = [
                    'id' => $respuesta->getId(),
                    'descripcion' => $respuesta->getDescripcion(),
                    'puntuacion' => $respuesta->getPuntuacion()? $respuesta->getPuntuacion(): null,
                    'complejidad' => $respuesta->getComplejidad() ? $respuesta->getComplejidad() : null,
                    'usuario' => $respuesta->getUsuario() ? $respuesta->getUsuario()->getId() : null,
                    'receta' => $respuesta->getReceta() ? $respuesta->getReceta()->getId() : null,
                    'comentario' => $respuesta->getComentario() ? $respuesta->getComentario()->getId() : null,
                ];
            }
    
            $data[] = [
                'id' => $comentario->getId(),
                'descripcion' => $comentario->getDescripcion(),
                'puntuacion' => $comentario->getPuntuacion()? $comentario->getPuntuacion(): null,
                'complejidad' => $comentario->getComplejidad() ? $comentario->getComplejidad() : null,
                'usuario' => $comentario->getUsuario() ? $comentario->getUsuario()->getId() : null,
                'receta' => $comentario->getReceta() ? $comentario->getReceta()->getId() : null,
                'comentario' => $comentario->getComentario() ? $comentario->getComentario()->getId() : null,
                'respuestas' => $respuestas,
            ];
        }
    
        return new JsonResponse($data, 200);
    }
}    