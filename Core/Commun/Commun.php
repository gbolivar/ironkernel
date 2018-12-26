<?php
namespace IRON\Core\Commun;
use IRON\Core\Load\Configuration;
use IRON\Core\Store\Cache;
/**
 * Commun permite terner un conjunto de funcionalidades muy utiles para el sistema que
 * pueden ser usada en cualquier momento
 * @author: Gregorio Jose Bolivar Bolivar <elalconxvii@gmail.com>
 * @Creation Date: 09/7/2017
 * @Audited by: Gregorio J Bolivar B
 * @Modified Date: 20/01/2017
 * @package: Commun.php
 * @version: 4.1
 */

trait Commun
{
    public $resp;

    /**
     * Permite procesar un nombre de la clase basado en el namespace que se encuentra
     * ejemplo:IRON\Complement\Console\App, busca solo el nombre de la clase
     * @return string $name, devuelve el nombre de la clase activa.
     */
    static function onlyClassActive(string $classNamespace)
    {
        $obj = explode('\\', $classNamespace);
        $name = end($obj);
        return $name;
    }

    /**
     * Permite crear un directorios
     * @param string $ruta , ruta donde procesara la creacion del directorio
     * @return boolean
     */
    static function mkddir(string $ruta)
    {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
            $resp = true;
        } else {
            $resp = false;
        }
        return $resp;
    }

    static function now()
    {
        $fe = date('Y-m-d H:i:s:v');
        return $fe;
    }

    /**
     * Permite imprimir objetos, arreglos, y valores enviados mostrado ordenadamente y parando el proceso
     * @param array $dataArray , parametro de entrada para ser impreso
     * @return array valores
     */
    public static function pp($dataArray)
    {
        self::statusHttp(201);
        echo "<pre>";
        print_r($dataArray);
        die();
    }

    /**
     * Permite parsear un arreglo en un objeto
     * @parrent $valorArray, Valor recibido en arreglo
     * @return $returnObject, Valor retornado en objeto
     */
    public static function parserObject(Array $valorArray)
    {
        return (object)$valorArray;
    }

    /**
     * Permite poner en modo desarrollador al sistema
     */
    public static function modDevelopment($opt=true)
    {
        if($opt){
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }else{
            error_reporting(0);
            ini_set("display_errors", 0);
        }

    }

    /**
     * Permite cambiar tu texto de entrada en formato came case, ejemplo: codigoBueno
     * @param string $texto , lo que deseas cambiar de formato
     * @return string $came, texto formateado
     */
    public static function cameCase(string $texto)
    {
        $resul = str_replace(array('_'), array(' '), $texto);
        $tmp = explode(' ', $resul);
        $res = '';
        if (count($tmp) == 0) {
            $res = ucfirst($texto);
        } else {
            foreach ($tmp as $key => $value) {
                if ($key == 0) {
                    $res .= strtolower(self::sanear_string($value));
                } else {
                    $res .= ucfirst(self::sanear_string($value));
                }
            }
        }
        return $res;
    }

    /**
     * Permite cambiar tu texto de extrada en formato upper case ejemplo: CodigoBueno
     * @param string $texto , lo que deseas cambiar de formato
     * @return string $valor;
     */
    public static function upperCase(string $texto)
    {
        $resul = str_replace(array('_'), array(' '), $texto);
        $tmp = explode(' ', $resul);
        $res = '';
        if (count($tmp) == 0) {
            $res = ucfirst($texto);
        } else {
            foreach ($tmp as $key => $value) {
                $res .= ucfirst(self::sanear_string($value));
            }
        }
        return $res;
    }

    /**
     * Permite cambiar un texto de entrada en minuscula
     * @param String $texto, lo que deseas cambiar a minuscula
     * @return String $valor, cambiado a minuscula
     */
    public static function low(String $texto)
    {
        return mb_strtolower($texto);
    }


    /**
     * Permite cambiar un texto de entrada en mayuscula
     * @param String $texto, lo que deseas cambiar a minuscula
     * @return String $valor, cambiado a mayuscula
     */
    public static function upper(String $texto)
    {
        return strtoupper($texto);
    }


    /**
     * Permite cambiar un texto de entrada con un formato adaptado para las rutas del sistema
     * @param String $texto, lo que deseas cambiar a minuscula
     * @return String $valor, cambiado a entra (Hola Code => hola_code)
     */
    public  static function formatRuta(String $texto): String
    {
        /*$matches = null;
        $validate=preg_match("/\s/" , $texto, $matches);
        if($validate!=1){
            return  self::low(str_replace(" ", "_", $texto));
        }else{
            return  self::low(str_replace(" ", "_", $texto));
        }*/
        // Permitir quitar espacios en blancos para mostrar
        return  self::low(str_replace(" ", "", $texto));
    }

    /**
     * Permite formatar la cabecera http para diferentes formatos
     * @param String $type, Permite indicar los formato que deseas procesar, ejemplo: xml, json u otros
     */
    public static function headerFormat($type)
    {
        switch ($type){
            case 'json':
                header('Content-Type: application/json');
            break;
            case 'xml':
                header('Content-type:text/xml');
            break;
        }
    }

    /**
     * Permite formatear la salida en json
     * @param array $datosJson, Arreglo de los datos que quieres darle el formato esto es la fuente
     * @param integer $num, Numero de respuesta del protocolo http 200 por default
     * @return \JsonSerializable $buffer, xml del registro del grid
     */
    public static function json($datosJson, $num = 200)
    {
        self::headerFormat('json');
        self::statusHttp($num);
        //$tmp = self::utf8enc($datos);
        echo json_encode($datosJson);
        die();
    }

    /**
     * Permite formatear la salida del modelo para adaptado para el formato en xml de la grid
     * @param array $datosXml, Arreglo de resultado de la consulta a base de datos
     * @param integer $num, Numero de respuesta del protocolo http 200 por default
     * @return \XMLWriter $buffer, xml del registro del grid
     */
    public static function xmlGridList($datosXml, $num = 200)
    {
        self::headerFormat('xml');
        self::statusHttp($num);
        $tmp = self::settingListXml($datosXml);
        //$xml = new \SimpleXMLElement($tmp);
        //echo $xml->asXML();
        print $tmp;
    }

    /**
     * Permite formatear la salida del modelo para adaptado para el formato en xml de la grid
     * @param array $data, Arreglo de resultado de la consulta a base de datos
     * @return \XMLWriter $buffer, xml del registro del grid
     */
    public static function settingListXml($data)
    {

        //$xmlTmp="<?xml version=\"1.0\" encoding=\"UTF-8\">";
        $xmlTmp='<rows total_count="'.$data['totalCount'].'" pos="'.$data['posStart'].'">';
        foreach ($data['data'] AS $key =>$value){
            unset($value->row);
            unset($value->cnt);
            $xmlTmp.='<row id="'.$value->id.'">';
            unset($value->id);
            foreach ($value AS $key2 =>$value2){
                $xmlTmp.='<cell>'.$value2.'</cell>';
            }
            $xmlTmp.='</row>';
        }
        $xmlTmp.='</rows>';
        return $xmlTmp;

    }

    public static function utf8enc($array)
    {
        if (!is_array($array)) return;
        $helper = array();
        foreach ($array as $key => $value) $helper[\utf8_encode($key)] = is_array($value) ? utf8enc($value) : \utf8_encode($value);
        return $helper;
    }

    public static function utf8dec($array)
    {
        if (!is_array($array)) return;
        $helper = array();
        foreach ($array as $key => $value) $helper[\utf8_decode($key)] = is_array($value) ? utf8enc($value) : \utf8_decode($value);
        return $helper;
    }

    /**
     *
     */
    public static function compressResponse(string $html)
    {
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '[\n|\r|\n\r|\t|\0|\x0B]');
        $replace = array('>', '<', '\\1');
        return preg_replace($search, $replace, trim(trim($html)));
    }

    /**
     *
     */
    public static function sanear_string(string $string)
    {
        $string = trim($string);
        $string = str_replace(
            array('ÃƒÂ¡', 'ÃƒÂ ', 'ÃƒÂ¤', 'ÃƒÂ¢', 'Ã‚Âª', 'Ãƒï¿½', 'Ãƒâ‚¬', 'Ãƒâ€š', 'Ãƒâ€ž'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂ¡'), utf8_decode('ÃƒÂ '), utf8_decode('ÃƒÂ¤'), utf8_decode('ÃƒÂ¢'), utf8_decode('Ã‚Âª'), utf8_decode('Ãƒï¿½'), utf8_decode('Ãƒâ‚¬'), utf8_decode('Ãƒâ€š'), utf8_decode('Ãƒâ€ž')),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );
        $string = str_replace(
            array('ÃƒÂ©', 'ÃƒÂ¨', 'ÃƒÂ«', 'ÃƒÂª', 'Ãƒâ€°', 'ÃƒË†', 'ÃƒÅ ', 'Ãƒâ€¹'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂ©'), utf8_decode('ÃƒÂ¨'), utf8_decode('ÃƒÂ«'), utf8_decode('ÃƒÂª'), utf8_decode('Ãƒâ€°'), utf8_decode('ÃƒË†'), utf8_decode('ÃƒÅ '), utf8_decode('Ãƒâ€¹')),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );
        $string = str_replace(
            array('ÃƒÂ­', 'ÃƒÂ¬', 'ÃƒÂ¯', 'ÃƒÂ®', 'Ãƒï¿½', 'ÃƒÅ’', 'Ãƒï¿½', 'ÃƒÅ½'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂ­'), utf8_decode('ÃƒÂ¬'), utf8_decode('ÃƒÂ¯'), utf8_decode('ÃƒÂ®'), utf8_decode('Ãƒï¿½'), utf8_decode('ÃƒÅ’'), utf8_decode('Ãƒï¿½'), utf8_decode('ÃƒÅ½')),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );
        $string = str_replace(
            array('ÃƒÂ³', 'ÃƒÂ²', 'ÃƒÂ¶', 'ÃƒÂ´', 'Ãƒâ€œ', 'Ãƒâ€™', 'Ãƒâ€“', 'Ãƒâ€�'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂ³'), utf8_decode('ÃƒÂ²'), utf8_decode('ÃƒÂ¶'), utf8_decode('ÃƒÂ´'), utf8_decode('Ãƒâ€œ'), utf8_decode('Ãƒâ€™'), utf8_decode('Ãƒâ€“'), utf8_decode('Ãƒâ€�')),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );
        $string = str_replace(
            array('ÃƒÂº', 'ÃƒÂ¹', 'ÃƒÂ¼', 'ÃƒÂ»', 'ÃƒÅ¡', 'Ãƒâ„¢', 'Ãƒâ€º', 'ÃƒÅ“'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂº'), utf8_decode('ÃƒÂ¹'), utf8_decode('ÃƒÂ¼'), utf8_decode('ÃƒÂ»'), utf8_decode('ÃƒÅ¡'), utf8_decode('Ãƒâ„¢'), utf8_decode('Ãƒâ€º'), utf8_decode('ÃƒÅ“')),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );
        $string = str_replace(
            array('ÃƒÂ±', 'Ãƒâ€˜', 'ÃƒÂ§', 'Ãƒâ€¡', '<', '>'),
            array('n', 'N', 'c', 'C', '', ''),
            $string
        );
        $string = str_replace(
            array(utf8_decode('ÃƒÂ±'), utf8_decode('Ãƒâ€˜'), utf8_decode('ÃƒÂ§'), utf8_decode('Ãƒâ€¡'), utf8_decode('Ã‚Â°')),
            array('n', 'N', 'c', 'C', ''),
            $string
        );
        $string = str_replace(
            array(utf8_decode('?'), utf8_decode('Ã‚Â¿'), utf8_decode('Ã‚Âº')),
            array('', '', ''),
            $string
        );
        $string = str_replace(
            array('Ñ', 'ñ', 'á', 'é', 'í', 'ó', 'ú','Á', 'É', 'Í', 'Ó', 'Ú'),
            array('n', 'n', 'a', 'e', 'i', 'o', 'u','A', 'E', 'I', 'O', 'U'),
            $string
        );
        $string = str_replace(
            array("\\", "Ã‚Â¨", "Ã‚Âº", "~", "Ã‚Âº",
                "#", "@", "|", "!", "\"",
                "Ã‚Â·", "$", "%", "&", "/",
                "(", ")", "?", "Ã‚Â¿", "'", "Ã‚Â¡",
                "Ã‚Â¿", "[", "^", "`", "]",
                "+", "}", "{", "Ã‚Â¨", "Ã‚Â´",
                ">", "< ", ";", ",", ":",
                "."),
            '',
            $string
        );
        $string = str_replace(" ", "-", $string);
        return $string;
    }

    /**
     * Unifica los eqiquetas con el valor pasado en el arreglo
     * @param string $string , Cadena de texto que vamos a parcear
     * @param array $options , Valores del arreglo a cambiar
     * @return string $result, cadena de texto con los datos reales
     */
    public static function mergeTaps($texto, $option)
    {
        $tmp = array();
        // Creamos las reglas en lote
        foreach ($option as $key => $b) {
            $tmp[] = '/{' . $key . '}/';
        }
        $result = preg_replace($tmp, $option, $texto);
        return $result;
    }

    /**
     * Methos encardado de eliminar recursivamente un directorio y sus archivos
     * @param string $carpeta , ruta donde esta la carpeta a eliminar
     * @author http://aprendizdealquimia.es/blog/?p=231
     */
    public function eliminarDir(string $carpeta)
    {
        foreach (glob($carpeta . "/*") as $archivos_carpeta) {
            //si es un directorio volvemos a llamar recursivamente
            if (is_dir($archivos_carpeta))
                @eliminarDir($archivos_carpeta);
            else//si es un archivo lo eliminamos
                @unlink($archivos_carpeta);
        }
        //echo ($carpeta); echo "\n";
        @rmdir($carpeta);
    }

    public static function statusHttp($num)
    {
        static $http = array(
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Error de excepcion del servidor db",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"
        );
        header($http[$num]);
    }

    /**
     * Redireccionar un elemento de una solicitud web personalizando la cabecera
     * @param string $url , Enlace donde quieres redireccionar
     * @param integer $num , numero de proceso
     */

    public static function redirect(string $url, $num = 200)
    {
        if(!empty($url)){
            self::statusHttp($num);
            @header("Location: $url");
        }else{
            die('enlace enviado vacio');
        }
    }

    /**
     * Permite hacer validaciones de campos basadao en los validadores de nativos de php
     * @param String $type
     * @param String $data
     * @return bool true o false
     */
    public static function validateRows(string $type, $data)
    {
        switch ($type) {
            case 'REQ': // Dato requ
                $retorno = ($data == '') ? FALSE : TRUE;
                break;
            case 'NUM': // Solo numericos
                $retorno = (filter_var($data, FILTER_VALIDATE_INT) === FALSE) ? FALSE : TRUE;
                break;
            case 'LET': // Solo letras
                $retorno = (filter_var($data, FILTER_VALIDATE_INT) === FALSE) ? FALSE : TRUE;
                break;
            case 'STR': // Solo String alfanumerico
                $retorno = (filter_var($data, FILTER_SANITIZE_STRING) === FALSE) ? FALSE : TRUE;
                break;
            case 'EMA': // Solo correos electrinico
                $retorno = (filter_var($data, FILTER_VALIDATE_EMAIL) === FALSE) ? FALSE : TRUE;
                break;
            case 'URL': // Solo direcciones de internet
                $retorno = (filter_var($data, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED) === FALSE) ? FALSE : TRUE;
                break;
            case 'BOO': // Identificar si el registro es booleano
                $retorno = (filter_var($data, FILTER_VALIDATE_BOOLEAN) === FALSE) ? FALSE : TRUE;
                break;
            case 'URLACT':
                $retorno = (@get_headers($data)) ? TRUE : FALSE;
                break;
            case 'IP':
                $retorno = (filter_var($data, FILTER_VALIDATE_IP));
                break;
        }
        return $retorno;
    }

    /**
     * Permite setear lasrutas absoluta de los archivo de configurcion cuando falla la hechas en las constastes
     * @param String $ruta , ruta que desea setear
     * @return Object $valor, rutas seteadas en objeto
     */
    public static function parseRutaAbsolut($ruta)
    {
        $valor = (object)str_replace('\lib\Core\Commun\..\..\..', '', $ruta);
        return $valor;
    }

    public static function deleteEndCaracter(String $texto): String
    {
        $myString = substr($texto, 0, -1);
        return $myString;  // 'number 1, number 2, number 3'
    }

    /**
     * Retornar la el nombre del directorio donde se esta ejecutando el sistema
     */
    public static function pathApps($dir)
    {
        $vista = @end(explode(DIRECTORY_SEPARATOR, dirname($dir)));
        return $vista;
    }

    public static function pathVista()
    {
        $vista = str_replace(array('/', 'Index', 'Listar', 'Create', 'Update', 'Show', 'Delete'), array('', '', '', '', '', '', ''), $_SERVER['PATH_INFO']);
        return $vista;
    }


    /**
     * Permite darle formato al a los campos proveniente de la vista para adaptarlo a los parametros necesarios para la grilla
     * @param Objeto $obj , Trae la definicion de los campos necesio de todas los valores que lleva la vista
     * @return array
     */
    /**
     * Permite darle formato al a los campos proveniente de la vista para adaptarlo a los parametros necesarios para la grilla
     * @param Objeto $obj , Trae la definicion de los campos necesio de todas los valores que lleva la vista
     * @return array
     */
    public function formatRows($obj)
    {
        $str = base64_decode($obj);
        $temp = explode('#', $str);
        $select = array();
        //$campos = array();
        foreach ($temp AS $index => $value) {
            // obtengo el segundo valor para el item
            $rest = explode('|', $value);
            $fina = str_replace('id:', '', $rest[0]);
            // Buscar elemento si hay
            $pos = strpos($fina, "[");
            if($pos !== false){
                $select[] = self::createSubSelectJoin($fina);
            }else{
                $select[] = $fina;
            }
        }
        return array('select' => $select);
    }

    /**
     * Encargado de crear una subconsulta para armar con la base de datos que muestre la relacion de as tablas
     * ejemplo (SELECT nombre FROM estado WHERE estado.id=estados_id) AS estado_id
     * @param String $valor, valor del campo formado especial
     * @return Strig $forma, Valor del subconsulta
     */
    private function createSubSelectJoin(String $valor){
        $tmp0 = str_replace(array('[',']'), array('',''), $valor);
        //  estados_id;Estado.id=>nombre
        // Campo real de la tabla origen $tmp1[0]
        $tmp1 = explode(';', $tmp0);
        // Extraer campo de la tabla destino
        $tmp2 = explode('=>', $tmp1[1]);

        // Extraer la entidad $tmp3[0]
        $tmp3 = explode('.', $tmp2[0]);
        // Quitar los elementos inecesario
        $tmp4 = str_replace(array('{','}'), array('',''), $tmp2[1]);
        //All::pp($tmp4);
        // Select resultado de la base de datos
        $tmp5 = "(SELECT ".$tmp4." FROM ".$tmp3[0]." WHERE ".$tmp2[0]."=".$tmp1[0].") AS ".$tmp1[0]."";
        return  $tmp5;
    }

    /**
     * Permite desencriptar y devolver el objeto de lo valores
     */
    public static function formatRelacio($obj)
    {
        $str = base64_decode($obj);
        $valor=explode('|',$str);
        return $valor;
    }

        /**
     * Permite ejecutar una ruta definida para el susuario donde va a redireccionar
     * @param String $rutaPerfil, Identificador del path perfil
     * @return Object $rutas Arreglo de objeto de rutas
     */
    public static function extraerRutasRedirec(String $rutaPerfil):Array
    {
        
        // Expresion regular 
        $re = '/^([A-Z]{1}[a-zA-Z]{1,}\:[a-zA-Z]{1,})$/m';

        // Verificar si hay mas de una que se divide con el simbolo de #
        $tmp = explode('#',$rutaPerfil);

        // Definir variable co arreglo
        $rutas = array();
        foreach ($tmp as $key => $value) {
            
            // Validar la expresion regular que cumple lo sifuiente: Operado:rurlComp
            preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
            if(empty($matches[0][0])){
                $log = new Logger();
                $obj = array('entidad' => '[seg_perfil]', 'perfil' => 'revisar perfil sin ruta agregada');
                $msj = All::getMsjException('Core', 'ruta-base-no-cumple',$obj);
                throw new \TypeError($msj);
            }
            // Extraer valores de la ruta real de la expresion regular
            $rutaTmp = explode(':',$matches[0][0]);

            
            //Extraer las rutas de los archivosss de configuracion del sistema
            $strFileName = (object)Configuration::fileConfigApp();

            // Verificcar si existe el archivo de configuracion solo necesito el de la app
            file_exists($strFileName->app)?$objFopen=parse_ini_file($strFileName->app,true):die(__CLASS__.__METHOD__.__LINE__);

            // Residuo de los valores de la credencial y el valor de la ruta
            $credencial = $rutaTmp[0];
            $rutaPlanea = $rutaTmp[1];

            Cache::set('credential', $credencial);
            if(empty($objFopen[$credencial])){
                $obj = array('apps' => $credencial);
                $msj = All::getMsjException('Core', 'error-index-rura',$obj);
                throw new \TypeError($msj);
            }
            $rutaRea = $objFopen[$credencial][$rutaPlanea];
            $tmpPerfil=array('valido'=>$credencial.':'.$rutaPlanea,'rutaReal'=>$rutaRea);
            $_SESSION["credential"]=$tmpPerfil; 
            $rutas[] = $rutaRea;
        }
        return $rutas;
    }


    /**
     * Funcion que devuelve un array con los valores:
     *    os => sistema operativo
     *    browser => navegador
     *    version => version del navegador
     */
    public function detect()
    {
        $browser = array("IE", "OPERA", "MOZILLA", "NETSCAPE", "FIREFOX", "SAFARI", "CHROME");
        $os = array("WIN", "MAC", "LINUX");

        # definimos unos valores por defecto para el navegador y el sistema operativo
        $info['browser'] = "OTHER";
        $info['os'] = "OTHER";

        # buscamos el navegador con su sistema operativo
        foreach ($browser as $parent) {
            $s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent);
            $f = $s + strlen($parent);
            $version = substr($_SERVER['HTTP_USER_AGENT'], $f, 15);
            $version = preg_replace('/[^0-9,.]/', '', $version);
            if ($s) {
                $info['browser'] = $parent;
                $info['version'] = $version;
            }
        }

        # obtenemos el sistema operativo
        foreach ($os as $val) {

            if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $val) !== false)
                $info['os'] = $val;
            }

        # devolvemos el array de valores
        return $info;
    }

}
?>