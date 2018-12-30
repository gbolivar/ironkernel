<?php
namespace IRON\Core\Store;

interface InterfCache 
{
        /**
         * Permite extraer los valores que se mantienen almacenado en cache
         * @param string $key, valor clave que esta almacenada
         * @return string valor de datos solicitado
         */
        public static function get($key);


        /**
         * Permite eliminar los valores almacenados en cache pasando la clave de la variable
         * @param string $key 
         * @return boolean
         */
        public static function rm($key);


        /**
	     * Description
	     * @param string $key, valor clave para almacenar los datos 
	     * @param string $Contenido del valor clave  
	     * @return boolean
	     */
        public static function set($key, $content);

}
