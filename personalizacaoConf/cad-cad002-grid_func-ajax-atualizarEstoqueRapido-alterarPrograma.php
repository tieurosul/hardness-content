<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad002-grid_func-ajax-atualizarEstoqueRapido/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());
   	/** 
		* Funcionamento em etapas
    	*/
		$valorTotal = $r_valorTotal; 
    	$registrosEtapa = floor($valorTotal / $r_etapas);
    	$etapa = $r_etapa;
    	$limit1 = floor(( $registrosEtapa * $etapa ) - $registrosEtapa);
    	if($etapa >= $r_etapas){
    		$limit2 = ceil($registrosEtapa + ($valorTotal - ($limit1 + $registrosEtapa)));
    	} else {
    		$limit2 = $registrosEtapa ;
    	}
    	$concluido = 0;

        $resposta['percentual'] = ceil((100/$r_etapas) * $etapa);
        
    	if($limit2 < $valorTotal){
        	$etapa = $r_etapa + 1;	
        }else{
        	$concluido = 1;
        }
        $resposta['etapa'] = $etapa;
        $resposta['concluido'] = $concluido;
    	// --

     	$sql = base64_decode($r_extraSql);
     	$sql = $sql . " LIMIT 0,100";
        $sql = preg_replace('/LIMIT [0-9]+,[0-9]+/', "LIMIT $limit1 , $limit2", $sql);
        $sql = "SELECT D001_Id, D009_Id FROM D001 ".$sql;
        log("SQL= ".$sql);

        $sql = mysql_query($sql);
    	$ok = 0;
    	$erro = 0;
    	$erros ="";

    	while($row = mysql_fetch_array($sql)){
         	set_time_limit(0);
            $retorno = $CAD002->D001_reprocessa_historico(false,$row['D009_Id'],150);
            $retorno = true;
            if($retorno !== true){
             	$erro++;
             	$erros .= $erro ." - ". $retorno."<br />";
            }else{
             	$ok++;
            }
        }

        $mysql_erro=mysql_error();
        if (!empty($mysql_erro)){
        	$erros .= $mysql_erro;
        }
        
    	$resposta['data']="Ocorreu um erro inesperado!";
        $resposta['code'] = true;
        $resposta['ok'] = $ok;
        $resposta['erro'] = $erro;
        $resposta['erros'] = $erros;

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";



