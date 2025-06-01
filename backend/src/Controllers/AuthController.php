<?php
namespace Controllers;

use Helpers\Session; 
use Services\AuthService; 
use Repositories\UserRepository;

class AuthController {
    private AuthService $authService;

    /**
     * Constructor de AuthController.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(\mysqli $db){ 
        $this->authService = new AuthService(new UserRepository($db)); 
    }
        
    /**
     * Verificar si el usuario está logueado.
     * @return boolean
     */
    public static function isLoggedIn(): bool{ 
        header('Content-Type: application/json'); 
        if($isLogged = Session::get('loggedin') === true){
            echo json_encode(['ok' => $isLogged, 'username' => Session::get('username')]); 
            return true;
        } 
        return false;
    }

    /**
     * Obtener el cuerpo de la solicitud.
     * @return array Datos del cuerpo de la solicitud.
     */
    private function getBody(): array {
        return $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
    }

    /**
     * Registrar un nuevo usuario.
     * @return void
     */
    public function register(){ 

        if($this -> isLoggedIn() == false){

            header('Content-Type: application/json');
            
            $body = $this->getBody();  
            if(!isset($body['username'], $body['password'], $body['email'], $body['bornDate']) ||
            empty($body['username']) || empty($body['password']) ||
            empty($body['email']) || empty($body['bornDate'])) {
                echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
                return;
            }

            $newUser = [
                'username' => $body['username'],
                'password' => $body['password'],
                'email' => $body['email'],          
                'born_date' => $body['bornDate']      
            ];  
            $result = $this->authService->register($newUser); 
            if($result['ok']){
                Session::set('username',$newUser['username']);
                Session::set('loggedin',true);
            } 
            echo json_encode($result);
        }

    }

    /**
     * Iniciar sesión de un usuario.
     * @return void
     */
    public function login(){ 

        if($this -> isLoggedIn() == false){

            $body = $this->getBody();
            if(!isset($body['username'], $body['password']) ||
            empty($body['username']) || empty($body['password'])) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
                return;
            }
            
            header('Content-Type: application/json'); 
            $result=$this->authService->login($body['username'],$body['password']); 
            if($result['ok']){
                Session::set('username',$body['username']);
                Session::set('loggedin',true);
            }
            echo json_encode($result);
        }
    }    

    /**
     * Cerrar sesión del usuario.
     * @return void
     */
    public function logout(){ 
        Session::clear(); 
        echo json_encode(['ok'=>true]); 
    }
}