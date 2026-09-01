<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /fis-fis012-form_func-ajax-carregaDadosNfeMdfe/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());

		$T284_T007_Id = isset($r_T284_T007_Id) ? $r_T284_T007_Id : false;
		$T276_Id = isset($r_T276_Id) ? $r_T276_Id : false;
		log("T276_Id no ajax carregaDadosNfeMdfe= ".$T276_Id);

		if (empty($T284_T007_Id)) {
			$resposta['code'] = false;
			$resposta['data']['erro'] = "NF-e não existe";
		} else {

			// $T276 = mysql_query("SELECT T276_Id 
			// 	                   FROM T276
			// 	              LEFT JOIN T284 ON T284_T276_Id=T276_Id
			// 	              LEFT JOIN T007 ON T284_T007_Id=T007_Id
			// 	                  WHERE T007_Id='{$T284_T007_Id}'
			// 	                    AND T276_Id='{$T276_Id}'");
			// log("SELECT T276_Id 
			// 	                   FROM T276
			// 	              LEFT JOIN T284 ON T284_T276_Id=T276_Id
			// 	              LEFT JOIN T007 ON T284_T007_Id=T007_Id
			// 	                  WHERE T007_Id='{$T284_T007_Id}'
			// 	                    AND T276_Id='{$T276_Id}'");
			// if(mysql_num_rows($T276) > 0){
			// 	$resposta['code'] = false;
			// 	$resposta['data']['erro'] = "Essa NF-e já está cadastrada";
			// }

			$sql = <<<EOT
				SELECT
					 T007_Chave_Acesso_Nfe,
					 T007_Total_Peso_Bruto,
					 T007_Peso_Bruto,
					 T007_Valor_Total,
					 D024_D018_Id,
                     D024_D020_Id,
                     D148_D020_Id,
                     D148_D018_Id,
                     D018_Codigo_IBGE,
                     D020_Codigo_IBGE,
					 T007_Numero_Nota_Fiscal,
					 D024_Nome_Empresa,
					 COALESCE(T007_D148_Id_Entrega, 0) AS UF_Entrega,
					 T007_D024_Id
				FROM T007
		   LEFT JOIN D024 ON T007_D024_Id = D024_Id
		   LEFT JOIN D148 ON T007_D148_Id_Entrega = D148_Id
           LEFT JOIN D020 ON D024_D020_Id = D020_Id
           LEFT JOIN D018 ON D024_D018_Id = D018_Id   
		   LEFT JOIN T284 ON T284_T007_Id = T007_Id 
           LEFT JOIN T276 ON T284_T276_Id = T276_Id
			   WHERE T007_Id = '{$T284_T007_Id}'
                 
EOT;
            log("SQL: ".$sql); 
			$result = mysql_query($sql);
			$arrayDataT007 = mysql_fetch_assoc($result);
			if($arrayDataT007['UF_Entrega'] > 0){ //se houver endereço de entrega cadastrado
                $D148 = mysql_query("SELECT D018_UF, 
                                            D018_Id,
                                            D018_Codigo_IBGE,
                                            D020_Nome_Cidade,
                                            D020_Codigo_IBGE,
                                            D020_Id
                                       FROM D018
                                  LEFT JOIN D020 ON D020_D018_Id=D018_Id
                                  LEFT JOIN D148 ON D148_D018_Id=D018_Id
                                      WHERE D148_Id='{$arrayDataT007['UF_Entrega']}'
                                        AND D018_Id='{$arrayDataT007['D148_D018_Id']}'
                                        AND D020_Id='{$arrayDataT007['D148_D020_Id']}'");
                $mD148 = mysql_fetch_array($D148);
                $Id_UF_Descarregamento = $mD148['D018_Id'];
                $sigla_UF_Descarregamento = $mD148['D018_UF']; 
                $Id_Cidade_Descarregamento = $mD148['D020_Id'];
                $nomeCidadeDescarregamento = $mD148['D020_Nome_Cidade'];
                $IBGE_Cidade_Descarregamento = $mD148['D020_Codigo_IBGE'];
                if($count == 0){
                    $UFtemporaria = $mD148['D018_UF'];
                    $count++;
                }                  
            }else{ //se não houver endereço de entrega, pega do cadastro do cliente
                $Id_UF_Descarregamento = $arrayDataT007['D024_D018_Id'];
                $sigla_UF_Descarregamento = $arrayDataT007['D018_UF'];
                $Id_Cidade_Descarregamento = $arrayDataT007['D024_D020_Id'];
                $nomeCidadeDescarregamento = $arrayDataT007['D020_Nome_Cidade'];
                $IBGE_Cidade_Descarregamento = $arrayDataT007['D020_Codigo_IBGE'];
                if($count == 0){
                    $UFtemporaria = $arrayDataT007['D018_UF'];
                    $count++;
                }
            }

			echo mysql_error();

			// Será utilizado o preço que estiver preenchido no formulário, se ele for maior que 0
			if($arrayDataT007['T007_Peso_Bruto'] > 0){
				$T284_Peso_Bruto_Total = gCorrigeNumero($arrayDataT007['T007_Peso_Bruto']);
			}else{
				$T284_Peso_Bruto_Total = gCorrigeNumero($arrayDataT007['T007_Total_Peso_Bruto']);
			}

			// Se a chave for vazia, essa NF ja esta em um MDFe.
			if(empty($arrayDataT007['T007_Chave_Acesso_Nfe'])) {
				// Busca em qual MDFe esta a NF
				$result = mysql_query("SELECT T284_T276_Id, T276_Numero_MDFe FROM T007 LEFT JOIN T284 ON T284_T007_Id = T007_Id LEFT JOIN T276 ON T284_T276_Id = T276_Id WHERE T007_Id = '{$T284_T007_Id}'");
				$mT007 = mysql_fetch_assoc($result);

				//$resposta['data']['T284_T276_Id'] = $mT007['T284_T276_Id'];
				//$resposta['data']['T276_Numero_MDFe'] = $mT007['T276_Numero_MDFe'];
			}

			$resposta['data']['T284_Chave_De_Acesso_NFe'] = $arrayDataT007['T007_Chave_Acesso_Nfe'];
			$resposta['data']['T284_Peso_Bruto_Total'] =  $T284_Peso_Bruto_Total;
			$resposta['data']['T284_Valor_Carga'] = gCorrigeNumero($arrayDataT007['T007_Valor_Total']);
			$resposta['data']['T284_Numero_NFe'] = $arrayDataT007['T007_Numero_Nota_Fiscal'];
			$resposta['data']['T284_Destinatario_Nome'] = $arrayDataT007['D024_Nome_Empresa'];
 			$resposta['data']['T284_D018_Id'] = gProcessaSelect(gGeraSelect('D018', 'D018_Id', 'D018_UF', 'WHERE D018_Id = ' . $Id_UF_Descarregamento . ' ORDER BY D018_UF'));				
 			$resposta['data']['T284_D020_Id'] = gProcessaSelect(gGeraSelect('D020', 'D020_Id', 'D020_Nome_Cidade', 'WHERE D020_Id = ' . $Id_Cidade_Descarregamento . ' ORDER BY D020_Nome_Cidade'));				
			$resposta['data']['T284_Cidade_Descarregamento'] = $nomeCidadeDescarregamento;
			$resposta['data']['T284_Cidade_Descarregamento_Codigo_IBGE'] = $IBGE_Cidade_Descarregamento;
		}


echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";

