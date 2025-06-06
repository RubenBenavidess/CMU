<?php
namespace Controllers;

use Helpers\Session;
use Services\AuthService;
use Repositories\UserRepository;

class AuthController {
    private AuthService $authService;
    private const CONTENT_TYPE_JSON = 'Content-Type: application/json';
    /**
     * Constructor de AuthController.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(\mysqli $db) {
        $this->authService = new AuthService(new UserRepository($db));
    }
        
    /**
     * Verificar si el usuario está logueado.
     * @return bool
     */
    public static function isLoggedIn(): bool {
        return Session::get('loggedin') === true;
    }

    /**
     * Obtener información del usuario logueado.
     * @return void
     */
    public function checkLoggedIn(): void {
        header(self::CONTENT_TYPE_JSON);
        if (self::isLoggedIn()) {
            echo json_encode(['ok' => true, 'username' => Session::get('username')]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
        }
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
    public function register(): void {
        if(!self::isLoggedIn()){
            
            header(self::CONTENT_TYPE_JSON);
            
            $body = $this->getBody();
            if (!isset($body['username'], $body['password'], $body['email'], $body['bornDate']) ||
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
            if ($result['ok']) {
                Session::set('idUsuario', $result['user']['idUsuario']);
                Session::set('username', $newUser['username']);
                Session::set('loggedin', true);
            }
            echo json_encode($result);
        }else{
            header(self::CONTENT_TYPE_JSON);
            echo json_encode(['ok' => false, 'msg' => 'already-logged-in']);
        }
    }

    /**
     * Iniciar sesión de un usuario.
     * @return void
     */
    public function login(): void {
        if (!self::isLoggedIn()) {
            $body = $this->getBody();
            if (!isset($body['username'], $body['password']) ||
                empty($body['username']) || empty($body['password'])) {
                header(self::CONTENT_TYPE_JSON);
                echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
                return;
            }
            
            header(self::CONTENT_TYPE_JSON);
            $result = $this->authService->login($body['username'], $body['password']);
            if ($result['ok']) {
                Session::set('idUsuario', $result['user']['idUsuario']);
                Session::set('username', $body['username']);
                Session::set('loggedin', true);
            }
            echo json_encode($result);
        } else {
            header(self::CONTENT_TYPE_JSON);
            echo json_encode(['ok' => false, 'msg' => 'already-logged-in']);
        }
    }

    /**
     * Cerrar sesión del usuario.
     * @return void
     */
    public function logout(): void {
        header(self::CONTENT_TYPE_JSON);
        Session::clear();
        echo json_encode(['ok' => true]);
    }
}
