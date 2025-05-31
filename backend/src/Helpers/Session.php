<?php
namespace Helpers;
class Session {
    /**
     * Cargar o iniciar una nueva sesión para la persistencia de sesiones de usuario.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    /**
     * Almacenar o cambiar un valor en la sesión.
     * @param string $k Clave de la sesión.
     * @param mixed $v Valor a almacenar.
     */
    public static function set(string $key, $value): void { 
        $_SESSION[$key] = $value; 
    }

    /**
     * Recuperar un valor de la sesión.
     * @param string $k Clave de la sesión.
     * @param mixed $def Valor por defecto si no existe la clave.
     * @return mixed Valor almacenado o el valor por defecto.
     */
    public static function get(string $key, $def=null) { 
        return $_SESSION[$key] ?? $def; 
    }
    
    /**
     * Destruir la sesión.
     */
    public static function clear(): void { 
        session_unset(); 
        session_destroy(); 
    }
}