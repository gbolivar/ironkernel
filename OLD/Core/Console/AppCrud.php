<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\{All,SimpleXMLExtended};

/**
 * Permite integrar un conjunto de funcionalidades de la consola pero en las aplicaciones
 * @Author: Ing. Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 09/08/2017
 * @updated Date: 29/08/2017
 * @version: 5.0.4
 */


class AppCrud extends App
{
    /**
     * AppCrud constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function createStructuraFileCRUD($app,$crud)
    {
        $this->app = All::upperCase($app);
        $ruta = $this->pathapp.$app.All::APP_CONTR;

        // Permite valirar si existe la app donde va el controller
        if (!file_exists($ruta)) {
            die(sprintf('The application "%s" does not exist.', $this->app));
        }else{
            $controller = All::upperCase($crud);
            $ruta =  $ruta.DIRECTORY_SEPARATOR.$controller.'Controller.php';
            if (file_exists($ruta)) {
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-existe');
            }else{
                $ruta = $this->pathapp.$app;
                self::createFileReadControllerCRUD($ruta,$app,$controller);
                self::createNewRutaXmlCRUD($ruta,$app,$controller);
                self::createFileModelCRUD($ruta,$app,$controller);
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-creado');
            }
            $msj=All::mergeTaps($msj,array('app'=>$this->app,'controller'=>$controller));
        }
        return $msj;
    }

    /**
     * Permite crear una plantilla archivo encargado de procesar el controller simple
     * @param string $ruta, ruta donde esta el xml
     * @param string $app, aplicacion que levanta los datos
     * @param string $controller, controller que se creara en el momento
     */
    private function createFileReadControllerCRUD(string $ruta, string $app , string $controller)
    {
        $ar = fopen($ruta.All::APP_CONTR.DIRECTORY_SEPARATOR."".$controller."Controller.php", "w+") or die("Problemas en la creaci&oacute;n del controlador del apps " . $app);
        // Inicio la escritura en el activo
        fputs($ar, '<?php'.PHP_EOL);
        fputs($ar, 'namespace APP\\'.$app.'\\Controller;'.PHP_EOL);
        fputs($ar, 'use IRON\\Core\\Commun\\Security;'.PHP_EOL);
        fputs($ar, 'use APP\\Admin\\Model AS Model;'.PHP_EOL.PHP_EOL);
        fputs($ar, '/**'.PHP_EOL);
        fputs($ar, ' * Generador de codigo de Controller de '.All::FW.' '.All::VERSION.''.PHP_EOL);
        fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
        fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
        fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
        fputs($ar, ' * @version: 1.0'.PHP_EOL);
        fputs($ar, ' */ '.PHP_EOL.PHP_EOL);
        fputs($ar, 'class '.$controller.'Controller extends Controller'.PHP_EOL);
        fputs($ar, "{".PHP_EOL);
        fputs($ar, '   use Security;'.PHP_EOL);
        fputs($ar, '   public $model;'.PHP_EOL);
        fputs($ar, '   public $session;'.PHP_EOL);
        fputs($ar, '   public function __construct()'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '       parent::__construct();'.PHP_EOL);
        fputs($ar, '       $this->session = $this->authenticated();'.PHP_EOL);
        fputs($ar, '       $this->model = new Model\\'.$controller.'Model();'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Listar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: GET $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Index($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'action\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
        fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Crear registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Create($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'action\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
        fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Ver registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Show($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'action\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
        fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Eliminar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Delete($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'action\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
        fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Actualizar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Update($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'action\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
        fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL);
        fputs($ar, '}'.PHP_EOL);
        fputs($ar, '?>'.PHP_EOL);
        // Cierro el archivo y la escritura
        fclose($ar);

    }

    /**
     * Method encargado de procesar rutas asociadas al al sistema
     * @param string $ruta, ruta donde se hace el proceso donde esta la apliacion
     * @param string $app, nombre de la aplicacion
     * @param string $controller, nombre del controllador
     * @param string $name, nombre con el cual se llama al sistema
     * @param string $method, permite identificar cual es el method que debe instanciar el clase
     * @param string $request, permite identificar si el method GET o POST
     */

    private function createNewRutaXmlCRUD(string $ruta, string $app , string $controller)
    {

        $archivoXML= $ruta.All::APP_ROUTE.DIRECTORY_SEPARATOR."Router.xml";
        $router = new SimpleXMLExtended($archivoXML, null, true) or die("Problemas en la creaci&oacute;n del router del apps Router.xml");
        $router->addComentario(' Bloque de configuracion de la ruta del controller '.ucfirst($controller));
        // Listar registro
        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.strtolower($controller).'Index');
        $personaje->addChild('controller', strtolower($controller));
        $personaje->addChild('method', 'run'.$controller.'Index');
        $personaje->addChild('request', 'GET');
        //
        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.strtolower($controller).'Create');
        $personaje->addChild('controller', strtolower($controller));
        $personaje->addChild('method', 'run'.$controller.'Create');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.strtolower($controller).'Show');
        $personaje->addChild('controller', strtolower($controller));
        $personaje->addChild('method', 'run'.$controller.'Show');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.strtolower ($controller).'Delete');
        $personaje->addChild('controller', strtolower($controller));
        $personaje->addChild('method', 'run'.$controller.'Delete');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.strtolower($controller).'Update');
        $personaje->addChild('controller', strtolower($controller));
        $personaje->addChild('method', 'run'.$controller.'Update');
        $personaje->addChild('request', 'POST');
        $router->asXML($archivoXML);
        $router->formatXml($archivoXML);
    }



    /**
     * Permite generar un formato del archivo modelo dentro de la aplicacion seleccionada
     * @param string $app, Nombre de la aplicacion a la cual se genera el modelo
     * @param string $modelo, Nombre del modelo a ser generado
     */
    private function createFileModelCRUD(string $ruta, string $app, string $modelo)
    {
        $app = All::upperCase($app);
        $ruta = $ruta.All::APP_MODEL.DIRECTORY_SEPARATOR.$modelo;

        $ar = fopen($ruta."Model.php", "w+") or die("Problemas en la add del model del apps". $app);
        // Inicio la escritura en el activo
        fputs($ar, '<?php'.PHP_EOL);
        fputs($ar, 'namespace APP\\'.$app.'\\Model;'.PHP_EOL);
        fputs($ar, 'use IRON\\Complements\\Database\\Base;'.PHP_EOL);
        fputs($ar, 'use IRON\\Core\\Commun\\All;'.PHP_EOL);
        fputs($ar, '/**'.PHP_EOL);
        fputs($ar, ' * Generador de codigo del Modelo de la App '.$app.PHP_EOL);
        fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
        fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
        fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
        fputs($ar, ' * @version: 2.0'.PHP_EOL);
        fputs($ar, ' */ '.PHP_EOL.PHP_EOL);
        fputs($ar, "class ". $modelo."Model extends Base".PHP_EOL);
        fputs($ar, "{");
        fputs($ar, '   public function __construct()'.PHP_EOL);
        fputs($ar, '   {');
        fputs($ar, '       // $this->tabla = \'nameTable\';'.PHP_EOL);
        fputs($ar, '       // $this->campoid = array(\'nameId\');'.PHP_EOL);
        fputs($ar, '       // $this->campos = array(\'campos\');'.PHP_EOL);
        fputs($ar, '       parent::__construct();'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        // Permite extraer las entidades de la conexion actual desde la informacion schema
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Extraer todos los registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function get'.$modelo.'Index()'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "SELECT * FROM miTabla";'.PHP_EOL);
        fputs($ar, '     $tablas=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Crear registros nuevos de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: Array $datos'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function set'.$modelo.'Create($datos)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "INERT INTO miTabla (campo1, campo) VALUES(\'valor1\',\'valor2\')";'.PHP_EOL);
        fputs($ar, '     $tablas=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Extraer un registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: String $id'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function get'.$modelo.'Show($id)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "SELECT * FROM miTabla WHERE id=$id";'.PHP_EOL);
        fputs($ar, '     $tablas=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Eliminar registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: string $id'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function rem'.$modelo.'Delete($id)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "DELETE FROM miTabla WHERE id=$id";'.PHP_EOL);
        fputs($ar, '     $tablas=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Actualizar registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: arreglo $obj'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function set'.$modelo.'Update($obj)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "UPDATE SET campo=".$obj[\'campo\']." miTabla WHERE id=".$obj[\'id\']." ";'.PHP_EOL);
        fputs($ar, '     $tablas=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL);
        fputs($ar, '}'.PHP_EOL);
        fputs($ar, '?>'.PHP_EOL);
        // Cierro el archivo y la escritura
        fclose($ar);
    }

}

