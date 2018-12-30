<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\All;
/**
 * Interpreta las configuraciones efectuadas por el termininal
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 22/08/2017
 * @version: 0.2
 */
class PreInterprete
{
        public $labmenu, $paser, $config;
        function __construct()
        {
                $this->labmenu = null;
                $this->paser = null;
                $this->config = null; 
        }
        /**
         * Methos encargado de extaer el objeto de configuracion de una indice pasado por parametro
         * @param string $grupo a que pertenece en la configuracion del json
         * @param string $index del json principal de la configuracion
         * @return $this->paser array de los datos 
         */
        static public function getConfigMaster($grupo,$index)
        {
            $response = self::getJsonConfig();
            $config = json_decode($response);
            if (empty($config)) {
                die('Error: Por favor revisar el parser del archivo json, presenta fallos.');
            }
            foreach ($config->$grupo as $key => $value) {
                if ($key == $index) {
                    //print_r($value); die();
                    return $value;
                }
            }
            die('No se encontro index[' . $index . '] sociado a la configuracion.');
        }

        static public function getConfigJson($index,$subIndex)
        {
           // print $index;
            $response=self::getConfigMaster($index,$subIndex);
            if(isset($response->$subIndex)){
                foreach ($response->$subIndex as $key => $value) {
                    $resp[][$key]=$value;
                }
            }else{
                $resp = $response;
            }
            return $resp;
        }

        /**
         * Se encarga de leer los mensajes de alertas enviados a los usuarios cuando se necesita notificar algo
         * @param string $index indice del grupo de mensaje
         * @param string $subIndex sub indice del mensaje
         * @return string
         */
        static public function getMsjConsoleInterno($index,$subIndex)
        {
           try{
            $response=self::getConfigMaster('mensajes',$index);
            $msj = $response->$subIndex;
           }catch (\TypeError $t){
               die($t->getMessage('MENSAJE'));
           }
           return $msj;
        }

        public function options($item)
        {
             $elemento = $this->getJsonConfig();
             $obj = json_decode($elemento);
             $valor = $obj->hornero->$item;
             self::setValor($valor);
        }

        /**
         * Permite mostrar el resultado en la consola del mensaje mediante terminal
         * @return string imprimir menu en terminal
         */
        public function showOptions()
        {
            $fwv=All::FW.' - '.All::VERSION;
            $fwv.="\n \n";
            $item=@count($this->labmenu);
            if($item==1){
                $fwv.= $this->labmenu;

            }else{
                foreach ($this->labmenu as  $value) {
                    $fwv.= $value;
                    $fwv.="\n";
                }
            }
            die($fwv);
        }


        /**
         * Ruta del Archivo json en el cual hay muchas mensajes que debe devolver al sistema
         * @return string $response toda la informacion del json 
         */
        public static function getJsonConfig()
        {
            $response = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.All::IDM.DIRECTORY_SEPARATOR.'core.json');
            return $response;
        }

        /**
         * Ruta del Archivo json en el cual hay muchas mensajes que debe devolver al sistema
         * @return string $response toda la informacion del json
         */
        public static function getDirDoc()
        {
            $ruta = __DIR__.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.All::IDM.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR;
            return $ruta;
        }

        /**
         * Mostrar el logo en ascii
         * @see web de generacion de ASCII url('https://www.ascii-art-generator.org/es.html')
         * @return devolver el logo en ascc
         */
        public static function getLogoAscii()
        {

            $response = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'banner.txt');
            return $response;
        }

        /**
         * Setter de los valores por el terminal
         */
        public function setValor($valor)
        {
            $this->labmenu = $valor;
        }

        private function getValor()
        {
            return $this->labmenu;
        }


}