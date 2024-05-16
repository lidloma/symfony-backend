<?php

namespace App\Controller;

use App\Entity\Usuario;
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
    public function login(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse
    {

        $body = json_decode($request->getContent(), true);

        $email = $body['email'];
        $contrasenia = $body['contrasenia'];

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy([
            'email' => $email,
        ]);

        if ($usuario && $passwordHasher->isPasswordValid($usuario, $contrasenia))
        {
            $token = $JWTManager->create($usuario);
            return new JsonResponse(['token' => $token], Response::HTTP_OK);
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




        // #[Route('/api/usuario/usuario_categorias_recetas', name: 'app_get_usuario', methods: ['POST'])]
        // public function obtenerRecetasCategoriasUsuario(Request $request, EntityManagerInterface $entityManager): JsonResponse{
        //     $body = json_decode($request->getContent(), true);

        //     $email = $body['email'];
    
        //     if (!$email)
        //     {
        //         return new JsonResponse(['message' => 'Email no proporcionado'], Response::HTTP_BAD_REQUEST);
        //     }
    
        //     $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
    
        //     if (!$usuario)
        //     {
        //         return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        //     }
    
        //     $data = [
        //         'id' => $usuario->getId(),
        //         'email' => $usuario->getEmail(),
        //         'nombre' => $usuario->getNombre(),
        //         'apellidos' => $usuario->getApellidos(),
        //         'nombreUsuario' => $usuario->getNombreUsuario(),
        //         'provincia' => $usuario->getProvincia(),
        //         'roles' => $usuario->getRoles(),
        //         'imagen' => $usuario->getImagen(),
        //     ];
    
        //     $categorias = $usuario->getCategorias();
        //     $recetas = [];
    
        //     foreach ($categorias as $categoria) {
        //         foreach ($categoria->getRecetas() as $receta) {
        //             $recetas[] = [
        //                 'id' => $receta->getId(),
        //                 'nombre' => $receta->getNombre(),
        //                 'ingredientes' => $receta->getIngredientes(),
        //                 'pasos' => $receta->getPasos(),
        //                 'listas' => $receta->getListas(),   
        //                 'imagenes' => $receta->getImagenes(),
        //                 'categorias' => $receta->getCategorias(),];
        //         }
        //     }
    
        //     $data['recetas'] = $recetas;
    
        //     return new JsonResponse($data, Response::HTTP_OK);
    

        // }
       

// //Obtener datos del usuario a través de su email
// #[Route('/email', name: 'app_usuario_email', methods: ['POST'])]

// public function getUsuarioPrueba(string $email, UsuarioRepository $usuarioRepository): JsonResponse {
//     $usuario = $usuarioRepository->find($email);

//     if (!$usuario) {
//         return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
//     }
    
//     //EJEMPLO DE COMO OBTENER LOS DATOS DE UNA RECETA 
//     // $pasos = [];
//     //     foreach ($receta->getPasos() as $paso) {
//     //         $pasos[] = [
//     //             'id' => $paso->getId(),
//     //             'descripcion' => $paso->getDescripcion(),
//     //             'imagen' => $paso->getImagen(),
//     //             'numero' => $paso->getNumero(),
//     //         ];
//     //     }

//     $data = [
//         'id' => $usuario->getId(),
//         'email' => $usuario->getEmail(),
//         'nombre' => $usuario->getNombre(),
//         'apellidos' => $usuario->getApellidos(),
//         'nombreUsuario' => $usuario->getNombreUsuario(),
//         'provincia' => $usuario->getProvincia(),
//         'roles' => $usuario->getRoles(),
//         'imagen' => $usuario->getImagen(),
//     ];

//     return new JsonResponse($data, Response::HTTP_OK);
    
//     }




}