<?php
namespace IRON\Core\Commun;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use IRON\Core\Store\Cache;


    /**
     * Clase encargada de gestionar todas las Exceptions del sistema con el objetivo de implementar
     * las acciones de errores pertenecientes a las fallas del sistema y e
     * @author: Gregorio Bolívar <elalconxvii@gmail.com>
     * @author: Blog: <http://gbbolivar.wordpress.com>
     * @created Date: 26/07/2017
     * @version: 0.1
     */

trait SendMail
{
    /**
     * $mail, variable interna del sistema
     */
    private $mail;

    /**
     * $addressPRI, Correo principal a enviar solo uno
     */
    public $addressPRI;

    /**
     * $addressCCC, Correos con copia oculta, puede existir varios
     */
    public $addressCCC = [];

    /**
     * $addressRPT, Correo a varios destinatarios, puede existir varios
     */
    public $addressBCC = [];

    /**
     * $attached Permite mostrar los adjuntos que pasaran por parametros al correo
     */
    public $attached = [];

    /**
     * $title este permite mostrar el titulo que tendrá el correo
     */
    public $title;

    /**
     * $body este permite mostrar el contenido que sera pasado al correo
     */
    public $body;

    public function __construct()
    {
        $this->addressPRI;
        $this->addressCCC;
        $this->addressBCC;
        $this->attached;
        $this->title;
        $this->body;
    }


    /**
     * Incializar el envio de correo
     */
    public function mailStart()
    {
        //echo (extension_loaded('openssl')?'SSL loaded':'SSL not loaded')."\n";
        //die();
        //Create a new PHPMailer instance
        $this->mail = new PHPMailer(true);

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        // Permite desabilitar las validaciones de ssl
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );


        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = Cache::get('smtp_debug');

        //Ask for HTML-friendly debug output
        $this->mail->Debugoutput = 'html';
        //die(Cache::get('smtp_server'));
        //Set the hostname of the mail server
        $this->mail->Host = Cache::get('smtp_server');//'smtp.gmail.com';

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = Cache::get('smtp_port');//587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = Cache::get('smtp_SMTPSecure');

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = Cache::get('smtp_SMTPAuth');

        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Cache::get('smtp_user');//"username@gmail.com";

        //Password to use for SMTP authentication
        $this->mail->Password = Cache::get('smtp_pass');//"yourpassword";

        //Set who the message is to be sent from
        $this->mail->setFrom(Cache::get('smtp_user'), Cache::get('smtp_user_name'));

    }


    /**
     * Encargado de enviar los mensajes de correos de todo el sistema
     * @param array $correos
     * @return string $datos
     */
    public function mailSend(){
        ### Remitentes


        // Para Set who the message is to be sent to
        $correo = $this->getAddressPRI();
        $this->mail->addAddress($correo->address, $correo->name);

        // Responde Set an alternative reply-to address
        //$this->mail->addReplyTo('elalconxvii@hotmail.com', 'First Last');

        $correo = $this->getAddressCCC();
        foreach ($correo AS $key=>$value){
            $this->mail->addCC($value->address, $value->name);
        }


        $correo = $this->getAddressBCC();
        foreach ($correo AS $key=>$value){
            $this->mail->addBCC($value->address, $value->name);
        }


        ###### Content #####
        // Set email format to HTML
        $this->mail->isHTML(true);

        //Set the subject line
        $this->mail->Subject = $this->getTitle();

        //convert HTML into a basic plain-text alternative body
        $this->mail->Body = $this->getBody();

        //Replace the plain text body with one created manually
        //$this->mail->AltBody = 'This is a plain-text message body';

        ###### Attach an image file #####
        $adjunto = $this->getAttached();
        foreach ($adjunto AS $key => $value){
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $this->mail->addAttachment($value->file,$value->name);    // Optional name
        }

        $sends = $this->mail->send();

        //send the message, check for errors
        if (!$sends) {
            $datos = false;
        } else {
            $datos = true;
        }

        return $datos;
    }


    /**
     * @example $this->setMailAddressPRI('gb@IRONlions.com', 'Gregorio Bolivar');
     */
    public function setMailAddressPRI($address, $name)
    {
        $this->addressPRI = (object)array('address' => $address, 'name'=>$name);
    }

    private function getAddressPRI()
    {
        return $this->addressPRI;
    }
    /**
     * @example $this->setMailAddressCCC('gregoriobolivar82@hotmail.com', 'Gregorio Bolivar');
     */
    public function setMailAddressCCC($address, $name)
    {
        $this->addressCCC[] = (object)array('address' => $address, 'name'=>$name);
    }

    private function getAddressCCC()
    {
        return $this->addressCCC;
    }
    /**
     * @example $this->setMailAddressBCC('gb@IRONlions.com', 'Gregorio Bolivar');
     */
    public function setMailAddressBCC($address,$name)
    {
        $this->addressBCC[] = (object)array('address' => $address, 'name' => $name);
    }


    private function getAddressBCC()
    {
        return $this->addressBCC;
    }
    /**
     * @example $this->setMailAttached('C:\WINDOWS\Temp\php-7.0.20_errors.log', 'Demo');;
     */
    public function setMailAttached($attached, $name)
    {
        $this->attached[] = (object)array('file' => $attached, 'name' => $name);
    }

    private function getAttached()
    {
        return $this->attached;
    }
    /**
     * @example $this->setMailTitle('Recuperar Clave de acceso');
     */
    public function setMailTitle($title)
    {
        $this->title = $title;
    }

    private function getTitle()
    {
        return $this->title;
    }
    /**
     * @example  $this->setMailBody($html);;
     */
    public function setMailBody($body)
    {
        $this->body = $body;
    }

    private function getBody()
    {
        return $this->body;
    }

}

?>
