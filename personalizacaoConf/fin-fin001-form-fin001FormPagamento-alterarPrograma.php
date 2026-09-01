<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /fin-fin001-form-fin001FormPagamento/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

			$acaoId = (isset($r_acaoId)) ? $r_acaoId : false; 
			$T005_Id = (isset($r_T005_Id)) ? $r_T005_Id : false;			
			
			//echo $T005_Id;
			//('qqqq');
			/**
			* PARTE 1: Variavéis envidas para o formulário
			*/
			// Variável indica qual tab do form será exibida
			$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : false;
			$r_divReload = isset($r_ven002FormPagamento) ? $r_ven002FormPagamento['divReload'] : $r_divReload;	
			$extra = isset($r_fin001FormPagamento) ? $r_fin001FormPagamento['extra'] : $r_extra;
		
			// Utilizada em UPDATE, é o ID do registro que está sendo editado		

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

			// A qual tabela este form está relacionada, e a chave primária da tabela
			$form_tabela = '';
			$form_campo_pk = '';
			// ID do botão que irá executar o envio desse formulário (o botão provavelmente foi criado dentro do content.php, use o mesmo ID de lá)
			$enviar_btnId = 'fin001FormPagamentoReceber_btn';
			// Form que será executado ao clicar no botão
			$enviar_acaoHref = '/fin/fin001/form/fin001FormPagamento/';

			//form editor
			$form_editor = array(
				'geral' => '{"Total_Parcelas":{"top":7,"left":64.21875,"width":164,"hidden":0},"selecionaPrimeiroVencimento":{"top":7,"left":311.3125,"width":112,"hidden":0},"primeiroVencimentoData":{"top":7,"left":528,"width":72,"hidden":0},"primeiroVencimentoNum":{"top":7,"left":528,"width":72,"hidden":0},"valorAcrescimo":{"top":7,"left":646.203125,"width":104,"hidden":0},"Portador_D027_Id":{"top":30,"left":56.234375,"width":165,"hidden":0},"selecionaDias":{"top":30,"left":312.484375,"width":112,"hidden":0},"diaFixo":{"top":30,"left":528,"width":72,"hidden":0},"IntervalorDias":{"top":30,"left":528,"width":72,"hidden":0},"valorDesconto":{"top":30,"left":652.03125,"width":104,"hidden":0},"Ndcto":{"top":53,"left":8.015625,"width":152,"hidden":0},"Emissao":{"top":53,"left":326.953125,"width":104,"hidden":0},"fin001FormPagamentoReceber_btn":{"top":55,"left":730,"width":null,"hidden":0}}',

			);
			
			$queryT002 = <<<EOT
			SELECT 
			T002_Numero_Duplicata, 
			T002_Data_Emissao, 
			D024_Nome_Empresa,
			D024_Id, 
			T002_Data_Vencimento, 
			T002_Valor_Duplicata, 
			T002_Valor_Saldo
			FROM T002 
			left join D024 on D024_Id=T002_D024_Id 
			left join D027 on D027_Id=T002_D027_Id 
			left join T117 on T117_Id=T002_T117_Id 
			left join D014 on D014_Id=T002_D014_Id 
			left join D046 on D046_Id=D024_D046_Id 
			left join T007 on T007_Id=T002_T007_Id 
			left join C007 on C007_Id=T007_C007_Id_Vendedor_Interno 
			left join T005 on T005_Id=T007_T005_Id
			WHERE 			 		
EOT;
			$queryT002 .= gPegarParteWhere(gInsertExtraWhere(base64_decode($extra), "(T002_T002_Id_Agrupado<=0 or T002_T002_Id_Agrupado is null) AND ifnull(T002_T035_Id,0)<=0 AND T002_Flag_Reparcelar='S' AND T002_Data_Recebimento='0000-00-00' AND T002_C004_Id='{$g['empresaAtual']}' "));
			$queryT002 .= " ORDER BY T002_Data_Vencimento ";
			$T002 = mysql_query($queryT002);
			
			$valorSomado = 0;
			while($aT002 = mysql_fetch_array($T002)){
				$D024_Id = $aT002['D024_Id'];
				$valorSomado = ($valorSomado + $aT002['T002_Valor_Saldo']);
			}
			
			// echo "D024_Id: ".$D024_Id."<br />Total: ".$valorSomado;
			// die(); 
			
			

			// novo formulário			
			$form = new Formulario('fin001FormPagamento');			
			
			$select = array();
			$select[] = array('value' => '0', 'title' => 'A COMBINAR');
			for ($i=1; $i<= 10 ; $i++) {
				$valorParcela=$FIN001->fin001calcularParcelaCombo($i,$valorSomado);
				if(@$r_simular == true){
					if($i=='1'){
						$select[] = array('value' => $i, 'title' => str_pad($i,2,"0",STR_PAD_LEFT) . ($i == 1 ? " Parcela&nbsp;&nbsp;" : " Parcelas"), 'selected'=>true);
					}else{
						$select[] = array('value' => $i, 'title' => str_pad($i,2,"0",STR_PAD_LEFT) . ($i == 1 ? " Parcela&nbsp;&nbsp;" : " Parcelas"));
					}
				} else {
					if($i=='1'){
						$select[] = array('value' => $i, 'title' => str_pad($i,2,"0",STR_PAD_LEFT) . ($i == 1 ? " Parcela&nbsp;&nbsp;" : " Parcelas") . " - " .$valorParcela, 'selected'=>true);
					}else{
						$select[] = array('value' => $i, 'title' => str_pad($i,2,"0",STR_PAD_LEFT) . ($i == 1 ? " Parcela&nbsp;&nbsp;" : " Parcelas") . " - " .$valorParcela);
					}
				}
			}


			$C004=mysql_query("select * from C004 where C004_Id='{$g['empresaAtual']}'");
			$mC004=mysql_fetch_array($C004);
			$CNPJ_Empresa_Corrente = substr($mC004['C004_Cnpj'], 0, -8);
			// Definição do Formulário (veja documentação dentro da classe Formulario para ver todas as opções disponiveis)
			$formArray = array(
				'campos' => array(
					'geral' => array(
						'Total_Parcelas' => array('name' => 'Total_Parcelas', 'label' => 'Parcelas', 'type' => 'select', 'select' => $select, 'extra' => 'style="width:164px;"', 'reject' => true),
						//'valorParcelaSimulacao' => array('name' => 'valorParcelaSimulacao', 'label' => '', 'type' => 'text','extra'=>'style="background:#E8E8E8"','reject' => true, 'length' => array(3,6), 'align' => 'right', 'readonly'=>'readonly'),
						'valorAcrescimo' => array('name' => 'valorAcrescimo', 'label' => 'Acréscimo', 'type' => 'moeda', 'reject' => true, 'length' => array(26,12)),
						'valorDesconto' => array('name' => 'valorDesconto', 'label' => 'Desconto', 'type' => 'moeda', 'reject' => true, 'length' => array(26,12)),
						'selecionaPrimeiroVencimento' => array('name' => 'selecionaPrimeiroVencimento', 'label'=>'Primeira Parcela.', 'reject'=>true, 'type' => 'select','select' => 
						array(
							array('title' => 'Dias', 'value' => 'Dias'),
							array('title' => 'Vencimento', 'value' => 'Data', 'selected' => true),
						)),
						'primeiroVencimentoNum' => array('name' => 'primeiroVencimentoNum', 'label' => '', 'type' => 'text', 'reject' => true, 'length' => array(3,6), 'align' => 'right'),
						'primeiroVencimentoData' => array('name' => 'primeiroVencimentoData', 'label' => '', 'type' => 'data', 'reject' => true, 'align' => 'right'),
						'selecionaDias' => array('name' => 'selecionaDias', 'label'=>'Demais Parcelas', 'type' => 'select', 'reject'=>true,'select' => 
						array(
							array('title' => 'Intervalo', 'value' => 'I', 'selected' => true),
							array('title' => 'Dia Fixo', 'value' => 'F'),
						)),			
						'IntervalorDias' => array('name' => 'IntervalorDias', 'label' => '', 'type' => 'text', 'reject' => true, 'length' => array(3,6), 'align' => 'right'),
						'diaFixo' => array('name' => 'diaFixo', 'label' => '', 'type' => 'text', 'reject' => true, 'length' => array(3,2), 'align' => 'right'),												
						'Portador_D027_Id' => array('name' => 'D027_Id','label'=>'Portador', 'extra'=>'style="width:165px;"', 'reject'=>true, 'type' => 'select', 'select' =>
							gGeraSelect('D027', 'D027_Id', 'D027_Portador','LEFT JOIN D007 ON D007_Id = D027_D007_Id LEFT JOIN C004 ON C004_Id = D007_C004_Id WHERE C004_Cnpj LIKE "%'.$CNPJ_Empresa_Corrente.'%" OR ifnull(D027_D007_Id,0) = 0 order by D027_Portador', true)
						),
						'Ndcto' => array('name' => 'Ndcto', 'label' => 'Número Documento', 'type' => 'text', 'reject' => true, 'length' => array(27,100)),
						'Emissao' => array('name' => 'Emissao', 'label' => 'Data Emissão', 'type' => 'data', 'value'=>date("d/m/Y"), 'reject' => true, 'length' => array(10,100)),
						'fin001FormPagamentoReceber_btn' => array('name' => 'fin001FormPagamentoReceber_btn', 'type'=>'submit','reload'=>false,'value'=>'Gerar Parcelas','align' => 'right', 'reject'=>true),
						//'fin001FormAdicionarParcela_btn' => array('name' => 'fin001FormAdicionarParcela_btn', 'type'=>'submit', 'reload' => false, 'value' => 'Adicionar Parcela', 'align' => 'right', 'style' => 'float:right;', 'reject' => true),
						// 'T005_Observacao_Pagamento' => array('name' => 'T005_Observacao_Pagamento', 'label' => 'Observação', 'type' => 'textarea', 'length' => array(1,23)),
						// 'ven002SalvarObservacao_btn' => array('name' => 'ven002SalvarObservacao_btn', 'type'=>'submit', 'reload' => false, 'value' => 'Salvar Observação', 'align' => 'right', 'style' => 'float:right; margin-right:120px; margin-top:15px;', 'reject' => true),						'divReload' => array('name' => 'divReload', 'label' => '', 'type' => 'hidden', 'value' => @$r_divReload, 'reject' => true),
						'divReload' => array('name' => 'divReload', 'label' => '', 'type' => 'hidden', 'value' => @$r_divReload, 'reject' => true),
						'D024_Id' => array('name' => 'D024_Id', 'label' => '', 'type' => 'hidden', 'value' => @$D024_Id, 'reject' => true),
						'valorSomado' => array('name' => 'valorSomado', 'label' => '', 'type' => 'hidden', 'value' => @$valorSomado, 'reject' => true),
						'extra' => array('name' => 'extra', 'type' => 'hidden', 'value' => @$r_extra, 'reject' => true),
					)
				)
			);
		
		if(@$r_simular == true){
			$formArray['campos']['geral']['ValorSomado'] = array('name' => 'ValorSomado', 'label' => 'Saldo Devedor', 'type' => 'moeda', 'value' => gCorrigeNumero($valorSomado), 'reject' => true, 'disabled' => true);
			$formArray['campos']['geral']['ValorCompra'] = array('name' => 'ValorCompra', 'label' => 'Valor da Compra', 'type' => 'moeda', 'reject' => true);
		}
 
		$form->definirFormularioArray($formArray);
		
		echo <<<EOT
		    <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		    	$('#fin001FormAdicionarParcela_btn').unbind('click');
				$('#fin001FormAdicionarParcela_btn').unbind('click').bind('click', function(data) {
					var id = '{$row['T005_D024_Id']}';
						$.getJSON('/fin/fin001/form_func-ajax/inserirParcela/?ajax=true=' + '&' + $("#{$formId}").serialize() + '&callback=?', function(request) {
							if (request.code) {															
								divRefresh("{$r_divReload}");
							} else {
								var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
    							dialogConfirm("Erro!", request.data, buttons)
							}
						});
					return false;
				});
				$('#ven002SalvarObservacao_btn').unbind('click').bind('click', function(data) {
					$.getJSON('/ven/ven002/form_func-ajax/salvarObservacao/?ajax=true=' + '&' + $("#{$formId}").serialize() + '&T005_Id=' + encodeURIComponent('{$T005_Id}') + '&callback=?', function(request) {
						if (request.code) {															
							divRefresh('{$g['divId']}');
						} else {
							var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
							dialogConfirm("Erro!", request.data, buttons)
						}
					});
					return false;
				});
				$('#{$g['divId']} #selecionaDias').change(function() {
					if ($(this).val() == 'I') {
						$('#{$g['divId']} #diaFixo').val('').hide();
						$('#{$g['divId']} #IntervalorDias').show();
						$('#{$g['divId']} #IntervalorDias').focus();
					} else {
						$('#{$g['divId']} #IntervalorDias').val('').hide();
						$('#{$g['divId']} #diaFixo').show();
						$('#{$g['divId']} #diaFixo').focus();
					}
				});
				$('#{$g['divId']} #selecionaDias').trigger('change');
				
				$('#{$g['divId']} #selecionaPrimeiroVencimento').change(function() {
					if ($(this).val() == 'Dias') {
						$('#{$g['divId']} #primeiroVencimentoData').val('').hide();
						$('#{$g['divId']} #primeiroVencimentoNum').show();
						$('#{$g['divId']} #primeiroVencimentoNum').focus();
					} else {
						$('#{$g['divId']} #primeiroVencimentoNum').val('').hide();
						$('#{$g['divId']} #primeiroVencimentoData').show();
						$('#{$g['divId']} #primeiroVencimentoData').focus();
					}
				});
				$('#{$g['divId']} #selecionaPrimeiroVencimento').trigger('change');	
				/*
				// calcula e mostra o valor das parcelas (simulação)
				var changeHandler = function() {					
					$.getJSON('/fin/fin001/form_func-ajax/fin001calcularParcelaSimulacao/?ajax=true=' + '&' + $("#{$formId}").serialize() + '&callback=?', function(request) {
						if (request.code) {															
							//divRefresh("{$r_divReload}");
							$('#{$g['divId']} #valorParcelaSimulacao').val(request.data);
						} else {
							var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
    						dialogConfirm("Erro!", request.data, buttons)
						}
					});										
				}				
				$('#{$g['divId']} #Total_Parcelas').change(changeHandler).keyup(changeHandler);
				*/							
		    } </script></div>
EOT;

		
		     
		/**
		 * PARTE 3: Processamento do formulário (se foi efnviado)
		 * 		   E carregamento dos valores (se não foi enviado e possui acaoId)
		 */

			if ($formCodigo = $form->processarFormulario($form_tabela)) {
				// Form com erros
				if ($formCodigo == 1) {
					echo gGeraAlertMsgErroForm($form);
					// Form sem erros
				} else if ($formCodigo == 2) {
					// Gerar Boleto			
					$retorno = $FIN001->fin001atualizarParcelas($form);
					if ($retorno === true) {
						$alert = gGeraAlertMsg('Operação OK', 'Parcelas cadastradas com sucesso.', false);
						echo <<<EOT
							<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
									if (typeof jQuery != 'undefined') {
                                       //divRefresh("{$g['divId']}", true);
                                       divRefresh('{$form->campoValorEnviado('divReload')}');
                                       //divRefresh('{$g['divId']}', true);
                               	}
							} </script></div>
EOT;
					} else {
						echo gGeraAlertMsg('Error', 'Erro ao gerar Pacelas:<br />' . $retorno);
					}
				}
			}  else {
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
		$gerado = gGeraCamposForm($form, $tab, $acaoId, $enviar_btnId, $enviar_acaoHref, $formId, $form_campo_pk);


		/**
		 * PARTE 5: Exibir o formulário
		 */
		$g['smarty']->assign('formularioID', $form->formularioID());
		$g['smarty']->assign('table', $gerado['table']); $g['smarty']->assign('form_tabela', isset($form_tabela) ? $form_tabela : '');
		echo $gerado['pagina'];
		if (!$formMultiTab) { $g["smarty"]->assign("formId", $formId); }
		$g['smarty']->display('formEditor.tpl');
