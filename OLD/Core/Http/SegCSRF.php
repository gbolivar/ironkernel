<?php
namespace IRON\Core\Http;
use IRON\Core\Commun\All;

/**
 * Permite generar token para evitar que se hagan peticiones equivocadas en CSRF
 * extraido parte del codigo de la referencia fua adaptado a nuestras necesidades
 * @ref http://bkcore.com/blog/code/nocsrf-php-class.html
 */

class SegCSRF
{
    protected static $doOriginCheck = false;

    /**
     * Check CSRF tokens match between session and $origin.
     * Make sure you generated a token in the form before checking it.
     *
     * @param String $key The session and $origin key where to find the token.
     * @param Mixed $origin Este es el objeto del origen del http getallheaders.
     * @param Boolean $throwException (Facultative) TRUE to throw exception on check fail, FALSE or default to return false.
     * @param Integer $timespan (Facultative) Makes the token expire after $timespan seconds. (null = never)
     * @param Boolean $multiple (Facultative) Makes the token reusable and not one-time. (Useful for ajax-heavy requests).
     *
     * @return Boolean Returns FALSE if a CSRF attack is detected, TRUE otherwise.
     */
    public static function checkTokenHaders( $key, $origin, $throwException=false, $timespan=null, $multiple=false )
    {


        if ( !isset( $_SESSION[ 'csrf_' . $key ] ) )
            if($throwException)
                throw new \Exception( 'Falta el token de sesion de la cebecera HTTP CSRF.' );
            else
                return false;

        if (!isset($origin['X-Auth-Token']))
            if($throwException)
                throw new \Exception( 'Falta el token de la cabecera HTTP[X-Auth-Token] CSRF.' );
            else
                return false;

        // Get valid token from session
        $hash = $_SESSION['csrf_'.$key];

        // Free up session token for one-time CSRF token usage.
        //if(!$multiple)
            //$_SESSION[ 'csrf_' . $key ] = null;

        // Origin checks
        if( self::$doOriginCheck && sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) != substr( base64_decode( $hash ), 10, 40 ) )
        {
            if($throwException)
                throw new \Exception( 'El origen del la cabecera HTTP[X-Auth-Token]  no coincide con el origen del token.' );
            else
                return false;
        }

        // Check if session token matches form token
        if ($origin['X-Auth-Token']!=$hash)
            if($throwException)
                throw new \Exception( 'Token CSRF no valido.' );
            else
                return false;

        // Check for token expiration
        if ( $timespan != null && is_int( $timespan ) && intval( substr( base64_decode( $hash ), 0, 60 ) ) + $timespan < time() )
            if($throwException)
                throw new \Exception( 'El token de CSRF ha caducado.' );
            else
                return false;

        return true;
    }


    /**
     * Check CSRF tokens match between session and $origin.
     * Make sure you generated a token in the form before checking it.
     *
     * @param String $key The session and $origin key where to find the token.
     * @param Mixed $origin Es un objeto asociativo de lo que se encia por parametro del los formularios POST sin ajax.
     * @param Boolean $throwException (Facultative) TRUE to throw exception on check fail, FALSE or default to return false.
     * @param Integer $timespan (Facultative) Makes the token expire after $timespan seconds. (null = never)
     * @param Boolean $multiple (Facultative) Makes the token reusable and not one-time. (Useful for ajax-heavy requests).
     * @example SegCSRF::checkTokenPOST( 'csrf_token', $_POST, true, 60*10, false );
     * @return Boolean Returns FALSE if a CSRF attack is detected, TRUE otherwise.
     */
    public static function checkTokenPOST( $key, $origin, $throwException=false, $timespan=null, $multiple=false )
    {


        if ( !isset( $_SESSION[ 'csrf_' . $key ] ) )
            if($throwException)
                throw new \TypeError( 'Falta el token de sesion de la  peticion POST en CSRF.' );
            else
                return false;


        if (!isset($origin[$key]))
            if($throwException)
                throw new \TypeError( 'Falta el token del formulario por post CSRF.' );
            else
                return false;


        // Origin checks
        if( self::$doOriginCheck && sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) != substr( base64_decode( $_SESSION['csrf_'.$key] ), 10, 40 ) )
        {
            if($throwException)
                throw new \TypeError( 'El origen del la peticion post  no coincide con el origen del token.' );
            else
                return false;
        }


        // Check if session token matches form token
        if ($origin[$key]!=$_SESSION['csrf_'.$key])
            if($throwException) {
                All::redirect($_SERVER['HTTP_REFERER']);
                //throw new \TypeError('Token CSRF no valido.');
                die();
            }else {
                return false;
            }

        // Check for token expiration
        if ( $timespan != null && is_int( $timespan ) && intval( substr( base64_decode( $_SESSION['csrf_'.$key] ), 0, 60 ) ) + $timespan < time() )
            if($throwException)
                throw new \TypeError( 'El token de CSRF ha caducado.' );
            else
                return false;

        return true;
    }

    /**
     * Adds extra useragent and remote_addr checks to CSRF protections.
     */
    public static function enableOriginCheck()
    {
        self::$doOriginCheck = true;
    }

    /**
     * CSRF token generation method. After generating the token, put it inside a hidden form field named $key.
     *
     * @example SegCSRF::generateToken('token')
     * @param String $key The session key where the token will be stored. (Will also be the name of the hidden field name)
     * @return String The generated, base64 encoded token.
     */
    public static function generateToken($key)
    {
        $extra = self::$doOriginCheck ? sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) : '';
        // token generation (basically base64_encode any random complex string, time() is used for token expiration)
        $token = base64_encode( time() . $extra . self::randomString( 32 ) );
        // store the one-time token in session
        $_SESSION['csrf_'.$key] = $token;
        return $token;
    }

    /**
     * Generates a random string of given $length.
     *
     * @param Integer $length The string length.
     * @return String The randomly generated string.
     */
    protected static function randomString($length)
    {
        $seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijqlmnopqrtsuvwxyz0123456789';
        $max = strlen( $seed ) - 1;

        $string = '';
        for ( $i = 0; $i < $length; ++$i )
            $string .= $seed{intval( mt_rand( 0.0, $max ) )};

        return $string;
    }

    /**
     * Permite eliminar la el cache en session
     * @example SegCSRF::destroyToken()
     */

    public static function destroyToken() {
            unset($_SESSION['csrf_token']);
    }

    /**
     * Permite generar el campo hidden para ser usado en el formulario
     * @example SegCSRF::getTokenField()
     * @return html campo hidden
     */
    public static function getTokenField()
    {
        echo  "<input type='hidden' name='csrf_token' id='csrf_token' value='". SegCSRF::generateToken('csrf_token')."' />";
    }

    /** ACL (Access Control Lists)
     * Permite recibir peticiones solo del mismo servidor
     */
    public static function firewallCrossDomain()
    {
        $referrer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"";
        if( stripos($referrer,$_SERVER[ 'SERVER_NAME' ] ) === false ) {
            die('CSRF HTTP REFERRER detected!');
        }
    }

}