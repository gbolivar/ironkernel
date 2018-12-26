<?php
namespace IRON\Core\Console;
use IRON\Core\Commun\All;
/**
 * Permite ejecutar el proyecto desde interprete de comando
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 30/08/2017
 * @version: 0.1
 */
class ServerInternoIron
{
    public $active;
    public $host;
    public $p;
    public function __construct()
    {
        $this->active = 'Server';
    }

    public function start($ip='localhost',$port=8000){

        if(!empty($ip) AND All::validateRows('IP',$ip)){
            $host = $ip;
        }else{
            $host='localhost';
        }
        if(!empty($port) AND ($port>=80 AND $port<=9999)){
            $p=$port;
        }else{
            $p=8000;
        }
        $this->comando =  'php -S '.$host.':'.$p.' -t '.All::PATH_SERVE.' ';
        $msj=PreInterprete::getMsjConsoleInterno($this->active,'server-start');
        $fwv=All::FW.' - '.All::VERSION;
        $fwv.="\n \n";
        $menj = $fwv.All::mergeTaps($msj,array('host'=>$host,'port'=>$p));
        print($menj);
        $t = system($this->comando);
        return $t;
    }
}