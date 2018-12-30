<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\{All};
use IRON\Core\Load\Configuration;

/**
 * Permite integrar un conjunto de funcionalidades de la consola pero en las aplicaciones
 * @Author: Ing. Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 09/08/2017
 * @updated Date: 22/12/2017
 * @version: 5.0.5
 */


class App
{
        public $pathapp;
        public $msj;
        public $active;
        public $app;
        public $ruta;
        // Constante de la clase
        const SUBITEM = __CLASS__;

    /**
     * App constructor.
     */
    public function __construct()
        {
                $this->pathapp = All::DIR_SRC;
                $this->active = All::onlyClassActive(App::SUBITEM);
                return $this;
        }
        /**
         * Methodo encargado de generar la estructura de las aplicacion dentro del sistema
         * @param string $name nombre de la aplicacion que se desea crear en el momento
         * @return string mensaje de respuesta
         */
        public function createStructura($name)
        {
                $app = All::upperCase($name);
                $active = All::onlyClassActive(App::SUBITEM);
                $ruta = $this->pathapp.DIRECTORY_SEPARATOR.$app;
                $dir = All::mkddir($ruta);
                // Verificar si creo la aplicacion
                if($dir){
                        self::createDirCache($ruta);
                        self::createDirRouter($ruta);
                        self::createFileReadRouter($ruta,$app);
                        self::createFileRouter($ruta,$app);
                        self::createDirController($ruta);
                        self::createFileController($ruta,$app);
                        self::createFileReadController($ruta,$app);
                        self::createDirModelo($ruta);
                        self::createDirView($ruta);
                        self::createFileViewHome($ruta,$app);
                        self::createNewConfigItemApp($app);
                        $msj=PreInterprete::getMsjConsoleInterno($this->active,'app-create');
                }else{
                        $msj=PreInterprete::getMsjConsoleInterno($this->active,'app-existe');
                }
                 $msj=All::mergeTaps($msj,array('name'=>$name));
                return $msj;
        }

        /**
         * Pemite crear comandos personalizados en la aplicacion indicada por parametros
         * @param String $apps, Aplicacion que se desea efectuar el comando personalizado
         * @param String $command, Comando el cual deseamos Crear
         * @return Void
         */
        public function createCommands($apps, $comand)
        {
            $app = All::upperCase($apps);
            $existe = self::existsApp($app);
            if($existe){
                $ruta = $this->pathapp.$app;
                self::createDirCommand($ruta);
                self::createFileCommands($app,$ruta,$comand);

                $msj=PreInterprete::getMsjConsoleInterno('Commands','command-create');
                $msj=All::mergeTaps($msj,array('name'=>$comand,'apps'=>$app));
                return $msj;
            }
        }
        /**
         * Perite crear el directorio donde se almacenara los command de la aplicacion creada
         * @return boolean
         */
        private function createDirCommand($ruta)
        {
            All::mkddir($ruta.All::APP_COMMAND);
        }

        /**
         * Perite crear el directorio donde se almacenara el cache de la aplicacion creada
         * @return boolean
         */
        private function createDirCache($ruta)
        {
            All::mkddir($ruta.All::APP_CACHE);
        }

        /**
         * Permite crear el directorio donde se almacenara los controladores de la aplicacion
         * @return boolean
         */
        private function createDirController($ruta)
        {
            All::mkddir($ruta.All::APP_CONTR);
        }

        /**
         * Permite crear el directorio del model de la aplicacion que se esta creando
         * @return boolean
         */
        private function createDirModelo($ruta)
        {
            All::mkddir($ruta.All::APP_MODEL);
        }

        /**
         * Permote crear el direcrorio donde se almacenaran las vista de la aplicacion
         * @return boolean
         */
        private function createDirView($ruta)
        {
            All::mkddir($ruta.All::APP_VIEWS);
            All::mkddir($ruta.All::APP_VHOME);
        }

        /**
         * Permite crear el directorio del model de la aplicacion que se esta creando
         * @return boolean
         */
        private function createDirRouter($ruta)
        {
            All::mkddir($ruta.All::APP_ROUTE);
        }

        /**
         * Permite gestionar las clases model del sistema en la aplicacion
         * @param string $app, Aplicacion que deberia estar creada donde se montara el model
         * @param string $modelo, Nombre del model que se generá en el sistema
         * @return string $msj
         */
        public function createStructuraFileModel($app, $modelo)
        {
            $this->app = All::upperCase($app);
            $ruta = $this->pathapp.$app.All::APP_MODEL;

            // Permite valirar si existe la app donde va el modelo
            if (!file_exists($ruta)) {
                die(sprintf('The application "%s" does not exist.', $this->app));
            }else{
                $modelo = All::upperCase($modelo);
                $ruta =  $ruta.DIRECTORY_SEPARATOR.$modelo.'.php';
                if (file_exists($ruta)) {
                    $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:model-existe');
                }else{
                    // Crear elementos
                    self::createFileModel($app,$modelo);
                    $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:model-create');
                }
                $msj=All::mergeTaps($msj,array('app'=>$this->app,'modelo'=>$modelo));
            }
            return $msj;
        }

    /**
     * Permite gestionar las clases controller del sistema en la aplicacion
     * @param string $app, Aplicacion que deberia estar creada donde se montara el model
     * @param string $controller, Nombre del controller que se generá en el sistema
     * @return string $msj
     */
    public function createStructuraFileController($app, $controller)
    {
        $this->app = All::upperCase($app);
        $ruta = $this->pathapp.$app.All::APP_CONTR;

        // Permite valirar si existe la app donde va el controller
        if (!file_exists($ruta)) {
            die(sprintf('The application "%s" does not exist.', $this->app));
        }else{
            $controller = All::upperCase($controller);
            $ruta =  $ruta.DIRECTORY_SEPARATOR.$controller.'Controller.php';
            if (file_exists($ruta)) {
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:controller-existe');
            }else{
                // Crear elementos
                //createFileReadController($ruta, $app , $controller = 'Home')
                $ruta = $this->pathapp.$app;
                self::createFileReadController($ruta,$app,$controller);
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:controller-create');
            }
            $msj=All::mergeTaps($msj,array('app'=>$this->app,'controller'=>$controller));
        }
        return $msj;
    }

        /**
         * Permite listar las aplicaciones que se encuentran creadas en e sistema
         * @return string $item
         */
        public function showApps()
        {
            $list = self::showAppsList();
            $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:list');
            $item = array();
            
            if(count($list)==1){
                $item=All::mergeTaps($msj,array('name'=>end($list)));
            }else{
                foreach ($list as $value) {

                   $tmp=All::mergeTaps($msj,array('name'=>$value));
                   $item[] =$tmp;
                }
            }
            return $item;
        }

        /**
         * Permite visuaizar un listado de las aplicaciones existente dentro del sistema
         * @return Array $list, listado de aplicaciones disponible
         */
        public function showAppsList()
        {
            $tmp = $this->pathapp;
            $list = array_diff(scandir($tmp), array('..', '.'));
            return $list;
        }

        /**
         * Permite verificar si existe o no una aplicacion
         * @param String $app
         * @return Bool $tmp;
         */
        public function existsApp(String $app)
        {
            $app = All::upperCase($app);
            $appList = self::showAppsList();
            $tmp = array_search($app,$appList);
            $val = (isset($tmp))?true:false;
            return $val;

        }

        /**
         * Permite crear el el archivo encargado de procesar el router
         * @param string $ruta, ruta donde esta el xml
         * @param string $app, aplicacion que levanta los datos
         */
        private function createFileReadRouter(string $ruta, string $app)
        {
                
            $ar = fopen($ruta.All::APP_ROUTE.DIRECTORY_SEPARATOR.$app."Configuration.php", "w+") or die("Problemas en la creaci&oacute;n del router del apps " . $app);
            // Inicio la escritura en el activo
            fputs($ar, '<?php'.PHP_EOL);
            fputs($ar, 'namespace APP\\'.$app.'\\Router;'.PHP_EOL);
            fputs($ar, 'use IRON\\Core\\Router\\Route;'.PHP_EOL);
            fputs($ar, '/**'.PHP_EOL);
            fputs($ar, ' * Generado por el generador de codigo de router de '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
            fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
            fputs($ar, ' * @version: 1.0'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL);
            fputs($ar, 'class '.$app.'Configuration'.PHP_EOL);
            fputs($ar, '{'.PHP_EOL);
            fputs($ar, '    public function initApp($application,$folder)'.PHP_EOL);
            fputs($ar, "    {".PHP_EOL);
            fputs($ar, '      $config_file = $folder.\'Router.xml\';'.PHP_EOL);
            fputs($ar, '      $config = simplexml_load_file($config_file);'.PHP_EOL);
            fputs($ar, '      new Route($application,$config);'.PHP_EOL);
            fputs($ar, '    }'.PHP_EOL);
            fputs($ar, '}'.PHP_EOL);
            fputs($ar, '?>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);
        }

        /**
         * Permite crear otros item nuevos si no existen dentro del archivo.ini
         */
        private function createNewConfigItemApp(String $apps)
        {
            $temp = All::parserObject(Configuration::fileConfigApp());
            $fileIni = parse_ini_file($temp->app, true);
            //All::pp($fileIni);
            if(@is_null($fileIni[$apps])) {
                $file = fopen($temp->app, "a") or die("Problemas en la creaci&oacute;n de nueva configuracion de app.ini del apps " . $apps);
                fwrite($file, "[$apps]" . PHP_EOL);
                fwrite($file, "    urlComp = 'http://localhost/" . All::low($apps) . ".php/';" . PHP_EOL);
                fwrite($file, "    urlWebs = 'http://localhost/';" . PHP_EOL);
                fwrite($file, "    urlAute = 'http://localhost/login'" . PHP_EOL);
                fwrite($file, "    urlLock = 'http://localhost/" . All::low($apps) . ".php/lockscreen'" . PHP_EOL);
                fwrite($file, "    dir_theme = '".All::low($apps)."'" . PHP_EOL);
                fwrite($file, "    srcImg  = '/IRON/img/'" . PHP_EOL);
                fwrite($file, "    srcJs   = '/IRON/js/'" . PHP_EOL);
                fwrite($file, "    srcCss  = '/IRON/css/'" . PHP_EOL);
                fwrite($file, PHP_EOL);
                fclose($file);
            }else{
                return true;
            }
        }

        /**
         * Permite Crear el archivo de configuración de la base de datos dinamicamente
         * @param String $label, Equiqueta unica de conexion
         * @param String $driver, Driver de conexion
         * @param String $host, Host donde se conecta el server
         * @param String $db, Base de datos que usara la conexion
         * @param String $user, Usuario de la conexion a base de datos
         * @param String $pass, Clave de la conexion de base de dato
         * @return Bool true
         */
        public static function createNewConexionItemApp(String $label, String $driver, String $host, String $dataBase, String $user, String $pass): bool
        {
            $temp = Configuration::fileConfigApp();
            $temp = All::parseRutaAbsolut($temp);
            $file = fopen($temp->db, "a") or die("Problemas en la creaci&oacute;n de nueva configuracion de database.ini del label " . $label);
            fwrite($file, "[$label]" . PHP_EOL);
            fwrite($file, "    motor = '$driver'" . PHP_EOL);
            fwrite($file, "    host = '$host'" . PHP_EOL);
            fwrite($file, "    port = '1433'" . PHP_EOL);
            fwrite($file, "    db = '$dataBase'" . PHP_EOL);
            fwrite($file, "    user = '$user'" . PHP_EOL);
            fwrite($file, "    pass  = '$pass'" . PHP_EOL);
            fwrite($file, "    encoding = 'UTF-8' " . PHP_EOL);
            fwrite($file, PHP_EOL);
            fclose($file);
            return true;
        }


        private function createFileRouter(string $ruta, string $app)
        {
            $ar = fopen($ruta.All::APP_ROUTE.DIRECTORY_SEPARATOR."Router.xml", "w+") or die("Problemas en la creaci&oacute;n del router del apps Router.xml");
            // Inicio la escritura en el activo
            fputs($ar, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
            fputs($ar, '<!-- Router configuration system-->'.PHP_EOL);
            fputs($ar, '<route>'.PHP_EOL);
            fputs($ar, '    <link>'.PHP_EOL);
            fputs($ar, '        <name>/home</name>'.PHP_EOL);
            fputs($ar, '        <controller>home</controller>'.PHP_EOL);
            fputs($ar, '        <method>runIndex</method>'.PHP_EOL);
            fputs($ar, '        <request>GET</request>'.PHP_EOL);
            fputs($ar, '    </link>'.PHP_EOL);
            fputs($ar, '</route>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);
        }


        /**
         * Permite crear el archivo controller principal base para luego ser instanciado por los demas creado
         * @param string $ruta, ruta donde esta el xml
         * @param string $app, aplicacion que levanta los datos
         * @param string $controller, controller que se creara en el momento
         */
        private function createFileController(string $ruta, string $app )
        {
            $ar = fopen($ruta.All::APP_CONTR.DIRECTORY_SEPARATOR."Controller.php", "w+") or die("Problemas en la creaci&oacute;n del controlller del apps " . $app);
            // Inicio la escritura en el activo
            fputs($ar, '<?php'.PHP_EOL);
            fputs($ar, 'namespace APP\\'.$app.'\\Controller;'.PHP_EOL);
            fputs($ar, 'use IRON\\Complements\\Template\\Plate;'.PHP_EOL);
            fputs($ar, 'use IRON\\Core\\Store\\Cache;'.PHP_EOL);
            fputs($ar, 'use IRON\\Core\\Commun\\All;'.PHP_EOL);
            fputs($ar, '/**'.PHP_EOL);
            fputs($ar, ' * Generador de codigo de Controller de '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
            fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
            fputs($ar, ' * @version: 1.0'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL);
            fputs($ar, 'class Controller extends All'.PHP_EOL);
            fputs($ar, "{".PHP_EOL);
            fputs($ar, '   public $tpl;'.PHP_EOL);
            fputs($ar, '   public $cache;'.PHP_EOL);
            fputs($ar, '   public function __construct()'.PHP_EOL);
            fputs($ar, '   {'.PHP_EOL);
            fputs($ar, '       $this->tpl = new Plate();'.PHP_EOL);
            fputs($ar, '       $this->cache = new Cache();'.PHP_EOL);
            fputs($ar, '       return $this;'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL.PHP_EOL);

            fputs($ar, '/**'.PHP_EOL);
            fputs($ar, ' * Validador de mascaras General, generador de codigo de Controller de '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @param String $vista, Nombre de la vista que se esta ejecutando'.PHP_EOL);
            fputs($ar, ' * @param Request $request, Request con los parametros enviados'.PHP_EOL);
            fputs($ar, ' * @return Bool $resultado'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL.PHP_EOL);
            fputs($ar, '  public function runValidarMascarasVista($vista,$request)'.PHP_EOL);
            fputs($ar, '  {'.PHP_EOL);
            fputs($ar, '        $resultado = true;'.PHP_EOL);
            fputs($ar, '        $tmp = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.\'..\'.DIRECTORY_SEPARATOR.\'Templates\'.DIRECTORY_SEPARATOR.\'vistas\'.DIRECTORY_SEPARATOR.\'prueba\'.DIRECTORY_SEPARATOR.$vista.DIRECTORY_SEPARATOR.\'mascaras.json\');'.PHP_EOL);
            fputs($ar, '        $data = json_decode($tmp);'.PHP_EOL);
            fputs($ar, '        $temp = (array)$request;'.PHP_EOL);
            fputs($ar, '        foreach ($data->mascaras AS $key=>$value){'.PHP_EOL);
            fputs($ar, '            $campo = $value->campo;'.PHP_EOL);
            fputs($ar, '            $patron = trim(base64_decode($value->mascaraPHP));'.PHP_EOL);
            fputs($ar, '            $contenido = $temp[$campo];'.PHP_EOL);
            fputs($ar, '            $matches = null;'.PHP_EOL);
            fputs($ar, '            $validate=preg_match("/$patron/" , $contenido, $matches);'.PHP_EOL);
            fputs($ar, '            if($validate!=1){'.PHP_EOL);
            fputs($ar, '                $dataJson[\'error\']=\'1\';'.PHP_EOL);
            fputs($ar, '                $dataJson[\'msj\'] = \'En el campo \'.$value->campo.\' \'.$value->mensaje;'.PHP_EOL);
            fputs($ar, '                $this->json($dataJson);'.PHP_EOL);
            fputs($ar, '            }'.PHP_EOL);
            fputs($ar, '            return $resultado;'.PHP_EOL);
            fputs($ar, '        }'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL);
            fputs($ar, '}'.PHP_EOL);
            fputs($ar, '?>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);
        }

        /**
         * Permite crear una plantilla archivo encargado de procesar el controller simple
         * @param string $ruta, ruta donde esta el xml
         * @param string $commands, commands que se desea ejecutar
         */
        private function createFileCommands(String $app, string $ruta, string $commands)
        {
            $ar = fopen($ruta.All::APP_COMMAND.DIRECTORY_SEPARATOR."".All::upperCase($commands)."Commands.php", "w+") or die("Problemas en la creaci&oacute;n el comando personalizados ");
            // Inicio la escritura en el activo
            fputs($ar, '<?php'.PHP_EOL);
            fputs($ar, 'namespace APP\\'.$app.'\\Commands;'.PHP_EOL);
            fputs($ar, 'use IRON\\Core\\Console;'.PHP_EOL);
            fputs($ar, 'use IRON\\Core\\Commun\\All;'.PHP_EOL);
            fputs($ar, '/**'.PHP_EOL);
            fputs($ar, ' * Generador de codigo de Commands de '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
            fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
            fputs($ar, ' * @version: 1.0'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL);
            fputs($ar, 'class '.All::upperCase($commands).'Commands'.PHP_EOL);
            fputs($ar, '{'.PHP_EOL);
            fputs($ar, '  static $nombre= \'run Commands Go.\';'.PHP_EOL);
            fputs($ar, '   public function __construct()'.PHP_EOL);
            fputs($ar, '   {}'.PHP_EOL);
            fputs($ar, '   public static function main() : void'.PHP_EOL);
            fputs($ar, '   {'.PHP_EOL);
            fputs($ar, '      die(self::$nombre);'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL);
            fputs($ar, '}'.PHP_EOL);
            fputs($ar, '?>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);

        }

        /**
         * Permite crear una plantilla archivo encargado de procesar el controller simple
         * @param string $ruta, ruta donde esta el xml
         * @param string $app, aplicacion que levanta los datos
         * @param string $controller, controller que se creara en el momento
         */
        private function createFileReadController(string $ruta, string $app , string $controller = 'Home')
        {

            $ar = fopen($ruta.All::APP_CONTR.DIRECTORY_SEPARATOR."".$controller."Controller.php", "w+") or die("Problemas en la creaci&oacute;n del controlador del apps " . $app);
            // Inicio la escritura en el activo
            fputs($ar, '<?php'.PHP_EOL);
            fputs($ar, 'namespace APP\\'.$app.'\\Controller;'.PHP_EOL);
            fputs($ar, '// use IRON\\Core\\Commun\\Security;'.PHP_EOL);
            fputs($ar, '// use APP\\Admin\\Model AS Model;'.PHP_EOL);
            fputs($ar, '/**'.PHP_EOL);
            fputs($ar, ' * Generador de codigo de Controller de '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
            fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
            fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
            fputs($ar, ' * @version: 1.0'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL);
            fputs($ar, 'class '.$controller.'Controller extends Controller'.PHP_EOL);
            fputs($ar, "{".PHP_EOL);
            fputs($ar, '   // use Security;'.PHP_EOL);
            fputs($ar, '   // public $model;'.PHP_EOL);
            fputs($ar, '   // public $session;'.PHP_EOL);
            fputs($ar, '   public function __construct()'.PHP_EOL);
            fputs($ar, '   {'.PHP_EOL);
            fputs($ar, '       parent::__construct();'.PHP_EOL);
            fputs($ar, '       // $this->session = $this->authenticated();'.PHP_EOL);
            fputs($ar, '       // $this->model = new Model\HomeModel();'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL);
            fputs($ar, '   public function runIndex($request)'.PHP_EOL);
            fputs($ar, '   {').PHP_EOL;
            fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
            fputs($ar, '     $this->tpl->add(\'nombre\',\'PRUEBA DE CONTROLLER\');'.PHP_EOL);
            fputs($ar, '     $this->tpl->renders(\'view::home/home\');'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL);
            fputs($ar, '}'.PHP_EOL);
            fputs($ar, '?>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);

        }


        /**
         * Permite generar un formato del archivo modelo dentro de la aplicacion seleccionada
         * @param string $app, Nombre de la aplicacion a la cual se genera el modelo
         * @param string $modelo, Nombre del modelo a ser generado
         */
        private function createFileModel(string $app, string $modelo)
        {
            $app = All::upperCase($app);
            $ruta = $this->pathapp.$app.All::APP_MODEL.DIRECTORY_SEPARATOR.$modelo;

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
            fputs($ar, ' * @version: 1.0'.PHP_EOL);
            fputs($ar, ' */ '.PHP_EOL);
            fputs($ar, "class ". $modelo."Model extends Base".PHP_EOL);
            fputs($ar, "{".PHP_EOL);
            fputs($ar, '   public function __construct()'.PHP_EOL);
            fputs($ar, '   {'.PHP_EOL);
            fputs($ar, '       // $this->tabla = \'nameTable\';'.PHP_EOL);
            fputs($ar, '       // $this->campoid = array(\'nameId\');'.PHP_EOL);
            fputs($ar, '       // $this->campos = array(\'campos\');'.PHP_EOL);
            fputs($ar, '       parent::__construct();'.PHP_EOL);
            fputs($ar, '   }'.PHP_EOL);
            fputs($ar, '}'.PHP_EOL);
            fputs($ar, '?>'.PHP_EOL);
            // Cierro el archivo y la escritura
            fclose($ar);
        }

        private function createFileViewHome($ruta, $app)
        {
                $ar = fopen($ruta.All::APP_VHOME.DIRECTORY_SEPARATOR."home.php", "w+") or die("Problemas en la creaci&oacute;n del view inicio.php");
                // Inicio la escritura en el activo
                fputs($ar, '<?php'.PHP_EOL);
                fputs($ar, '$this->layout(\'base\', [\'title\' => \'User Profile\']) ?>'.PHP_EOL);
                fputs($ar, '<h1>User Profile</h1>'.PHP_EOL);
                fputs($ar, ' <p>Hello, <?=$this->e($nombre)?></p>'.PHP_EOL);
                fclose($ar);
        }
}

