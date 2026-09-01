<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven001-content-ven001ContentEditarOrcamento/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

// Limpando variável T004_Codigo_Local (ambiente) utilizada para memorizar último ambiente incluído no último produto do orçamento
		echo <<<EOT
			<script type="text/javascript">
				var T004_Codigo_Local = '';
			</script>
EOT;

		require_once('bibliotecas/classes/NOTIFICACOES.php');
		$NOTIFICACOES = new NOTIFICACOES();

		$T003=mysql_query("SELECT T003_Id,
								  T003_D013_Id, 
								  T003_C007_Id_Vendedor_Interno, 
								  T003_Flag_Notificacao_Liberacao,
								  T003A_Id,
								  T003_Flag_Perdido,
								  T003_Flag_Status_Orcamento	
							 FROM T003 
					    LEFT JOIN T003A ON T003_Id = T003A_T003_Id
							WHERE T003_Id='{$r_acaoId}'");
		$mT003 = mysql_fetch_assoc($T003);

		$T003A_Id = $mT003['T003A_Id'];

		if ($mT003['T003_C007_Id_Vendedor_Interno'] == $g['usuarioAtual']) {

			//Remover notificação de analise de orçamento
			if($mT003['T003_Flag_Notificacao_Liberacao'] != '') {
				if($mT003['T003_Flag_Notificacao_Liberacao'] == 'S') {
					mysqli_query("UPDATE T003 SET T003_Flag_Notificacao_Liberacao = '' WHERE T003_Id = '{$r_acaoId}'");
					//$NOTIFICACOES->orcamentoLiberado();
					$alertLiberacao = gGeraAlertMsg('Atenção', 'Este orçamento foi <b>aprovado</b>.');
				} else if($mT003['T003_Flag_Notificacao_Liberacao'] == 'N') {
					mysqli_query("UPDATE T003 SET T003_Flag_Notificacao_Liberacao = '' WHERE T003_Id = '{$r_acaoId}'");
					//$NOTIFICACOES->orcamentoNaoLiberado();
					echo gGeraAlertMsg('Atenção', 'Este orçamento foi <b>negado</b>.');
				}
			}

			$sqlT004 = mysql_query("SELECT T219_Flag_Comprar,
			                               T219_Flag_Lido, 
										   T003_Flag_Notificacao_Liberacao, 
										   T003_C007_Id_Vendedor_Interno, 
										   T219_Id, 
										   T004_Id, 
										   T004_Flag_Comprar,
										   T219_T218_Id,
										   T219_Observacao_Entrega 
									  FROM T004 
								 LEFT JOIN T219 ON T219_Id = T004_T219_Id 
								 LEFT JOIN T003 ON T003_Id = T004_T003_Id 
								 LEFT JOIN T004A ON T004_Id = T004A_T004_Id
								     WHERE T004_T003_Id = '{$r_acaoId}'");

			$alertCompras = '';
			$alertAnaliseCusto = '';

			while($rowT004 = mysql_fetch_array($sqlT004)){
				//Remover notificação de retorno de compras
				if($rowT004['T219_Flag_Comprar'] == 'S' && $rowT004['T219_Flag_Lido'] !='S') {
					if($rowT004['T219_T218_Id'] <= 0 && empty($rowT004['T219_Observacao_Entrega'])){
						// caso de solcitar prazo e ainda não ter sido respondido
						continue;
					}
					if (empty($alertCompras)) {
						$alertCompras = gGeraAlertMsg('Atenção', 'Existe retorno de compras para este orçamento.');
					}
					mysql_query("UPDATE T219 SET T219_Flag_Comprar = 'N', T219_Flag_Lido = 'S' WHERE T219_Id = '{$rowT004['T219_Id']}'");
				}

				//Remover notificação de retorno de análise de custo
				if($rowT004['T004_Flag_Comprar'] == 'C') {
					if (empty($alertAnalisCusto)) {
						$alertAnaliseCusto = gGeraAlertMsg('Atenção', 'Existe retorno de P.C.P para este orçamento.');
					}
					mysql_query("UPDATE T004 SET T004_Flag_Comprar = 'N' WHERE T004_Id = '{$rowT004['T004_Id']}'");
				}
			}

			echo $alertCompras;
			echo $alertAnaliseCusto;

		}
		
        // 22/01/2025 - Felipe Carrano
        // Validação por segurança porque em alguns casos estava zerando o T004_Percentual_Desconto e ajustando o T004_Valor_Preco_Unitario mantendo o valor final do item errado
        // Ainda não foi encontrado o problema
		$validaDesconto = $VEN001->validaDescontoAplicado($r_acaoId);
		if($validaDesconto !== true){
			echo gGeraAlertMsg('Atenção', $validaDesconto);
		}

		$D013_Id=$mT003['T003_D013_Id'];

		if (!empty($r_acaoId) && mysql_num_rows($T003)<=0) {
			$alert = gGeraAlertMsg('Erro', 'Este orçamento não existe!', false);
			echo <<<EOT
		   		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		   			$("#{$g['divId']}").closest('.ui-dialog-content').bind( "dialogclose", function(data, ui) {
									setTimeout(function() {
										{$alert}
									}, 150);
	  							});
					$("#{$g['divId']}").closest('.ui-dialog-content').dialog('close');
		   		} </script></div>
EOT;
			die();
		}
		/*
		 * Divisão da Area em Conteúdos
		 */
		// Como será dividio o conteúdo? (veja em: /smarty/templates/content-*.tpl)
		$content1 = uniqid();
		$g['smarty']->assign('content1', $content1);
		$g['smarty']->assign('tipo', 'unico');
		// O que será carregado em cada conteúdo?
		$actions = array(
			// identificador, url, paramentros get (opcional)
			array($content1, '/ven/ven001/tab/ven001TabEditarOrcamento/', '&acaoId=' . urlencode($r_acaoId) . '&T003A_Id=' . urlencode($T003A_Id) . '&divIdMain=' . urlencode($content1)),
		);
		echo gProcessaAcoes($actions, false);
		
		$disabled = false;
		if($mT003['T003_Flag_Perdido'] == 'S'){
			$disabled = true;
		}

		if($g["C031"]["bloqueioOrcamentoConfirmado"] == 'S') {
			$T005 = mysql_query("SELECT
									T005_Id,      
									(SELECT SUM(T004_Quantidade) FROM T004 WHERE T004_T003_Id = T003.T003_Id ) AS QtdOrcamento,
									(SELECT SUM(T006_Quantidade) FROM T005 LEFT JOIN T006 ON T006_T005_Id = T005_Id WHERE T005_T003_Id = T003.T003_Id) AS QtdPedido
								FROM T003
								LEFT JOIN T004 ON T003_Id = T004_T003_Id
								LEFT JOIN T005 ON T005_T003_Id = T003_Id
								WHERE T003_Id = '{$r_acaoId}'
								GROUP BY T003_Id");

			$mT005 = mysql_fetch_assoc($T005);
			if(!empty($mT005['T005_Id']) && $mT005['QtdPedido'] ==  $mT005['QtdOrcamento']) {
				$disabled = true;
			}
		}

		/**
		 * Botões
		 */

		 $botoes = array(
			array('titulo' => 'Excluir', 'id' => 'ven001ExcluirItems', 'desabilitar' => $disabled),
			array('titulo' => 'Desconto', 'id' => 'ven001AplicarDescontoTabOrcamento', 'exibirCallback' => 'return ($g["C031"]["botaoAplicarDesconto"] == "S");', 'desabilitar' => $disabled),
			array('titulo' => 'Importar', 'id' => 'ven001ImportarOrcamentoTabOrcamento', 'desabilitar' => $disabled),
			array('titulo' => 'Atualizar', 'id' => 'ven001AtualizarOpcoes', 'desabilitar' => $disabled),
			array('titulo' => 'Conf. tudo', 'id' => 'ven001ContetnProdutosBtnConfirmarTudo', 'exibirCallback' => "return ({$_REQUEST['acaoId']});", 'desabilitar' => $disabled),
			array('titulo' => 'Desconf. tudo', 'id' => 'ven001ContetnProdutosBtnZerarTudo', 'exibirCallback' => "return ({$_REQUEST['acaoId']});", 'desabilitar' => $disabled),
			array('titulo' => 'Gerar Pedido', 'id' => 'ven001GerarPedidoTabOrcamento', 'exibirCallback' => "return ({$_REQUEST['acaoId']});", 'desabilitar' => $disabled),
			array('titulo' => 'E-mail', 'id' => 'ven001EnviarEmail'),
			array('titulo' => 'WhatsApp', 'id' => 'ven001EnviarWhats'),
			array('titulo' => 'Imprimir', 'id' => 'ven001ImprimirOrcamento'),
			array('titulo' => 'Compras', 'id' => 'ven001Compras', 'exibirCallback' => 'return ($g["C031"]["desabilitarCotacaoEmergencia"] == "N");', 'desabilitar' => $disabled),
			array('titulo' => 'Dividir', 'id' => 'ven001DividirOrcamentoBtn', 'exibir' => false),
			array('titulo' => 'Fechar', 'tipo' => 'codigojs', 'callback' => "function callback($array) { return 'fecharJanela(\'".$g['divId']."\');'; }", 'posicao' => 'direita')
		);

		echo Botoes::processar($botoes, 'content');


		 //Verificar se o usuário possui alçadas para enviar e-mail e gerar pedido
		$usarAlcada = $g['C031']['usarAlcada'];
		 
		// Definição dos botões
/* 		$botoesMid = '';
		$botoesEsq = '';
		$botoesDir = '';
		$botoesEsq .= '<button type="submit" id="ven001ExcluirItems">Excluir</button>';
		//$botoesEsq .= '<button type="submit" id="ven001AplicarIpvTabOrcamento">Aplicar IPV</button>';
		if($g['C031']['botaoAplicarDesconto'] == 'S'){
			$botoesEsq .= '<button type="submit" id="ven001AplicarDescontoTabOrcamento">Desconto</button>';
		}
		$botoesEsq .= '<button type="submit" id="ven001ImportarOrcamentoTabOrcamento">Importar</button>';
		//$botoesEsq .= '<button type="submit" id="ven001AtualizarAliquotaBtn">Atualizar</button>';
		$botoesEsq .= '<button type="submit" id="ven001AtualizarOpcoes">Atualizar</button>';
		if(!empty($_REQUEST['acaoId'])){
			$botoesEsq .= '<button type="submit" id="ven001ContetnProdutosBtnConfirmarTudo">Confirmar tudo</button>';
			$botoesEsq .= '<button type="submit" id="ven001ContetnProdutosBtnZerarTudo">Desconfirmar tudo</button>';
			$botoesEsq .= '<button type="submit" id="ven001GerarPedidoTabOrcamento">Gerar Pedido</button>';
		}
		$botoesEsq .= '<button type="submit" id="ven001EnviarEmail">E-mail</button>';
		$botoesEsq .= '<button type="submit" id="ven001EnviarWhats">WhatsApp</button>';
		$botoesEsq .= '<button type="submit" id="ven001ImprimirOrcamento">Imprimir</button>';
		//$botoesEsq .= '<button type="submit" id="ven001DividirOrcamentoBtn">Dividir</button>';
		if($g['C031']['desabilitarCotacaoEmergencia'] == 'N') {
			$botoesEsq .= '<button type="submit" id="ven001Compras">Compras</button>';
		}
		//$botoesEsq .= '<button type="submit" id="ven001PDV">PDV</button>';
		$botoesDir .= '<button onclick="fecharJanela(\''.$g['divId'].'\'); return false;" class="btnFechar">Fechar</button>';
		//$g['smarty']->assign('botoesId', $g['divId']);
		$g['smarty']->assign('botoesEsq', $botoesEsq);
		$g['smarty']->assign('botoesMid', $botoesMid);
		$g['smarty']->assign('botoesDir', $botoesDir);
		$g['smarty']->display('buttons-floatBottom.tpl'); */
		// Ações dos botões
		$alertAlcada = gGeraAlertMsg('Gerar Pedido', 'Você não tem permissão para gerar orçamentos!', false);
		$novoIdTelaForm = uniqid();
		echo <<<EOT
		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {		
			$('#ven001DividirOrcamentoBtn').bind('click', function(data) {
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Dividir Orçamento', '/ven/ven001/content/ven001ContentDividirOrcamento/', '&tab=geral&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [1000,600]);
			});

			$('#ven001GerarPedidoTabOrcamento').unbind('click').bind('click', function(data) {

				$.getJSON('/ven/ven001/form_func-ajax/verificaAlcadaNovo/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&gerarPedido=true&callback=?', function(request) {
					$('#{$g['divId']}').hideLoading();
					if (request.code == 'erro') {
						dialogConfirm('Erro', request.data);
					} else if (request.code == 'analise') {
						dialogConfirm('Ok', request.data);
					} else if (request.code === true) {
						var \$buttons = {
							"Sim": function() {
								$('#{$g['divId']}').showLoading();
								$(this).dialog("destroy").remove();
								$.getJSON('/ven/ven001/form_func-ajax/gerarPedido/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
									$('#{$g['divId']}').hideLoading();
									if (request.code) {
										if (isset(request.alerta)) {
											dialogConfirm('Ok', request.alerta[1]);
											abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}' ,unique(), '', 'Editar Cliente/Fornecedor - ID: ' + request.alerta[0], '/cad/cad001/content/cad001contentCadastro/', '&acaoId=' + encodeURIComponent(request.alerta[0]) + '&tabela=D024', [930,583]);
										} else if(isset(request.notificacao)) {
											var \$buttons = {
												"Abrir Pedido": function() {
													$(this).dialog("destroy").remove();
													fecharJanela('{$g['divId']}');
													abrirJanela(data, '{$g['divIdRoot']}', '{$g['divIdParent']}' ,unique(), '', 'Editar Pedido - ID: ' + request.T005_Id, '/ven/ven002/content/ven002content02/', '&acaoId=' + encodeURIComponent(request.T005_Id), [990,605]);
													// O FW atualiza a linha do grid automaticamente, somente recarregar se não for grid
							                        if ($('#{$g['divIdParent']}').find('table.grid').length === 0) {
													    divRefresh('{$g['divIdParent']}', true);
													}
												}
											};
											dialogConfirm('Gerar Pedido', request.notificacao, \$buttons);
										} else {
											var \$buttons = {
												"Fechar": function() {
													$(this).dialog("destroy").remove();
													fecharJanela('{$g['divId']}');
													abrirJanela(data, '{$g['divIdRoot']}', '{$g['divIdParent']}' ,unique(), '', 'Editar Pedido - ID: ' + request.T005_Id, '/ven/ven002/content/ven002content02/', '&acaoId=' + encodeURIComponent(request.T005_Id), [990,605]);
													// O FW atualiza a linha do grid automaticamente, somente recarregar se não for grid
							                        if ($('#{$g['divIdParent']}').find('table.grid').length === 0) {
													    divRefresh('{$g['divIdParent']}', true);
												    }
												} 
											};
											dialogConfirm('Ok', request.data, \$buttons);
										}
									} else {
										dialogConfirm('Erro', request.data);
									}
								});
							},
							"Não": function() { $(this).dialog("destroy").remove(); } 
						};
						dialogConfirm('Gerar Pedido', request.data, \$buttons);

					} else {

						var \$buttons = {
							"Sim": function() {
								$('#{$g['divId']}').showLoading();
								$(this).dialog("destroy").remove();
								$.getJSON('/ven/ven001/form_func-ajax/enviarParaAnalise/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request2) {
									$('#{$g['divId']}').hideLoading();
									if (request2.code) {
										var \$buttons = {
											"Fechar": function() {
							 					$(this).dialog("destroy").remove();
							 					fecharJanela('{$g['divId']}');
							 					// O FW atualiza a linha do grid automaticamente, somente recarregar se não for grid
							                    if ($('#{$g['divIdParent']}').find('table.grid').length === 0) {
							 					    divRefresh('{$g['divIdParent']}', true);
							 					}
											}
										};
			    						dialogConfirm('Ok', request2.data, \$buttons);
									} else {
										var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
			    						dialogConfirm('Erro', request2.data, \$buttons);
									}
								});
							},
							"Não": function() { $(this).dialog("destroy").remove(); }
						};
			    		dialogConfirm('Gerar Pedido - Alçada', request.data, \$buttons);
					}
				});
			});
			
			$('#ven001AplicarIpvTabOrcamento').bind('click', function(data) {
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Aplicar IPV', '/ven/ven001/content/ven001ContentAplicarIpv/', '&tab=geral&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [320,130]);
			});
			$('#ven001AplicarDescontoTabOrcamento').bind('click', function(data) {
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Aplicar Desconto', '/ven/ven001/content/ven001ContentAplicarDesconto/', '&tab=geral&idTabelaCabecalho=' + encodeURIComponent("{$r_acaoId}") + '&tabelaCabecalho=T003&tabelaProduto=T004', [600,200]);
			});
			
			$('#ven001ImportarOrcamentoTabOrcamento').bind('click', function(data) {
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Importar Ítens', '/ven/ven001/content/ven001ContentImportarOrcamento/', '&tab=geral&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [320,130]);
			});
			
			$('#ven001AtualizarAliquotaBtn').bind('click', function(data) {
				var \$buttons = {
					"Sim": function() {
						var T003_Id = $('#{$g['divId']} #T003_Id').val();
						$('#{$g['divId']}').showLoading();
						$.getJSON('/ven/ven001/form_func-ajax/atualizarAliquota/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							if (request.code) {
								var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
								dialogConfirm('Ok', request.data, \$buttons);
								divRefresh('{$g['divId']}');
							} else {
								var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
								dialogConfirm('Erro', request.data, \$buttons);
							}
							$('#{$g['divId']}').hideLoading();
						});
						$(this).dialog("destroy").remove();
					},
					"Não": function() { $(this).dialog("destroy").remove(); }
				};
				dialogConfirm('Atualizar Alíquota', 'Você tem certeza? ', \$buttons);
			});

			$('#ven001EnviarEmail').bind('click', function(data) {
				$('#{$g['divId']}').showLoading();
				$.getJSON('/ven/ven001/form_func-ajax/verificarParcelas/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request1) {
					$('#{$g['divId']}').hideLoading();
					if(request1.code){
						$('#{$g['divId']}').showLoading();
						$.getJSON('/ven/ven001/form_func-ajax/verificaAlcadaNovo/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							$('#{$g['divId']}').hideLoading();
							if (request.code == 'erro') {
								dialogConfirm('Erro', request.data);
							} else if (request.code == 'analise') {
									dialogConfirm('Ok', request.data);
							} else if (request.code) {
								// Conferido
								$.getJSON('/ven/ven001/form_func-ajax/verificaPermissaoEnviarEmail/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
									if(request.code)
									{
										abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Enviar um Email', '/etc/etc006/content/etc006ContentEmailEnviar/', '&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [900,550]);
									}
									else
									{
										dialogConfirm('Erro', request.data);
									}
								});
							} else {
								var \$buttons = {
									"Sim": function() {
										$('#{$g['divId']}').showLoading();
										$(this).dialog("destroy").remove();
										$.getJSON('/ven/ven001/form_func-ajax/enviarParaAnalise/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&alcadaUpdate=' + encodeURIComponent(request.data) + '&callback=?', function(request2) {
											$('#{$g['divId']}').hideLoading();
											if (request2.code) {
												var \$buttons = {
													 "Fechar": function() {
														$(this).dialog("destroy").remove();
														fecharJanela('{$g['divId']}');
														divRefresh('{$g['divIdParent']}', true);
													 }
												};
												dialogConfirm('Ok', request2.data, \$buttons);
											} else {
												var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
												dialogConfirm('Erro', request2.data, \$buttons);
											}
										});
									},
									"Não": function() { $(this).dialog("destroy").remove(); }
								};
								dialogConfirm('Enviar E-mail', request.data, \$buttons);
							}
						});
					} else {
						var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
						dialogConfirm('Erro', request1.data, \$buttons);
					}
				});
			});

			$('#ven001EnviarWhats').bind('click', function(data) {
				$.getJSON('/ven/ven001/form_func-ajax/verificarParcelas/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request1) {
					if(request1.code){
						$.getJSON('/ven/ven001/form_func-ajax/verificaAlcadaNovo/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							if (request.code == 'erro') {
								dialogConfirm('Erro', request.data);
							} else if (request.code == 'analise') {
									dialogConfirm('Ok', request.data);
							} else if (request.code) {
								$.getJSON('/ven/ven001/form_func-ajax/ven001PhoneWhats/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request1) {
									dialogConfirm('Enviar orçamento WhatsApp: ' + decodeURIComponent(request1.phoneWhatsApp), "O que deseja fazer? " + decodeURIComponent(request1.notificaSemPhone) , [
										{
											text: "Enviar formato texto",
											click: function() {
												$.getJSON('/ven/ven001/form_func-ajax/orcamentoWhats/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&phone=' + request1.phoneWhatsApp + '&callback=?', function(request) {
													nova_janela('https://api.whatsapp.com/send?phone=' + request1.phoneWhatsApp + '&text=' + request.data,0,0,750,500,'yes','no','no','no','yes','no','WhatsApp');
												});
												$(this).dialog("destroy").remove();
												return false;
											}
										},
										{
											text: "Enviar link PDF",
											click: function() {
												$.getJSON('/ven/ven001/form_func-ajax/orcamentoWhatsPDF/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&phone=' + request1.phoneWhatsApp + '&callback=?', function(request) {
													if (request.code){
														nova_janela('https://api.whatsapp.com/send?phone=' + request1.phoneWhatsApp + '&text=' + request.data,0,0,750,500,'yes','no','no','no','yes','no','WhatsApp');
													}else{
														dialogConfirm('Erro', request.data);
													}		
												});
												$(this).dialog("destroy").remove();
												return false;
											}
										},
EOT;
										if (!empty($D013_Id)) {
											echo <<<EOT
											{
												text: "Atualizar celular contato",
												click: function() {
													abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Alterar contato', '/cad/cad001/content/cad001ContentAlterarContato/', '&tab=geral&acaoId=' + encodeURIComponent("{$D013_Id}"), [600,300]);
													$(this).dialog("destroy").remove();
													return false;
												}
											},
EOT;
										}
										echo <<<EOT
										{
											text: "Cancelar",
											click: function() {
												$(this).dialog("destroy").remove();
											}
										}
									]);
								});
							} else {
								var \$buttons = {
									"Sim": function() {
										$('#{$g['divId']}').showLoading();
										$(this).dialog("destroy").remove();
										$.getJSON('/ven/ven001/form_func-ajax/enviarParaAnalise/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&alcadaUpdate=' + encodeURIComponent(request.data) + '&callback=?', function(request2) {
											$('#{$g['divId']}').hideLoading();
											if (request2.code) {
												var \$buttons = {
													 "Fechar": function() {
														$(this).dialog("destroy").remove();
														fecharJanela('{$g['divId']}');
														divRefresh('{$g['divIdParent']}', true);
													 }
												};
												dialogConfirm('Ok', request2.data, \$buttons);
											} else {
												var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
												dialogConfirm('Erro', request2.data, \$buttons);
											}
										});
									},
									"Não": function() { $(this).dialog("destroy").remove(); }
								};
								dialogConfirm('Enviar E-mail', request.data, \$buttons);
							}
						});
					} else {
						var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
						dialogConfirm('Erro', request1.data, \$buttons);
					}
				});
			});

			$('#ven001ImprimirOrcamento').bind('click', function(data) {
				$('#{$g['divId']}').showLoading();
				$.getJSON('/ven/ven001/form_func-ajax/verificarParcelas/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request1) {
					$('#{$g['divId']}').hideLoading();
					if(request1.code){
						$('#{$g['divId']}').showLoading();
						$.getJSON('/ven/ven001/form_func-ajax/verificaAlcadaNovo/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							$('#{$g['divId']}').hideLoading();
							if (request.code == 'erro') {
								dialogConfirm('Erro', request.data);
							} else if (request.code == 'analise') {
								dialogConfirm('Ok', request.data);
							} else if (request.code) {
								$('#{$g['divId']}').showLoading();

								// Conferido
								$.getJSON('/ven/ven001/form_func-ajax/verificacaoGenericaImpressao/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
									if (!request.code) {
										dialogConfirm('Erro', request.data);
										$('#{$g['divId']}').hideLoading();
									}
									else
									{
										$.getJSON('/ven/ven001/form_func-ajax/ven001VerificarMoedaImpressao/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
											$('#{$g['divId']}').hideLoading();
											if(request.code === true){
												opcaoImprimir(1);
											} else if(request.code == 'verificarMoeda'){
												dialogConfirm('Imprimir Orçamento', "Selecione a moeda para impressão", [
													{
														text: "REAL",
														click: function() {
																opcaoImprimir(1);
																$(this).dialog("destroy").remove();
															return false;
														}
													},
													{
														text: request.moeda,
														click: function() {
																opcaoImprimir(request.data);
																$(this).dialog("destroy").remove();
															return false;
														}
													},
													{
														text: "Cancelar",
														click: function() {
																$(this).dialog("destroy").remove();
														}
													}
												]);
											} else {
												var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
												dialogConfirm('Erro', request.data, \$buttons);
											}
										});
									}
								});
							} else {
								var \$buttons = {
									"Sim": function() {
										$('#{$g['divId']}').showLoading();
										$(this).dialog("destroy").remove();
										$.getJSON('/ven/ven001/form_func-ajax/enviarParaAnalise/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&alcadaUpdate=' + encodeURIComponent(request.data) + '&callback=?', function(request2) {
											$('#{$g['divId']}').hideLoading();
											if (request2.code) {
												var \$buttons = {
													 "Fechar": function() {
														$(this).dialog("destroy").remove();
														fecharJanela('{$g['divId']}');
														divRefresh('{$g['divIdParent']}', true);
													 }
												};
												dialogConfirm('Ok', request2.data, \$buttons);
											} else {
												var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
												dialogConfirm('Erro', request2.data, \$buttons);
											}
										});
									},
									"Não": function() { $(this).dialog("destroy").remove(); }
								};
								dialogConfirm('Imprimir Orçamento', request.data, \$buttons);
							}
						});
					} else {
						var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
						dialogConfirm('Erro', request1.data, \$buttons);
					}
				});
			});

			function opcaoImprimir(moeda) {
				var buttons = {
					"Normal": function() {
						$(this).dialog("destroy").remove();
						imprimirOrcamento(moeda, 0);
					},
					"Inglês": function() {
						$(this).dialog("destroy").remove();
						imprimirOrcamento(moeda, 1);
					},
				};
				dialogConfirm('Imprimir Orçamento', 'Deseja imprimir a descrição do produto em inglês ou normal?', buttons);
			}

			function imprimirOrcamento(moeda,descIngles){
				$('#{$g['divId']}').showLoading();
				$.getJSON('/ven/ven001/form_func-ajax/ven001ImprimirOrcamento/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&moeda=' + encodeURIComponent(moeda) + '&descIngles=' + encodeURIComponent(descIngles) + '&callback=?', function(request) {
					$('#{$g['divId']}').hideLoading();
					if(request.code === true){
						var w = window.open(request.data);
						w.addEventListener("load", function(event) {
							setTimeout(function() {
								$.getJSON('/ven/ven001/form_func-ajax/ven001DeletarPDFOrcamento/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {});
							}, 3000);
						});
					} else {
						var \$buttons = { "OK": function() { $(this).dialog("destroy").remove(); } }
						dialogConfirm('Imprimir Orçamento', request.data, \$buttons);
					}
				});	
			}

			$('#ven001Compras').bind('click', function(data) {
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Compras', '/ven/ven001/content/ven001ContentCompras/', '&tab=geral&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [850,600]);
			});

			$('#ven001ExcluirItems').bind('click', function(data) {
				var \$buttons = {
					"Sim": function() {
						$('#{$g['divId']}').showLoading();
						$.getJSON('/ven/ven001/form_func-ajax/excluirItensMarcados/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
							$('#{$g['divId']}').hideLoading();
							if (request.code) {
								divRefresh('{$g['divId']}', true);
								dialogConfirm('Ok', request.data);
							} else {
								dialogConfirm('Erro', request.data);
							}
						});
						$(this).dialog("destroy").remove();
					},
					"Não": function() { $(this).dialog("destroy").remove(); }
				};
				dialogConfirm('Atenção', 'Deseja excluir os itens marcados?', \$buttons);
			});

			$('#ven001AtualizarOpcoes').bind('click', function(data) {
				var \$buttons = {
					"Atualizar Aliquotas": function() {
						var \$buttons2 = {
							"Sim": function() {
								var T003_Id = $('#{$g['divId']} #T003_Id').val();
								idProgressBar = dialogAlert('Atualizando alíquotas',"<span id='progressBar'>Processando 0%</span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' />");
					            ven001AtualizarAliquotasLoop(idProgressBar,T003_Id,1);
					            $(this).dialog("destroy").remove();
							},
							"Não": function() { $(this).dialog("destroy").remove(); }
						};
						$(this).dialog("destroy").remove();
						dialogConfirm('Atenção', 'Deseja atualizar as aliquotas?', \$buttons2);
					},
					"Atualizar CFOP": function() { 
						abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Atualizar CFOP', '/ven/ven001/content/ven001ContentAtualizarCFOP/', '&tab=geral&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [500,250]);
						$(this).dialog("destroy").remove();
					},
					"Atualizar preço tabela": function() {
						var \$buttons2 = {
							"Sim": function() {
								var T003_Id = $('#{$g['divId']} #T003_Id').val();
								$('#{$g['divId']}').showLoading();
								$.getJSON('/ven/ven001/form_func-ajax/atualizarPrecoTabela/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
									if (request.code) {
										var \$buttons2 = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
										dialogConfirm('Ok', "Preço(s) tabela atualizado com sucesso!", \$buttons2);
										divRefresh('{$g['divId']}');
									} else {
										var \$buttons2 = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
										dialogConfirm('Erro', request.data, \$buttons2);
									}
									$('#{$g['divId']}').hideLoading();
								});
								$(this).dialog("destroy").remove();
							},
							"Não": function() { $(this).dialog("destroy").remove(); }
						};
						$(this).dialog("destroy").remove();
						dialogConfirm('Atenção', 'Deseja atualizar o preço tabela dos produtos?', \$buttons2);
					},
					"Aplicar Preço P/ Kg": function() { 
						abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Aplicar Preço por KG', '/ven/ven001/content/ven001ContentAplicarPrecoPorKg/', '&T003_Id=' + encodeURIComponent("{$r_acaoId}"), [400,180]);
						$(this).dialog("destroy").remove();
					},
					"Cancelar": function() { 
						$(this).dialog("destroy").remove();
					}
				};
				var dialog = dialogConfirm('Atualizar', 'Qual atualização você deseja? ', \$buttons);
				var buttons = $('#dc-' + dialog).parent().find('.ui-dialog-buttonpane button')
				var botaoAplicarPrecoKg = $(buttons[3]);
				if('{$g['C031']['habilitarAplicarPrecoKgOrcamento']}' != 'S'){
					$(botaoAplicarPrecoKg).remove();
				}
			});

			function ven001AtualizarAliquotasLoop(idProgressBar, T003_Id, inicio){
                $.getJSON('/ven/ven001/form_func-ajax/atualizarAliquota/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&extra=' + encodeURIComponent("{$extra_}") + '&inicio=' + inicio + '&callback=?', function(request) {
                    if (request.concluido == 0) {
                    	$('#progressBar').html('<b>Processado '+ request.percentual + '%</b>' + '<br/><br/>Produtos atualizados: <b>' + request.ok + '</b><br/>Produtos com erro: <b> ' + request.erro + '</b><br /><br/><b> <font color="red">' + request.erros + '</font></b>');
                    	ven001AtualizarAliquotasLoop(idProgressBar,T003_Id,0);
                    }else{
                    	if (request.code) {
                    		$('#dc-' + idProgressBar).dialog("destroy").remove();
                    		dialogConfirm('Alíquotas atualizadas','<b>Processado '+ request.percentual + '%</b>' + '<br/><br/>Produtos atualizados: <b>' + request.ok + '</b><br/>Produtos com erro: <b> ' + request.erro + '</b><br /><br/><b> <font color="red">' + request.erros + '</font></b>');
                            divRefresh('{$g['divId']}');
                        } else {
                        	$('#dc-' + idProgressBar).dialog("destroy").remove();
                            dialogConfirm('Erro!', request.data);
                        }
                    } 
                });	                    
	        }

			if ("{$g['C031']['atualizaCFOPAliquotasPrecoAoEntrarVendaPendente']}" == 'S' && "{$r_atualizaDados}" == 'S' && "{$mT003['T003_Flag_Status_Orcamento']}" == 'P') {
				atualizaDadosDaVenda();
				divSetarAcaoId('{$g['divId']}', '', 'atualizaDados');
			}
			
			function atualizaDadosDaVenda() {
				$.getJSON('/ven/ven001/form_func-ajax/atualizarCFOP/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
					if (request.code) {
						T003_Id = $r_acaoId;
						idProgressBar = dialogAlert('Atualizando CFOP, Alíquotas, Preço Tabela',"<span id='progressBar'>Processando 0%</span><img src='/hardness3/static/img/loading.gif' style='width: 24px; height: 24px; margin: 6px; float: left;' />");
						ven001AtualizarCFOPAliquotasLoop(idProgressBar,T003_Id,1);  
					}
				});
			}
			
			function ven001AtualizarCFOPAliquotasLoop(idProgressBar, T003_Id, inicio){
                $.getJSON('/ven/ven001/form_func-ajax/atualizarAliquota/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&extra=' + encodeURIComponent("{$extra_}") + '&inicio=' + inicio + '&callback=?', function(request) {
					if (request.concluido == 0) {
						$('#progressBar').html('<b>Processado '+ request.percentual + '%</b>' + '<br/><br/>Produtos atualizados: <b>' + request.ok + '</b><br/>Produtos com erro: <b> ' + request.erro + '</b><br /><br/><b> <font color="red">' + request.erros + '</font></b>');
						ven001AtualizarCFOPAliquotasLoop(idProgressBar,T003_Id,0);
					} else {
						if (request.code) {
							// Aqui, após a conclusão da atualização das alíquotas, chamamos a função para atualizar o preço
							atualizarPrecoTabela();
							$('#dc-' + idProgressBar).dialog("destroy").remove();
						} 
					} 
				});                       
			}

			function atualizarPrecoTabela() {
				$.getJSON('/ven/ven001/form_func-ajax/atualizarPrecoTabela/?ajax=true&T003_Id=' + encodeURIComponent("{$r_acaoId}") + '&callback=?', function(request) {
					// Aqui você pode tratar a resposta, se necessário
				}); 
			}
			
			} </script></div>
EOT;

		// Ação botão PDV
		$sql = <<<EOT
SELECT
	D009_Id, D001_Descricao_Produto
FROM
	D001
left join D049 on D049_D001_Id=D001_Id  
left join D041 on D041_D049_Id=D049_Id  
left join D009 on D009_D049_Id=D049_Id  
LEFT JOIN D083 ON D001_Id=D083_D001_Id
EOT;
		$sql = base64_encode($sql);
        $cod = base64_encode(serialize(array('D001_Codigo_Barras', 'D083_Codigo_Barras')));
		$id = base64_encode("D009_Id");
        $desc = base64_encode("D001_Descricao_Produto");
		$concluirUrl = base64_encode("/ven/ven001/outros/adicionarItensPDV/");
		$concluirParams = base64_encode("&T003_Id={$r_acaoId}");
		// o preço pode ser um código PHP (neste caso precisa ser uma chamada para pegar o preço)
		$preco = <<<'EOT'
namespace hardness;
$_REQUEST['D009_Id'] = $rowGrid[$id];
$_REQUEST['T003_Id'] = '[[T003_Id]]';
$row = gCarregarPrograma('/ven/ven001/form_func-ajax/atualizarCamposProdutos/', 'row');
return $row['Preco_Produto'];
EOT;
		$preco = str_replace('[[T003_Id]]', $r_acaoId, $preco);
		$preco = base64_encode($preco);
		echo <<<EOT
<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {

$('#ven001PDV').unbind('click').bind('click', function(data) {
	abrirJanela(data, '{$g['divIdRoot']}', $('#{$g['divId']}').find('.grid').closest('.content').attr('id') , unique(), '', 'PDV', '/etc/etc016/content/etc016VerificarPorCodbarra/', '&tab=geral&pdv=true&sql=' + encodeURIComponent('{$sql}') + '&cod=' + encodeURIComponent('{$cod}') + '&id=' + encodeURIComponent('{$id}') + '&desc=' + encodeURIComponent('{$desc}') + '&concluirUrl=' + encodeURIComponent('{$concluirUrl}') + '&concluirParams=' + encodeURIComponent('{$concluirParams}') + '&preco=' + encodeURIComponent('{$preco}'), [900,550]);
});

} </script></div>
EOT;
