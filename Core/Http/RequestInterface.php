<?php
namespace IRON\Core\Http;

interface RequestInterface
{
    /**
     * Permite capturar peticiones del protocolo http del proceso GET
     * @param String $index, indece para buscar dentro del objeto $_GET
     * @return \http\get $dataGet
     */
    public function getParameter($index='');

    /**
     * Permite capturar peticiones del protocolo http del proceso POST
     * @param String $index, indece para buscar dentro del objeto $_POST
     * @return \http\post $dataGet
     */
    public function postParameter($index='');




}