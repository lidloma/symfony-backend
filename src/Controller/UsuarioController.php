<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\CategoriaRepository;
use App\Repository\RecetaRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Categoria;

#[Route('/api/usuarios')]
class UsuarioController extends AbstractController
{

    //Obtener a través de las categorías que sigue un usuario todas las recetas de esas categorías
    #[Route('/{id}', name: 'app_usuario_id', methods: ['GET'])]
    public function getDatosUsuarioId(UsuarioRepository $usuarioRepository, RecetaRepository $recetaRepository, int $id): JsonResponse {
        $usuario = $usuarioRepository->find($id);
    
        // Obteniendo las categorías que sigue el usuario
        $categorias = [];
        foreach ($usuario->getCategorias() as $categoria) {
            $categorias[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'estado' => $categoria->getEstado(),
                'imagen' => $categoria->getImagen(),  // Asegúrate de que este método devuelve la imagen correcta
            ];
        }
    
        // Obteniendo los comentarios hechos por el usuario
        $comentarios = [];
        foreach ($usuario->getComentarios() as $comentario) {
            $comentarios[] = [
                'id' => $comentario->getId(),
                'usuario_id' => $comentario->getUsuario()->getId(),
                'receta_id' => $comentario->getReceta()->getId(),
                'descripcion' => $comentario->getDescripcion(),
                'puntuacion' => $comentario->getPuntuacion(),
                'complejidad' => $comentario->getComplejidad(),
            ];
        }

        
        // Obteniendo las recetas creadas por el usuario
        $recetas = [];
        foreach ($usuario->getRecetas() as $receta) {
            $imagenesReceta = [];
            foreach ($receta->getImagenes() as $imagenReceta) {
                $imagenesReceta[] = [
                    'id' => $imagenReceta->getId(),
                    'imagen' => $imagenReceta->getImagen(),
                    'receta_id' => $imagenReceta->getReceta()->getId(),
                ];
            }

    $comentariosReceta = [];
    foreach ($receta->getComentarios() as $comentarioReceta) {
        $comentariosReceta[] = [
            'id' => $comentarioReceta->getId(),
            'descripcion' => $comentarioReceta->getDescripcion(),
            'puntuacion' => $comentarioReceta->getPuntuacion(),
            'complejidad' => $comentarioReceta->getComplejidad(),
            'usuario_id' => $comentarioReceta->getUsuario()->getId(),
            'receta_id' => $comentarioReceta->getReceta()->getId(),
        ];
    }

    $recetas[] = [
        'id' => $receta->getId(),
        'nombre' => $receta->getNombre(),
        'descripcion' => $receta->getDescripcion(),
        'estado' => $receta->getEstado(),
        'fecha' => $receta->getFecha()->format('d-m-Y'),
        'imagen' => $imagenesReceta,
        'ingredientes' => $receta->getIngredientes(),
        'tiempo' => $receta->getTiempo(),
        'pasos' => $receta->getPasos(),
        'comentarios' => $comentariosReceta,
        'numeroPersonas' => $receta->getNumeroPersonas(),
        'complejidad' => $receta->getComplejidad(),
    ];
}
    
        // Obteniendo las listas del usuario
        $listas = [];
        foreach ($usuario->getListas() as $lista) {
            $listas[] = [
                'id' => $lista->getId(),
                'usuario_id' => $lista->getUsuario()->getId(),
                'nombre' => $lista->getNombre(),
                'descripcion' => $lista->getDescripcion(),
                'imagen' => $lista->getImagen(),
            ];
        }
    
        // Obteniendo usuarios relacionados
        $usuarios = [];
        foreach ($usuario->getUsuarios() as $relatedUsuario) {
            $recetas = [];
            foreach ($relatedUsuario->getRecetas() as $receta) {
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
                    'categorias' => $categorias,
                    'comentarios' => $comentarios,
                    'descripcion' => $receta->getDescripcion(),
                    'estado' => $receta->getEstado(),
                    'fecha' => $receta->getFecha()->format('d-m-Y'),
                    'imagen' => $imagenes, 
                    'usuario' => $usuario,
                    'tiempo' => $receta->getTiempo(),
            ];
        }
            $usuarios[] = [
                'id' => $relatedUsuario->getId(),
                'email' => $relatedUsuario->getEmail(),
                'nombre' => $relatedUsuario->getNombre(),
                'apellidos' => $relatedUsuario->getApellidos(),
                'nombreUsuario' => $relatedUsuario->getNombreUsuario(),
                'provincia' => $relatedUsuario->getProvincia(),
                'roles' => $relatedUsuario->getRoles(),
                'imagen' => $relatedUsuario->getImagen(),
                'recetas' => $recetas
            ];
        }
    
        // Creando el arreglo final de datos del usuario
        $data = [
            'id' => $usuario->getId(),
            'email' => $usuario->getEmail(),
            'nombre' => $usuario->getNombre(),
            'apellidos' => $usuario->getApellidos(),
            'nombreUsuario' => $usuario->getNombreUsuario(),
            'provincia' => $usuario->getProvincia(),
            'roles' => $usuario->getRoles(),
            'imagen' => $usuario->getImagen(),
            'categorias' => $categorias,
            'comentarios' => $comentarios,
            'recetas' => $recetas,
            'listas' => $listas,
            'usuarios' => $usuarios,
        ];
    
        return $this->json($data, Response::HTTP_OK);
    }
    

    //Loguear al usuario y obtener el token
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login( UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse {
        
        $body = json_decode($request->getContent(), true);
        $email = $body['email'];
        $contrasenia = $body['contrasenia'];

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy([
            'email' => $email,
        ]);

        $id = $usuario->getId();

        if ($usuario && $passwordHasher->isPasswordValid($usuario, $contrasenia)){
            $token = $JWTManager->create($usuario);
            return new JsonResponse(['token' => $token, 'id' => $id], Response::HTTP_OK );
        }

        return new JsonResponse (['message' => 'Incorrect credentials'], Response::HTTP_BAD_REQUEST);
    }

  
    #[Route('/registro', name: 'app_registro', methods: ['POST'])]
    public function registro(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $email = $body['email'];
        $nombre = $body['nombre'];
        $apellidos = $body['apellidos'];
        $nombreUsuario = $body['nombreUsuario'];
        $contrasenia = $body['contrasenia'];
        $provincia = $body['provincia'];
        $roles = $body['roles'];
        $imagen = $body['imagen'];
        $categorias = $body['categorias'];

        // Crear una nueva instancia de usuario
        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setNombre($nombre);
        $usuario->setApellidos($apellidos);
        $usuario->setNombreUsuario($nombreUsuario);
        $usuario->setProvincia($provincia);
        $usuario->setRoles($roles);
        $usuario->setImagen($imagen);
        $categoriaRepository = $entityManager->getRepository(Categoria::class);
        foreach($categorias as $categoriaData){
            $categoriaId = $categoriaData['id'];
            $categoria = $categoriaRepository->find($categoriaId);
            if ($categoria) {
                $usuario->addCategoria($categoria);
            }
        }
    
        // Codificar la contraseña
        $usuario->setPassword($passwordHasher->hashPassword($usuario, $contrasenia));

        // Guardar el usuario en la base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Usuario registrado con éxito'], Response::HTTP_CREATED);
    }

    //Obtener datos del usuario a través de su email
    #[Route('/email', name: 'app_usuario_email', methods: ['POST'])]

    public function getUsuario(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {

        $body = json_decode($request->getContent(), true);
    
            $email = $body['email'];
    
            if (!$email)
            {
                return new JsonResponse(['message' => 'Email no encontrado'], Response::HTTP_NOT_FOUND);
            }

            $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

            if (!$usuario)
            {
                return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
            }
    
            $data = [
                'id' => $usuario->getId(),
                'email' => $usuario->getEmail(),
                'nombre' => $usuario->getNombre(),
                'apellidos' => $usuario->getApellidos(),
                'nombreUsuario' => $usuario->getNombreUsuario(),
                'provincia' => $usuario->getProvincia(),
                'roles' => $usuario->getRoles(),
                'imagen' => $usuario->getImagen(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

        //MODIFICAR  NO SE OBTIENEN TODOS LOS DATOS DEL USUARIO     
        #[Route('/', name: 'app_get_usuario', methods: ['GET'])]
        public function getUsuarios(EntityManagerInterface $entityManager, Request $request): JsonResponse {
            $usuarios = $entityManager->getRepository(Usuario::class)->findAll();
            $data = [];
    
            foreach ($usuarios as $usuario) {
                $data[] = [
                    'id' => $usuario->getId(),
                    'email' => $usuario->getEmail(),
                    'nombre' => $usuario->getNombre(),
                    'apellidos' => $usuario->getApellidos(),
                    'nombreUsuario' => $usuario->getNombreUsuario(),
                    'provincia' => $usuario->getProvincia(),
                    'roles' => $usuario->getRoles(),
                    'imagen' => $usuario->getImagen(),
                ];
            }
    
            return new JsonResponse($data, Response::HTTP_OK);
        }

        //Obtener a través de las categorías que sigue un usuario todas las recetas de esas categorías
        #[Route('/categorias_receta_usuario/{id}', name: 'app_usuario_id_categoria_receta', methods: ['GET'])]
        public function getUsuarioIdCategoriaReceta(UsuarioRepository $usuarioRepository, RecetaRepository $recetaRepository, string $id): JsonResponse
        {
            $usuario = $usuarioRepository->find($id);

        $recetas = [];
        foreach ($usuario->getCategorias() as $categoria) {
            foreach ($categoria->getRecetas() as $receta) {
                
            $categorias = [];
            foreach ($receta->getCategorias() as $categoria) {
                $categorias[] = [
                    'id' => $categoria->getId(),
                    'nombre' => $categoria->getNombre(),
                    'estado' => $categoria->getEstado(),
                    'imagen' => $categoria->getImagen(),

                ];
            }
    
            $comentarios = [];
            foreach ($receta->getComentarios() as $comentario) {
                $comentarios[] = [
                'id' => $comentario->getId(),
                'usuario_id' => $comentario->getUsuario()->getId(),
                'receta_id' => $comentario->getReceta()->getId(),
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
               
            $usuario = [
                'id' => $receta->getUsuario()->getId(),
                'email' => $receta->getUsuario()->getEmail(),
                'nombre' => $receta->getUsuario()->getNombre(),
                'nombreUsuario' => $receta->getUsuario()->getNombreUsuario(),
                // Agrega aquí más campos según sea necesario
            ];
    
                $recetas[] = [
                    'id' => $receta->getId(),
                    'nombre' => $receta->getNombre(),
                    'categorias' => $categorias,
                    'comentarios' => $comentarios,
                    'descripcion' => $receta->getDescripcion(),
                    'estado' => $receta->getEstado(),
                    'fecha' => $receta->getFecha()->format('d-m-Y'),
                    'imagen' => $imagenes, 
                    'ingrediente' => $ingredientes, 
                    'usuario' => $usuario,
                    'tiempo' => $receta->getTiempo(),
                    'paso' => $pasos
                    ];
            }
        }
        

            return new JsonResponse($recetas, Response::HTTP_OK);
        }

}