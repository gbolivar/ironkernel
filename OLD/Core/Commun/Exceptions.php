<?php
namespace IRON\Core\Commun;
use IRON\Core\Console\PreInterprete;

/**
 * Clase encargada de gestionar todas las Exceptions del sistema con el objetivo de implementar
 * las acciones de errores pertenecientes a las fallas del sistema y e
 * @author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 26/07/2017
 * @note http://php.net/manual/es/class.exception.php
 * @version: 0.2
 */

class Exceptions extends \Error implements \Throwable
{
    use SendMail;
    /**
     * Se encarga de leer los mensajes de exepciones
     * @param string $index indice del grupo de mensaje
     * @param string $subIndex sub indice del mensaje
     * @param array $obj, arreglo con los datos que pasa los indice de los tab del mensaje
     * @return string
     */
        static public function getMsjException($index, $subIndex, $obj = array())
        {
            $response=PreInterprete::getConfigMaster('exepciones',$index);
            $msj = $response->$subIndex;
            $search = (bool)preg_match('/[[a-z0-9]]/',$msj);
            if($search){
                $text = explode('[',$msj);
                $datos = str_replace(']',' ', $text[1]);
                $files = PreInterprete::getDirDoc().trim($datos).'.html';
                $htm = file_get_contents($files);
                $msj = $text[0]."<br>".$htm;
            }
            //$senMej=new Exceptions();
            //$senMej->sendMailException('mensaje');
            return All::mergeTaps($msj, $obj);
        }

        public function sendMailException($msj){
            echo  $this->send($msj);
        }
}

?>
