<?php

namespace App\Controller;

use App\Entity\Receta;
use App\Form\RecetaType;
use App\Repository\RecetaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/api/recetas')]

class RecetaController extends AbstractController
{
    //Obtener todas las recetas
    #[Route('/', name: 'ver_recetas', methods: ['GET'])]
    public function getRecetas(EntityManagerInterface $entityManager): JsonResponse {
        $recetas = $entityManager->getRepository(Receta::class)->findAll();
        foreach ($recetas as $receta) {
            $categorias = [];
            foreach ($receta->getCategorias() as $categoria) {
                $categorias[] = $categoria->getNombre();
            }
    
            $comentarios = [];
            foreach ($receta->getComentarios() as $comentario) {
                $comentarios[] = $comentario->getDescripcion();
            }
            
            $imagenes = [];
            foreach ($receta->getImagenes() as $imagen) {
                $imagenes[] = $imagen->getImagen(); 
            }
    
            $ingredientes = [];
            foreach ($receta->getIngredientes() as $ingrediente) {
                $ingredientes[] = $ingrediente->getNombre(); 
            }
    
            $pasos = [];
            foreach ($receta->getPasos() as $paso) {
                $pasos[] = [
                    'id' => $paso->getId(),
                    'descripcion' => $paso->getDescripcion(),
                    'imagen' => $paso->getImagen(),
                    'numero' => $paso->getNumero(),
                ];
            }
    
            $data[] = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                'categorias' => $categorias,
                'comentarios' => $comentarios,
                'descripcion' => $receta->getDescripcion(),
                'estado' => $receta->getEstado(),
                'fecha' => $receta->getFecha()->format('Y-m-d'),
                'imagen' => $imagenes, 
                'ingrediente' => $ingredientes, 
                'usuario' => $receta->getUsuario()->getNombreUsuario(),
                'tiempo' => $receta->getTiempo(),
                'paso' => $pasos
            ];
        }
        return $this->json($data);
    }  


    #[Route('/search', name: 'buscar_receta', methods: ['GET'])]
    public function getRecetaPorNombre(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
    $nombreReceta = $request->query->get('q');
        // Obtener las recetas cuyo nombre contiene la palabra pasada como parámetro
        $recetas = $entityManager->getRepository(Receta::class)->createQueryBuilder('receta')
            ->where('LOWER(receta.nombre) LIKE :nombreReceta')
            ->setParameter('nombreReceta', '%' . strtolower($nombreReceta) . '%')
            ->getQuery()
            ->getResult();

        // Verificar si se encontraron recetas
        if (empty($recetas)) {
            throw $this->createNotFoundException('No se encontraron recetas con ese nombre');
        }

        foreach ($recetas as $receta) {
            $categorias = [];
            foreach ($receta->getCategorias() as $categoria) {
                $categorias[] = $categoria->getNombre();
            }
    
            $comentarios = [];
            foreach ($receta->getComentarios() as $comentario) {
                $comentarios[] = $comentario->getDescripcion();
            }
            
            $imagenes = [];
            foreach ($receta->getImagenes() as $imagen) {
                $imagenes[] = $imagen->getImagen(); 
            }
    
            $ingredientes = [];
            foreach ($receta->getIngredientes() as $ingrediente) {
                $ingredientes[] = $ingrediente->getNombre(); 
            }
    
            $pasos = [];
            foreach ($receta->getPasos() as $paso) {
                $pasos[] = $paso->getDescripcion(); 
            }
    
            $data[] = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                'categorias' => $categorias,
                'comentarios' => $comentarios,
                'descripcion' => $receta->getDescripcion(),
                'estado' => $receta->getEstado(),
                'fecha' => $receta->getFecha()->format('Y-m-d'),
                'imagen' => $imagenes, 
                'ingrediente' => $ingredientes, 
                'usuario' => $receta->getUsuario()->getNombreUsuario(),
                'tiempo' => $receta->getTiempo(),
                'paso' => $pasos
            ];
        }
        return $this->json($data);
    }

    //Crear nueva receta (para formulario de cliente)
   

    //Obtener receta por ID IMPORTANTE
    #[Route('/{id}', name: 'api_receta_id', methods: ['GET'])]
    public function getReceta(int $id, RecetaRepository $recetaRepository): JsonResponse
    {
        $receta = $recetaRepository->find($id);

            $categorias = [];
            foreach ($receta->getCategorias() as $categoria) {
                $categorias[] = [
                    'id' => $categoria->getId(),
                    'nombre' => $categoria->getNombre(),
                    'estado' => $categoria->getEstado(),
                ];
            }
    
            $comentarios = [];
            foreach ($receta->getComentarios() as $comentario) {
                $comentarios[] = [
                'id' => $comentario->getId(),
                'usuario_id' => $comentario->getUsuario(),
                'receta_id' => $comentario->getReceta(),
                'comentario_id' => $comentario->getComentarios(),
                'descripcion' => $comentario->getDescripcion(),
                'puntuacion' => $comentario->getPuntuacion(),
                'complejidad' => $comentario->getComplejidad()
                
                ];
            }
            
            $imagenes = [];
            foreach ($receta->getImagenes() as $imagen) {
                $imagenes[] = [
                'id' => $imagen->getId(),
                'receta_id' => $imagen->getReceta(),
                'imagen' => $imagen->getImagen(),
                
            ]; 
            }
    
            $ingredientes = [];
            foreach ($receta->getIngredientes() as $ingrediente) {
                $ingredientes[] = [
                    'id' => $ingrediente->getId(),
                    'descripcion' => $ingrediente->getNombre(),
                    'imagen' => $ingrediente->getCantidad(),
                    'numero' => $ingrediente->getUnidad(),
                ]; 
            }
    
            $pasos = [];
            foreach ($receta->getPasos() as $paso) {
                $pasos[] = [
                    'id' => $paso->getId(),
                    'descripcion' => $paso->getDescripcion(),
                    'imagen' => $paso->getImagen(),
                    'numero' => $paso->getNumero(),
                ];
            }
    
            $data = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                'categorias' => $categorias,
                'comentarios' => $comentarios,
                'descripcion' => $receta->getDescripcion(),
                'estado' => $receta->getEstado(),
                'fecha' => $receta->getFecha()->format('d-m-Y'),
                'imagen' => $imagenes, 
                'ingrediente' => $ingredientes, 
                'usuario' => $receta->getUsuario()->getNombreUsuario(),
                'tiempo' => $receta->getTiempo(),
                'paso' => $pasos
            ];
        
        return $this->json($data);
    }

   
    #[Route('/borrar/{id}', name: 'api_receta_delete', methods: ['DELETE'])]
    public function delete(Receta $receta, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($receta);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Receta eliminada'], Response::HTTP_OK);
    }

    private function recetaToArray(Receta $receta): array {
    $ingredientes = [];
    foreach ($receta->getIngredientes() as $ingrediente) {
        $ingredientes[] = [
            'id' => $ingrediente->getId(),
            'nombre' => $ingrediente->getNombre(),
        ];
    }

    $pasos = [];
    foreach ($receta->getPasos() as $paso) {
        $pasos[] = [
            'id' => $paso->getId(),
            'descripcion' => $paso->getDescripcion(),
        ];
    }

    $imagenes = [];
    foreach ($receta->getImagenes() as $imagen) {
        $imagenes[] = [
            'id' => $imagen->getId(),
            'url' => $imagen->getImagen(),
        ];
    }

    $comentarios = [];
    foreach ($receta->getComentarios() as $comentario) {
        $comentarios[] = [
            'id' => $comentario->getId(),
            'contenido' => $comentario->getDescripcion(),
        ];
    }

    $categorias = [];
    foreach ($receta->getCategorias() as $categoria) {
        $categorias[] = [
            'id' => $categoria->getId(),
            'nombre' => $categoria->getNombre(),
            'estado' => $categoria->getEstado(),
            'imagen' => $categoria->getImagen(),

        ];
    }

    return [
        'id' => $receta->getId(),
        'tiempo' => $receta->getTiempo(),
        'descripcion' => $receta->getDescripcion(),
        'estado' => $receta->getEstado(),
        'fecha' => $receta->getFecha()->format('d-m-Y'), 
        'usuario' => [
            'id' => $receta->getUsuario()->getId(),
            'nombre' => $receta->getUsuario()->getNombre(), 
        ],
        'ingredientes' => $ingredientes,
        'pasos' => $pasos,
        'imagenes' => $imagenes,
        'comentarios' => $comentarios,
        'categorias' => $categorias,
        'nombre' => $receta->getNombre(),
        ];
    }

}