<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Entity\Usuario;
use App\Repository\CategoriaRepository;
use App\Repository\RecetaRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categorias')]
class CategoriaController extends AbstractController
{
    private CategoriaRepository $categoriaRepository;

    public function __construct(CategoriaRepository $categoriaRepository)
    {
        $this->categoriaRepository = $categoriaRepository;
    }

    private function generarImagenUrl($imagen):string{
        return 'data:image/jpeg;base64,'.base64_encode(stream_get_contents($imagen));
    }

    #[Route('/', name: 'app_categorias')]
    public function getCategorias(): JsonResponse
    {
        $categorias = $this->categoriaRepository->findAll();

        $categoriasArray = [];
        foreach ($categorias as $categoria) {
            $categoriasArray[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'estado' => $categoria->getEstado(),
                'imagen' => $this->generarImagenUrl($categoria->getImagen()),
            ];
        }

        return $this->json($categoriasArray);
    }

    // #[Route('/actualizar', name: 'app_update_user_categories', methods: ['POST'])]
    // public function updateUserCategories(Request $request, UsuarioRepository $userRepository, CategoriaRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    // {
    //     $userId = $request->get('usuario_id');
    
    //     $categories = $request->get('categoria');
    
    //     $user = $userRepository->find($userId);
    
    //     if (!$user) {
    //         throw $this->createNotFoundException('No user found for id '.$userId);
    //     }
    
    //     $user->getCategorias()->clear();
    
    //     foreach ($categories as $categoryId) {
    //         $category = $categoryRepository->find($categoryId);
    //         if ($category) {
    //             $user->addCategoria($category);
    //         }
    //     }
    
    //     $entityManager->persist($user);
    //     $entityManager->flush();
    
    //     return new Response('Updated user categories successfully');
    // }

    #[Route('/{id}/actualizar', name: 'actualizar_categorias_usuario', methods: ['PUT'])]
    public function actualizarCategoriasUsuario(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        // Obtener el usuario
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            // Si el usuario no existe, retornar una respuesta 404
            return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obtener las categorías enviadas en la solicitud
        $data = json_decode($request->getContent(), true);

        // Verificar si se enviaron categorías
        if (!isset($data['categorias'])) {
            return new JsonResponse(['message' => 'Se requieren categorías para actualizar'], Response::HTTP_BAD_REQUEST);
        }

        // Obtener las categorías seleccionadas por el usuario
        $categoriasSeleccionadas = $data['categorias'];

        // Limpiar las categorías anteriores del usuario
        $usuario->getCategorias()->clear();

        // Agregar las nuevas categorías seleccionadas por el usuario
        foreach ($categoriasSeleccionadas as $categoriaId) {
            $categoria = $entityManager->getRepository(Categoria::class)->find($categoriaId);

            if ($categoria) {
                $usuario->addCategoria($categoria);
            }
        }

        // Guardar los cambios en la base de datos
        $entityManager->flush();

        // Retornar una respuesta exitosa
        return new JsonResponse(['message' => 'Categorías actualizadas exitosamente'], Response::HTTP_OK);
    }
}

