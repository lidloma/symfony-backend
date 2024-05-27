<?php

namespace App\Controller;

use App\Entity\Lista;
use App\Repository\ListaRepository;
use App\Repository\RecetaRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/lista')]
class ListaController extends AbstractController
{
    #[Route('/{id}', name: 'app_lista', methods: ['GET'])]
    public function getListasUsuario(UsuarioRepository $usuarioRepository, RecetaRepository $recetaRepository, int $id): JsonResponse
    {
        $usuario = $usuarioRepository->find($id);
        if (!$usuario) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = [];
        foreach ($usuario->getListas() as $lista) {
            $recetas = [];
            foreach ($lista->getRecetas() as $receta) {
                $imagenes = [];
                foreach ($receta->getImagenes() as $imagen) {
                    $imagenes[] = [
                        'id' => $imagen->getId(),
                        'imagen' => $imagen->getImagen(),
                    ];
                }
                $recetas[] = [
                    'id' => $receta->getId(),
                    'nombre' => $receta->getNombre(),
                    'imagen' => $imagenes
                ];
            }

            $data[] = [
                'id' => $lista->getId(),
                'nombre' => $lista->getNombre(),
                'recetas' => $recetas,
                'imagen' => $imagenes // ¿Es necesario tener esto aquí?
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}/add-receta', name: 'add_receta_to_lista', methods: ['POST'])]
    public function addRecetaToLista(Request $request, int $id, RecetaRepository $recetaRepository, ListaRepository $listaRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $body = json_decode($request->getContent(), true);

            if (!$body) {
                return new JsonResponse(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $recetaId = $body['receta_id'] ?? null;

            if (!$recetaId) {
                return new JsonResponse(['error' => 'Missing receta_id'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $lista = $listaRepository->find($id);
            if (!$lista) {
                return new JsonResponse(['error' => 'Lista not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $receta = $recetaRepository->find($recetaId);
            if (!$receta) {
                return new JsonResponse(['error' => 'Receta not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            $lista->addReceta($receta);
            $entityManager->persist($lista);
            $entityManager->flush();

            return new JsonResponse(['success' => 'Receta added to Lista successfully'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/crear', name: 'crear_lista', methods: ['POST'])]
    public function crearLista(Request $request, UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Decodificar el cuerpo de la solicitud JSON
        $data = json_decode($request->getContent(), true);

        // Verificar si el cuerpo de la solicitud está vacío o es inválido
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Obtener los datos del cuerpo de la solicitud
        $usuarioId = $data['usuario_id'] ?? null;
        $nombre = $data['nombre'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $imagen = $data['imagen'] ?? null;

        // Verificar si faltan campos obligatorios
        if (!$usuarioId || !$nombre || !$descripcion || !$imagen) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Obtener el usuario por ID
        $usuario = $usuarioRepository->find($usuarioId);

        // Verificar si el usuario existe
        if (!$usuario) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Crear una nueva lista
        $lista = new Lista();
        $lista->setUsuario($usuario);
        $lista->setNombre($nombre);
        $lista->setDescripcion($descripcion);
        $lista->setImagen($imagen);

        // Persistir la lista en la base de datos
        $entityManager->persist($lista);
        $entityManager->flush();

        // Devolver una respuesta con el ID de la lista creada
        return new JsonResponse(['success' => 'Lista created successfully', 'id' => $lista->getId()], Response::HTTP_CREATED);
    }

}