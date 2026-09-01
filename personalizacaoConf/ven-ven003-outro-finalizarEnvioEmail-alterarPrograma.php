<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven003-outro-finalizarEnvioEmail/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

        $T007 = mysql_query("select T007_TX_Resposta_Codigo from T007 where T007_Id='$r_acaoId'");
        $mT007 = mysql_fetch_array($T007);
        mysql_query("update T007 set T007_TX_Resposta_Codigo='9' where T007_Id='$r_acaoId'");
        echo "NOTA FISCAL FINALIZA"; 
