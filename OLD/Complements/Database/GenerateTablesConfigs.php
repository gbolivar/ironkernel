<?php
namespace IRON\Complements\Database;
use IRON\Core\Commun\{Commun,Constant};
use IRON\Core\Configuration;

/**
 * Clase encargada de crear o regenerar las entidades de config
 * @author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 19/08/2017
 * @version: 0.1
 */

trait GenerateTablesConfigs  {
    public $sql;
    public function constructTablesConfigs()
    {

        $a=$this->createTableEntidad();
        print_r($a);
    }

    public function createTableEntidad(){
        switch ($this->motor){
            case 'sql':
                $sql  ='CREATE TABLE ho_entidad (';
                $sql .=' id int IDENTITY(1,1) NOT NULL,';
                $sql .=' name varchar(255) NOT NULL,';
                $sql .=' crud bit NOT NULL';
                $sql .=');';
                break;
            default:
                break;
        }
        return $sql;
    }
}

?>
