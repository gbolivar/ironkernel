<?php
namespace IRON\Core\Commun;

/**
 * Clase encargada de interpretar un xml para agregarle elemento
 * @author: Gregorio Bolívar <elalconxvii@gmail.com>
 * @author: Blog: <http://gbbolivar.wordpress.com>
 * @created Date: 01/09/2017
 * @version: 0.1
 */

class SimpleXMLExtended  extends \SimpleXMLElement
{

    public function init()
    {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $no->preserveWhiteSpace = false;
        $no->formatOutput = true;
        $obj = (object)array('no' => $no, 'node' => $node);
        return $obj;
    }

    public function addCDATA($cData)
    {
        $obj = self::init();
        $obj->node->appendChild($obj->no->createCDATASection($cData));
    }

    public function addComentario($msj)
    {
        $obj = self::init();
        $obj->node->appendChild($obj->no->createComment($msj));
    }

    public function formatXml($archivoXml)
    {
        $obj = self::init();
        $obj->no->load($archivoXml);
        $obj->no->save($archivoXml);
    }
}

