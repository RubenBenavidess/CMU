<?php

namespace Services;
use Repositories\UserRepository;

class AuthService {
    
    /**
     * @param UserRepository $userRepository
     * @return void 
     */
    public function __construct(private UserRepository $userRepository){}
    
    /**
     * Registrar un nuevo usuario.
     * @param array $data Datos del usuario a registrar.
     * @return array Resultado con 'ok' y 'msg' o 'id' del usuario creado.
     */
    public function register(array $data): array {
        if($this->userRepository->findBy('username', $data['username'])) 
            return ['ok'=>false,'msg'=>'username-exists'];
        if($this->userRepository->findBy('correo', $data['email'])) 
            return ['ok'=>false,'msg'=>'email-exists'];
        $id=$this->userRepository->create($data); 
        return ['ok'=>true,'id'=>$id];
    }
    
    /**
     * Iniciar sesión de un usuario.
     * @param string $username Nombre de usuario o correo.
     * @param string $password Contraseña.
     * @return array Resultado con el usuario como array asociativo.
     */
    public function login(string $username,string $password): array {
        $user=$this->userRepository->findBy('username', $username);
        if(!$user || !password_verify($password,$user['contrasenia'])) 
            return ['ok'=>false, 'msg'=>'invalid-credentials'];
        unset($user['contrasenia']); 
        return ['ok'=>true,'user'=>$user];
    }
}