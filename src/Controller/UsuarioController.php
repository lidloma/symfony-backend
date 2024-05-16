<?php

namespace App\Controller;

use App\Entity\Usuario;
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

#[Route('/api/usuarios')]
class UsuarioController extends AbstractController
{
  
    //Loguear al usuario y obtener el token
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse {
        
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

        // Crear una nueva instancia de usuario
        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setNombre($nombre);
        $usuario->setApellidos($apellidos);
        $usuario->setNombreUsuario($nombreUsuario);
        $usuario->setProvincia($provincia);
        $usuario->setRoles($roles);
        $usuario->setImagen($imagen);

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
        #[Route('/categorias_receta_usuario/{id}', name: 'app_usuario_id', methods: ['GET'])]
        public function getUsuarioId(UsuarioRepository $usuarioRepository, RecetaRepository $recetaRepository, string $id): JsonResponse
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
                    'usuario' => $receta->getUsuario()->getNombreUsuario(),
                    'tiempo' => $receta->getTiempo(),
                    'paso' => $pasos
                    ];
            }
        }
        

            return new JsonResponse($recetas, Response::HTTP_OK);
        }

}