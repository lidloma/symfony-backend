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
    
    private function generarImagenUrl($imagen):string{
        return 'data:image/jpeg;base64,'.base64_encode(stream_get_contents($imagen));
    }

    //Obtener a través de las categorías que sigue un usuario todas las recetas de esas categorías
    #[Route('/{id}', name: 'app_usuario_id', methods: ['GET'])]
    public function getDatosUsuarioId(
        UsuarioRepository $usuarioRepository, 
        RecetaRepository $recetaRepository, 
        int $id
    ): JsonResponse {
        $usuario = $usuarioRepository->find($id);

        if (!$usuario) {
            return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obteniendo las categorías que sigue el usuario
        $categorias = [];
        foreach ($usuario->getCategorias() as $categoria) {
            $categorias[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'estado' => $categoria->getEstado(),
                'imagen' =>  $this->generarImagenUrl($categoria->getImagen()),
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
                    'imagen' => $this->generarImagenUrl($imagenReceta->getImagen())
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
                'ingredientes' => $receta->getIngredientes()->toArray(),
                'tiempo' => $receta->getTiempo(),
                'pasos' => $receta->getPasos()->toArray(),
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
                'imagen' =>  $this->generarImagenUrl($lista->getImagen()),
            ];
        }

        // Obteniendo usuarios relacionados (esto asume que tienes algún tipo de relación de usuarios, como amigos, seguidores, etc.)
        $usuarios = [];
        foreach ($usuario->getUsuarios() as $relatedUsuario) {
            $usuarios[] = [
                'id' => $relatedUsuario->getId(),
                'email' => $relatedUsuario->getEmail(),
                'nombre' => $relatedUsuario->getNombre(),
                'apellidos' => $relatedUsuario->getApellidos(),
                'nombreUsuario' => $relatedUsuario->getNombreUsuario(),
                'provincia' => $relatedUsuario->getProvincia(),
                'roles' => $relatedUsuario->getRoles(),
                'imagen' => $this->generarImagenUrl($relatedUsuario->getImagen()),

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
            'imagen' => $this->generarImagenUrl($usuario->getImagen()),
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
                'imagen' =>  $this->generarImagenUrl($usuario->getImagen()),
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
                    'imagen' => $this->generarImagenUrl($usuario->getImagen()),
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
                    'imagen' =>  $this->generarImagenUrl($categoria->getImagen()),

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
                'imagen' => $this->generarImagenUrl($imagen->getImagen())
                
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
                    'imagen' =>  $this->generarImagenUrl($paso->getImagen()),
                    'numero' => $paso->getNumero(),
                ];
            }
               
            $usuario = [
                'id' => $receta->getUsuario()->getId(),
                'email' => $receta->getUsuario()->getEmail(),
                'nombre' => $receta->getUsuario()->getNombre(),
                'nombreUsuario' => $receta->getUsuario()->getNombreUsuario(),
                'imagen' => $this->generarImagenUrl($receta->getUsuario()->getImagen())
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
                    'paso' => $pasos,
                    'numeroPersonas'=> $receta->getNumeroPersonas(),
                    'complejidad' => $receta->getComplejidad()
                    ];
            }
        }
        

            return new JsonResponse($recetas, Response::HTTP_OK);
        }


    #[Route('/{id}/seguir', name: 'app_usuario_seguir', methods: ['POST'])]
    public function seguirUsuario(UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager, int $id, Request $request): JsonResponse
    {
        // Obtener el usuario que va a seguir a otro usuario
        $body = json_decode($request->getContent(), true);
        $seguidorId = $body['seguidor_id'];

        $usuarioSeguidor = $usuarioRepository->find($seguidorId);
        $usuarioASeguir = $usuarioRepository->find($id);

        if (!$usuarioSeguidor || !$usuarioASeguir) {
            return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Agregar el usuario a la lista de usuarios seguidos
        $usuarioSeguidor->addUsuario($usuarioASeguir);

        // Guardar los cambios en la base de datos
        $entityManager->persist($usuarioSeguidor);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Usuario seguido con éxito'], Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_actualizar_usuario', methods: ['PUT'])]
    public function actualizarUsuario(int $id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            return new JsonResponse(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);

        if (isset($body['email'])) {
            $usuario->setEmail($body['email']);
        }
        if (isset($body['nombre'])) {
            $usuario->setNombre($body['nombre']);
        }
        if (isset($body['apellidos'])) {
            $usuario->setApellidos($body['apellidos']);
        }
        if (isset($body['nombreUsuario'])) {
            $usuario->setNombreUsuario($body['nombreUsuario']);
        }
        if (isset($body['provincia'])) {
            $usuario->setProvincia($body['provincia']);
        }
        if (isset($body['imagen'])) {
            $imagenBase64 = base64_decode($body['imagen']);
            $usuario->setImagen($imagenBase64);
        }

        if (isset($body['contrasenia'])) {
            $usuario->setPassword($passwordHasher->hashPassword($usuario, $body['contrasenia']));
        }

        
        $entityManager->flush();

        return new JsonResponse(['message' => 'Usuario actualizado con éxito'], Response::HTTP_OK);
    }
}


