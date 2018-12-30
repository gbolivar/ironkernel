<?php
namespace IRON\Core\Commun;

/**
 * Clase encargada de gestionar todas las contantes de toda la estructura del sistema
 * Recomendable no modificarla puede influir el funcionamiento del sistema
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 25/07/2017
 * @Updated Date: 20/01/2018
 * @version: 1.0
 */

interface Constant 
{
    // Const Cache
    const DIR_INT = __DIR__ . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    // Const Cache
    const DIR_INT2 = __DIR__ . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

    // Const src de la carpeta donde estan las aplicaciones
    const DIR_SRC = Constant::DIR_INT.'src'.DIRECTORY_SEPARATOR;

    // Const de la la carpeta config, donde esta las configuraciones principal del sistema
    const DIR_CONFIG = Constant::DIR_INT."config".DIRECTORY_SEPARATOR;

    // Const donde estas el thema principal del sistema theme
    const DIR_THEME = Constant::DIR_INT.'theme'.DIRECTORY_SEPARATOR;

    // Const la carpeta web
    const DIR_WEB = Constant::DIR_INT.'web'.DIRECTORY_SEPARATOR;

    // Const Version del la Arquitectura
    const VERSION = "V1";
    const FW = "IRON";
    const IDM = "es";

    // Constant necesaria para la generación de la aplicaciones

    // Const Cache
    const APP_CACHE = DIRECTORY_SEPARATOR.'Cache';

    // Const Router
    const APP_ROUTE = DIRECTORY_SEPARATOR.'Router';

    // Const Command
    const APP_COMMAND = DIRECTORY_SEPARATOR.'Command';

    // Const Command File extends
    const FILE_COMMAND = 'Commands';

    // Const Valor Command
     const APP_VALOR_COMMAND = 'ironcore';

    // Const Model
    const APP_MODEL = DIRECTORY_SEPARATOR.'Model';

    // Const Constrolador
    const APP_CONTR = DIRECTORY_SEPARATOR.'Controller';

    // Const Templates Principal de una apps
    const APP_VIEWS = DIRECTORY_SEPARATOR.'Templates';

    // Const Vista personalizadas del home
    const APP_VHOME = DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'home';

    // Const Vistas del autogeneradas
    const APP_VISTA = DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'vistas';

    // Const de carpeta de enlaces symbolico extends
    const APP_TKEYS = DIRECTORY_SEPARATOR.'extends';

    // Minimo de PHP requerimienro en el php
    const PHP_VER_REQ = '7.0.0';

    // Request Methods
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';

    // Server Path Web
    const PATH_SERVE = 'public'.DIRECTORY_SEPARATOR;

    // Valores del Registro de log de Acciones
    const LOG_CONS = 'Consulta de Registro';
    const LOG_ALTA = 'Alta de Registro';
    const LOG_MODI = 'Actualizacion de Registro';
    const LOG_BAJA = 'Baja de Registro';

    // Valores del LogFile
    const LOG_DIR = Constant::DIR_INT.'logs'.DIRECTORY_SEPARATOR;
    const LOG_RECYCLING_MODEL_DIR = Constant::DIR_INT.'logs'.DIRECTORY_SEPARATOR.'Recycling'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
    const LOG_RECYCLING_CONSTROLLER_DIR = Constant::DIR_INT.'logs'.DIRECTORY_SEPARATOR.'Recycling'.DIRECTORY_SEPARATOR.'Controller'.DIRECTORY_SEPARATOR;
    const LOG_RECYCLING_VISTAS_DIR = Constant::DIR_INT.'logs'.DIRECTORY_SEPARATOR.'Recycling'.DIRECTORY_SEPARATOR.'Vistas'.DIRECTORY_SEPARATOR;
    const LOG_RECYCLING_DB_DIR = Constant::DIR_INT.'logs'.DIRECTORY_SEPARATOR.'Recycling'.DIRECTORY_SEPARATOR.'DataBase'.DIRECTORY_SEPARATOR;


}

?>
