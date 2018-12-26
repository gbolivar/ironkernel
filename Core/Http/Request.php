<?php
namespace IRON\Core\Http;
use IRON\Core\Commun\{All,Logs};
use IRON\Core\Http\SegCSRF;

/**
 * Permite contener un cunjunto de funcionalidades del protocolo http
 * @Author: Ing. Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 30/11/2017
 * @updated Date: 30/11/2017
 * @version: 0.1
 */

class Request extends SegCSRF implements RequestInterface
{
    use Logs;
    public $dataPost;
    public $dataGet;
    public function __construct()
    {
        $this->dataPost;
        $this->dataGet;
        return $this;
    }
    /**
     * Permite capturar peticiones del protocolo http del proceso GET
     * @param String $index, indece para buscar dentro del objeto $_GET
     * @return \http\get $dataGet
     */
    public function getParameter($index='')
    {
        if($_SERVER['REQUEST_METHOD']==All::METHOD_GET) {
            $obj = self::complementsRequest($index,All::METHOD_GET);
            return $obj;
        }else{
            die('Error al intenta extraer datos de un GET y estas haciendo una peticion POST');
        }

    }

    /**
     * Permite capturar peticiones del protocolo http del proceso POST
     * @param String $index, indece para buscar dentro del objeto $_POST
     * @return \http\post $dataGet
     */
    public function postParameter($index='')
    {
        if($_SERVER['REQUEST_METHOD']==All::METHOD_POST) {
            //self::isJsRequest(); desabilitado temporal
            $obj = self::complementsRequest($index, All::METHOD_POST);
            return $obj;
        }else{
            die('Error al intenta extraer datos de un POST y estas haciendo una peticion GET');
        }

    }

    /**
     * Permite unir todos los funcionamientos de captura de ddatos por POST,GET
     * @param String $index, Permite identificar el valor de entrada del indece asociado solicitado
     * @param String $method, Method recibido
     * @return Strinf $data
     */
    private function complementsRequest($index,$method){
        // Verificaoms si la solicitud esta vacio y le envia el objeto del proceso
        try {
            if (empty($index) AND $method == 'GET') {
                return (object)$_GET;
            } elseif (empty($index) AND $method == 'POST') {
                return (object)$_POST;
            } elseif (!empty($index)) {
                $data = [];
                $tmp = explode(',', $index);
                //print_r($_GET); die();
                //print_r($tmp);
                foreach ($tmp AS $key) {
                    if ($method == 'GET') {
                        if(isset($_GET[$key])){
                            $data[$key] = $_GET[$key];
                        }else{
                            $obj = array('idx' => $key);
                            $msj = All::getMsjException('Core', 'get-val-no-existe',$obj);
                            $this->logError($msj);
                            //throw new \TypeError($msj);
                            return false;
                        }
                    } else {
                        if(isset($_POST[$key])){
                            $data[$key] = $_POST[$key];
                        }else{
                            $obj = array('idx' => $key);
                            $msj = All::getMsjException('Core', 'post-val-no-existe',$obj);
                            $this->logError($msj);
                            //throw new \TypeError($msj);
                            return false;
                        }
                    }
                }
                // Validar si es un solo item o varios
                if (count($tmp) < 2) {
                    $temp = array_values($data);
                    return $temp[0];
                } else {
                    return (object)$data;
                }
            }
        }catch (\TypeError $t){
            All::statusHttp(400);
            die($t->getMessage());
        }

    }

    /** 
     *  Permite validar los elementos necesario para que las peticiones POST no sea vulnerables a un X-CSRF
     *  te permite validar peticiones de envio de formularios nativos o envios de datos por post mediante ajax
     * @return bool tue o false
     */
    protected static function isAjaxRequest():bool
    {
        $validate = false;
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
             $validate = true;
        }
        return $validate;
    }

    /** Permite validar los elementos necesario para que las peticiones POST no sea vulnerables a un X-CSRF
     *  te permite validar peticiones de envio de formularios nativos o envios de datos por post mediante ajax
     * @return bool tue o false
     */
    protected static function validateRequestSendTockenCSRF()
    {
        try{
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
                //echo "# Ejecuta si la petición es a través de AJAX por POST VALIDAR CABECERA";
                // Validar XSRF-TOKEN
                //print_r(getallheaders());
                // Run CSRF check, on getallheaders data, in exception mode, for 30 minutes, in one-time mode.
                SegCSRF::checkTokenHaders('iron_token', getallheaders(),true,60*60,false);
            }else{
                //echo "# Ejecuta si la petición NO es a través de AJAX.";
                // Run CSRF check, on POST data, in exception mode, for 30 minutes, in one-time mode.
                SegCSRF::checkTokenPOST('iron_token', $_POST,true,60*60,false);
            }
        }catch ( \TypeError $t ){
            // CSRF attack detected
            All::statusHttp(401);
            die($t->getMessage());
        }

    }

}