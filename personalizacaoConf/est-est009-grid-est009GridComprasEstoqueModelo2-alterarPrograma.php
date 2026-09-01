<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est009-grid-est009GridComprasEstoque/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

include('prog/est/est009/grid_func-js.php');


		$filtroObrigatorio = true;
		$meses = 12;
		$periodo = date('Y-m-d', strtotime("-{$meses} month"));
		/**
		* Definição do Grid (Tabela)
		*/
		
		$grid = array();
		//array('colunaValor' => "excluirLinha", 'colunaTipo' => "livre", 'callback' => 'gExcluirLinha', 'callbackParameter' => array('D001_Id', $g['divId'])),
		$grid[] = array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'Id', 'chavePrimaria' => true,'colunaExibir' => false,'extra' => 'style="width:1%;"');
		$grid[] = array('colunaValor' => 'D009_Id', 'colunaTitulo' => 'Id', 'chavePrimaria' => true,'colunaExibir' => false,'style' => 'width:1%;','colunaAlinhar' => 'center');
		$grid[] = array('colunaValor' => 'D049_Id', 'colunaTitulo' => 'Id', 'chavePrimaria' => true, 'colunaExibir' => false);
		$grid[] = array('colunaValor' => 'D001_Codigo_Produto', 'colunaTitulo' => 'Código', 'style' => 'width:1%;font-weight:bold', 'callback' => 'abrirJanela', 'callbackParameter' => array('Produto', '/cad/cad002/content/form2/', 'D001_Id', '1000,583'));
		$grid[] = array('colunaValor' => 'D001_Descricao_Produto', 'colunaTitulo' => 'Descrição', 'style'=>'font-weight:bold');
		$grid[] = array('colunaValor' => 'D082_Marca', 'colunaTitulo' => 'Marca');
        $grid[] = array('colunaValor' => 'D009_Quantidade_Estoque', 'colunaTitulo' => 'Estq<BR>Físico', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'style' => 'font-weight:bold');
		$grid[] = array('colunaValor' => 'D009_Quantidade_Estoque_Loja', 'colunaTitulo' => 'Estq<BR>Loja', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right');
		$grid[] = array('colunaValor' => 'D009_Quantidade_Estoque_Liquido as Pedido', 'colunaTitulo' => 'Estq<br>Líquido', 'callback' => 'qtePedidos', 'colunaAlinhar' => 'right', 'style' => 'font-weight:bold;width:1%');
		$grid[] = array('colunaValor' => 'D009_Quantidade_Estoque_Fora', 'colunaTitulo' => 'Estq<br/>Fora', 'callback' => 'estoqueFora', 'colunaAlinhar' => 'right', 'style' => 'font-weight:normal;width:1%');
		$grid[] = array('colunaValor' => 'D009_Quantidade_Estoque_Similar', 'colunaTitulo' => 'Estq<BR>Similar', 'callback' => 'abrirJanela', 'callbackParameter' => array('Estoque Similares', '/cad/cad002/content/cad002ContentEstoqueSimilares/', 'D001_Id', '850,400', true), 'style' => 'width: 1%;', 'colunaAlinhar' => 'right');
		$grid[] = array('colunaValor' => 'D009_Quantidade_OC', 'colunaTitulo' => 'Qtde<BR>OC', 'callback' => 'abrirJanela', 'callbackParameter' => array('Ordens de Compra', '/ven/ven001/content/ven001contentProdutoOC/', 'D009_Id', '700,400', true), 'style' => 'width: 1%;', 'colunaAlinhar' => 'right');

		$mes = strtotime('-3 month');
		for ($i=0;$i<4;$i++){
			$dataInicial = date('Y-m-1', $mes);
			$dataFinal = date('Y-m-t', $mes);
			$tituloMes = date('m/Y', $mes);
			$grid[] = array('colunaValor' => "(select sum(T008_Quantidade) from T008 left join D009 on D009_Id=T008_D009_Id left join D049 on D049_Id=D009_D049_Id left join D006 on D006_Id=T008_D006_Id left join T007 on T007_Id=T008_T007_Id where D049_D001_Id=D001_Id and D006_Flag_MMF='S' and T007_Flag_Cancelada!='S' and T007_Data_Emissao>='$dataInicial' and T007_Data_Emissao<='$dataFinal') as vendas", 'colunaTitulo' => "$tituloMes", 'colunaCasasDecimais'=>'2', 'colunaAlinhar'=>'right', 'style' => 'background:#FFFFCC;');
			$mes = strtotime('+1 month', $mes);
		}
		
		$grid[] = array('colunaValor' => 'D009_Quantidade_Consumo', 'colunaTitulo' => 'Vendido', 'colunaAlinhar' => 'right', 'style'=>'background:#FFFF66;font-weight:bold', 'callback' => 'abrirNotificacao', 'callbackParameter' => array('Estatística de venda do produto', '/ven/ven001/outro/vendasDozeMeses/', 'D009_Id', 'right', true, false, false, '720'));

		$mes = time();
		for ($i=0;$i<4;$i++){
			$dataInicial = date('Y-m-1', $mes);
			$dataFinal = date('Y-m-t', $mes);
			$tituloMes = date('m/Y', $mes);
			$campo = date('Y-m-01', $mes);
			//Criação de uma coluna com concat para ser utilizado na passagem de parametros
			$grid[] = array('colunaValor' => "concat(D049_Id,'|','{$mes}') as parametro".$mes, 'colunaExibir' => false);
			$grid[] = array('colunaValor' => "(select sum(T055_Quantidade) from T055 left join T075 on T075_Id=T055_T075_Id where T055_D009_Id=D009_Id and ifnull(T075_Data_Estoque,'0000-00-00')='0000-00-00' and T075_Data_Previsao>='{$dataInicial}' and T075_Data_Previsao<='{$dataFinal}' and T075_Flag_MP != 1)", 'colunaTitulo' => "$tituloMes", 'colunaAlinhar'=>'right', 'style' => 'background:lightblue', 'callback' => 'abrirJanela', 'callbackParameter' => array('Previsão DI', '/est/est009/content/est009ContentPrevisaoDI/', 'parametro'.$mes, '700,400', true));
			$mes = strtotime('+1 month', strtotime($dataInicial));
		}

		$mes = time();
		for ($i=0;$i<4;$i++){
			$dataInicial = date('Y-m-1', $mes);
			$dataFinal = date('Y-m-t', $mes);
			$tituloMes = date('m/Y', $mes);
			$campo = date('Y-m-01', $mes);
			$grid[] = array('colunaValor' => "(SELECT IFNULL(T232_Quantidade,0) FROM T232 WHERE T232_D009_Id=D009_Id AND T232_MesAno = '{$campo}')", 'colunaTitulo' => "$tituloMes", 'colunaCasasDecimais'=>'2', 'colunaAlinhar'=>'right', 'colunaEditar' => 'true', 'colunaEditarJSCallback' => 'compraEstoqueProjetarFuturo', 'style' => 'background:#FFFF99;');
			$mes = strtotime('+1 month', strtotime($dataInicial));
		}


		//$grid[] = array('colunaValor' => "D009_Quantidade_Minima_Estoque", 'colunaTitulo' => 'Média<br>Mês', 'colunaAlinhar'=>'right', 'callback'=>'gCorrigeNumero');
		//$grid[] = array('colunaValor' => "D009_Frequencia_Venda", 'colunaTitulo' => 'Freq<br>Venda', 'colunaAlinhar'=>'center');
		//$grid[] = array('colunaValor' => "D009_Clientes_Consumo", 'colunaTitulo' => 'Clientes', 'colunaAlinhar'=>'center');
		$grid[] = array('colunaValor' => "D009_Quantidade_Comprar_Calculada", 'colunaTitulo' => 'Comprar', 'colunaAlinhar'=>'right', 'callback'=>'gCorrigeNumero', 'style'=>'background:lightgreen;font-weight:bold');
		$grid[] = array('colunaValor' => 'D049_Quantidade_Embalagem_Compra', 'colunaTitulo' => 'Emba<br>lagem', 'colunaAlinhar' => 'center', 'colunaCasasDecimais' => '2', 'colunaEditar' => true, 'colunaEditarTodos' => true);
        $grid[] = array('colunaValor' => 'D049_Quantidade_Minima_Compra', 'colunaTitulo' => 'Compra<br>mínima', 'colunaAlinhar' => 'center', 'colunaCasasDecimais' => '2', 'colunaEditar' => true, 'colunaEditarTodos' => true, 'style' => 'border-right:1px dashed silver');
		$grid[] = array('colunaValor' => 'D009_Quantidade_Comprar', 'colunaTitulo' => 'Comprar', 'colunaEditar'=>true, 'colunaCasasDecimais'=>'2', 'style' => 'background:#FCFC9F;font-weight:bold;font-size:14px');
		$grid[] = array('colunaValor' => 'D009_Flag_Cotar', 'colunaTitulo' => '', 'callback' => 'validaCampoCotar', 'style' => 'width:1%;background:#FCFC9F;', 'colunaAlinhar' => 'center', 'colunaEditarTodos' => 'S', 'colunaEditarTipo' => 'flag', 'colunaEditarExtra' => 'S|N', 'colunaEditarUsuario' => 'D009_C007_Id_Comprar');
		$grid[] = array('colunaValor' => 'D009_C007_Id_Comprar', 'colunaTitulo' => 'D009_C007_Id_Comprar', 'colunaExibir' => false);
		$grid[] = array('colunaValor' => 'D009_C004_Id', 'colunaTitulo' => 'D009_C004_Id', 'colunaExibir' => false);
		$grid[] = array('colunaValor' => 'D009_Meses_MMF', 'colunaTitulo' => 'Meses MMF', 'colunaEditar'=>true, 'colunaCasasDecimais'=>'2');
		//$grid[] = array('colunaValor' => 'D009_Meses_Compra', 'colunaTitulo' => 'Meses Compra', 'colunaEditar'=>true, 'colunaCasasDecimais'=>'2');
		//$grid[] = array('colunaValor' => 'D009_Flag_Cotar', 'colunaTitulo' => 'Cotar', 'callback' => 'validaCampoCotar');
		//$grid[] = array('colunaValor' => 'D009_Flag_Comprar', 'colunaTitulo' => 'Comprar', 'callback'=>'validaCampoComprar');
		//$grid[] = array('colunaValor' => "D009_Data_Ultima_Entrada", 'colunaTitulo' => 'Última entrada', 'callback'=>'gCorrigeData', 'colunaAlinhar'=>'right');
		//$grid[] = array('colunaValor' => "D009_Data_Ultima_Venda", 'colunaTitulo' => 'Última venda', 'callback'=>'gCorrigeData', 'colunaAlinhar'=>'right');
		//$grid[] = array('colunaValor' => "D009_Data_Ultima_Saida", 'colunaTitulo' => 'Última saída', 'callback'=>'gCorrigeData', 'colunaAlinhar'=>'right');

		/**
		* Definição da SQL
		*/
		$from = 'D009 as A';
		
		//left join D116 on D116_Id=D131_D116_Id	
		$extra = <<<EOT
			left join D049 on D049_Id=D009_D049_Id
			left join D001 on D001_Id=D049_D001_Id
			left join D082 on D082_Id=D049_D082_Id
			left join C008 on C008_Id=D001_C008_Id
			left join D003 on D003_Id=D001_D003_Id
			left join D060 on D001_D060_Id=D060_Id
			left join D002 on D001_D002_Id=D002_Id
			left join D015 on D001_D015_Id=D015_Id
			where (
				D009_C004_Id='{$g['empresaAtual']}'
				AND D001_Flag_Ativo='S'
			)
			group by D009_Id
EOT;
		
		//left join D116 on D116_Id=D131_D116_Id	
		$extraTotal = <<<EOT
			LEFT JOIN T187 on T187_D009_Id=D009_Id
			left join D049 on D049_Id=D009_D049_Id
			left join D001 on D001_Id=D049_D001_Id
			left join D082 on D082_Id=D049_D082_Id
			left join D083 on D083_D049_Id=D049_Id
			left join D024 on D024_Id=D083_D024_Id
			left join D037 on D037_Id=D001_D037_Id
			left join C008 on C008_Id=D001_C008_Id
			left join D003 on D003_Id=D001_D003_Id
			left join D060 on D001_D060_Id=D060_Id
			left join D002 on D001_D002_Id=D002_Id
			left join D015 on D001_D015_Id=D015_Id
			left join T001 on T001_Id=D009_T001_Id
			left join C007 on C007_Id=D009_C007_Id_Comprar
			where (
				D009_C004_Id='{$g['empresaAtual']}'
				AND D001_Flag_Ativo='S'
			)
EOT;

		/**
		* Filtro
		*/
		$gridFiltro = array(
			//'D131_Tipo' => array('titulo' => 'Tipos', 'tipo' => 'texto'),
			'D001_Codigo_Produto' => array('titulo' => 'Código', 'tipo' => 'numero'),
			'D083_Codigo_Produto_Fornecedor' => array('titulo' => 'Código Cliente', 'tipo' => 'numero'),
			'D001_Descricao_Produto' => array('titulo' => 'Descrição', 'tipo' => 'texto'),
			'D082_Marca' => array('titulo' => 'Marca', 'tipo' => 'texto'),
			'D009_Quantidade_Estoque' => array('titulo' => 'Estoque', 'tipo' => 'numero'),
			'D009_Quantidade_Comprar_Calculada' => array('titulo' => 'Qtd. Comprar', 'tipo' => 'numero'),
			'D009_Flag_Cotar' => array('titulo' => 'Cotar', 'tipo' => 'texto'),
			'D009_Flag_Comprar' => array('titulo' => 'Comprar', 'tipo' => 'texto'),
		);
		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		// Gera o filtro e acrescenta ao $extraTotal
		$extraTotal = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extraTotal, false, false, true);
		
		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra, 100);
		
		/**
		* Botões (Versão Acima do GRID)
		*/
		//$extra_ = urlencode(base64_encode($extra));
		$sql_ = urlencode(base64_encode($geradoSql));
		if (!personalizacaoBotoes()) { 
			echo <<<EOT
				<div class="buttonsBottomTop">
					<button type="button" id="est009GerarCotacaoNovoM2">Gerar Cotação Manual</button>
					<button type="button" id="est009GerarCotacaoAutomaticaM2">Gerar Cotações Automáticas</button>
					<button type="button" id="est009GerarOTEstoqueM2">Gerar Transferência</button>
					<button type="button" id="est009GerarMontagemEstoqueM2">Gerar Montagem</button>
					<button type="button" id="est014AtualizarCalculoEstoqueM2">Atualizar estoque</button>
				</div>
EOT;
		}			
		echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				$('#est014AtualizarCalculoEstoqueM2').unbind('click').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							$('#{$g['divId']}').showLoading();
							$.getJSON('/est/est009/grid_func-ajax/processaCalcularEstoque/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
								if (request.code) {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									dialogConfirm('Operação Ok', request.data, \$buttons);
									divRefresh('{$g['divId']}', true);
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
					dialogConfirm('Atualizar Estoque', 'Deseja atualizar o estoque? ', \$buttons);
				});

				$('#est009GerarCotacaoNovoM2').unbind('click').bind('click', function(data) {
					$('#{$g['divId']}').showLoading();
					$.getJSON('/est/est009/grid_func-ajax/est009GerarCotacao/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
						$('#{$g['divId']}').hideLoading();
						if (request.code) {
							var \$buttons = {
								"Adicionar": function() {
									var numeroCotacao = $(this).find('input').val();
									$('#{$g['divId']}').showLoading();
									$.getJSON('/est/est009/grid_func-ajax/adicionarProdutosCotacao/?ajax=true&T218_Id=' + numeroCotacao + '&extra=' + encodeURIComponent("{$sql_}") + '&tipo=comprasEstoque&callback=?', function(request) {
										$('#{$g['divId']}').hideLoading();
										if (request.code) {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Ok', request.data, \$buttons);
											abrirJanela(false, '{$g['divIdRoot']}', '{$g['divIdParent']}', unique(), '', 'Editar Cotação de Compra', '/est/est015/content/est015ContentEditarOrcamento/', '&acaoId=' + numeroCotacao, [1000,610]);
										} else {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Erro', request.data, \$buttons);
										}
										divRefresh('{$g['divId']}');
									});
									$(this).dialog("destroy").remove();
									$('#{$g['divId']}').find('.ui-tabs-panel:eq(0)').data('fechaForm', 1);
								},
								"Nova Cotação": function() {
									$('#{$g['divId']}').showLoading();
									abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Cadastrar Cotação', '/est/est015/content/est015ContentOrcamento/', '&tab=geral&GerarCotacaoEstoque=true&extra=' + encodeURIComponent("{$sql_}"), [650,500]);
									$('#{$g['divId']}').hideLoading();
									$(this).dialog("destroy").remove();
								},
								"Cancelar": function() { $(this).dialog("destroy").remove(); } 
							};
							var novoId = dialogConfirm('Gerar Cotação', request.data + '<br /><br />Digite o número de uma cotação para adicionar os produtos selecionados.<br /><br />Cotação: ', \$buttons);
							$('<input>').attr('id', 'numeroCotacao').addClass('formInput ui-state-default ui-corner-all').appendTo($("#dc-" + novoId).find('p')).focus();
							return false;
						} else {
							var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
				    		dialogConfirm('Erro', request.data, \$buttons);
						}
						$('#{$g['divId']}').hideLoading();
					});
				});
				
				$('#est009GerarCotacaoAutomaticaM2').unbind('click').bind('click', function(data) {
					$('#{$g['divId']}').showLoading();
					$.getJSON('/est/est009/grid_func-ajax/est009VerificarCotacaoEstoqueAutomatica/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
						$('#{$g['divId']}').hideLoading();
						if (request.code) {
							var \$buttons = {
								"Gerar Cotações": function() {
									var numeroCotacao = $(this).find('input').val();
									$('#{$g['divId']}').showLoading();
									$.getJSON('/est/est009/grid_func-ajax/est009GerarCotacaoAutomatica/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&tipo=cotacaoEmergencia&callback=?', function(request) {
										$('#{$g['divId']}').hideLoading();
										if (request.code) {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Ok', "Cotações geradas com sucesso!", \$buttons);
											abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Cotações Geradas - Grupo: ' + request.data, '/est/est015/content/est015ContentCotacoesGrupo/', '&T218_Grupo=' + encodeURIComponent(request.data), [800,600]);
										} else {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Erro', request.data, \$buttons);
										}
										divRefresh('{$g['divId']}');
									});
									$(this).dialog("destroy").remove();
									$('#{$g['divId']}').find('.ui-tabs-panel:eq(0)').data('fechaForm', 1);
								},
								"Cancelar": function() { $(this).dialog("destroy").remove(); } 
							};
							dialogConfirm('Gerar Cotação', request.data, \$buttons);
						} else {
							var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
							dialogConfirm('Erro', request.data, \$buttons);
						}
						$('#{$g['divId']}').hideLoading();
					});
				});
				$('#est009GerarOTEstoqueM2').unbind('click').bind('click', function(data) {
					$('#{$g['divId']}').showLoading();
					$.getJSON('/est/est009/grid_func-ajax/est009GerarOTEstoque/?ajax=true&sql=' + encodeURIComponent($('.divToScroll:visible:last').attr('gridsql')) + '&callback=?', function(request) {
						$('#{$g['divId']}').hideLoading();
						if (request.code) {
							abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Transferência', '/est/est016/content/GerarOT/', '&tab=geral&sql=' + encodeURIComponent($('.divToScroll:visible:last').attr('gridsql')) + '&count=' + encodeURIComponent(request.data) + '&origem=estoque', [550,200]);
							return false;
						} else {
							var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
							dialogConfirm('Erro', request.data, \$buttons);
						}
						$('#{$g['divId']}').hideLoading();
					});
				});
				$('#est009GerarMontagemEstoqueM2').unbind('click').bind('click', function(data) {
					$('#{$g['divId']}').showLoading();
					$.getJSON('/est/est009/grid_func-ajax/est009VerificarGerarMontagemEstoque/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
						$('#{$g['divId']}').hideLoading();
						if (request.code) {
							var \$buttons = {
								"Gerar Montagem": function() {
									var numeroCotacao = $(this).find('input').val();
									$('#{$g['divId']}').showLoading();
									$.getJSON('/est/est009/grid_func-ajax/est009GerarMontagemEstoque/?ajax=true&extra=' + encodeURIComponent("{$sql_}") + '&callback=?', function(request) {
										$('#{$g['divId']}').hideLoading();
										if (request.code) {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Ok', "Montagem gerada com sucesso!", \$buttons);
											abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Montagem', '/est/est007/content/est007ContentMontagensEditarNovo/', '&acaoId=' + encodeURIComponent(request.data), [850,500]);
										} else {
											var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
											dialogConfirm('Erro', request.data, \$buttons);
										}
										divRefresh('{$g['divId']}');
									});
									$(this).dialog("destroy").remove();
									$('#{$g['divId']}').find('.ui-tabs-panel:eq(0)').data('fechaForm', 1);
								},
								"Cancelar": function() { $(this).dialog("destroy").remove(); } 
							};
							dialogConfirm('Gerar Montagem', request.data, \$buttons);
						} else {
							var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
							dialogConfirm('Erro', request.data, \$buttons);
						}
						$('#{$g['divId']}').hideLoading();
					});
				});
			} </script></div>
EOT;
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);
		

		if (!personalizacaoTotais()) { 
			/**
			 * Totalizadores
			 */
			$itens = $g['sqlAuto']->pegarSqlCampo("count(distinct D009_Id)", $from, $extraTotal);
			$total = gCorrigeNumero($g['sqlAuto']->pegarSqlCampo("sum(T001_Valor_Preco_Unitario*if(D009_Quantidade_Comprar>0,D009_Quantidade_Comprar,if(D009_Quantidade_Comprar_Calculada>0,D009_Quantidade_Comprar_Calculada,0)))", $from, $extraTotal));
			
			echo <<<EOT
				<div class="buttonsBottomTop">
					<span class="item" style="text-align:right">Itens<br><input type="text" value="{$itens}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Valor compra R$<br><input type="text" value="{$total}" class="whiteBg" id="" size="11" readonly></span>
					<div style="clear:both;"></div>
				</div>
EOT;
		}

		/**
		* Define ação do click de abertura (ao clicar em uma linha do grid)
		*/
		$novoIdTelaForm = uniqid();
		echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				var dbclickPrevent = false;
				$('#{$g['divId']} .grid tr').unbind('click').unbind('click').bind('click', function(data) {
					// Previne duplo click (abrir duas janelas)
					if (dbclickPrevent) { return false; }
					dbclickPrevent = true;
					setTimeout(function() { dbclickPrevent = false; }, 500);
					// (substituir pelo mesmo ID da chave primária do grid)
					var chavePrimaria = 'D047_Id';	
					if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR') { return true; }
					var id = $(this).closest('tr').attr('id').split('|');
					var D001_Id = id[0].replace(/D001_Id-/, '');
					// Ação: abrir nova janela
	        		abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,'{$novoIdTelaForm}', '', 'Cadastro Produto', '/cad/cad002/content/form2/', '&acaoId=' + encodeURIComponent(D001_Id), [1000,600]);
		        });
				$('#{$g['divId']} .grid .campoEditavel').unbind('click').bind('click', function(event) { return false; });

				function compraEstoqueProjetarFuturo(idChavePrimaria, campoEditado, valorAntigo, novoValor) {
					$.getJSON('/est/est008/grid_func-ajax/compraEstoqueProjetarFuturo/?ajax=true&D009_Id=' + encodeURIComponent(idChavePrimaria) + '&campoEditado=' + encodeURIComponent(campoEditado) + '&novoValor=' + encodeURIComponent(novoValor) + '&callback=?', function(request) {
						if (request.code) {
							//
						} else {
							var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
				    		dialogConfirm("Erro!", request.data, \$buttons);
						}
					});
				}

			} </script></div>
EOT;

		/**
		 * Geração: Grid
		 */
		$gridTabela = $g['ui']->grid($grid, $geradoDados);
		$g['smarty']->assign('thead', $gridTabela['thead']);
		$g['smarty']->assign('tbody', $gridTabela['tbody']);
		$g['smarty']->assign('tfoot', $gridTabela['tfoot']);
		$g['smarty']->assign('gridId', md5($g['r_']));
		$g['smarty']->assign('gridPersonalizacao', json_encode(array($g['r_'], $g['divIdRoot'], $g['divId']), JSON_FORCE_OBJECT));
		if ($g['debug']) { $g['smarty']->assign('gridSql', $geradoSql); }
		gBotaoAuditoria($from);
		$primeiraLinhaGridHead = <<<EOT
			<tr>
				<th colspan="3" class="ui-widget-header ui-corner-all" style="text-align: center;">Produto</th>
				<th colspan="6" class="ui-widget-header ui-corner-all" style="text-align: center;">Estoque</th>
				<th colspan="4" class="ui-widget-header ui-corner-all" style="text-align: center;">Últimas vendas</th>
				<th colspan="1" class="ui-widget-header ui-corner-all" style="text-align: center;"></th>
				<th colspan="4" class="ui-widget-header ui-corner-all" style="text-align: center;">Importações</th>
			    <th colspan="4" class="ui-widget-header ui-corner-all" style="text-align: center;">Projeção venda</th>
				<th colspan="6" class="ui-widget-header ui-corner-all" style="text-align: center;"></th>
EOT;
		$g['smarty']->assign('primeiraLinhaGridHead', $primeiraLinhaGridHead);
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }




