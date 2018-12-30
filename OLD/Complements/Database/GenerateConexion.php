<?php
  namespace IRON\Complements\Database;
  use IRON\Core\Load\Configuration;
  use IRON\Core\Commun\Constant;

  /**
   * Clase encargada de regenerar la conexion a base de datos permite multiple
   * @author: Gregorio Bolívar <elalconxvii@gmail.com>
   * @author: Blog: <http://gbbolivar.wordpress.com>
   * @Creation Date: 5/08/2017
   * @version: 2.1
   */

  trait GenerateConexion
  {
      public function constructConexion()
      {
          // Construimos automaticamente el archivo ConfigDatabaseTMP.php
          $this->constructConfigDataBase();
          // Validamos que el archivo temporal creado anteriormente sea el
          // mismo de la conexion de lo contrario procedemos a copiar el tmp
          $this->validateFileIdentico();
          $file = __DIR__ . DIRECTORY_SEPARATOR . 'ConfigDatabase.php';

      }

      private function constructConfigDataBase()
      {
          $file = Configuration::fileConfigApp();
          $file = $file['db'];
          if (file_exists($file)) {
              $config = parse_ini_file($file, true);
              $ar = fopen(__DIR__ . DIRECTORY_SEPARATOR . "ConfigDatabaseTmp.php", "w+") or die("Problemas en la creaci&oacute;n del archivo  " . $file);
              // Inicio la escritura en el activo
              fputs($ar, '<?php'.PHP_EOL);
              fputs($ar, 'namespace IRON\Complements\Database;'.PHP_EOL);
              fputs($ar, '/**'. PHP_EOL);
              fputs($ar, ' * Configuracion de las conexiones bb ' . Constant::FW . ' ' . Constant::VERSION . ''.PHP_EOL);
              fputs($ar, ' * @propiedad: ' . Constant::FW . ' ' . Constant::VERSION . ''.PHP_EOL);
              fputs($ar, ' * @utor: Gregorio Bolivar <elalconxvii@gmail.com>'.PHP_EOL);
              fputs($ar, ' * @created: ' . date('d/m/Y') . ''.PHP_EOL);
              fputs($ar, ' * @version: 1.0'. PHP_EOL);
              fputs($ar, ' */ '.PHP_EOL.PHP_EOL);

              // capturador del get que esta pasando por parametro
              fputs($ar, 'trait ConfigDatabase'.PHP_EOL);
              fputs($ar, "{".PHP_EOL.PHP_EOL);
              fputs($ar, '  public $motor;'.PHP_EOL);
              fputs($ar, '  public $host;'.PHP_EOL);
              fputs($ar, '  public $port;'.PHP_EOL);
              fputs($ar, '  public $db;'.PHP_EOL);
              fputs($ar, '  public $user;'.PHP_EOL);
              fputs($ar, '  public $pass;'.PHP_EOL);
              fputs($ar, '  public $encoding;'.PHP_EOL);
              fputs($ar, '  function __construct()'.PHP_EOL);
              fputs($ar, "  {".PHP_EOL);
              fputs($ar, '   $this->motor;'.PHP_EOL);
              fputs($ar, '   $this->host;'.PHP_EOL);
              fputs($ar, '   $this->port;'.PHP_EOL);
              fputs($ar, '   $this->db;'.PHP_EOL);
              fputs($ar, '   $this->user;'.PHP_EOL);
              fputs($ar, '   $this->pass;'.PHP_EOL);
              fputs($ar, '   $this->encoding;'.PHP_EOL);
              fputs($ar, ' }'.PHP_EOL.PHP_EOL);
              foreach ($config as $key => $value):
                  fputs($ar, '  /** Inicio  del method  de ' . $key . '  */'.PHP_EOL);
                  fputs($ar, '  public function ' . $key . '()'.PHP_EOL);
                  fputs($ar, "  {".PHP_EOL);
                  fputs($ar, "   // Driver de Conexion con la de base de datos".PHP_EOL);
                  //self::validateDriverConexion($value['motor']);
                  fputs($ar, '   $this->motor = \'' . $value['motor'] . '\';'.PHP_EOL);
                  fputs($ar, "   // IP o HOST de comunicacion con el servidor de base de datos".PHP_EOL);
                  fputs($ar, '    $this->host = \'' . $value['host'] . '\';'.PHP_EOL);
                  fputs($ar, "   // Puerto de comunicacion con el servidor de base de datos".PHP_EOL);
                  fputs($ar, '   $this->port = \'' . $value['port'] . '\';'.PHP_EOL);
                  fputs($ar, "   // Nombre base de datos".PHP_EOL);
                  fputs($ar, '   $this->db = \'' . $value['db'] . '\';'.PHP_EOL);
                  fputs($ar, "   // Usuario de acceso a la base de datos".PHP_EOL);
                  fputs($ar, '   $this->user = \'' . $value['user'] . '\';'.PHP_EOL);
                  fputs($ar, "   // Clave de acceso a la base de datos".PHP_EOL);
                  fputs($ar, '   $this->pass = \'' . $value['pass'] . '\';'.PHP_EOL);
                  fputs($ar, "   // Codificacion de la base de datos".PHP_EOL);
                  fputs($ar, '   $this->encoding = \'' . $value['encoding'] . '\';'.PHP_EOL);
                  fputs($ar, '   return $this;'.PHP_EOL);
                  fputs($ar, '  }'.PHP_EOL);
                  fputs($ar, '  /** Fin del caso del method de ' . $key . ' */'.PHP_EOL);
              endforeach;
              fputs($ar, "}".PHP_EOL.PHP_EOL);
              fputs($ar, "?>");
              // Cierro el archivo y la escritura
              fclose($ar);
          } else {
              throw new Exceptions('El archivo <b>ConfigDatabase.php</b> se esta construyendo.');
          }
          return true;
      }

      /**
       * Permite validar que los archivos sean identico basado en el contenido de MD5
       * @access private
       * @return boolean
       */
      private function validateFileIdentico()
      {
          $fileCofNol = __DIR__ . '/ConfigDatabase.php';
          $fileTmpNol = __DIR__ . '/ConfigDatabaseTmp.php';
          $fileCofMd5 = md5(@file_get_contents($fileCofNol));
          $fileTmpMd5 = md5(file_get_contents($fileTmpNol));
          if ($fileCofMd5 != $fileTmpMd5) {
              copy($fileTmpNol, $fileCofNol);
          }
          return true;
      }

      /*private function validateDriverConexion($driv){
        $drivers = PDO::getAvailableDrivers();
        if (!in_array($driv, $drivers,true)) {
          throw new typeError('El archivo <b>databases.ini</b> solicitaron el driver <b>'.$driver.'</b> por PDO que no esta soportado por el servidor.');
        }
      }*/
  }