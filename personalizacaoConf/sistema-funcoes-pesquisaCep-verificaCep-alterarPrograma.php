<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /sistema-funcoes-pesquisaCep-verificaCep/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


$resposta = array('code' => true, 'data' => array());
        require_once('bibliotecas/classes/CAD001.php');
        $CAD001 = new CAD001();

        $cep = isset($r_CEP) ? $r_CEP : false;
        $W004_Id = isset($r_idCep) ? $r_idCep : false;
        $whereW004 = ($W004_Id>0 ? " WHERE W004_Id ='{$W004_Id}'" : " WHERE W004_Cep ='{$cep}'");

        $sql = mysql_query("SELECT * FROM global.W004 {$whereW004}");
        $result = mysql_fetch_array($sql);
        
        $sql2 = mysql_query("SELECT D020_Id FROM D020 WHERE D020_Codigo_IBGE ='{$result['W004_IBGE_Cidade']}'");
        $result2 = mysql_fetch_array($sql2);
        
        $sql3 = mysql_query("SELECT D018_Id FROM D018 WHERE D018_Codigo_IBGE ='{$result['W004_IBGE_UF']}'");
        $result3 = mysql_fetch_array($sql3);
        
        $sql4 = mysql_query("SELECT D030_Id FROM D030 WHERE D030_Nome_Pais LIKE '%BRASIL%'");
        $result4 = mysql_fetch_array($sql4);

        
        // Se não encontra faz as buscas nos webservices
        if(mysql_num_rows($sql) <= 0){
            $retorno = $CAD001->pesquisaTerceirosByjg($cep);
            log("cep: ".$retorno);
            if($retorno['Cep']>0){
                
                $retorno['Logradouro'] = explode('-', $retorno['Logradouro']);
                $retorno['Logradouro'] = $retorno['Logradouro'][0];

                if (!($retorno['Cep'] == '81250-670')) {
                    $insW004=mysql_query("INSERT INTO global.W004 (
                        W004_Logradouro,
                        W004_Bairro,
                        W004_Cep,
                        W004_Cidade,
                        W004_UF,
                        W004_IBGE_Cidade,
                        W004_IBGE_UF
                    ) VALUES (
                        '{$retorno['Logradouro']}',
                        '{$retorno['Bairro']}',
                        '{$retorno['Cep']}',
                        '{$retorno['Cidade']}',
                        '{$retorno['UF']}',
                        '{$retorno['IBGE_Cidade']}',
                        '{$retorno['IBGE_UF']}'
                    );");
                    error_log("entrou");
                }
                // Se encontra atualiza a tabela W004 da base global

                $sql2 = mysql_query("SELECT D020_Id FROM D020 WHERE D020_Codigo_IBGE ='{$retorno['IBGE_Cidade']}'");
                $result2 = mysql_fetch_array($sql2);
                
                $sql3 = mysql_query("SELECT D018_Id FROM D018 WHERE D018_Codigo_IBGE ='{$retorno['IBGE_UF']}'");
                $result3 = mysql_fetch_array($sql3);
                
                $sql4 = mysql_query("SELECT D030_Id FROM D030 WHERE D030_Nome_Pais LIKE '%BRASIL%'");
                $result4 = mysql_fetch_array($sql4);

                $result['W004_Logradouro'] = $retorno['Logradouro'];
                $result['W004_Cep'] = $cep;
                $result['W004_Bairro'] = $retorno['Bairro'];
            }
        }

        if((mysql_num_rows($sql) > 0) || ($retorno['Cep']>0)){
            $resposta['code'] = true;
            $resposta['data']['EnderecoDb'] = $result['W004_Logradouro'];
            $resposta['data']['Cep'] = $result['W004_Cep'];
            $resposta['data']['BairroDb'] = $result['W004_Bairro'];
            //$resposta['data']['CidadeDb'] = $result2['D020_Id'];
            $resposta['data']['UfDb'] = $result3['D018_Id'];
            $resposta['data']['PaisDb'] = $result4['D030_Id'];
            $resposta['data']['CidadeDb'] = gProcessaSelect(gGeraSelect('D020', 'D020_Id', 'D020_Nome_Cidade', 'WHERE D020_D018_Id = '.$result3['D018_Id'].' AND D020_Id='.$result2['D020_Id'].' ORDER BY D020_Nome_Cidade'));
        } else {
            $resposta['code'] = false;
        }

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";

