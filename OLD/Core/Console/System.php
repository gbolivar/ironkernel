<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\{All,Logs};
use IRON\Complements\Database\Base;
use IRON\Core\Console\App;


class System extends Base
{
    public $pathapp;
    public $msj;
    public $active;
    use Logs;

    // Constante de la clase
    const SUBITEM = __CLASS__;
    public function __construct(){
        $this->pathapp = All::DIR_SRC;
        $this->active = All::onlyClassActive(App::SUBITEM);
        // Constructor del Base para base de datos
        parent::__construct();
    }

    /**
     * Permite limpiar toda la base de datos y dejarla como como por primera vez
     */
    public function cleanSystemRefresh() : void
    {
         $msj=PreInterprete::getMsjConsoleInterno('Commands','system-refresh-init');
         $msj=All::mergeTaps($msj,array());
         $this->logInfo("##### ".$msj." #####"); echo $msj;

         // Verificar el environment antes de procesar
         $elemento = self::getTablasSystem();

         // Hacer backup de base de datos
         self::backupSql();

         // Todo el esquema de la conexion
         $elementoGeneral = $this->informationschema();
         foreach ($elementoGeneral AS $item=>$value){
             if($value->TABLE_TYPE=='BASE TABLE'){

                 foreach ($elemento AS $item2=>$value2){
                     if($value->TABLE_NAME == $value2['tabla']){
                         // Permite verificar si la tabla se le hace truncate
                         $msj = '';
                         $msj=PreInterprete::getMsjConsoleInterno('Commands','system-refresh');
                         if($value2['truncar']=='SI'){
                             $msj=All::mergeTaps($msj,array('tabla'=>$value2['tabla'],'truncate'=>$value2['truncar']));
                             $this->logInfo($msj); echo $msj;
                             $sql = "TRUNCATE TABLE ".$value2['tabla'].";";
                             $this->executeQuery($sql);
                         }else{
                             $msj=All::mergeTaps($msj,array('tabla'=>$value2['tabla'],'truncate'=>$value2['truncar']));
                             $this->logInfo($msj); echo $msj;
                         }
                     }
                }

                 $tempValue = self::getValidateTableNoExistente($value->TABLE_NAME,$elemento);
                 if($tempValue){
                     $msj=PreInterprete::getMsjConsoleInterno('Commands','system-refresh-eliminar');
                     $msj=All::mergeTaps($msj,array('tabla'=>$value->TABLE_NAME));
                     $this->logInfo($msj); echo $msj;
                     self::removeFileModel($value->TABLE_NAME);
                     self::removeFileController($value->TABLE_NAME);
                     self::removeFileVistas($value->TABLE_NAME);


                     $sql = "DROP TABLE ".$value->TABLE_NAME.";";
                     $this->executeQuery($sql);
                 }
            }

         }
        $this->logInfo("##### Fin del proeceso #####"); die();
    }

    /**
     * Permite validar las tablas que son las que se van a eliminar de estas manera el sistema quedara como nuevo para su uso
     * @param String $search, Valor de la tabla que vamos a buscar su existenia
     * @param Array $tablas, Valor de tablas que estan definidas reservadas por el sistema
     * @return Bool $valor, permite devolver true si la tabla a buscar no existe y false si existe
     */
    private static function getValidateTableNoExistente(String $search ,Array $tablas):Bool
    {
         $valor = 0;
         $tmpValor = [];
         foreach($tablas AS $item=>$tabla){
             array_push($tmpValor,$tabla['tabla']);
         }
         if (in_array($search, $tmpValor)) {
             $valor = 0;
         }else{
             $valor = 1;
         }
         return $valor;
    }


    private function removeFileModel(String $nameFile): bool
    {
        $dirOrigen = All::APP_MODEL;
        $dir = All::LOG_RECYCLING_MODEL_DIR;
        $fileExt = All::upperCase($nameFile)."Model.php";
        self::logCambiosMoverArchivosLogConstVistaModel($dirOrigen, $dir, $fileExt);
        return true;
    }

    private function removeFileController(String $nameFile): bool
    {
        $dirOrigen = All::APP_CONTR;
        $dir = All::LOG_RECYCLING_CONSTROLLER_DIR;
        $fileExt = All::upperCase($nameFile)."Controller.php";
        self::logCambiosMoverArchivosLogConstVistaModel($dirOrigen, $dir, $fileExt);
        return true;
    }

    private function removeFileVistas(String $nameFile): bool
    {
        $dirOrigen = All::APP_VISTA;
        $dir = All::LOG_RECYCLING_VISTAS_DIR;
        $fileExt = All::cameCase($nameFile);
        self::logCambiosMoverArchivosLogConstVistaModel($dirOrigen, $dir, $fileExt);
        return true;

    }

    private function backupSql()
    {
        $dir = 'E:\backup\\';
        $fileCambTmp = $dir.str_replace(array(' ',':'),array('_',''),All::now());
        All::mkddir($fileCambTmp);
        $backup_file = $fileCambTmp.DIRECTORY_SEPARATOR.$this->db.".bak";
        $sql = "BACKUP DATABASE ".$this->db." TO DISK = '".$backup_file."';";
        //$this->get($sql);
        return true;
    }

    private function logCambiosMoverArchivosLogConstVistaModel(String $dirOrigen, String $dirDestino, String $fileExt  )
    {
        $apps = new App();
        $listApps = $apps->showAppsList();
        $tmpFile = $fileExt;
        $rutaLog = $dirDestino;
        $fileCambTmp = $rutaLog.str_replace(array(' ',':'),array('_',''),All::now());
        foreach ($listApps AS $item => $valor){
            $tmp = All::DIR_SRC.$valor.$dirOrigen.DIRECTORY_SEPARATOR.$tmpFile;
            if (file_exists($tmp)) {
                All::mkddir($fileCambTmp);
                rename($tmp,$fileCambTmp.DIRECTORY_SEPARATOR.$tmpFile);
                $msj=PreInterprete::getMsjConsoleInterno('Commands','system-refresh-existe');
                $msj=All::mergeTaps($msj,array('file'=>$tmp));
                $this->logInfo($msj);
            }else{
                $msj=PreInterprete::getMsjConsoleInterno('Commands','system-refresh-no-existe');
                $msj=All::mergeTaps($msj,array('file'=>$tmp));
                $this->logError($msj);
            }
        }
    }

    private static function getTablasSystem()
    {
       $tconfig = array(
            array('tabla'=>'ho_conexiones','truncar'=>'SI'),
            array('tabla'=>'ho_entidades','truncar'=>'SI'),
            array('tabla'=>'ho_vistas','truncar'=>'SI'),
            array('tabla'=>'ho_keyboard','truncar'=>'SI'),
            array('tabla'=>'ho_mascaras','truncar'=>'NO'),
            array('tabla'=>'ho_menu','truncar'=>'SI'),
            array('tabla'=>'ho_tipo_dato','truncar'=>'NO'),
            array('tabla'=>'seg_cambio_clave','truncar'=>'SI'),
            array('tabla'=>'seg_log_autenticacion','truncar'=>'SI'),
            array('tabla'=>'seg_log_eventos','truncar'=>'SI'),
            array('tabla'=>'seg_perfil','truncar'=>'NO'),
            array('tabla'=>'seg_perfil_roles','truncar'=>'SI'),
            array('tabla'=>'seg_roles','truncar'=>'SI'),
            array('tabla'=>'seg_usuarios','truncar'=>'NO'),
            array('tabla'=>'seg_usuarios_perfil','truncar'=>'SI')
        );
        return $tconfig;

    }

    /**
     * Permite inicializar las tablas principales del sistema y hacer la instalacion base
     */
    public function cleanSystemInitialize() : void
    {
        $ini = self::getTablasStaticSystem();
        foreach ($ini AS $item => $tablas){
            $this->getAll($tablas['date']);
        }
        die();
    }


    /**
     * Permite procesar los datos que se van a setear los valores
     */
    private static function getTablasStaticSystem(){
        $config = array(
             array('table'=>'ho_conexiones',
             'date'=>'CREATE TABLE ho_conexiones (
                        id int Identity(1,1) NOT NULL,
                        label varchar(20) NOT NULL,
                        driver varchar(30) NOT NULL,
                        host varchar(20) NOT NULL,
                        db varchar(30) NOT NULL,
                        usuario varchar(15) NOT NULL,
                        clave varchar(50) NOT NULL,
                        CONSTRAINT pk_ho_conexiones PRIMARY KEY(id)
                      );
                      ALTER TABLE ho_conexiones
                      ADD UNIQUE (label,driver,host);'),
             array('table'=>'ho_entidades',
             'date'=>'CREATE TABLE ho_entidades (
                        id BIGINT Identity(1,1) NOT NULL,
                        conexiones_id int NOT NULL,
                        entidad varchar(64) NOT NULL,
                        field varchar(100) NOT NULL,
                        type varchar(20) NOT NULL,
                        nulo varchar(10) NOT NULL,
                        dimension int,
                        fijo varchar(60) NULL,
                        restrincion varchar(10),
                        CONSTRAINT pk_ho_entidades PRIMARY KEY(id)
                      );
                      ALTER TABLE ho_entidades
                        ADD UNIQUE (conexiones_id,tabla,field);'),
            array('table'=>'ho_vistas',
                'date'=>'CREATE TABLE ho_vistas (
                    id BIGINT Identity(1,1),
                    apps varchar(50) NOT NULL,
                    conexiones_id int NOT NULL,
                    entidad varchar(64) NOT NULL,
                    nombre varchar(64) NOT NULL,
                    field varchar(64) NOT NULL,
                    type varchar(20) NOT NULL,
                    dimension int,
                    fijo varchar(60) NULL,
                    restrincion varchar(10),
                    label varchar(50) NOT NULL,
                    mascara varchar(50) NOT NULL,
                    nulo varchar(3) NOT NULL,
                    place_holder varchar(14),
                    relacionado varchar(10),
                    tabla_vista varchar(50) NULL,
                    vista_campo varchar(50),
                    orden int,
                    hidden_form bit ,
                    hidden_list bit ,
                    procesado bit,
                    CONSTRAINT pk_ho_vistas PRIMARY KEY(id)
                  );
                  ALTER TABLE ho_vistas
                   ADD UNIQUE  (conexiones_id, entidades_tabla,nombre,field,label,mascara);')
        );
        return $config;
    }

}