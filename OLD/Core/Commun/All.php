<?php
namespace IRON\Core\Commun;

/**
 * Interpreta integrar las herencias e implementacion de las clases commun
 * @Author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @Author: Blog: <http://gbbolivar.wordpress.com>
 * @Creation Date: 29/08/2017
 * @version: 0.1
 */

class All extends Exceptions implements Constant
{
 use Commun;
 use Logs;
 //use SendMail;
}