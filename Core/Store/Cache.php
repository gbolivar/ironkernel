<?php
namespace IRON\Core\Store;
use IRON\Core\Commun\All;
/**
 * Clase encargada de gestionar el cache del sistema y de cualquier elemento que se desee pasar por cache
 * @author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 31/07/2017
 * @version: 3.1
 */
Class Cache
{
    static $cacheDir;// = 'cache';
    static $expiryInterval;// = 2592000; //30*24*60*60;
    static $files;

        public  function __construct() {
            self::$cacheDir = All::DIR_SRC .ucfirst(APP).All::APP_CACHE.DIRECTORY_SEPARATOR.'System';
            //All::pp(self::$cacheDir);
            self::$expiryInterval = 25920000;
        }
 
        public static function setCacheDir($val) {  self::$cacheDir = $val; }
        public static function setExpiryInterval($val) {  self::$expiryInterval = $val; }
 
        public static function exists($key)
        {
            $files=self::pathFiles($key);
                $filename_cache = $files['filename_cache'];
                $filename_info = $files['filename_info']; //Store info
 
                if (file_exists($filename_cache) && file_exists($filename_info))
                {
                        $cache_time = file_get_contents ($filename_info) + (int)self::$expiryInterval; //Last update time of the cache file
                        $time = time(); //Current Time
 
                        $expiry_time = (int)$time; //Expiry time for the cache
 
                        if ((int)$cache_time >= (int)$expiry_time) //Compare last updated and current time
                        {
                                return true;
                        }
                }
 
                return false;
        }
 
        /**
         * Permite extraer los valores que se mantienen almacenado en cache
         * @param string $key, valor clave que esta almacenada
         * @return string valor de datos solicitado
         */
        public static function get($key)
        {
                $files=self::pathFiles($key);
                $filename_cache = $files['filename_cache'];
                $filename_info = $files['filename_info']; //Store info

 
                if (file_exists($filename_cache) && file_exists($filename_info))
                {
                        $cache_time = file_get_contents ($filename_info) + (int)self::$expiryInterval; //Last update time of the cache file
                        $time = time(); //Current Time
 
                        $expiry_time = (int)$time; //Expiry time for the cache
 
                        if ((int)$cache_time >= (int)$expiry_time) //Compare last updated and current time
                        {
                            $cont = file_get_contents ($filename_cache);   //Get contents from file
                            return base64_decode($cont); 
                        }
                }
 
                return null;
        }

        /**
         * Permite eliminar los valores almacenados en cache pasando la clave de la variable
         * @param string $key 
         * @return boolean
         */
        public static function rm($key)
        {
               
                $files=(object)self::pathFiles($key);
                $filename_cache = $files->filename_cache;
                $filename_info = $files->filename_info; //Store info
 
                if (file_exists($filename_cache) && file_exists($filename_info))
                {
                        $cache_time = file_get_contents ($filename_info) + (int)self::$expiryInterval; //Last update time of the cache file
                        $time = time(); //Current Time
 
                        $expiry_time = (int)$time; //Expiry time for the cache
 
                        if ((int)$cache_time >= (int)$expiry_time) //Compare last updated and current time
                        {
                                unlink($filename_cache);
                                unlink($filename_info);
                                
                               return true;   //Get contents from file
                        }
                }
 
                return null;
        }
 
    /**
     * Description
     * @param string $key, valor clave para almacenar los datos 
     * @param string $Contenido del valor clave  
     * @return boolean
     */
        public static function set($key, $content)
        {
                $time = time(); //Current Time

                if (! file_exists(self::$cacheDir))
                        mkdir(self::$cacheDir);
                            
                $files=self::pathFiles($key);
                $filename_cache = $files['filename_cache'];
                $filename_info = $files['filename_info']; //Store info
 
                file_put_contents ($filename_cache ,  base64_encode($content)); // save the content
                file_put_contents ($filename_info , $time); // save the time of last cache update
                return true;
        }
        /**
         * Encargadad de extraer la ruta donde se encuentra los archivos del cache
         * @param string $key, valor clave para crear el registro
         * @return object $file, Objeto de los archivos creados
         */
        public static function pathFiles($key){
            $files['filename_cache'] = self::$cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.cache'; //Store filename
            $files['filename_info'] = self::$cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.info'; //Store info
            return $files;
        }
 
}