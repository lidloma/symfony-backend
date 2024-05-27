<?php

namespace App\Controller;

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
                'imagen' => $categoria->getImagen(),
            ];
        }

        return $this->json($categoriasArray);
    }

    #[Route('/api/actualizar', name: 'app_update_user_categories', methods: ['POST'])]
    public function updateUserCategories(Request $request, UsuarioRepository $userRepository, CategoriaRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = $request->get('usuario_id');
    
        $categories = $request->get('categoria');
    
        $user = $userRepository->find($userId);
    
        if (!$user) {
            throw $this->createNotFoundException('No user found for id '.$userId);
        }
    
        $user->getCategorias()->clear();
    
        foreach ($categories as $categoryId) {
            $category = $categoryRepository->find($categoryId);
            if ($category) {
                $user->addCategoria($category);
            }
        }
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return new Response('Updated user categories successfully');
    }

}