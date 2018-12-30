<?php
namespace IRON\Complements\Database;
use IRON\Core\Commun\All;
use IRON\Core\Commun\Constant;
use IRON\Core\Store\Cache;
use Medoo\Medoo;


/**
 * Clase integradora de herencia multiple de la conexion a base de datos
 * @author: Ing Gregorio Bolivar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @creation Date: 07/08/2017
 * @version: 4.1
 */

class Base implements Constant
{
    use GenerateConexion, GenerateTablesConfigs;

    public $idx;
    public $active;
    public $base;
    public $db;
    public $msj;

    public function start(String $index='')
    {
        try{
            // Construir las variables de conexion
            $this->constructConexion();

            $this->active = 'Core';
            // Permite instanciar la class app

            // Capturar las variables pasadas a la clase directamente por parametro
            $this->idx = $index;//func_get_args();

           // Verificar que la instancia de la clsse tenga valor definido por parametro
            $tmp = (!empty($this->idx))?$this->idx:'';

            // Clases donde fue instanciada
            $instan = get_class($this);

            // Instancia a la conexion dinamica
            $conn = new ConfigDatabase();
            
            // Verificar que solo sea instanciado desde el modelo o el Core si lo hace de otra parte lanza exepcion
            if(\preg_match('/Model/',$instan) OR \preg_match('/Core/',$instan)){
                if(!empty($tmp)){

                    $ext = method_exists($this,strtolower($tmp));
                    if(!$ext){
                        $obj = array('idxConexion' => $tmp);
                        $msj = All::getMsjException($this->active, 'idx-conn-no-existe',$obj);
                        throw new \TypeError($msj.__class__.'-'.__line__);
                    }
                    //$this->base = new Core($tmp);
                    // Conexion enviada por parametro
                    $indx = $tmp;
                    $datos = $conn->$indx();

                }else{
                    $datos = $conn->default();
                }

                if((bool)Cache::get('conecDb')) {
                    //die($datos->pass);
                    try {
                        $this->base = new Medoo([
                            'database_type' => $datos->motor,
                            'database_name' => $datos->db,
                            'server' => $datos->host,
                            'username' => $datos->user,
                            'password' => $datos->pass,
                            'port' => $datos->port
                        ]);
                        //echo $this->base->info()['dsn'];

                    } catch (\PDOException $e) {

                        // Output expected PDOException.
                        die('El identificador de conexion(<b>'.$this->idx.'</b>), '.$e->getMessage());
                    } 
                    
        
   
                }else{
                    $this->msj = 'La conexion a base de datos no esta activada, debes activar [connecDB=true] en app.ini ';
                    throw new \TypeError($this->msj);
                }

            }else{
                $obj = array();
                $msj = All::getMsjException($this->active, 'error-stnd-connect',$obj);
                throw new \TypeError($msj);
            }

        }catch (\TypeError $t){
            die(__class__.'('.__line__.'):'.$t->getMessage());
        }
        return $this;
    }
}
