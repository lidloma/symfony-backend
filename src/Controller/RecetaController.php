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
   

    //Obtener receta por ID
    #[Route('/{id}', name: 'api_receta_show', methods: ['GET'])]
    public function show(Receta $receta): JsonResponse
    {
        $data = $this->recetaToArray($receta);

        return new JsonResponse($data, Response::HTTP_OK);
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


    #[Route('/buscar', name: 'buscar_recetas', methods: ['GET'])]
    public function buscarRecetasPorNombre(Request $request, RecetaRepository $recetaRepository): JsonResponse
    {
        $nombre = $request->query->get('q');
    
        if (!$nombre) {
            // Si no se proporciona un nombre de búsqueda, devolver un error
            return new JsonResponse(['error' => 'Se requiere un parámetro "q" para la búsqueda'], Response::HTTP_BAD_REQUEST);
        }
    
        // Obtener recetas por nombre utilizando el método personalizado del repositorio
        $recetas = $recetaRepository->findRecetasByNombre($nombre);
    
        // Formatear las recetas en el formato deseado
        $data = [];
        foreach ($recetas as $receta) {
            $data[] = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                // Agregar aquí otros atributos de la receta que desees incluir en la respuesta JSON
            ];
        }
    
        // Devolver las recetas como una respuesta JSON
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/buscar-por-ingredientes', name: 'buscar_recetas_por_ingredientes', methods: ['GET'])]
    public function buscarRecetasPorIngredientes(Request $request, RecetaRepository $recetaRepository): JsonResponse
    {
        $ingredientes = $request->query->get('ingredientes', '');
        $ingredientesArray = explode(',', $ingredientes);

        // Buscar recetas por ingredientes
        $recetas = $recetaRepository->findRecetasByIngredientes($ingredientesArray);

        // Devolver las recetas como una respuesta JSON
        return $this->json($recetas);
    }

    #[Route('/categorias-siguiendo', name: 'recetas_categorias_siguiendo', methods: ['GET'])]
    public function recetasCategoriasSiguiendo(): JsonResponse
    {
        // Obtener categorías que sigue el usuario actual (suponiendo que tienes una relación entre Usuario y Categoria)
        $usuario = $this->getUser();
        $categorias = $usuario->getCategorias();

        // Obtener recetas asociadas a las categorías que sigue el usuario
        $recetas = [];
        foreach ($categorias as $categoria) {
            $recetasCategoria = $categoria->getRecetas();
            foreach ($recetasCategoria as $receta) {
                $recetas[] = $receta;
            }
        }

        // Ordenar las recetas por fecha de manera descendente
        usort($recetas, function($a, $b) {
            return $b->getFecha() <=> $a->getFecha();
        });

        // Serializar las recetas a un arreglo de datos para la respuesta JSON
        $data = [];
        foreach ($recetas as $receta) {
            $data[] = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                // Agregar aquí otros atributos de la receta que desees incluir en la respuesta JSON
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/usuarios-siguiendo', name: 'recetas_usuarios_siguiendo', methods: ['GET'])]
    public function recetasUsuariosSiguiendo(): JsonResponse
    {
        // Obtener usuarios que sigue el usuario actual
        $usuario = $this->getUser();
        $usuariosSeguidos = $usuario->getUsuarios();

        // Obtener recetas creadas por los usuarios seguidos
        $recetas = [];
        foreach ($usuariosSeguidos as $usuarioSeguido) {
            $recetasUsuario = $usuarioSeguido->getRecetas();
            foreach ($recetasUsuario as $receta) {
                $recetas[] = $receta;
            }
        }

        // Ordenar las recetas por fecha de manera descendente
        usort($recetas, function($a, $b) {
            return $b->getFecha() <=> $a->getFecha();
        });

        // Serializar las recetas a un arreglo de datos para la respuesta JSON
        $data = [];
        foreach ($recetas as $receta) {
            $data[] = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                // Agregar aquí otros atributos de la receta que desees incluir en la respuesta JSON
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
    
    #[Route('/categorias-seguidas', name: 'recetas_categorias_seguidas', methods: ['GET'])]
    public function recetasCategoriasSeguidas(Request $request): JsonResponse
    {
        // Obtener el usuario actual (asumiendo que ya tienes implementada la lógica para gestionar la autenticación)
        $usuario = $this->getUser();

        // Obtener las categorías seguidas por el usuario
        $categoriasSeguidas = $usuario->getCategorias();

        // Inicializar un array para almacenar las recetas
        $recetas = [];

        // Para cada categoría seguida por el usuario, obtener las recetas asociadas
        foreach ($categoriasSeguidas as $categoria) {
            // Obtener las recetas asociadas a la categoría
            $recetasCategoria = $categoria->getRecetas();

            // Agregar las recetas al array de recetas
            foreach ($recetasCategoria as $receta) {
                $recetas[] = [
                    'id' => $receta->getId(),
                    'nombre' => $receta->getNombre(),
                    // Agrega otros campos de la receta según necesites
                ];
            }
        }

        // Devolver las recetas encontradas en formato JSON
        return $this->json($recetas);
    }

    protected function transformJsonBody(Request $request) {
        $data = json_decode($request->getContent(), true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        if($data == null){
            return $request;
        }

        $request->request->replace($data);
        return $request;
    }






}