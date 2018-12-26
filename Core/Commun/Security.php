<?php
namespace IRON\Core\Commun;
use IRON\Core\Store\Cache;

/**
 * Clase encargada de gestionar todas las sessiones del sistema
 * @author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 26/08/2017
 * @version: 0.2
 */
trait Security
{
    public $cache;
    public function init()
    {
        session_start();
    }

    /**
     * Methos encargado de verificar que para ver el modulo tiene que estar autenticado
     */
    public function authenticated()
    {
        //die(Cache::get('urlWebs'));
        // Si no tiene nada activo enviar autenticar
        if (self::getSession('autenticado')=="NO" AND empty(self::getSession('user')) OR empty(self::getSession('autenticado'))) {
            //header('location: '.);
            Commun::redirect(Cache::get('urlAute'),200);
            die('Error, Debes verificar la configuracion del servidor 1.');
        }else{
            return true;
        }
    }
    public function setSession(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }
    // Method encargado de extraer la informacion en cache
     public function getSession(string $key)
    {
        if (isset($_SESSION[$key])) {
            $tmp=$_SESSION[$key];
        } else {
            $tmp=false;
            #return ('Session (' . $key . '), no se encuentra registrada.');
        }
        return $tmp;
    }

    // Method encargado de eliminar la informacion en cache
    function delSession($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        } else {
            return false;
        }
        return true;
    }

    // Method encargado de eliminar la informacion en cache
    public function delSessionAll()
    {
        session_unset();
        session_destroy();
        Cache::set('msjError','');
        self::authenticated();
    }

    // Method encargado de eliminar la informacion en cache
    public function getSessionAll()
    {
        return $_SESSION;
    }

    // Method encargado de mostrar mensaje de error si tiene permiso o no al acceder a un lugar Index
    public function validatePermisos($acceso, bool $returnJson=false)
    {
        if($returnJson==false AND $acceso->permiso=='NO'){
            // Proceso solo cuando se procesa una pagina normal
            die('!Uff¡ No tienes acceso a esta acción contactar al administrador del sistema y solicitar acceso. ');
        }else if($returnJson==true AND $acceso->permiso=='NO'){
            // Proceso solo cuando se procesa un solicitud por json
            $data['error']=1;
            $data['msj']='!Uff¡ No tienes acceso a esta acción contactar al administrador del sistema y solicitar acceso. ';
            All::json($data);
        }else{
            return true;
        }
    }



}
