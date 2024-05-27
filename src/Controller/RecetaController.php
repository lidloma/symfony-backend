<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Entity\Imagen;
use App\Entity\Ingrediente;
use App\Entity\Paso;
use App\Entity\Receta;
use App\Form\RecetaType;
use App\Repository\RecetaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Usuario;
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

            $usuario = [];
            foreach ($recetas as $receta) {
                $usuario = [
                    'id' => $receta->getUsuario()->getId(),
                    'email' => $receta->getUsuario()->getEmail(),
                    'nombre' => $receta->getUsuario()->getNombre(),
                    'nombreUsuario' => $receta->getUsuario()->getNombreUsuario(),
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
                'usuario' => $usuario,
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
    
        $data = [];
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
    
            // Obtener datos del usuario asociado a la receta actual
            $usuario = [
                'id' => $receta->getUsuario()->getId(),
                'email' => $receta->getUsuario()->getEmail(),
                'nombre' => $receta->getUsuario()->getNombre(),
                'nombreUsuario' => $receta->getUsuario()->getNombreUsuario(),
            ];
    
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
                'usuario' => $usuario,
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
                    'cantidad' => $ingrediente->getCantidad(),
                    'unidad' => $ingrediente->getUnidad(),
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

            
            $usuario = [
                'id' => $receta->getUsuario()->getId(),
                'email' => $receta->getUsuario()->getEmail(),
                'nombre' => $receta->getUsuario()->getNombre(),
                'nombreUsuario' => $receta->getUsuario()->getNombreUsuario(),
            ];
    
            $data = [
                'id' => $receta->getId(),
                'nombre' => $receta->getNombre(),
                'categorias' => $categorias,
                'comentarios' => $comentarios,
                'complejidad' => $receta->getComplejidad(),
                'descripcion' => $receta->getDescripcion(),
                'estado' => $receta->getEstado(),
                'fecha' => $receta->getFecha()->format('d-m-Y'),
                'imagen' => $imagenes, 
                'ingrediente' => $ingredientes, 
                'usuario' => $usuario,
                'tiempo' => $receta->getTiempo(),
                'numeroPersonas' => $receta->getNumeroPersonas(),
                'paso' => $pasos
            ];
        
        return $this->json($data);
    }

    #[Route('/crear', name: 'api_receta_crear', methods: ['POST'])]
    public function crearReceta(EntityManagerInterface $entityManager, Request $request): JsonResponse {
        try {
            $body = json_decode($request->getContent(), true);
        
            // Obtener datos del cuerpo de la solicitud
            $tiempo = $body['tiempo'];
            $descripcion = $body['descripcion'];
            $estado = $body['estado'];
            $fecha = new \DateTime($body['fecha']); // Assuming the date is passed as a string
            $nombre = $body['nombre'];
            $categoriasData = $body['categorias'];
            $pasosData = $body['pasos'];
            $imagenesData = $body['imagenes'];
            $ingredientesData = $body['ingredientes'];
            $usuarioId = $body['usuario'];
            $numeroPersonas = $body['numeroPersonas'];
            $complejidad = $body['complejidad'];

            // Encontrar al usuario
            $usuario = $entityManager->getRepository(Usuario::class)->find($usuarioId);
            if (!$usuario) {
                return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_BAD_REQUEST);
            }

            // Crear una nueva instancia de Receta
            $receta = new Receta();
            $receta->setTiempo($tiempo);
            $receta->setDescripcion($descripcion);
            $receta->setEstado($estado);
            $receta->setFecha($fecha);
            $receta->setNombre($nombre);
            $receta->setNumeroPersonas($numeroPersonas);
            $receta->setComplejidad($complejidad);
            $receta->setUsuario($usuario);

            // Manejar categorías
            $categoriaRepository = $entityManager->getRepository(Categoria::class);
            foreach ($categoriasData as $categoriaId) {
                $categoria = $categoriaRepository->find($categoriaId);
                if ($categoria) {
                    $receta->addCategoria($categoria);
                }
            }

            // Manejar ingredientes
            $ingredienteRepository = $entityManager->getRepository(Ingrediente::class);
            foreach ($ingredientesData as $ingredienteData) {
                $ingrediente = new Ingrediente();
                $ingrediente->setNombre($ingredienteData['nombre']);
                $ingrediente->setCantidad($ingredienteData['cantidad']);
                $ingrediente->setUnidad($ingredienteData['unidad']);
                $entityManager->persist($ingrediente);
                $receta->addIngrediente($ingrediente);
            }

            // Manejar pasos
            foreach ($pasosData as $pasoData) {
                $paso = new Paso();
                $paso->setNumero($pasoData['numero']);
                $paso->setDescripcion($pasoData['descripcion']);
                $paso->setImagen($pasoData['imagen']);
                $paso->setReceta($receta);
                $entityManager->persist($paso);
                $receta->addPaso($paso);
            }

            // Manejar imágenes
            foreach ($imagenesData as $imagenData) {
                $imagen = new Imagen();
                $imagen->setImagen($imagenData['imagen']);
                $imagen->setReceta($receta);
                $entityManager->persist($imagen);
                $receta->addImagene($imagen);
            }

            // Persistir la Receta
            $entityManager->persist($receta);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Receta registrada con éxito'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Manejar la excepción
            return new JsonResponse(['message' => 'Error al procesar la solicitud: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}