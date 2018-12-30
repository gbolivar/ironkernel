<?php
/*
  Clase encargada de procesar patron de diseño Faactory para encapsular elementos
*/
namespace IRON\Tools\Times;


Trait Main
{
	static public $todo;

	/**
	 * Permite mostrar solo la fecha actual 
	 * @return fecha, imprime la fecha actual en formato Y-m-d
	 */
	static function date()
	{
		$date = new \DateTime();
        $fecha = $date->format('Y-m-d');
        return $fecha;
	}

	/**
	 * Permite mostrar solo la hora actual 
	 * @return fecha, imprime la fecha actual en fornato H:i:s
	 */
	static function time()
	{
		$date = new \DateTime();
        $fecha = $date->format('H:i:s');
        return $fecha;
	}

	/**
	 * Permite mostrar solo la fecha y hora actual 
	 * @return fecha, imprime la fecha actual, en formato Y-m-d H:i
	 */
	static function datetime()
	{
		$date = new \DateTime();
        $fecha = $date->format('Y-m-d H:i');
        return $fecha;
	}

	/**
	 * Permite mostrar solo la fecha y hora con micreosegundos actual 
	 * @return fecha, imprime la fecha, hora con micreosegundos actual en formato Y-m-d H:i:s.v
	 */
	static function timestamp()
	{
		$date = new \DateTime();
        $fecha = $date->format('Y-m-d H:i:s.v');
        return $fecha;
	}

	/**
	 * Permite mostrar solo la fecha basada en el formato efectuado
	 * @param formato String, formato de fecha ejemplo: d/m/Y H:i:s
	 * @return fecha, imprime la fecha actual en formato pasado por parametro
	 */
	static function customer(String $formart)
	{
		$date = new \DateTime();
        $fecha = $date->format($formart);
        return $fecha;
	}

	/**
	 * Permite cambiar los formato de fechas  
	 * @param formato String, formato de fecha ejemplo: d/m/Y H:i:s
	 * @param fecha String, fecha que desea cambiar
	 * @return fecha, el formato de fechq 	ue desea cambiar
	 */
	static function changeDate(String $format, String $fecha)
	{
		$fecha = date($format, strtotime($fecha));
		return $fecha;
	}
}
