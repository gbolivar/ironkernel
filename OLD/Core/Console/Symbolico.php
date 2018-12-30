<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\All;
use IRON\Core\Load\Configuration;
/**
 * Permite procesar los enlaces symbolicos dentro del sistema por el terminal
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 22/08/2017
 * @version: 0.2
 */

class Symbolico
{
		public $paththeme;
		public function __construct(){
            $this->paththeme = All::DIR_THEME;
        }

        /**
         * Methodo encargado de publicar los elementos publicos del sistema JS, CSS, IMG  
         * @param string $name nombre de la aplicacion que se desea crear en el momento
         * @return string mensaje de respuesta
         */
        public function filesWebPublic($name) 
        {
        	$app = All::upperCase($name);

        	// Extraer el archivo de configuracion
			$link = Configuration::fileConfigApp();

        	$this->createSymbolicoWeb($app, $link);
        }

        public function createSymbolicoWeb($name, $link){
        	// Leer archivo de configuracion donde deben estar los parametros necesarios
			file_exists($link['app']) ? $objFopen = parse_ini_file($link['app'], true) : die("<strong>Uff:</strong> Se encontro el siguiente error:<ul><li> Clase: ".__CLASS__.'.<br> En el Method: '.__METHOD__.'.<br/> En la Linea: '.__LINE__.'<br/> El achivo: <b>' . $name.'</b>.<br>Nota: <b>Problema de ruta del Archivo no se encuentra.</b></li><ul>');

			
        	if(isset($objFopen[$name]['dir_theme'])){

        		$dir = All::DIR_THEME.''.$objFopen[$name]['dir_theme'].All::APP_TKEYS;
        		$objetivo = All::DIR_WEB.''.$objFopen[$name]['dir_theme'].DIRECTORY_SEPARATOR;

        		if ($gestor = opendir($dir)) {
				    while (false !== ($entrada = readdir($gestor))) {
				        if ($entrada != "." && $entrada != "..") {
				        	$origen  = $dir.DIRECTORY_SEPARATOR.$entrada; 
				        	$destino = $objetivo.''.$entrada;   
				        	symlink($origen, $destino);
				        	link($origen, $destino);
				        }
				    }
				    closedir($gestor);
				}
        		
        		
        	}
        
        }

}

