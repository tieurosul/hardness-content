<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class PORTALPOSTAL extends PORTALPOSTAL_ {
	// defina os métodos para sobreescrever

	public function consumirWS() {
        global $cPasta_XML_Portal_Postal;
        $client = new \SoapClient('http://www.portalpostal.com.br/axis2/services/PrePostagemWS?wsdl');

        $function = 'PrePostagemXml';

        $cXml = $this->gerarXml();
        $cXml = str_replace(array(chr(13),chr(10),"\r","\n","\r\n")," ",$cXml);

        $cArqXml = $cPasta_XML_Portal_Postal . 'chave_' . $this->getChave().'.xml';

        file_put_contents($cArqXml, $cXml);

        $arguments = array('PrePostagemXml' => array(
                        'xml'   => $cXml,
                        'codAgencia' => $this->getConexaoCodigoAgencia(),
                        'login'      => $this->getConexaoLogin(),
                        'senha'      => $this->getConexaoSenha()
        ));

        $options = array('location' => 'http://www.portalpostal.com.br/axis2/services/PrePostagemWS', 'namespace' => 'http://postagem/xsd');

        log("function: {$function}; arguments: ".json_encode(($arguments),JSON_PRETTY_PRINT).";<br> options: ".json_encode($options,JSON_PRETTY_PRINT));
        try {
            $result = $client->__soapCall($function, $arguments, $options);
            //print_r($result);
            $this->tratarRetorno($result->return);
            return true;
        } catch(Exception $e){
            return $e->getMessage();
        }
    }
}

