<?php
namespace JPH\Complements\Database;
use JPH\Complements\Database\Query AS Query;
use JPH\Core\Commun\{
    All, Logs
};
/**
 * Representa un modelo generico algo similar a orm, para setiar valores campos y tablas
 */
class Comun{


    public $database;
    public $db;
    public $query;
    public $campoid;
    public $tabla;
    public $campos;
    public $json;
    public $todos;
    public $where;
    public $usuario;
    public $active;
    use Logs;


    public function __construct($id = false)
    {


        $this->database = $this->db;
        $this->db = get_class($this);
        $this->id = true;
        $this->active = 'Model';
        $this->query = new Query();

        
    }



}

?>