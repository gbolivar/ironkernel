<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\{All, SimpleXMLExtended, Logs};

/**
 * Permite integrar un conjunto de funcionalidades que permite generar CRUD de forma automatica
 * @Author: Ing. Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 09/08/2017
 * @updated Date: 20/12/2017
 * @version: 6.0
 */


class AppCrudVista extends App
{
    /**
     * AppCrud constructor.
     */
    use Logs;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Encargado de controlar los eventos para generar las vistas del sistema
     * @param string $app, Nombre de la apicacion
     * @param string $crud, nombre del la Vista
     * @param string $tabla, nombre de la tabla
     * @param array $campos, arrego de los campos que tiene la la vista
     * @param array $columnsReal, arrego de los campos que tiene la vista
     * @param array $mascaras, Mascaras disponibles para validar los campos
     * @return bool true
     */
    public function createStructuraFileCRUD($app,$crud,$tabla,$campos,$columnsReal, $mascaras)
    {
       // echo 'App:'.$app.'--,Crud:'.$crud.'--,tabla:'.$tabla; //die();
        foreach ($campos AS $key=>$value)
        {
            if($key==0){
                $campos['entida'] = @$value->entidad;
                $campos['vista'] = @$value->nombre;
                $campos['vista_alias'] = @$value->nombre_alias;
            }
            if($value->restrincion=='PRI'){
                $campos['pk'] = @$value->field;
            }
            $temp[]=$value->field;
            $temp1[]=@$value->label;
            //   [hidden_form] => 1 | [hidden_list] => 1 | [relacionado] 1 | [tabla_vista] personal--personalD1 | [vista_campo] id
            $hidden_f=(empty($value->hidden_form))?0:$value->hidden_form;
            $hidden_l=(empty($value->hidden_list))?0:$value->hidden_list;
            $relacion=(empty($value->relacionado))?0:$value->relacionado;
            $vista=(empty($value->tabla_vista))?0:$value->tabla_vista;
            $campo=(empty($value->vista_campo))?0:$value->vista_campo;
            $cart_separacion=(empty($value->cart_separacion))?' ':$value->cart_separacion;
            $temp2[$value->field]=$hidden_f.'#'.$hidden_l.'#'.$relacion.'#'.$vista.'#'.$campo.'#'.$cart_separacion;
        }

        $requerido = $temp2;
        $campos['campos']= $temp;
        $campRealEnti = array();

        foreach ($columnsReal AS $key=>$value)
        {
            if($key==0){
                $campRealEnti['entida'] = $value->entidad;
                //$columnsReal['vista'] = $value->nombre;
            }
            if($value->restrincion=='PRI'){
                $campRealEnti['pk'] = $value->field;
            }else{
                $temp[]=$value->field;
            }

            //$temp1[]=$value->label;
        }

        // Permite quitar los indices repetidos
        $campRealEnti['campos'] = array_unique($temp);

        $this->app = All::upperCase($app);
        $ruta = $this->pathapp.$app.All::APP_CONTR;

        // Permite validar si existe la app donde va el controller
        if (!file_exists($ruta)) {
            die(sprintf('The application "%s" does not exist.', $this->app));
        }else{
            // Variables necesarias para poder generar la vista
            $controller = All::upperCase($crud);
            $entidad = All::upperCase($tabla);
            $rutaApp = $this->pathapp.$app;
            $temp = All::parseRutaAbsolut($rutaApp);
            $rutaPadre = $temp->scalar.All::APP_VISTA.DIRECTORY_SEPARATOR.ALL::cameCase($tabla);
            $rutaHija  = $rutaPadre.DIRECTORY_SEPARATOR.All::formatRuta($crud);
            $rutaVista = 'vistas'.'/'.ALL::cameCase($tabla).'/'.All::formatRuta($crud);
            $rutaVistaD = 'vistas'.'/'.ALL::cameCase($tabla).'/';

            // Verificar si existe el controllador de lo contrario se va a generar en el momento
            $archivoController = $rutaApp.All::APP_CONTR.DIRECTORY_SEPARATOR."".$entidad."Controller.php";
            if (file_exists($archivoController)) {
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-existe');
            }else{
                self::createFileReadControllerCRUD($archivoController,$this->app,$entidad,$campos,$rutaVistaD);
            }

            // Verificar si existe modelo de lo contrario se genera en el momento
            $archivoModel = $rutaApp.All::APP_MODEL.DIRECTORY_SEPARATOR."".$entidad."Model.php";
            if (file_exists($archivoModel)) {
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-existe');
            }else{
               self::createFileModelCRUD($archivoModel,$this->app,$entidad,$campRealEnti);
            }

            // Verificar si existe el Router.xml de lo contrario se genera al momento
            $archivoRoute = $rutaApp.All::APP_ROUTE.DIRECTORY_SEPARATOR."Router.xml";
            $existe = self::existsRuta($archivoRoute,$crud);
            if ($existe) {
                $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-existe');
            }else{
                self::createNewRutaXmlCRUD($archivoRoute,$crud,$tabla);
            }

            // Permite crear la carpeta donde estará la vistas generada si no existe
            self::createDirViews($rutaPadre,$rutaHija);

            // Permite crear los archivos necesarios de la vista que se procesara en el moment

            // Generacion del Index
            $fileViewIndex = $rutaHija . DIRECTORY_SEPARATOR . "index.php";
            self::createFileViewIndex($fileViewIndex, $campos, $rutaVista, $requerido);

            // Generacion del Assent donde esta las configuraciones de la grid y los valores de la vista
            $fileViewAssent = $rutaHija . DIRECTORY_SEPARATOR . "assent.php";
            self::createFileViewAssent($fileViewAssent, $campos, $rutaVista, $requerido);


            // Generacion del Form donde esta la configuración de los campos del formulario
            $fileViewForm = $rutaHija.DIRECTORY_SEPARATOR."form.php";
            self::createFileViewForm($fileViewForm, $campos, $requerido);


            // Generacion del Listado
            $fileViewListado = $rutaHija.DIRECTORY_SEPARATOR."listado.php";
            self::createFileViewListado($fileViewListado, $campos, $campRealEnti);


            // Generacion del Json ecargado de procesar las mascaras
            $fileViewJsonMascaras = $rutaHija.DIRECTORY_SEPARATOR."mascaras.json";
            self::createFileViewJsonMascaras($fileViewJsonMascaras, $mascaras);


            $msj=PreInterprete::getMsjConsoleInterno($this->active,'app:crud-creado');

        }
        $msj=All::mergeTaps($msj,array('app'=>$this->app,'controller'=>$entidad));
        $this->logInfo($msj);
        return true;
    }

    /**
     * Permote crear el direcrorio donde se almacenaran las vista de la aplicacion
     * @return boolean
     */
    private function createDirViews($rutaPadre,$rutaHija)
    {
        if (!file_exists($rutaPadre)) {
            All::mkddir($rutaPadre);
        }
        if (!file_exists(All::formatRuta($rutaHija))) {
            All::mkddir(All::formatRuta($rutaHija));
        }

    }

    /**
     * Permite crear una plantilla archivo encargado de procesar el controller simple
     * @param string $ruta, ruta donde esta el xml
     * @param string $app, aplicacion que levanta los datos
     * @param string $controller, controller que se creara en el momento
     * @param string $campos, ruta de la vista dinamica que segenera
     * @param string $rutaVista, ruta de la vista dinamica que segenera
     */
    private function createFileReadControllerCRUD($archivoController, $app , $controller,  $campos,  $rutaVista)
    {
        $ar = fopen($archivoController, "w+") or die("Problemas en la creaci&oacute;n del controlador del apps " . $app);
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
        fputs($ar, ' * @version: 2.0'.PHP_EOL);
        fputs($ar, ' */ '.PHP_EOL.PHP_EOL);

        fputs($ar, 'class '.$controller.'Controller extends Controller'.PHP_EOL);
        fputs($ar, "{".PHP_EOL);
        fputs($ar, '   use Security;'.PHP_EOL);
        fputs($ar, '   public $model;'.PHP_EOL);
        fputs($ar, '   public $session;'.PHP_EOL.PHP_EOL);
        fputs($ar, '   // Variables de Seguridad asociado a los roles'.PHP_EOL);
        fputs($ar, '   private $apps;'.PHP_EOL);
        fputs($ar, '   private $entidad;'.PHP_EOL);
        fputs($ar, '   private $vista;'.PHP_EOL);
        fputs($ar, '   private $permisos;'.PHP_EOL);
        fputs($ar, '   public $comps;'.PHP_EOL.PHP_EOL);

        fputs($ar, '   public function __construct()'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '       parent::__construct();'.PHP_EOL);
        fputs($ar, '       $this->session = $this->authenticated();'.PHP_EOL);
        fputs($ar, '       $this->ho'.$controller.'Model = new Model\\'.$controller.'Model();'.PHP_EOL);
        fputs($ar, '       $this->valSegPerfils = new Model\SegUsuariosPerfilModel();'.PHP_EOL);
        fputs($ar, '       $this->apps = $this->pathApps(__DIR__);'.PHP_EOL);
        fputs($ar, '       $this->entidad = $this->ho'.$controller.'Model->tabla;'.PHP_EOL);
        fputs($ar, '       $this->vista = $this->pathVista();'.PHP_EOL);
        fputs($ar, '       $this->comps = $this->apps .\' - \'. $this->entidad .\' - \'. $this->vista;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Mostrar el index de la vista'.$controller.PHP_EOL);
        fputs($ar, '    * @param: GET $resquest'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Index($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->permisos = \'CONSULTAS|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '     $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos));'.PHP_EOL.PHP_EOL);
        fputs($ar, '     $this->tpl->addIni();'.PHP_EOL);
        //fputs($ar, '     $listado = $this->ho'.$controller.'Model->get'.$controller.'Listar($request->postParameter());'.PHP_EOL);
        //fputs($ar, '     $this->tpl->add(\'listado\', $listado);;'.PHP_EOL);
        fputs($ar, '     $this->tpl->add(\'usuario\', $this->getSession(\'usuario\'));'.PHP_EOL);//
        fputs($ar, '     $this->tpl->renders(\'view::'.$rutaVista.'\'.$this->pathVista().\'/index\');'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Listar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    * @return: XML $result'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Listar($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      // Validar roles de acceso;'.PHP_EOL);
        fputs($ar, '      $this->permisos = \'CONSULTA|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '      $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos),true);'.PHP_EOL.PHP_EOL);

        fputs($ar, '      // Bloque de proceso de la grilla'.PHP_EOL);
        fputs($ar, '      $result = $this->formatRows($request->getParameter(\'obj\'));'.PHP_EOL.PHP_EOL);

        fputs($ar, '      // Procesar los datos del modelo para el paginado'.PHP_EOL);
        fputs($ar, '      $rows = $this->ho'.$controller.'Model->get'.$controller.'Listar($request->getParameter(),$result);'.PHP_EOL.PHP_EOL);

        fputs($ar, '      // Exportar el resultado en xml para mostrar los datos'.PHP_EOL);
        fputs($ar, '      $this->xmlGridList($rows);'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Crear registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    * @return: JSON $result'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Create($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      // Verificar permisologia'.PHP_EOL);
        fputs($ar, '      $this->permisos = \'ALTA|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '      $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos),true);'.PHP_EOL.PHP_EOL);
        fputs($ar, '      // Verificar las mascaras'.PHP_EOL);
        fputs($ar, '      parent::runValidarMascarasVista(\''.$rutaVista.'\', $this->pathVista(),$request->postParameter());'.PHP_EOL.PHP_EOL);
        fputs($ar, '      $result = $this->ho'.$controller.'Model->set'.$controller.'Create($request->postParameter());'.PHP_EOL);
        fputs($ar, '      if(is_null($result)){'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'1\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\']=\'Error en procesar el registro\';'.PHP_EOL);
        fputs($ar, '      }else{;'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'0\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\'] = \'Registro efectuado exitosamente\';'.PHP_EOL);
        fputs($ar, '      }'.PHP_EOL);
        fputs($ar, '      $this->json($dataJson);'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Ver registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    * @return: JSON $result'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Show($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      // Verificar permisologia'.PHP_EOL);
        fputs($ar, '      $this->permisos = \'CONSULTA|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '      $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos),true);'.PHP_EOL.PHP_EOL);
        fputs($ar, '      $result = $this->ho'.$controller.'Model->get'.$controller.'Show($request->postParameter());'.PHP_EOL);
        fputs($ar, '      $this->json($result);'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Eliminar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    * @return: JSON $result'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Delete($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      // Verificar permisologia'.PHP_EOL);
        fputs($ar, '      $this->permisos = \'BAJA|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '      $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos),true);'.PHP_EOL.PHP_EOL);
        fputs($ar, '      $result = $this->ho'.$controller.'Model->rem'.$controller.'Delete($request->postParameter());'.PHP_EOL);
        fputs($ar, '      if(is_null($result)){'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'0\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\']=\'Registro eliminado exitosamente\';'.PHP_EOL);
        fputs($ar, '      }else{'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'1\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\'] = \'Error en procesar la actualizacion\';'.PHP_EOL);
        fputs($ar, '      }'.PHP_EOL);
        fputs($ar, '      $this->json($dataJson);'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Actualizar registros de '.$controller.PHP_EOL);
        fputs($ar, '    * @param: POST $resquest'.PHP_EOL);
        fputs($ar, '    * @return: JSON $result'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function run'.$controller.'Update($request)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      // Verificar permisologia'.PHP_EOL);
        fputs($ar, '      $this->permisos = \'MODIFICACION|CONTROL TOTAL\';'.PHP_EOL);
        fputs($ar, '      $this->validatePermisos($this->valSegPerfils->valSegPerfilRelacionUser($this->comps,$this->permisos),true);'.PHP_EOL.PHP_EOL);
        fputs($ar, '      // Verificar las mascaras'.PHP_EOL);
        fputs($ar, '      parent::runValidarMascarasVista(\''.$rutaVista.'\',$this->pathVista(),$request->postParameter());'.PHP_EOL.PHP_EOL);
        fputs($ar, '      $result = $this->ho'.$controller.'Model->set'.$controller.'Update($request->postParameter());'.PHP_EOL);
        fputs($ar, '      if(is_null($result)){'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'0\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\']=\'Actualizacion efectuado exitosamente\';'.PHP_EOL);
        fputs($ar, '      }else{'.PHP_EOL);
        fputs($ar, '        $dataJson[\'error\']=\'1\';'.PHP_EOL);
        fputs($ar, '        $dataJson[\'msj\'] = \'Error en procesar la actualizacion\';'.PHP_EOL);
        fputs($ar, '      }'.PHP_EOL);
        fputs($ar, '        $this->json($dataJson);'.PHP_EOL);
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
    private function createFileModelCRUD(string $archivoModel, string $app, string $modelo , $campos)
    {
        unset($campos['campos'][0]);
        $app = All::upperCase($app);

        $ar = fopen($archivoModel, "w+") or die("Problemas en la add del model del apps". $app);
        $camposCompleto = $campos['campos'];
        // Inicio la escritura en el activo
        fputs($ar, '<?php'.PHP_EOL);
        fputs($ar, 'namespace APP\\'.$app.'\\Model;'.PHP_EOL);
        fputs($ar, 'use IRON\\Complements\\Database\\Base;'.PHP_EOL);
        fputs($ar, 'use IRON\\Core\\Commun\\{All,Security};'.PHP_EOL);
        fputs($ar, '/**'.PHP_EOL);
        fputs($ar, ' * Generador de codigo del Modelo de la App '.$app.PHP_EOL);
        fputs($ar, ' * @propiedad: '.All::FW.' '.All::VERSION.''.PHP_EOL);
        fputs($ar, ' * @autor: Ing. Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
        fputs($ar, ' * @created: ' .date('d/m/Y') .''.PHP_EOL);
        fputs($ar, ' * @version: 2.0'.PHP_EOL);
        fputs($ar, ' */ '.PHP_EOL.PHP_EOL);

        fputs($ar, "class ". $modelo."Model extends Base".PHP_EOL);
        fputs($ar, "{".PHP_EOL);
        fputs($ar, "   use Security;".PHP_EOL);
        fputs($ar, '   public function __construct()'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '       $this->tabla = \''.$campos['entida'].'\';'.PHP_EOL);
        fputs($ar, '       $this->campoid = array(\''.$campos['pk'].'\');'.PHP_EOL);
        fputs($ar, '       $this->campos = array(\''.implode("','",$campos['campos']).'\');'.PHP_EOL);
        fputs($ar, '       // Clase de registro de auditoria de las acciones'.PHP_EOL);
        //fputs($ar, '       $this->hoSegLogAccionesModel = new Model\SegLogAccionesModel();'.PHP_EOL);
        fputs($ar, '       $this->segLogAccionesModel = new SegLogAccionesModel();'.PHP_EOL);
        fputs($ar, '       parent::__construct();'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Extraer todos los registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function get'.$modelo.'ListarCombo($request,$result)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $tablas=$this->leerTodos($datos);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        // Permite extraer las entidades de la conexion actual desde la informacion schema
        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Extraer todos los registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function get'.$modelo.'Listar($request,$result)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL.PHP_EOL);

        fputs($ar, '        // Variables definidas para del paginador'.PHP_EOL);
        fputs($ar, '        $limit = 100;'.PHP_EOL);
        fputs($ar, '        if(isset($request->posStart)){'.PHP_EOL);
        fputs($ar, '            $posStart = $request->posStart;'.PHP_EOL);
        fputs($ar, '        }else{'.PHP_EOL);
        fputs($ar, '            $posStart = 0;'.PHP_EOL);
        fputs($ar, '        }'.PHP_EOL);
        fputs($ar, '        if(isset($request->count)){'.PHP_EOL);
        fputs($ar, '            $count = $request->posStart+$limit;'.PHP_EOL);
        fputs($ar, '        }else{'.PHP_EOL);
        fputs($ar, '            $count = $limit;'.PHP_EOL);
        fputs($ar, '        }'.PHP_EOL.PHP_EOL);

        fputs($ar, '        // Permite identificar hay una definicion de busqueda de algun campo mediante el search'.PHP_EOL);
        fputs($ar, '        $search= \'\';'.PHP_EOL);
        fputs($ar, '        foreach ($request AS $key=>$value){'.PHP_EOL);
        fputs($ar, '            if(\preg_match(\'/search_\w/\',$key) AND !empty($value)){'.PHP_EOL);
        fputs($ar, '                $campo = str_replace(\'search_\',\'\', $key);'.PHP_EOL);
        fputs($ar, '                $search.= " AND $campo like \'%$value%\'";'.PHP_EOL);
        fputs($ar, '            }'.PHP_EOL);
        fputs($ar, '        }'.PHP_EOL.PHP_EOL);
        fputs($ar, '        // Variables definidas para el ordenamiento DESC y ASC'.PHP_EOL);
        fputs($ar, '        $order = \'\';'.PHP_EOL);
        fputs($ar, '        $by = \'\';'.PHP_EOL);
        fputs($ar, '        if(!empty($request->orderBy) AND $request->orderBy!=\'undefined\'){'.PHP_EOL);
        fputs($ar, '            $tmp = $this->campos[$request->orderBy];'.PHP_EOL);
        fputs($ar, '            if (!isset($request->direction) || $request->direction=="asc") {'.PHP_EOL);
        fputs($ar, '                $by = \'ASC\';'.PHP_EOL);
        fputs($ar, '                $order = " ORDER BY $tmp ".$by;'.PHP_EOL);
        fputs($ar, '            }else{'.PHP_EOL);
        fputs($ar, '                $by = \'DESC\';'.PHP_EOL);
        fputs($ar, '                $order = " ORDER BY $tmp ".$by;'.PHP_EOL);
        fputs($ar, '            }'.PHP_EOL);
        fputs($ar, '        }'.PHP_EOL.PHP_EOL);

        fputs($ar, '        // Elemento cuando hay relacion'.PHP_EOL);
        fputs($ar, '         $relation = All::formatRelacio(@$request->relacion);'.PHP_EOL);
        fputs($ar, '         if(!empty($relation[0])){'.PHP_EOL);
        fputs($ar, '            foreach ($relation AS $option) {'.PHP_EOL);
        fputs($ar, '                $search.="  AND $option";'.PHP_EOL);
        fputs($ar, '            }'.PHP_EOL);
        fputs($ar, '         }'.PHP_EOL.PHP_EOL);

        // Elemento cuando hay relacion
        $relation = All::formatRelacio(@$request->relacion);
        //($relation); die();
        if(!empty($relation[0])){
            $search.="  AND $relation[0]";
        }

        fputs($ar, '         // Primero extraer la cantidad de registros'.PHP_EOL);
        fputs($ar, '         $sqlCount = "Select count(*) as items FROM ".$this->tabla.\' WHERE 0=0 \'.$search ;'.PHP_EOL);
        fputs($ar, '         $resCount = $this->executeQuery($sqlCount);'.PHP_EOL);

        fputs($ar, '         //create query to products table'.PHP_EOL);
        fputs($ar, '         $sql = implode(\',\', $result[\'select\']).", ".$this->campoid[0]." FROM ".$this->tabla.\' WHERE 0=0 \'.$search ;'.PHP_EOL);
        fputs($ar, '         //if this is the first query - get total number of records in the query result'.PHP_EOL);
        fputs($ar, '         $sqlCount = "SELECT * FROM (SELECT ROW_NUMBER() OVER( ORDER BY ".$this->campoid[0]." ".$by." ) AS row, ".$resCount[0]->items." AS cnt, $sql ) AS sub";'.PHP_EOL);
        fputs($ar, '         $resQuery = $this->get($sqlCount);'.PHP_EOL);
        fputs($ar, '         $rowCount =  $this->fetch();'.PHP_EOL);
        fputs($ar, '         $totalCount = (empty($rowCount->cnt))?0:$rowCount->cnt;'.PHP_EOL);
        fputs($ar, '         //add limits to query to get only rows necessary for the output'.PHP_EOL);
        fputs($ar, '         $sqlCount.= " WHERE row>=".$posStart." AND row<=".$count;'.PHP_EOL);
        fputs($ar, '         $sqlCount.= $order;'.PHP_EOL.PHP_EOL);

        fputs($ar, '         // Definir las variables para el uso para el XML'.PHP_EOL);
        fputs($ar, '         $items = array();'.PHP_EOL);
        fputs($ar, '         $items[\'data\'] = $this->executeQuery($sqlCount);'.PHP_EOL);
        fputs($ar, '         $items[\'totalCount\'] = (isset($request->posStart))?\'\':$totalCount;'.PHP_EOL);
        fputs($ar, '         $items[\'posStart\'] = $posStart;'.PHP_EOL);
        fputs($ar, '         return $items;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);


        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Crear registros nuevos de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: Array $datos'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function set'.$modelo.'Create($datos)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->fijarValores($datos);'.PHP_EOL);
        // Valores reservados del Sistema
        if (in_array("created_usuario_id", $camposCompleto)) {
            fputs($ar, '     $this->fijarValor(\'created_usuario_id\',$user->id);'.PHP_EOL);
        }
        if (in_array("created_at", $camposCompleto)) {
            fputs($ar, '     $this->fijarValor(\'created_at\',All::now());'.PHP_EOL);
        }
        // Fin de seteo de valores reservado del sistema
        fputs($ar, '     $this->guardar();'.PHP_EOL);
        fputs($ar, '     $val = $this->lastId();'.PHP_EOL.PHP_EOL);
        fputs($ar, '    // Registra log de auditoria de registro de acciones'.PHP_EOL);
        fputs($ar, '    $user = $this->getSession(\'usuario\');'.PHP_EOL);
        fputs($ar, '    $this->segLogAccionesModel->cargaAcciones($this->tabla, $val,serialize($datos),\'\', $user->id, parent::LOG_ALTA);'.PHP_EOL);
        fputs($ar, '     return $val;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Extraer un registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: String $id'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function get'.$modelo.'Show($data)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $sql = "SELECT * FROM ".$this->tabla." WHERE id=".$data->data;'.PHP_EOL);
        fputs($ar, '     $tmp=$this->executeQuery($sql);'.PHP_EOL);
        fputs($ar, '     $tablas[\'datos\'] = $tmp[0];'.PHP_EOL);
        fputs($ar, '     $tablas[\'error\'] = 0;'.PHP_EOL);
        fputs($ar, '     // Registro de Auditoria'.PHP_EOL);
        fputs($ar, '     $user = $this->getSession(\'usuario\');'.PHP_EOL);
        fputs($ar, '     $this->segLogAccionesModel->cargaAcciones($this->tabla, $data->data, \'\',\'\', $user->id, parent::LOG_CONS);'.PHP_EOL);
        fputs($ar, '     return $tablas;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Eliminar registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: string $id'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function rem'.$modelo.'Delete($datos)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '      $valor=base64_decode($datos->obj);'.PHP_EOL);
        fputs($ar, '      $this->fijarValor(\'id\',$valor);'.PHP_EOL);
        fputs($ar, '      $val = $this->borrar();'.PHP_EOL);
        fputs($ar, '      // Registro de Auditoria'.PHP_EOL);
        fputs($ar, '      $user = $this->getSession(\'usuario\');'.PHP_EOL);
        fputs($ar, '      $this->segLogAccionesModel->cargaAcciones($this->tabla, $valor,\'\',\'\', $user->id, parent::LOG_BAJA);'.PHP_EOL);
        fputs($ar, '      return $val;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL.PHP_EOL);

        fputs($ar, '    /**'.PHP_EOL);
        fputs($ar, '    * Actualizar registros de '.$modelo.PHP_EOL);
        fputs($ar, '    * @param: arreglo $obj'.PHP_EOL);
        fputs($ar, '    * @return array $tablas'.PHP_EOL);
        fputs($ar, '    */ '.PHP_EOL);
        fputs($ar, '   public function set'.$modelo.'Update($datos)'.PHP_EOL);
        fputs($ar, '   {'.PHP_EOL);
        fputs($ar, '     $this->fijarValores($datos);'.PHP_EOL);
        // Valores reservados del Sistema
        if (in_array("updated_usuario_id", $camposCompleto)) {
            fputs($ar, '     $this->fijarValor(\'updated_usuario_id\',$user->id);'.PHP_EOL);
        }
        if (in_array("updated_at", $camposCompleto)) {
            fputs($ar, '     $this->fijarValor(\'updated_at\',All::now());'.PHP_EOL);
        }
        // Fin de seteo de valores reservado del sistema
        fputs($ar, '     $val = $this->guardar();'.PHP_EOL);
        fputs($ar, '     // Setear log de registro de acciones'.PHP_EOL);
        fputs($ar, '      $user = $this->getSession(\'usuario\');'.PHP_EOL);
        fputs($ar, '     $this->segLogAccionesModel->cargaAcciones($this->tabla, $datos->id,\'\', json_encode($datos), $user->id, parent::LOG_MODI);'.PHP_EOL);
        fputs($ar, '     return $val;'.PHP_EOL);
        fputs($ar, '   }'.PHP_EOL);
        fputs($ar, '}'.PHP_EOL);
        fputs($ar, '?>'.PHP_EOL);
        // Cierro el archivo y la escritura
        fclose($ar);
    }


    /**
     * Method encargado de procesar rutas asociadas al al sistema
     * @param string $archivoRoute, ruta donde se hace el proceso donde esta la apliacion
     * @param string $controller, nombre del controllador
     * @param string $tabla, nombre con el cual se llama al sistema
     * @param string $method, permite identificar cual es el method que debe instanciar el clase
     * @param string $request, permite identificar si el method GET o POST
     */

    private function createNewRutaXmlCRUD(string $archivoRoute, string $controller, string $tabla)
    {
        //print_r($controller);
        $router = new SimpleXMLExtended($archivoRoute, null, true) or die("Problemas en la creaci&oacute;n del router del apps Router.xml");
        $router->addComentario(' Bloque de configuracion de la ruta del controller '.ucfirst($controller));
        // Listar registro
        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Index');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Index');
        $personaje->addChild('request', 'GET');
        //
        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Listar');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Listar');
        $personaje->addChild('request', 'GET');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Create');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Create');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Show');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Show');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Delete');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Delete');
        $personaje->addChild('request', 'POST');

        $personaje = $router->addChild('link');
        $personaje->addChild('name', '/'.All::formatRuta($controller).'Update');
        $personaje->addChild('controller', All::cameCase($tabla));
        $personaje->addChild('method', 'run'.All::upperCase($tabla).'Update');
        $personaje->addChild('request', 'POST');
        $router->asXML($archivoRoute);
        $router->formatXml($archivoRoute);
    }

    /**
     * Method encargado de generar el index de la vista que se procesa en el momento desde el generador
     * @param string $fileViewIndex, Indica donde se genera el index de la vista de la vista
     * @param array $campos, indica toda la configuracion de la vista, campos y detalle en general
     * @param array $rutaVista, indica toda la configuracion de la ruta real de la vista
     * @param array $requerido, indica toda la configuracion de los campos requeridos
     * @return bool true
     */
    private function createFileViewIndex($fileViewIndex, $campos, $rutaVista, $requerido)
    {
        $ar = fopen($fileViewIndex, "w+") or die("Problemas en la creaci&oacute;n del view index.php");
        // Inicio la escritura en el activo
        fputs($ar, '<?php' . PHP_EOL);
        fputs($ar, '$breadcrumb=(object)array(\'actual\'=>\'' . $campos['vista_alias'] . '\',\'titulo\'=>\'Vista de integrada de gestion de ' . $campos['vista_alias']. '\',\'ruta\'=>\'' . $campos['vista_alias'] . '\')?>' . PHP_EOL);
        fputs($ar, '<?php $this->layout(\'base\',[\'usuario\'=>$usuario,\'breadcrumb\'=>$breadcrumb])?>' . PHP_EOL);

        fputs($ar, '<?php $this->push(\'addCss\')?>' . PHP_EOL);
        fputs($ar, '<?php $this->end()?>' . PHP_EOL);

        fputs($ar, '<?php $this->push(\'title\') ?>' . PHP_EOL);
        fputs($ar, ' Gestionar de la vista ' . ALL::upperCase($campos['vista_alias']) . PHP_EOL);
        fputs($ar, '<?php $this->end()?>' . PHP_EOL);

        fputs($ar, '<div class="row">' . PHP_EOL);
        fputs($ar, '    <!-- left column -->' . PHP_EOL);
        fputs($ar, '    <div class="col-md-7">' . PHP_EOL);
        fputs($ar, '        <!-- general form elements -->' . PHP_EOL);
        fputs($ar, '        <?php $this->insert(\'view::' . $rutaVista . '/listado\') ?>' . PHP_EOL);
        fputs($ar, '    </div>' . PHP_EOL);
        fputs($ar, '        <div class="col-md-5">' . PHP_EOL);
        fputs($ar, '        <?php $this->insert(\'view::' . $rutaVista . '/form\') ?>' . PHP_EOL);
        fputs($ar, '    </div>' . PHP_EOL);
        fputs($ar, '</div>' . PHP_EOL);

        /** Vista con hijos */
        foreach ($campos['campos'] AS $key => $value){
            $mostrar = explode('#',$requerido[$value]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -

            $valores=explode('--',$mostrar[3]);
            if($mostrar[2] =='grilla' AND count($valores) > 0 ) {
                fputs($ar, '<!-- Incluir las de la vista de navegacion de ### ('.$mostrar[3].') ### -->' . PHP_EOL);
                fputs($ar, '<div class="row">' . PHP_EOL);
                fputs($ar, '    <!-- left column -->' . PHP_EOL);
                fputs($ar, '    <div class="col-md-7">' . PHP_EOL);
                fputs($ar, '        <!-- general form elements -->' . PHP_EOL);
                fputs($ar, '        <?php $this->insert(\'view::vistas/'.All::cameCase($valores[0]).'/'.$valores[1].'/listado\') ?>' . PHP_EOL);
                fputs($ar, '    </div>' . PHP_EOL);
                fputs($ar, '        <div class="col-md-5">' . PHP_EOL);
                fputs($ar, '        <?php $this->insert(\'view::vistas/'.All::cameCase($valores[0]).'/'.$valores[1].'/form\') ?>' . PHP_EOL);
                fputs($ar, '    </div>' . PHP_EOL);
                fputs($ar, '</div>' . PHP_EOL);
            }
        }
        fputs($ar, '<?php $this->push(\'addJs\') ?>' . PHP_EOL);
        fputs($ar, '<script>' . PHP_EOL);
        fputs($ar, '    // Definicion los campos del DataTable de esta vista' . PHP_EOL);
        fputs($ar, '    var Config = {};' . PHP_EOL);

        fputs($ar, '    <?php $this->insert(\'view::' . $rutaVista . '/assent\') ?>' . PHP_EOL);

        fputs($ar, '    Core.Vista.Util = {}' . PHP_EOL);
        fputs($ar, '    Core.Vista.Util = {' . PHP_EOL);
        // Fragmento de codigo que permite hacer los combos dinamicos
        fputs($ar, '        priListaLoad: function (){ ' . PHP_EOL);
        foreach ($campos['campos'] AS $key => $value){
            $mostrar = explode('#',$requerido[$value]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
            $valores=explode('--',$mostrar[3]);
            if($mostrar[0]==0 AND $mostrar[2]=='combo' AND count($valores)>0) {
                fputs($ar, '            // Configurar de los campos '.$mostrar[3].' \';' . PHP_EOL);
                fputs($ar, '            var html'.$key.' = \'<option>Seleccionar</option>\';' . PHP_EOL);
                fputs($ar, '              $.ajax({' . PHP_EOL);
                fputs($ar, '                url: \'/getEntidadComun\',' . PHP_EOL);
                fputs($ar, '                type: "POST",' . PHP_EOL);
                fputs($ar, '                headers: {' . PHP_EOL);
                fputs($ar, '                        \'X-Auth-Token\' : $(\'#csrf_token\').val()' . PHP_EOL);
                fputs($ar, '                },' . PHP_EOL);
                fputs($ar, '                data: {"tipo":"combo","tabla_vista":"'.$mostrar[3].'","vista_campo":"'.$mostrar[4].'","cart_separacion":"'.$mostrar[5].'"},' . PHP_EOL);
                fputs($ar, '                dataType: \'JSON\',' . PHP_EOL);
                fputs($ar, '                success : function(dataJson) {' . PHP_EOL);
                fputs($ar, '                    $.each(dataJson.datos,function(key,value){' . PHP_EOL);
                fputs($ar, '                    html'.$key.' += \'<option value="\'+value.id+\'">\'+value.nombre+\'</option>;\'' . PHP_EOL);
                fputs($ar, '                    });' . PHP_EOL);
                fputs($ar, '                    $(".'.$mostrar[3].'").html(html'.$key.')' . PHP_EOL);
                fputs($ar, '                }' . PHP_EOL);
                fputs($ar, '            });' . PHP_EOL);
            }
        }


        fputs($ar, '        },' . PHP_EOL);
        // Fin de combo dinamicos
        // Fragmento de codigo para mostrar los datos del hijo seleccionando el padre
        fputs($ar, '        priListaClick: function (dataJson){'.PHP_EOL);
        foreach ($campos['campos'] AS $key => $value){
            $mostrar = explode('#',$requerido[$value]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3] tabla_vista--personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
            $valores=explode('--',$mostrar[3]);
            if($mostrar[2]=='grilla' AND count($valores)>0) {
                fputs($ar, '           <?php $this->insert(\'view::vistas/'.All::cameCase($valores[0]).'/'.$valores[1].'/assent\') ?>' . PHP_EOL);
                fputs($ar, '            Config.relacionPadre = {' . PHP_EOL);
                fputs($ar, '                "field":\''.$valores[2].'\',' . PHP_EOL);
                fputs($ar, '                "value": \''.$mostrar[4].'\','. PHP_EOL);
                fputs($ar, '                "id": dataJson.datos.id'. PHP_EOL);
                fputs($ar, '            };' . PHP_EOL. PHP_EOL);
                fputs($ar, '            Core.Vista.main(\''.All::upperCase($valores[1]).'\',Config);' . PHP_EOL);
            }
        }
        fputs($ar, '        }, ' . PHP_EOL);
        // Fin de Fragento de codigo para extraer los datos del click del padre
        fputs($ar, '        priClickProcesarForm: function(){ }, ' . PHP_EOL);

        // Codigo encargado para validar las mascaras
        fputs($ar, '        validateMascaras: function (send) {' . PHP_EOL);
        fputs($ar, '            return Core.VistaMascara.main(send);' . PHP_EOL);
        fputs($ar, '        }' . PHP_EOL);
        fputs($ar, '    };' . PHP_EOL);

        fputs($ar, '    $(function () {' . PHP_EOL);
        fputs($ar, '        Core.main();' . PHP_EOL);
        fputs($ar, '        Core.Vista.main(Config.show.vista,Config);' . PHP_EOL);
        fputs($ar, '    })' . PHP_EOL);
        fputs($ar, '' . PHP_EOL);
        fputs($ar, '</script>' . PHP_EOL);
        fputs($ar, '<?php $this->end() ?> ' . PHP_EOL);
        fclose($ar);
    }

    /**
     * Method encargado de generar el Assent de la vista que se procesa en el momento desde el generador
     * @param string $fileViewAssent, Indica donde se genera el archivo Assent de la vista
     * @param array $campos, indica toda la configuracion de la vista, campos y detalle en general
     * @param array $rutaVista, indica toda la configuracion de la ruta real de la vista
     * @param array $requerido, indica toda la configuracion de los campos requeridos
     * @return bool true
     */
    private function createFileViewAssent($fileViewAssent, $campos, $rutaVista, $requerido)
    {
        $ar = fopen($fileViewAssent, "w+") or die("Problemas en la creaci&oacute;n del view index.php");
        // Inicio la escritura en el activo
        fputs($ar, '    // Definicion de las variables necesarias para la grilla y validacion de mascaras' . PHP_EOL);
        fputs($ar, '    var Config = {};' . PHP_EOL);
        fputs($ar, '    Config.colums = [' . PHP_EOL);
        $filtro = array();
        foreach ($campos['campos'] AS $key => $value){
                $mostrar = explode('#',$requerido[$value]);
                //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
                if((int)$mostrar[1]!=1) {
                    fputs($ar, '        { \'id\':\''.$campos[$key]->field.'\', \'type\':\'ro\', \'align\':\'left\', \'sort\':\'server\', \'value\':\''.$campos[$key]->label.'\', \'widths\':\'*\' },' . PHP_EOL);
                    if($campos[$key]->mascara=='texto' OR $campos[$key]->mascara=='varchar'){
                        array_push($filtro,'#text_filter');
                    }else{
                        array_push($filtro,'&nbsp;');
                    }
                }
            }
        fputs($ar, '    ];' . PHP_EOL. PHP_EOL);
        fputs($ar, '    // Configuracion de visual de la grilla' . PHP_EOL);
        fputs($ar, '    // #text_filter, #select_filter, #combo_filter, #text_search, #numeric_filter' . PHP_EOL);
        fputs($ar, '    Config.show = {' . PHP_EOL);
        fputs($ar, '        \'vista\':\''.All::upperCase($campos['vista']).'\',' . PHP_EOL);
        fputs($ar, '        \'tableTitle\':\'Listado de Registros.\',' . PHP_EOL);
        //fputs($ar, '        \'autoWidth\':\''.implode(',',$filtro).'\',' . PHP_EOL);
        fputs($ar, '        \'autoWidth\':true,' . PHP_EOL);
        fputs($ar, '        \'multiSelect\':false,' . PHP_EOL);
        fputs($ar, '        \'pageSize\':50,' . PHP_EOL);
        fputs($ar, '        \'pagesInGrp\':10' . PHP_EOL);
        fputs($ar, '    }' . PHP_EOL. PHP_EOL);

        fputs($ar, '    // Configuracion de relacion de entidad' . PHP_EOL);
        fputs($ar, '    Config.relacionPadre = {' . PHP_EOL);
        fputs($ar, '        "field":"",' . PHP_EOL);
        fputs($ar, '        "value": ""' . PHP_EOL);
        fputs($ar, '    }' . PHP_EOL. PHP_EOL);

        fputs($ar, '    '.All::upperCase($campos['vista']).' = {}' . PHP_EOL);
        fputs($ar, '    '.All::upperCase($campos['vista']).' = {' . PHP_EOL);
        // Fragmento de codigo que permite hacer los combos dinamicos
        fputs($ar, '        loadListaMenu: function (){ ' . PHP_EOL);
        $exisste = [];
        foreach ($campos['campos'] AS $key => $value){
            $mostrar = explode('#',$requerido[$value]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
            $valores=explode('--',$mostrar[3]);
            if($mostrar[0]==0 AND $mostrar[2]=='combo' AND count($valores)>0) {
                $existe[]=$mostrar[3];
                fputs($ar, '            // Configurar de los campos '.$mostrar[3].' \';' . PHP_EOL.PHP_EOL);
                fputs($ar, '            var html'.$key.' = \'<option value=" ">Seleccionar</option>\';' . PHP_EOL);
                fputs($ar, '            $.ajax({' . PHP_EOL);
                fputs($ar, '              url: \'/getEntidadComun\',' . PHP_EOL);
                fputs($ar, '              type: "POST",' . PHP_EOL);
                fputs($ar, '              headers: {' . PHP_EOL);
                fputs($ar, '                       \'X-Auth-Token\' : $(\'#csrf_token\').val()' . PHP_EOL);
                fputs($ar, '              },' . PHP_EOL);
                fputs($ar, '              data: {"tipo":"combo","tabla_vista":"'.$mostrar[3].'","vista_campo":"'.$mostrar[4].'","cart_separacion":"'.$mostrar[5].'"},' . PHP_EOL);
                fputs($ar, '              dataType: \'JSON\',' . PHP_EOL);
                fputs($ar, '              success : function(dataJson) {' . PHP_EOL);
                fputs($ar, '                 $.each(dataJson.datos,function(key,value){' . PHP_EOL);
                fputs($ar, '                        html'.$key.' += \'<option value="\'+value.id+\'">\'+value.nombre+\'</option>;\'' . PHP_EOL);
                fputs($ar, '                 });' . PHP_EOL);

                fputs($ar, '                 var data = sessionStorage.getItem(".'.$mostrar[3].'");' . PHP_EOL);
                fputs($ar, '                 var cant = $(".'.$mostrar[3].' option").length;' . PHP_EOL);
                fputs($ar, '                 if(data == typeof null){' . PHP_EOL);
                fputs($ar, '                     sessionStorage.setItem(".'.$mostrar[3].'",window.btoa(html4));' . PHP_EOL);
                fputs($ar, '                     $(".'.$mostrar[3].'").html(html'.$key.');' . PHP_EOL);
                fputs($ar, '                 }else if(cant < 2 ){' . PHP_EOL);
                fputs($ar, '                     $(".'.$mostrar[3].'").html(html'.$key.');' . PHP_EOL);
                fputs($ar, '                 }' . PHP_EOL);
                fputs($ar, '              }' . PHP_EOL);
                fputs($ar, '            });' . PHP_EOL);
            }
        }

        fputs($ar, '        },' . PHP_EOL);
        fputs($ar, '     }' . PHP_EOL);
        // Fin de combo dinamicos
        // Configuracion de las mascaras
        fputs($ar, '<?php'.PHP_EOL);
        fputs($ar, '       $fies = file_get_contents(__DIR__.\'/mascaras.json\');'.PHP_EOL);
        fputs($ar, '       $dataJson = json_decode($fies);'.PHP_EOL);
        fputs($ar, ' ?>' . PHP_EOL);
        fputs($ar, 'Core.Vista.Mascara = ['.PHP_EOL);
        fputs($ar, '<?php'.PHP_EOL);
        fputs($ar, 'foreach ($dataJson->mascaras AS $key => $val){'.PHP_EOL);
        fputs($ar, '    echo "{\'type\':\'".$val->type."\',\'mascara\':\'".base64_decode($val->mascaraJS)."\',\'mensaje\':\'".$val->mensaje."\',\'input\':\'".$val->input."\',\'campo\':\'".$val->campo."\'},".PHP_EOL;'.PHP_EOL);
        fputs($ar, '}'.PHP_EOL);
        fputs($ar, '?>'.PHP_EOL);
        fputs($ar, '];'.PHP_EOL);
        if(@count($existe)>0){
            fputs($ar, '    '.All::upperCase($campos['vista']).'.loadListaMenu();' . PHP_EOL);
        }
        fclose($ar);
    }

    private function createFileViewForm($fileViewForm, $campos, $requerido)
    {

        $ar = fopen($fileViewForm, "w+") or die("Problemas en la creaci&oacute;n del view form.php");
        // Inicio la escritura en el activo
        fputs($ar, '<div class="box box-primary">'.PHP_EOL);
        fputs($ar, '<div class="box-header with-border">'.PHP_EOL);
        fputs($ar, '<h3 class="box-title">Formulario de '.$campos['vista_alias'].'</h3>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fputs($ar, '<!-- /.box-header -->'.PHP_EOL);
        fputs($ar, '<!-- form start -->'.PHP_EOL);
        fputs($ar, '<form role="form" method="post" class="'.All::upperCase($campos['vista']).'" id="send'.All::upperCase($campos['vista']).'Procesar" enctype="multipart/form-data">'.PHP_EOL);
        fputs($ar, '   <div class="box-body">'.PHP_EOL);
        $mascara = '';
        $items = $campos;
        // Nota si agregas mas elementos aca debes eliminar ese indice ejemplo como vista_alis
        unset($items['entida']);
        unset($items['vista']);
        unset($items['pk']);
        unset($items['campos']);
        unset($items['vista_alias']);
        //print_r($items); die();


        foreach ($items AS $key=>$value){

            //print_r($value); die();
            if(@$value->restrincion=='PRI' AND !empty($value->restrincion)){
                fputs($ar, '<input type="hidden" id="id" name="'.$value->field.'">'.PHP_EOL);
            }else{
                $classes = '';
                $classes = @self::valTipoMascara($value->mascara);
                //print_r($value->nulo);
                $classes .= ($value->nulo!='YES')?' requerido':'';
                $mostrar = explode('#',$requerido[$value->field]);
                //All::pp($mostrar);
                //   [0][hidden_form] => 1 # [1][hidden_list] => 1 #  [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -

                if($mostrar[0]==0 AND $mostrar[2]==0 AND $mostrar[2]!='combo' AND $mostrar[2]!='grilla') { // mostrar Si y no es relacionado
                    fputs($ar, '<div class="form-group">'.PHP_EOL);
                    fputs($ar, '    <label for="'.$value->label.'">'.$value->label.'</label>'.PHP_EOL);
                    $default = (!empty($value->fijo))?'value="'.$value->fijo.'"':'';
                    $maxlength = ($value->dimension!=-1)?'maxlength="'.$value->dimension.'" data-item="'.$value->dimension.'"':'';
                    fputs($ar, '    <input type="text" name="'.$value->field.'" class="form-control contar '.$classes.'" id="'.$value->field.'" placeholder="'.$value->place_holder.'" '.$default.' '.$maxlength.'>'.PHP_EOL);
                    fputs($ar, '    <i class="help" id="help-'.$value->field.'"></i>'.PHP_EOL);

                    fputs($ar, '</div>'.PHP_EOL);
                }elseif ($mostrar[0]==0 AND $mostrar[2]=='combo'){ // mostrar Si y es relacionado
                    //print_r($mostrar[0].'-'.$mostrar[2].' \n');
                    fputs($ar, '<div class="form-group">'.PHP_EOL);
                    fputs($ar, '    <label for="'.$value->label.'">'.$value->label.'</label>'.PHP_EOL);
                    fputs($ar, '    <select name="'.$value->field.'" class="form-control '.$classes.' '.$mostrar[3].' " id="'.$value->field.'"  placeholder="'.$value->place_holder.'"><option value="">Seleccionar</option></select>'.PHP_EOL);
                    fputs($ar, '    <i class="help" id="help-'.$value->field.'"></i>'.PHP_EOL);
                    fputs($ar, '</div>'.PHP_EOL);
                }
            }
        }
        fputs($ar, '  </div>'.PHP_EOL);
        fputs($ar, '  <!-- /.box-body -->'.PHP_EOL);
        fputs($ar, '   <div class="box-footer">'.PHP_EOL);
        fputs($ar, '       <div class="col-md-4 col-sm-6 col-xs-12 pull-left" id="divDelete"></div>'.PHP_EOL);
        fputs($ar, '       <div class="col-md-4 col-sm-6 col-xs-12 pull-right"><button id="submit" class="btn btn-primary" value="Procesar">Procesar registro.</button></div>'.PHP_EOL);
        fputs($ar, '   </div>'.PHP_EOL);
        fputs($ar, '</form>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fclose($ar);
    }

    /**
     * Method encargado de crear un archivo listado donde se mostrará la grilla
     * @param string $fileViewListado, Indica el archivo donde se mostrara la GRID
     * @param array $campos, indica toda la configuracion de la vista, campos y detalle en general
     * @param array $campRealEnti, indica toda la configuracion de solo los campos reales
     * @return bool true
     */
    private function createFileViewListado($fileViewListado, $campos, $campRealEnti){
        $ar = fopen($fileViewListado, "w+") or die("Problemas en la creaci&oacute;n del view listado.php");
        fputs($ar, '<div class="box box-primary">'.PHP_EOL);
        fputs($ar, '    <div class="box-header with-border">'.PHP_EOL);
        fputs($ar, '        <h3 class="box-title">Lista de '.$campos['vista_alias'].'</h3>'.PHP_EOL);
        fputs($ar, '    </div>'.PHP_EOL);

        // Bloque de filtros de busqueda
        fputs($ar, '    <div id="'.ALL::upperCase($campos['vista']).'" class="listFiltros">'.PHP_EOL);
        foreach ($campRealEnti['campos'] AS $key=>$value){
            //$mostrar = explode('#',$requerido[$value->field]);
            foreach ($campos AS $key2=>$value2){
                //var_dump(@$value2->hidden_list!=1);
                if($value!=$campRealEnti['pk'] AND (@$value2->field==$value AND is_null($value2->hidden_list))){
                    fputs($ar, '    <input type="text" class="search" id="search_'.$value.'" placeholder="'.ALL::upperCase($value).'" onKeyDown="Core.VistaGrid.doSearch(arguments[0]||event,\''.ALL::upperCase($campos['vista']).'\')">'.PHP_EOL);
                    break;
                }
            }

        }
        fputs($ar, '        <button onClick="Core.VistaGrid.reloadGrid(\''.ALL::upperCase($campos['vista']).'\')" id="submitButton'.ALL::upperCase($campos['vista']).'" style="margin-left:30px;">Buscar</button>'.PHP_EOL);
        //fputs($ar, '//<!--input type="checkbox" id="autosearch" onClick="Core.VistaGrid.enableAutosubmit(this.checked,\'Pppp\');"> Enable Autosearch-->'.PHP_EOL);
        fputs($ar, '        <?php IRON\Core\Http\SegCSRF::getTokenField(); ?>'.PHP_EOL);
        fputs($ar, '    </div>'.PHP_EOL);

        // Bloque de Grid del contenido
        fputs($ar, '    <div class="box-body">'.PHP_EOL);
        fputs($ar, '        <div id="dataIRON'.ALL::upperCase($campos['vista']).'" class="listGrid"></div>'.PHP_EOL);
        fputs($ar, '        <div id=\'pagingArea'.ALL::upperCase($campos['vista']).'\'></div>'.PHP_EOL);
        fputs($ar, '        <div id=\'recfound'.ALL::upperCase($campos['vista']).'\'></div>'.PHP_EOL);
        fputs($ar, '    </div>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fclose($ar);
        return true;
    }

    /**
     * Method encargado de crear un archivo JsonMascara donde se mostrará las mascaras de la vista
     * @param string $fileViewJsonMascaras, Indica donde se debe general la vista generada
     * @campos array $campos, indica toda la configuracion de la vista, campos y detalle en generall
     * @return bool true
     */
    private function createFileViewJsonMascaras($fileViewJsonMascaras, $mascaras){
        $ar = fopen($fileViewJsonMascaras, "w+") or die("Problemas en la creaci&oacute;n del view listado.php");
        fputs($ar, '{'.PHP_EOL);
        fputs($ar, '    "mascaras" : ['.PHP_EOL);
        $tmpItem = '';
        foreach ($mascaras AS $key => $value){
            if($value->hidden=='NO') {
                $tmpItem .= '{"type":"' . $value->type . '","mascaraJS":"' . base64_encode($value->mascaraJS) . '","mascaraPHP":"' . base64_encode($value->mascaraPHP) . '","mensaje":"' . $value->mensaje . '","input":"' . $value->clase_input . '","campo":"' . $value->label . '"},';
            }
        }
        $tmp = All::deleteEndCaracter($tmpItem);
        fputs($ar, '        '.$tmp.PHP_EOL);
        fputs($ar, '    ]'.PHP_EOL);
        fputs($ar, '}'.PHP_EOL);
        fclose($ar);
        return true;
    }

    /**
     * Method encargado de procesar los datos del listado NO disponible
     * @deprecated
     */
    private function createFileViewListadoOld($rutaHija, $campos, $requerido)
    {
        $ar = fopen($rutaHija.DIRECTORY_SEPARATOR."listado.php", "w+") or die("Problemas en la creaci&oacute;n del view listado.php");
        // Inicio la escritura en el activo
        $items = $campos;
        unset($items['entida']);
        unset($items['vista']);
        unset($items['pk']);
        unset($items['campos']);

        fputs($ar, '<div class="box box-primary">'.PHP_EOL);
        fputs($ar, '<div class="box-header with-border">'.PHP_EOL);
        fputs($ar, '<h3 class="box-title">Listado de '.$campos['vista'].'</h3>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fputs($ar, '<!-- /.box-header -->'.PHP_EOL);
        fputs($ar, '<!-- form start -->'.PHP_EOL);
        fputs($ar, '<div class="box-body">'.PHP_EOL);
        fputs($ar, '    <table id="dataIRON'.ALL::upperCase($campos['vista']).'" class="table table-bordered table-striped">'.PHP_EOL);
        // Listado de cabecera
        fputs($ar, '       <thead>'.PHP_EOL);
        fputs($ar, '        <tr>'.PHP_EOL);
        foreach ($items AS $key=>$value){
            $mostrar = explode('#',$requerido[$value->field]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
            if($mostrar[1]!=1) {
                fputs($ar, '            <th>' . $value->label . '</th>' . PHP_EOL);
            }
        }
        fputs($ar, '        </tr>'.PHP_EOL);
        fputs($ar, '       </thead>'.PHP_EOL);

        //Listado footer
        fputs($ar, '       <tfoot>'.PHP_EOL);
        fputs($ar, '        <tr>'.PHP_EOL);
        foreach ($items AS $key=>$value){
            $mostrar = explode('#',$requerido[$value->field]);
            //   [0][hidden_form] => 1 # [1][hidden_list] => 1 # [2][relacionado] (grilla|combo) # [3][tabla_vista] personal--personalD1 # [4][vista_campo] id # [5] [$cart_separacion] -
            if($mostrar[1]!=1) {
                fputs($ar, '            <th>' . $value->label . '</th>' . PHP_EOL);
            }
        }
        fputs($ar, '       </tr>'.PHP_EOL);
        fputs($ar, '       </tfoot>'.PHP_EOL);
        fputs($ar, '   </table>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fputs($ar, '</div>'.PHP_EOL);
        fclose($ar);
    }
    private function existsRuta($archivoRoute,$controller)
    {
        $config = simplexml_load_file($archivoRoute);
        foreach($config->link AS $key=>$value){
            //echo 'corrida:'.$value->name.'-- Comparar:'.'/'.All::cameCase($controller).'Index'.'<br>';
            //die('/'.All::cameCase($controller).'Index');
            if($value->name=='/'.All::cameCase($controller).'Index'){
                return true;
            }
        }
        return false;
    }
    /**
     * Method encargado de procesar todos las mascaras de tipo de datos del sistema generador desde la interfaz
     * @param string $mascara
     * @return string $classes;
     */
    private function valTipoMascara($mascara)
    {
        switch ($mascara){
            case 'integer':
                $classes ='integer';
            break;
            case 'text':
                $classes ='texto';
            break;
            case 'color':
                $classes ='color';
            break;
            default:
                $classes = $mascara;
            break;
        }
        return $mascara;
    }

}

