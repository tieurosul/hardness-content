<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est/est002/form/est002SeparacaoPorLocacao/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
		/**
		* PARTE 1: Variavéis envidas para o formulário
		*/
		// Variável indica qual tab do form será exibida
		$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : false;
		// Utilizada em UPDATE, é o ID do registro que está sendo editado
		$T005_Id = (isset($r_T005_Id)) ? $r_T005_Id : false;
		$T238_Id = (isset($T238_Id)) ? $T238_Id : false;

		/**
		* PARTE 2: Definição do formulário
		*/
		// Este mesmo form será exibido em multiplas tabs ou não?
		$formMultiTab = false;
		if ($formMultiTab) {
			$formId = (isset($_REQUEST['formId'])) ? $_REQUEST['formId'] : false;
		} else {
			$formId = uniqid();
		}

		$form_editor = array('geral' => '{"T238_T006_Id":{"top":6.98529052734375,"left":76.30206298828125,"width":543,"hidden":0},"T238_T066_Id":{"top":29.993865966796875,"left":93.52020263671875,"width":415,"hidden":0},"alterarLocacao":{"top":27.98712158203125,"left":559.98779296875,"width":null,"hidden":0},"T238_Quantidade_Separacao":{"top":52.98712158203125,"left":11.73406982421875,"width":72.03921600000001,"hidden":0},"est002SeparacaoPorLocacaoSepProd_btn":{"top":73.98898315429688,"left":335.998779296875,"width":null,"hidden":0}}');

		// A qual tabela este form está relacionada, e a chave primária da tabela
		$form_tabela = 'T238';
		$form_campo_pk = 'T238_Id';
		// ID do botão que irá executar o envio desse formulário (o botão provavelmente foi criado dentro do content.php, use o mesmo ID de lá)
		$enviar_btnId = 'est002SeparacaoPorLocacaoSepProd_btn';
		// Form que será executado ao clicar no botão
		$enviar_acaoHref = '/est/est002/form/est002SeparacaoPorLocacao/';

		// novo formulário
		$form = new Formulario('est002SeparacaoPorLocacaoSepProd_form');

		// campos
		$disabled = false;
		$extraD004 = 'LEFT JOIN D004 ON T066_D004_Id = D004_Id';
		$T238_T006_Id = gGeraSelect('T006','T006_Id','T006_Descricao_Produto,IFNULL(T006_Quantidade_Separacao[[virgula]]0),T006_Quantidade',"left join D009 on D009_Id=T006_D009_Id left join T066 on T066_D009_Id=D009_Id left join D004 on D004_Id=T066_D004_Id left join T005 on T005_Id=T006_T005_Id LEFT JOIN T238 ON T238_T006_Id=T006_Id WHERE T006_T005_Id='{$r_T005_Id}' AND (IFNULL(T006_Quantidade_Separacao, 0) < T006_Quantidade || IFNULL(T238_T006_Id,0) <= 0) AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id group by T006_Id ORDER BY D004_Local,T006_Id", false, false, "%1 (%2 / %3)");
		if (empty($T238_T006_Id)) {
			$disabled = true;
			$T238_T006_Id[] = array(
				'title' => 'Todos os produtos já foram separados!',
				'value' => 0
			);
			$extraD004 = "LEFT JOIN D004 ON D004_Id  = T066_D004_Id
			              LEFT JOIN D009 ON D009_Id  = T066_D009_Id
			              LEFT JOIN D049 ON D049_Id  = D009_D049_Id
			              LEFT JOIN D001 ON D001_Id  = D049_D001_Id
			              LEFT JOIN D001A ON D001_Id = D001A_D001_Id
			              LEFT JOIN T066A ON T066_Id = T066A_T066_Id
			                  WHERE (1=2)
							  AND IFNULL(T066A_Flag_Ativo, 'S') = 'S'";
		} else {
			$sql = "SELECT T006_D009_Id, T006_T066_Id FROM T006 WHERE T006_Id = '{$T238_T006_Id[0]['value']}'";
			$resT006 = mysql_query($sql);
			$rowT006 = mysql_fetch_assoc($resT006);
			//$extraD004 = "WHERE D004_Id IN (SELECT D004_Id FROM T006 LEFT JOIN T066 ON T066_D009_Id=T006_D009_Id LEFT JOIN D004 ON D004_Id=T066_D004_Id WHERE T006_Id = '{$T238_T006_Id[0]['value']}')";
			$extraD004 = "LEFT JOIN D004 ON D004_Id  = T066_D004_Id 
						  LEFT JOIN D009 ON D009_Id  = T066_D009_Id
						  LEFT JOIN D049 ON D049_Id  = D009_D049_Id
						  LEFT JOIN D001 ON D001_Id  = D049_D001_Id
						  LEFT JOIN D001A ON D001_Id = D001A_D001_Id
						  LEFT JOIN T066A ON T066_Id = T066A_T066_Id
					          WHERE T066_D009_Id = '{$rowT006['T006_D009_Id']}'
							  AND IFNULL(T066A_Flag_Ativo, 'S') = 'S'
				           ORDER BY IF(T066_Id='{$rowT006['T006_T066_Id']}',1,0) DESC, T066_Id ASC";
		}
		$T238_T066_Id = gGeraSelect('T066','T066_Id','D004_Local,FORMAT(T066_Quantidade_Estoque[[virgula]]2),T066_Id,if(T066A_Data_Validade = "0000-00-00","", concat("- VAL: ",DATE_FORMAT(T066A_Data_Validade,"%d/%m/%Y"))), IF(IFNULL(T066A_Numero_Lote,"") = "", "ND", T066A_Numero_Lote)', $extraD004, false, false, "LOTE: %5 - LOC: %1 (%2) - %3 %4");
		
		// Definição do Formulário
		$formArray = array(
			'campos' => array(
				'geral' => array(
					'T238_T006_Id' => array('name' => 'T238_T006_Id', 'label' => 'Produto', 'type' => 'select', 'select' => $T238_T006_Id, 'disabled' => $disabled, 'extra' => "onchange=\"separacaoProdutoPorLocacao(this, '{$g['divId']}');\""),
					'T238_T066_Id' => array('name' => 'T238_T066_Id', 'label' => 'Lote', 'type' => 'select', 'select' => $T238_T066_Id, 'disabled' => $disabled),
					'alterarLocacao' => array('name' => 'alterarLocacao', 'type'=>'button', 'reload' => false, 'value' => 'Alterar Locação', 'reject' => true),
					'T238_Quantidade_Separacao' => array('name' => 'T238_Quantidade_Separacao', 'label' => 'Quantidade Separação', 'type' => 'moeda', 'disabled' => $disabled, 'casasDecimais' => '4'),
					'T238_T005_Id' => array('name' => 'T238_T005_Id', 'type' => 'hidden', 'value' => $r_T005_Id),
					'T238_D004_Id' => array('name' => 'T238_D004_Id', 'type' => 'hidden'),
				)
			)
		);
        //$formArray['campos']['geral']['btnFechar'] = array('name' => 'btnFechar', 'type' => 'button', 'reload' => false, 'value' => 'Cancelar', 'align' => 'right', 'extra' => 'onclick="fecharJanela(\''.$g['divId'].'\');"','style' => 'float:right;', 'reject' => true);
        $formArray['campos']['geral']['est002SeparacaoPorLocacaoSepProd_btn'] = array('name' => 'est002SeparacaoPorLocacaoSepProd_btn', 'type' => 'submit', 'reload' => false, 'value' => 'Separar', 'align' => 'right', 'style' => 'float:right;', 'reject' => true);
		$form->definirFormularioArray($formArray);

		echo <<<EOT
		    <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		    	$('#alterarLocacao').attr('D009_Id', '{$rowT006['T006_D009_Id']}');
				$('#alterarLocacao').unbind('click').bind('click', function(data) {
					abrirJanela(event, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Locação', '/est/est003/content/est003ContentLocacoes/','&acaoId=' + encodeURIComponent($('#alterarLocacao').attr('D009_Id')) + '&D009_Id=' + encodeURIComponent($('#alterarLocacao').attr('D009_Id')) + '&separacao=true', [700,350]);
				});
		    } </script></div>
EOT;
		/**
		* PARTE 3: Processamento do formulário (se foi enviado)
		* 		   E carregamento dos valores (se não foi enviado e possui acaoId)
		*/
		if ($formCodigo = $form->processarFormulario($form_tabela)) {
			// Form com erros
			if ($formCodigo == 1) {
				echo gGeraAlertMsgErroForm($form);
				// Form sem erros
			} else if ($formCodigo == 2) {
				// Insert ou Update?
				if (!empty($acaoId)) {
					// 
				} else {					
					// Insert
						$sqlT066 = mysql_query("SELECT T066_Quantidade_Estoque,
													D006_Flag_Estoque 
												FROM T066 
											LEFT JOIN T006 ON T006_Id='{$form->campoValorEnviado('T238_T006_Id')}'
											LEFT JOIN D006 ON D006_Id=T006_D006_Id 
												WHERE T066_Id = '{$form->campoValorEnviado('T238_T066_Id')}'");
						$rowT066 = mysql_fetch_array($sqlT066);		

						$quantidadeSeparacao = gCorrigeNumeroInverte($form->campoValorEnviado('T238_Quantidade_Separacao'));
						if($rowT066['T066_Quantidade_Estoque'] < $quantidadeSeparacao  && $g['C031']['permitirFaturarSemEstoque'] == 'N' && $rowT066['D006_Flag_Estoque'] == 'D'){
						echo gGeraAlertMsg("Erro", "A locação selecionada não possui estoque suficiente para a quantidade informada");
						// refresh
						echo <<<EOT
						<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
							divRefresh('{$g['divId']}');
						} </script></div>
EOT;
					} else {
						$sqlD004 = mysql_query("SELECT T066_Id FROM T066 WHERE T066_Id='{$form->campoValorEnviado('T238_T066_Id')}' and ifnull(T066_D004_Id,0)=0");
				        if(mysql_num_rows($sqlD004) > 0) {
				            echo gGeraAlertMsg("Erro", "Não foi informado a LOCAÇÃO de todos os produtos.<br />VERIFIQUE!");
				        } else {			
							$sql = "SELECT T006_Quantidade_Separacao, T006_Quantidade FROM T006 WHERE T006_Id = '{$form->campoValorEnviado('T238_T006_Id')}'";
							$resT006 = mysql_query($sql);
							$rowT006 = mysql_fetch_assoc($resT006);
							if ( ( gCorrigeNumeroInverte($form->campoValorEnviado('T238_Quantidade_Separacao')) + $rowT006['T006_Quantidade_Separacao'] ) > $rowT006['T006_Quantidade'] ) {
								echo gGeraAlertMsg('Erro', "Quantidade separada informada é maior que a quantidade total necessária para separar");
							} else {
								$form->campoSetarValor('T238_D004_Id', getD004($form->campoValorEnviado('T238_T066_Id')), true);
								if ($sql = $g['sqlAuto']->gerarSQLInsert($form_tabela, $form)) {
									// refresh
									echo <<<EOT
										<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
											divRefresh('{$g['divId']}');
											divRefresh('{$r_divRefresh}');
										} </script></div>
EOT;
								}
							}
						}
					}
				}
			}
		} else {
			// Carrega os valores caso seja um update
			if (!empty($acaoId)) {
				$processado = $form->carregarValoresCampos($form_tabela, $form_campo_pk, $acaoId);
				// mostra erros de formulário uteis ao desenvolvedor
				if ($processado !== true) {
					reset($form->formArray['campos']);
					if ($tab == key($form->formArray['campos'])) {
						echo $processado;
					}
				}
			}
		}

		/**
		* PARTE 4: Gerar o formulário
		*/
		$gerado = gGeraCamposForm($form, $tab, $T238_Id, $enviar_btnId, $enviar_acaoHref, $formId, $form_campo_pk);

		/**
		 * PARTE 5: Exibir o formulário
		 */
		$g['smarty']->assign('formularioID', $form->formularioID());
		$g['smarty']->assign('table', $gerado['table']); $g['smarty']->assign('form_tabela', isset($form_tabela) ? $form_tabela : '');
		echo $gerado['pagina'];
		if (!$formMultiTab) { $g["smarty"]->assign("formId", $formId); }
		$g['smarty']->display('formEditor.tpl');



