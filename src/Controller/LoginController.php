<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsuarioRepository;

class LoginController extends AbstractController
{
    private $JWTManager;

    public function __construct(JWTTokenManagerInterface $JWTManager)
    {
        $this->JWTManager = $JWTManager;
    }


    #[Route("/api/login", name:"app_login", methods:["POST"])]
    public function login(Request $request, UsuarioRepository $usuarioRepository)
{
    $data = json_decode($request->getContent(), true);

    var_dump($data);
    if (!isset($data['email']) || !isset($data['contrasenia'])) {

        return $this->json(['message' => 'Email and password are required.'], 400);
    }
    
    $user = $usuarioRepository->findOneBy(['email' => $data['email']]);
    var_dump($user);

    if (is_null($user) || !password_verify($data['contrasenia'], $user->getPassword())) {
        return $this->json(['message' => 'Email or password is wrong.'], 400);
    }

    $token = $this->JWTManager->create($user);

    return $this->json(['token' => $token]);

    }
}