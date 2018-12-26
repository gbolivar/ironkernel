<?php
namespace IRON\Complements\Database;
/**
 * Configuracion de las conexiones bb IRON V1
 * @propiedad: IRON V1
 * @utor: Gregorio Bolivar <elalconxvii@gmail.com>
 * @created: 24/12/2018
 * @version: 1.0
 */ 

trait ConfigDatabase
{

  public $motor;
  public $host;
  public $port;
  public $db;
  public $user;
  public $pass;
  public $encoding;
  function __construct()
  {
   $this->motor;
   $this->host;
   $this->port;
   $this->db;
   $this->user;
   $this->pass;
   $this->encoding;
 }

  /** Inicio  del method  de default  */
  public function default()
  {
   // Driver de Conexion con la de base de datos
   $this->motor = 'pgsql';
   // IP o HOST de comunicacion con el servidor de base de datos
    $this->host = 'localhost';
   // Puerto de comunicacion con el servidor de base de datos
   $this->port = '5432';
   // Nombre base de datos
   $this->db = 'balanza_db';
   // Usuario de acceso a la base de datos
   $this->user = 'jph';
   // Clave de acceso a la base de datos
   $this->pass = 'Jphl10n$135';
   // Codificacion de la base de datos
   $this->encoding = 'UTF-8';
   return $this;
  }
  /** Fin del caso del method de default */
}

?>