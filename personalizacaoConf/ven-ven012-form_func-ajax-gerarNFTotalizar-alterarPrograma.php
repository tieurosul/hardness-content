<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven012-form_func-ajax-gerarNFTotalizar/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


// Felipe Kadanos - 10/08/2026
// Personalizado retirando o paramentro $venda do D001_reprocessa_historico, para evitar que o estoque fique negativo

$resposta = array('code' => true, 'data' => array());
        global $g;
		require_once("bibliotecas/classes/CAD002.php");	
		$CAD002 = new CAD002();
		require_once("bibliotecas/classes/VEN012.php");	
		$VEN012 = new VEN012();

		$EtapaRoolback = $r_EtapaRoolback;

		//cria pasta para salvar o cache----------
		// $Dir = $g['pathDados'] . 'tmp/';
		// is_dir($Dir) or mkdir($Dir, 0777, true);
		// $cacheT007 = "{$Dir}cacheT007-{$g['usuarioAtual']}.txt";
		//-----------------------------------------

		/*inicio calculo etapas*/
        if ($r_inicio==1){

			$T005_Id = isset($r_T005_Id) ? $r_T005_Id : false;
			$resposta['validacaoST'] = false;
			$cacheRoolback = unserialize(apc_fetch("cacheRoolback-{$g['usuarioAtual']}"));
	
			$T007 = mysql_query("SELECT T007_Id,
										T007_Observacao_Nota_Fiscal_1, 
										T007_Numero_Nota_Fiscal 
								   FROM T007 
								  WHERE T007_T005_Id = '{$T005_Id}' 
									AND T007_Flag_Cancelada != 'S'");
			$mT007 = mysql_fetch_assoc($T007);
	
			$resposta['T007_Id']= $mT007['T007_Id'];

			//----------------------------------------------------------
        	//mysql_query("call T007_Gravar_Totalizacao_4('{$resposta['T007_Id']}')");
			//Para chamar a trigger T007, o T007_Valor_Total é calculado no T007_Gravar_Totalizacao_4
			mysql_query("update T007 set T007_Valor_Total = '0' where T007_Id= '{$resposta['T007_Id']}' ");
        	//----------------------------------------------------------
	        $cSql_T005 = mysql_query("SELECT T008_Id, 
										     T006_D009_Id,
											 D006_Observacao_Produto, 
											 T008_Codigo_Produto,
											 D006_Flag_Estoque
	        							FROM T007
								   LEFT JOIN T008 ON T008_T007_Id=T007_Id
								   LEFT JOIN T006 ON T006_Id=T008_T006_Id
								   LEFT JOIN D006 ON D006_Id=T008_D006_Id
	        					       WHERE T007_Id = '{$resposta['T007_Id']}'
								    GROUP BY T008_Id"); 
	        $count = 0;
        	while($rowCache = mysql_fetch_assoc($cSql_T005)) {
        		$cache[$count]['T008_Id'] = $rowCache['T008_Id'];
			    $cache[$count]['T006_D009_Id'] = $rowCache['T006_D009_Id'];
				$cache[$count]['D006_Observacao_Produto'] = trim($rowCache['D006_Observacao_Produto']);
				$cache[$count]['T008_Codigo_Produto'] = trim($rowCache['T008_Codigo_Produto']);
				$cache[$count]['D006_Flag_Estoque'] = trim($rowCache['D006_Flag_Estoque']);
				$count++;
			}
			$resposta['totalRegistros'] = count($cache);
			$resposta['etapas'] = round($resposta['totalRegistros'] / 1);
	    	$resposta['etapas'] = ($resposta['etapas'] < 1) ? 1 : $resposta['etapas'];
	    	$resposta['registrosEtapa'] = round( $resposta['totalRegistros'] / $resposta['etapas'] );
			$resposta['etapa'] = 1;
	        $resposta['ok'] = 0;
    	    $resposta['erro'] = 0;
			$resposta['concluido'] = 0;
			apc_delete("cacheT007-{$g['usuarioAtual']}");
			apc_delete("cacheT007-resposta-{$g['usuarioAtual']}");
			
			// salvando cache em disco--------------------------------------------------
			// $h = fopen($cacheT007, "a");
			// $r = fwrite($h, serialize($cache));
			// fclose($h);
			// $retorno = file_exists($cacheT007);
			//--------------------------------------------------------------------------
	        $retorno = apc_store("cacheT007-{$g['usuarioAtual']}", serialize($cache));
			//--------------------------------------------------------------------------
	        if(!$retorno){
	        	$resposta['concluido'] = 1;
	        	$resposta['code'] = false;
	        	$resposta['data'] = "Erro ao criar variável no cache";
	        	/*$retornoRoolback = $VEN012->roolbackNF($T005_Id,$resposta['T007_Id'], $cacheRoolback,$EtapaRoolback);
				if($retornoRoolback !== true){
					$resposta['erroRoolback'] = $retornoRoolback;
				}*/
				apc_delete("cacheRoolback-{$g['usuarioAtual']}");
	        	echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
	        }
	    } else {
			// Recuperando dados do disco------------------------------------------------------
			// $cache = unserialize(file_get_contents($cacheT007));
			// if(!is_array($cache)){
	        // 	$resposta['code'] = false;
	        // 	$resposta['data'] = 'Erro ao recuperar arquivo com o cache T005.';
	        // 	$resposta['concluido'] = 1;
	        // 	echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
			// }
			//---------------------------------------------------------------------------------
			$cache = unserialize(apc_fetch("cacheT007-{$g['usuarioAtual']}"));
			// --------------------------------------------------------------------------------
			$resposta = unserialize(apc_fetch("cacheT007-resposta-{$g['usuarioAtual']}"));
	        if(!$resposta){
	        	$resposta['code'] = false;
	        	$resposta['data'] = "Erro ao recuperar variável no cache resposta";
	        	/*$retornoRoolback = $VEN012->roolbackNF($T005_Id,$resposta['T007_Id'], $cacheRoolback,$EtapaRoolback);
				if($retornoRoolback !== true){
					$resposta['erroRoolback'] = $retornoRoolback;
				}*/
				apc_delete("cacheRoolback-{$g['usuarioAtual']}");
	        	echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
	        }
	    }
	    $resposta['limit1'] = floor( ( $resposta['registrosEtapa'] * $resposta['etapa'] ) - $resposta['registrosEtapa']);

		if($resposta['etapa'] >= $resposta['etapas']){
			$resposta['limit2'] += ceil($resposta['registrosEtapa'] + ($resposta['totalRegistros'] - ($resposta['limit1'] + $resposta['registrosEtapa'])));
		} else {
			$resposta['limit2'] += $resposta['registrosEtapa'] ;
		}

	    $resposta['percentual'] = ceil((100/$resposta['etapas']) * $resposta['etapa']);	

		if ($resposta['percentual']>100) {
			$resposta['percentual'] = 100;
		}	
	   
	    $r_opcoes = json_decode($r_opcoes);

	    if(!is_array($r_opcoes)) { 
	        $opcoes = get_object_vars($r_opcoes);
	    }

	    $ok = 0;
		$erro = 0;
		$erros ="";
	    for ($i=$resposta['limit1']; $i < $resposta['limit2'] ; $i++) { 
	    	$T008_Id = $cache[$i]['T008_Id'];
	    	$D009_Id = $cache[$i]['T006_D009_Id'];
			$D006_Observacao_Produto = $cache[$i]['D006_Observacao_Produto'];
			$D006_Flag_Estoque = $cache[$i]['D006_Flag_Estoque'];

			// Chama a trigger para popular informações, esse valor é recalculado no T008_Gravar_Totalizacao
			mysql_query("UPDATE T008 SET T008_Valor_Total_Custo=0 WHERE T008_Id={$T008_Id}");

			if ($D006_Flag_Estoque == 'C') {
				$CAD002->D001_reprocessa_historico(0, $D009_Id, 10);
			} else {
				$CAD002->D001_reprocessa_historico(0, $D009_Id, 20);
			}

	        //mysql_query("call T008_Gravar_Totalizacao_4('{$T008_Id}')");

	        // Busca Observação Produto: da CFOP
    		if (!empty($D006_Observacao_Produto)) {

				$Observacao_Fiscal_Produto = $D006_Observacao_Produto . ';';

	        	// Seleciona os campos (valores) a serem substituidos na observação do produto
	        	$sqlT008 = mysql_query("SELECT *,
											   T290_Valor_Base_ICMS_Substituicao AS T008_BC_ICMS_ST_Compra, 
				                               T290_Valor_ICMS_Substituicao AS T008_Valor_ICMS_ST_Compra 
				                          FROM T008 
 	 							     LEFT JOIN D005 on D005_Id=T008_D005_Id 
									 LEFT JOIN T290 on T008_Id=T290_T008_Id										  
										 WHERE T008_Id = '{$T008_Id}'");
	       		$mT008   = mysql_fetch_assoc($sqlT008);

	            // Váriaveis dentro da Observação Fiscal
	            foreach ($mT008 as $key => $value) {
	                $key = preg_replace('#^[^_]+_#', '', $key);
	                $key = strtoupper($key);
	                if (preg_match('#^([0-9])+\.([0-9])+$#', $value)) {
	                    $value = gCorrigeNumero($value);
	                }
	                $Observacao_Fiscal_Produto = str_replace('[' . strtoupper($key) . ']', $value, $Observacao_Fiscal_Produto);
	            }
	            // Novo formato: [FORMULA: VALOR_TOTAL_BASE_ICMS * 0.075]
	            if (preg_match_all('/\[FORMULA:\s*([^\]]+)\]/i', $Observacao_Fiscal_Produto, $formulas, PREG_SET_ORDER)) {
	            	foreach ($formulas as $formula) {
	            		$expressaoOriginal = $formula[1];
	            		$expressaoCalc = strtoupper($expressaoOriginal);
	            		foreach ($mT008 as $keyExpr => $valueExpr) {
	            			if (!is_string($keyExpr) || ctype_digit($keyExpr)) {
	            				continue;
	            			}
	            			$keyExpr = strtoupper(preg_replace('#^[^_]+_#', '', $keyExpr));
	            			$valorExpr = is_numeric($valueExpr) ? str_replace(',', '.', (string) $valueExpr) : 0;
	            			$expressaoCalc = preg_replace('/\b' . preg_quote($keyExpr, '/') . '\b/', $valorExpr, $expressaoCalc);
	            		}
	            		$expressaoCalc = preg_replace('/(?<=\d),(?=\d)/', '.', $expressaoCalc);
	            		if (preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $expressaoCalc)) {
	            			$resultadoFormula = 0;
	            			eval('$resultadoFormula = ' . $expressaoCalc . ';');
	            			$Observacao_Fiscal_Produto = str_replace($formula[0], gCorrigeNumero($resultadoFormula), $Observacao_Fiscal_Produto);
	            		}
	            	}
	            }
	           
	            mysqli_query("UPDATE T008 SET T008_Informacoes_Adicionais='{$Observacao_Fiscal_Produto}' WHERE T008_Id={$T008_Id}");
	            
	        }

	        $retornoTotalizacao = mysql_error();     	 
           	if(!empty($retornoTotalizacao)){
           		$resposta['concluido'] = 1;
				/*$retornoRoolback = $VEN012->roolbackNF($T005_Id,$resposta['T007_Id'], $cacheRoolback,$EtapaRoolback);
				if($retornoRoolback !== true){
					$resposta['erroRoolback'] = $retornoRoolback;
				}*/
				apc_delete("cacheRoolback-{$g['usuarioAtual']}");
           	 	$resposta['code'] = false;
           	 	$resposta['data'] = $retorno;
           	 	echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
       		}else{
       			$ok++;
       		}
        }
	    $resposta['ok'] += $ok;
	    $resposta['erro'] += $erro;
	    $resposta['erros'] .= $erros;

		if($resposta['etapa'] >= $resposta['etapas']){
			//última etapa-----------------------------------------
			$sql = mysql_query("SELECT T005_Valor_ICMS_Substituicao_Retencao
			                      FROM T005 
			                     WHERE T005_Id = '{$T005_Id}'");
			$result = mysql_fetch_assoc($sql);
			$valorST = $result['T005_Valor_ICMS_Substituicao_Retencao'];

			if($g['C031']['gerarStContasPagar'] == 'S' && $valorST > 0){
				$resposta['validacaoST'] = true;
			}

			//mysql_query("call T007_Gravar_Totalizacao_4('{$resposta['T007_Id']}')");
			// ************************************************************************************************************************************************
        	// CRIANDO A OBSERVAÇÃO DA NOTA
        	// ************************************************************************************************************************************************
	        $sqlT007 = mysql_query("SELECT * 
									  FROM T007 
								 LEFT JOIN D006 ON D006_Id = T007_D006_Id 
								 LEFT JOIN D024 ON D024_Id = T007_D024_Id 
								 LEFT JOIN D018 ON D018_Id = D024_D018_Id 
								 LEFT JOIN D024A ON D024_Id = D024A_D024_Id
								     WHERE T007_Id = '{$resposta['T007_Id']}'");
	        $T007 = mysql_fetch_assoc($sqlT007);
	        
	        $sqlT008 = mysql_query("SELECT D006_Observacao 
									  FROM T007 
								 LEFT JOIN T008 ON T008_T007_Id = T007_Id 
								 LEFT JOIN D006 ON T008_D006_Id = D006_Id 
								     WHERE T007_Id = '{$resposta['T007_Id']}' 
								  GROUP BY D006_Observacao");

	        $Observacao_Fiscal = $T007['T007_Observacao_Nota_Fiscal_1'];
	        
            while ($T008 = mysql_fetch_array($sqlT008)) {
            	if(!empty($T008['D006_Observacao'])){
                 	$Observacao_Fiscal .= $T008['D006_Observacao'] . ';';
            	}
	        }

			if (!empty($Observacao_Fiscal)) {
				// CALCULANDO O DIFERIMENTO DO ICMS PARA PODER EXIBIR NA OBS DA CFOP
				// $valor_Diferido = $mT008[T008_Valor_ICMS_Oculto] - $mT008[T008_Valor_ICMS];
				$T008=mysql_query("SELECT (SUM(T008_Valor_ICMS_Oculto) - SUM(T008_Valor_ICMS)) AS T007_Valor_Total_Diferimento  FROM T008 WHERE T008_T007_Id='{$resposta['T007_Id']}'");
				$T008_Dif=mysql_fetch_assoc($T008);
				$T007=array_merge($T007,$T008_Dif);

				$sqlT290 = mysql_query("SELECT SUM(T290_Valor_Base_ICMS_Substituicao) AS T007_BC_ICMS_ST_Compra, SUM(T290_Valor_ICMS_Substituicao) AS T007_Valor_ICMS_ST_Compra, T290_T008_Id FROM T290 LEFT JOIN T008 ON T008_Id=T290_T008_Id WHERE T008_T007_Id='{$resposta['T007_Id']}'");
				$T290=mysql_fetch_assoc($sqlT290);
				$T007=array_merge($T007,$T290);

				// Váriaveis dentro da Observação Fiscal
				foreach ($T007 as $key => $value) {
					$key = preg_replace('#^[^_]+_#', '', $key);
					$key = strtoupper($key);
					if (preg_match('#^([0-9])+\.([0-9])+$#', $value)) {
						$value = gCorrigeNumero($value);
					}
					$Observacao_Fiscal = str_replace('[' . strtoupper($key) . ']', $value, $Observacao_Fiscal);
				}
				// Novo formato: [FORMULA: VALOR_TOTAL_BASE_ICMS * 0.075]
				if (preg_match_all('/\[FORMULA:\s*([^\]]+)\]/i', $Observacao_Fiscal, $formulas, PREG_SET_ORDER)) {
					foreach ($formulas as $formula) {
						$expressaoOriginal = $formula[1];
						$expressaoCalc = strtoupper($expressaoOriginal);
						foreach ($T007 as $keyExpr => $valueExpr) {
							if (!is_string($keyExpr) || ctype_digit($keyExpr)) {
								continue;
							}
							$keyExpr = strtoupper(preg_replace('#^[^_]+_#', '', $keyExpr));
							$valorExpr = is_numeric($valueExpr) ? str_replace(',', '.', (string) $valueExpr) : 0;
							$expressaoCalc = preg_replace('/\b' . preg_quote($keyExpr, '/') . '\b/', $valorExpr, $expressaoCalc);
						}
						$expressaoCalc = preg_replace('/(?<=\d),(?=\d)/', '.', $expressaoCalc);
						if (preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $expressaoCalc)) {
							$resultadoFormula = 0;
							eval('$resultadoFormula = ' . $expressaoCalc . ';');
							$Observacao_Fiscal = str_replace($formula[0], gCorrigeNumero($resultadoFormula), $Observacao_Fiscal);
						}
					}
				}

 				//Trava a nota para alteração
				/*$upT007Obs1 = "UPDATE T007 SET T007_Observacao_Nota_Fiscal_1='{$Observacao_Fiscal}', T007_Flag_Travar_Nota = 'S' WHERE T007_Id={$resposta['T007_Id']};";
				log($upT007Obs1);

				if($Observacao_Fiscal != ''){
					mysql_query($upT007Obs1);
				} */
			}
			
			$updateT007  = "UPDATE T007 SET";
			if($Observacao_Fiscal != ''){
				$Observacao_Fiscal = mysql_real_escape_string($Observacao_Fiscal);
				$updateT007 .= " T007_Observacao_Nota_Fiscal_1 = '{$Observacao_Fiscal}' ,";
			}
			$updateT007 .= " T007_Flag_Travar_Nota = 'S'";
			$updateT007 .= " WHERE T007_Id='{$resposta['T007_Id']}'";

			mysql_query($updateT007);

	        // ************************************************************************************************************************************************
        	// FIM OBSERVAÇÃO NOTA
        	// ************************************************************************************************************************************************
			
			// ----------------------------------------------------------------------------
			// unlink("{$Dir}cacheT007-{$g['usuarioAtual']}.txt");
			// Se for usar o arquivo em disco, usar o unlink, senão usa o apc_delete-------
			apc_delete("cacheT007-{$g['usuarioAtual']}");
			// -------------------------------------------------------------------------------
			apc_delete("cacheT007-resposta-{$g['usuarioAtual']}");
			apc_delete("cacheRoolback-{$g['usuarioAtual']}");
			$resposta['concluido'] = 1;
		} else {
	        apc_delete("cacheT007-resposta-{$g['usuarioAtual']}");
	       	$resposta['etapa'] = $resposta['etapa'] + 1;
	        apc_store("cacheT007-resposta-{$g['usuarioAtual']}", serialize($resposta));
		}
echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
