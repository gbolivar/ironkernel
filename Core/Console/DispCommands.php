<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\All;

/**
 * Permite integrar un conjunto de funcionalidades del console de sistema 
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 09/08/2017
 * @Update Date: 09/08/2018
 * @version: 2.4
 */

class DispCommands
{

        /**
         * Argumentos integrador del console de sistema con varios parametros de respuesta
         * @param string $argv, argumentos del terminal
         */
        protected function arguments(Array $argv)
        {
            try{

                $v = count(@$argv);
                // Optiones del menu con todos los valores del menu
                $inpre = new PreInterprete();
                $command = str_replace('.\\','',$argv[0]);
                if($v==1 AND ($command==All::APP_VALOR_COMMAND))
                {
                    // Limpiando los valores del arreglo principal que sea enviado Ironcore o .\Ironcore
                    $valor = str_replace('.\\','',$argv[0]);
                    $vist = $inpre->getConfigJson($command,'all');
                    $inpre->setValor($vist);
                }
                elseif ($v==3 AND $argv[1]=='app' AND !empty($argv[2])) 
                {
                    $app = new App();
                    $vist = $app->createStructura($argv[2]);
                    $inpre->setValor($vist);
                }
                elseif ($v==4 AND $argv[1]=='app:public' AND !empty($argv[2]))
                {
                    $link = new Symbolico();
                    $vist=$link->filesWebPublic($argv[2]);
                    $inpre->setValor($vist);
                }
                elseif ($v==4 AND $argv[1]=='command:app' AND !empty($argv[2]) AND !empty($argv[3]))
                {
                    $app = new App();
                    $vist = $app->createCommands($argv[2],$argv[3]);
                    $inpre->setValor($vist);

                }
                elseif ($v==4 AND $argv[1]=='command:app:run' AND !empty($argv[2]) AND !empty($argv[3]))
                {
                    Terminal::runCommands($argv[2] ,$argv[3]);

                }
                elseif ($v==4 AND $argv[1]=='app:model' AND !empty($argv[2]) AND !empty($argv[3]))
                {
                    $model = new App();
                    $vist = $model->createStructuraFileModel($argv[2],$argv[3]);
                    $inpre->setValor(($vist));
                }
                elseif ($v==4 AND $argv[1]=='app:controller')
                {
                    $model = new App();
                    $vist = $model->createStructuraFileController($argv[2],$argv[3]);
                    $inpre->setValor($vist);
                }
                elseif ($v==4 AND $argv[1]=='app:crud' AND !empty($argv[2]) AND !empty($argv[3]))
                {
                    $crud = new AppCrud();
                    $vist = $crud->createStructuraFileCRUD($argv[2],$argv[3]);
                    $inpre->setValor($vist);
                }
                elseif ($v==2 AND $argv[1]=='app:list')
                {
                    $list = new App();
                    $vist = $list->showApps();
                    $inpre->setValor($vist);
                }
                elseif ($v==2 AND $argv[1]=='cache:clean')
                {
                    $cache = new Cache();
                    $msj = $cache->cleanCacheApps();
                    var_dump($msj);
                    //$inpre->setValor($msj);
                }
                elseif(($v>=2 AND $v<=6 AND $argv[1]=='server') OR @$argv[2]=='--host' OR @$argv[4]=='--port')
                {
                    $temp = new ServerInternoIron();
                    $msj=$temp->start(@$argv[3],@$argv[5]);
                    $inpre->setValor($msj);

                }
                elseif ($v==2 AND $argv[1]=='system:initialize')
                {
                    $temp= new System();
                    $msj = $temp->cleanSystemInitialize();
                    $inpre->setValor($msj);
                }
                elseif ($v==2 AND $argv[1]=='system:refresh')
                {
                    $temp= new System();
                    $msj = $temp->cleanSystemRefresh();
                    $inpre->setValor($msj);
                }
                else
                {
                    $a = $inpre->getLogoAscii();
                    $inpre->setValor($a);
                }
                $inpre->showOptions();
            }catch (\TypeError $t){
                die($t);
            }
        }
}

