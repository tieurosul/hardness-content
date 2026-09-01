<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est004-grid-est004GridNotasFiscaisRecebidas/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

if (!function_exists('hardness\\est004SelecionarParanaCompetitivo')) {
    function est004SelecionarParanaCompetitivo($array)
    {
        $id = (int) $array['todosCampos']['T013_Id'];

        return '<input type="checkbox" class="paranaCompetitivoSelecionar" value="' . $id . '" />';
    }
}

		$filtroObrigatorio = ($g['C031']['obrigatorioFiltroTelaTransicao'] == 'S') ? true : false;
		
		$grid = array(
            array('colunaValor' => 'paranaCompetitivoSelecionar', 'colunaTitulo' => 'PC', 'colunaTipo' => 'livre', 'callback' => 'est004SelecionarParanaCompetitivo', 'colunaAlinhar' => 'center', 'style' => 'width:1%;'),
            array('colunaValor' => 'T013_Id', 'colunaTitulo' => 'Id', 'chavePrimaria' => true, 'colunaAlinhar' => 'center', 'style' => 'font-size:10px'),
			array('colunaValor' => 'T013A_Id', 'colunaTitulo' => 'T013A_Id', 'chavePrimaria' => true, 'colunaAlinhar' => 'center', 'style' => 'font-size:10px', 'colunaExibir' => false),
            array('colunaValor' => 'T013_Flag_Estoque_Finalizado', 'colunaTitulo' => 'Recebido', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Numero_Controle', 'colunaTitulo' => 'Contr', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Numero_Nota_Fiscal', 'colunaTitulo' => 'N.F.', 'colunaAlinhar' => 'center'),
            array('colunaValor' => '(SELECT GROUP_CONCAT(DISTINCT T225_T224_Id) FROM T014 LEFT JOIN T225 ON T225_Id = T014_T225_Id WHERE T014_T013_Id = T013_Id)', 'colunaTitulo' => 'OC', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'group_concat(distinct substr(Comprador.C007_Primeiro_Nome,1,10))', 'colunaTitulo' => 'Comprador', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Data_Emissao', 'colunaTitulo' => 'Emissão', 'callback' => 'gCorrigeData', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Data_Entrada', 'colunaTitulo' => 'Entrada', 'callback' => 'gCorrigeData', 'colunaAlinhar' => 'center', 'colunaEditar' => true, 'colunaEditarNivel' => '4', 'colunaEditarOculto' => true),
            array('colunaValor' => 'T013_Data_Entrada_Estoque', 'colunaTitulo' => 'Estoque', 'callback' => 'gCorrigeData', 'callbackParameter' => '1'),
            array('colunaValor' => 'T013_Data_Financeiro', 'colunaTitulo' => 'Financeiro', 'callback' => 'gCorrigeData', 'callbackParameter' => '1'),
            array('colunaValor' => 'D024_Id', 'colunaTitulo' => 'Código', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'Fornecedor', 'callback' => 'coloreLinha', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'D018_UF', 'colunaTitulo' => 'UF', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Tipo', 'colunaTitulo' => 'Tipo', 'callback' => 'validaCampoTipo'),
            array('colunaValor' => 'D006_Codigo_CFOP', 'colunaTitulo' => 'CFOP', 'colunaAlinhar' => 'center', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'T013_Valor_Total', 'colunaTitulo' => 'Total', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T013_Valor_Total_Produtos', 'colunaTitulo' => 'Produtos', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T013_Valor_ICMS_Substituicao', 'colunaTitulo' => 'ICMS ST', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T013_Valor_Frete', 'colunaTitulo' => 'Frete', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T013_Valor_Total_IPI', 'colunaTitulo' => 'IPI', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T013_Valor_Total_ICMS', 'colunaTitulo' => 'ICMS', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'Almoxarifado', 'colunaTitulo' => 'Estoque', 'callback' => 'validaCampoAlmoxarifado', 'colunaTipo' => 'livre', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'Cancelar', 'colunaTitulo' => 'Cancelar', 'callback' => 'validaCampoCancelar', 'colunaTipo' => 'livre', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'DANFE', 'colunaTitulo' => 'DANFE', 'callback' => 'validaCampoDanfe', 'colunaTipo' => 'livre', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'XML', 'colunaTitulo' => 'XML', 'callback' => 'validaCampoXML', 'colunaTipo' => 'livre', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T013_Chave_Acesso_Nfe', 'colunaTitulo' => 'Chave Acesso'),
            array('colunaValor' => 'T013_Numero_Protocolo_Nfe', 'colunaTitulo' => 'Protocoloo NFe'),
            array('colunaValor' => 'T013_Retorno_Nfe', 'colunaTitulo' => 'Retorno SEFAZ'),
            array('colunaValor' => 'Comprador.C007_Primeiro_Nome', 'colunaTitulo' => 'Comprador', 'colunaAlinhar' => 'center', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'T013_Flag_Cancelada', 'colunaTitulo' => '', 'colunaExibir' => false),
            array('colunaValor' => 'T013_Flag_Travar_Nota', 'colunaTitulo' => '', 'colunaExibir' => false),
            array('colunaValor' => 'T013_Flag_Financeiro_Finalizado', 'colunaTitulo' => 'Financeiro', 'colunaAlinhar' => 'center', 'colunaExibir'=> ''),
			array('colunaValor' => 'T013A_Codigo_Situacao_Documento', 'colunaTitulo' => 'Cód.Situação', 'colunaEditar' => true, 'colunaExibir' => false, 'colunaAlinhar' => 'center', 'colunaEditarTodos' => true,'colunaEditarGeraSelect' => array(
					// Esse campo vai ser utilizado apenas no SPED FISCAL
					array('title' => '...', 'value' => ''),
					array('title' => '00 - DOCUMENTO REGULAR', 'value' => '00'),
					array('title' => '01 - ESCRITURAÇÃO EXTEMPORÂNEA DE DOCUMENTO REGULAR', 'value' => '01'),
					array('title' => '02 - DOCUMENTO CANCELADO', 'value' => '02'),
					array('title' => '03 - ESCRITURADAÇÃO EXTEMPORÂNEA DE DOCUMENTO CANCELADO', 'value' => '03'),
					array('title' => '04 - NFe, NFCe OU CTe DENEGADO', 'value' => '04'),
					array('title' => '05 - NFe, NFCe OU CTe NUMERAÇÃO INUTILIZADA', 'value' => '05'),
					array('title' => '06 - DOCUMENTO FISCAL COMPLEMENTAR', 'value' => '06'),
					array('title' => '07 - ESCRITURADAÇÃO EXTEMPORÂNEA DE DOCUMENTO COMPLEMENTAR', 'value' => '07'),
					array('title' => '08 - DOCUMENTO FISCAL EMITIDO COM BASE EM REGIME ESPECIAL OU NORMAL ESPECÍFICA', 'value' => '08'),
					
				),
			),
			array('colunaValor' => 'T013A_Valor_Total_Base_ICMS_Mono_Retido', 'colunaTitulo' => 'Base ICMS Mono Ret.', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right','colunaExibir' => false),
			array('colunaValor' => 'T013A_Valor_Total_ICMS_Mono_Retido', 'colunaTitulo' => 'ICMS Mono Ret', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaExibir' => false),
			array('colunaValor' => 'T013A_Flag_Destaca_Frete_SPED', 'colunaTitulo' => 'Destaca Frete no SPED', 'colunaExibir' => true, 'colunaEditar' => true, 'colunaAlinhar' => 'center', 'colunaEditarTodos' => true,'colunaEditarGeraSelect' => array(
					// Esse campo vai ser utilizado apenas no SPED FISCAL
					array('title' => '...', 'value' => ''),
					array('title' => 'SIM', 'value' => 'S'),
					array('title' => 'NÃO', 'value' => 'N'),
				),
			),

        );
		
		/**
		 * Definição da SQL
		 */
		$from = "T013";
		
		$extra = <<<EOT
		-- JOIN
			left join D024 on D024_Id=T013_D024_Id
			left join D018 on D018_Id=D024_D018_Id
			left join D006 on D006_Id=T013_D006_Id
			left join C007 as Comprador on Comprador.C007_Id=T013_C007_Id_Vendedor_Retorno
			left join C007 as Supervisor on Supervisor.C007_Id=Comprador.C007_C007_Id
			left join T013A on T013_Id = T013A_T013_Id
		-- WHERE
			where T013_C004_Id='{$g['empresaAtual']}'
			and (IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', Comprador.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', (Supervisor.C007_Id='{$g['usuarioAtual']}' or Comprador.C007_Id='{$g['usuarioAtual']}') ,'1=1')) OR (Comprador.C007_Id IS NULL OR Comprador.C007_Id=0 OR Comprador.C007_Id='')) 
		-- GROUP BY	
			GROUP BY T013_Id
EOT;

		$extraTotais = <<<EOT
			left join D024 on D024_Id=T013_D024_Id
			left join D018 on D018_Id=D024_D018_Id
			left join C007 as Comprador on Comprador.C007_Id=T013_C007_Id_Vendedor_Retorno
			left join C007 as Supervisor on Supervisor.C007_Id=Comprador.C007_C007_Id
			where T013_C004_Id='{$g['empresaAtual']}'
			and (IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', Comprador.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', (Supervisor.C007_Id='{$g['usuarioAtual']}' or Comprador.C007_Id='{$g['usuarioAtual']}') ,'1=1')) OR (Comprador.C007_Id IS NULL OR Comprador.C007_Id=0 OR Comprador.C007_Id='')) 
EOT;


		/**
		* Botões (Versão Acima do GRID)
		*/
	
		$botaoIncluir = '';
		/*if(isset($g['c029Ids'][188])){
			$botaoIncluir = '<button type="button" id="est004IncluirNotaFiscal_Btn">Incluir</button>';
		}*/

		echo <<<EOT
			<div class="buttonsBottomTop">
				{$botaoIncluir}		
				<!-- <button type="button" onclick="nova_janela('/hardness/nota_recebida_lancamento/atualizar_pedidos_venda.php',0,0,820,600,'no','no','no','no','yes','yes',0);">Atualizar Pedidos Venda</button> -->
				<!--<button type="button" id="est004AtualizarPedidosVenda_Btn">Atualizar Pedidos Venda</button>-->
				
			</div>
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
				$('#est004IncluirNotaFiscal_Btn').unbind('click').bind('click', function(data) {
					abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Incluir nota Fiscal', '/est/est004/content/est004ContentCadastroNotaFiscal/', '&tab=geral', [990,550]);
				});
				$('#est004AtualizarPedidosVenda_Btn').unbind('click').bind('click', function(data) {
					$('#' + '{$g['divId']}').showLoading();
					$.getJSON('/est/est004/grid_func-ajax/atualizarPedidosVenda/?ajax=true&callback=?', function(request) {
					if (request.code) {
						var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
			    		dialogConfirm("Sucesso!", request.data, buttons)
			    		divRefresh('{$g['divId']}'); 
					} else {
						var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
			    		dialogConfirm("Erro!", request.data, buttons)
					}
				});
			});
			} </script></div>
EOT;
		/**
		* Filtro
		*/
		$gridFiltro = array(
		    'D024_Nome_Empresa,D024_Nome_Fantasia,D024_Id' => array('titulo' => 'Fornecedor', 'tipo' => 'texto'),
		    'T013_Numero_Nota_Fiscal' => array('titulo' => 'N.F', 'tipo' => 'texto'),
		    'T013_Data_Emissao' => array('titulo' => 'Emissão', 'tipo' => 'data'),
		    'Comprador.C007_Primeiro_Nome' => array('titulo' => 'Comprador', 'tipo' => 'texto'),
		    'T013_Numero_Controle' => array('titulo' => 'Controle', 'tipo' => 'texto'),
		    'T013_Data_Entrada' => array('titulo' => 'Entrada', 'tipo' => 'data'),
		    'T013_Data_Entrada_Estoque' => array('titulo' => 'Estoque', 'tipo' => 'data'),
		    'T013_Data_Financeiro' => array('titulo' => 'Financeiro', 'tipo' => 'data'),
		    'T013_Flag_Cancelada' => array('titulo' => 'NF Cancelada', 'tipo' => 'select', 'select' => array(
		        array('title' => 'TODAS', 'value' => ''),
		        array('title' => 'SIM', 'value' => 'S'),
		        array('title' => 'NÃO', 'value' => 'N'),
		      ),
		    ),
		    'D018_UF' => array('titulo' => 'UF', 'tipo' => 'texto'),
		    'T013_Numero_Protocolo_Nfe' => array('titulo' => 'Protocolo NFe', 'tipo' => 'texto'),
		    'T013_Chave_Acesso_Nfe' => array('titulo' => 'Chave NFe', 'tipo' => 'texto'),
		    'T224_Id' => array('titulo' => 'OC', 'tipo' => 'numero'),
		    'T013_Valor_ICMS_Substituicao' => array('titulo' => 'ICMS ST', 'tipo' => 'numero'),
			'T013_Valor_Frete' => array('titulo' => 'Frete', 'tipo' => 'numero'),
			'T013_Valor_Total_IPI' => array('titulo' => 'IPI', 'tipo' => 'numero'),
			'T013_Valor_Total_ICMS' => array('titulo' => 'ICMS', 'tipo' => 'numero'),
			'D006_Codigo_CFOP,D006_Descricao' => array('titulo' => 'CFOP', 'tipo' => 'texto'),
		    'T013_Tipo' => array('titulo' => 'Tipo', 'tipo' => 'select', 'select' => array(
		        array('title' => 'TODAS', 'value' => ''),
		        array('title' => 'NF-e', 'value' => '1'),
		        array('title' => 'ACP', 'value' => '2'),
				array('title' => 'NF CONSUMIDOR', 'value' => '3'),
				array('title' => 'Cupom Fiscal', 'value' => '4'),
				array('title' => 'NF', 'value' => '5'),
				array('title' => 'NF/Conta de Energia Elétrica', 'value' => '6'),
				array('title' => 'NFS de Comunicação', 'value' => '7'),
				array('title' => 'NFS de Telecomunicação', 'value' => '8'),		
				array('title' => 'NFS', 'value' => '10'),		
				array('title' => 'Outros', 'value' => '9')
		      ),
		    ),
		);

		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		$extra_ = base64_encode($extra);
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);

		function gridLinha($array) {
			if($array['todosCampos']['T013_Flag_Cancelada']=='S'){
				return 'linhaVermelho';
			} else  if($array['todosCampos']['T013_Flag_Estoque_Finalizado']=='S'){
				return 'linhaCinza';
			}
		}
		/**
		 * Totais Almir
		 */
		$totais = array(
			// sql
			array('nome' => 'Count', 'titulo' => 'Registros', 'totalizador' => 'count', 'callbackParameter' => 0),
			array('nome' => 'Valor_Produtos', 'valor' => 'T013_Valor_Total_Produtos', 'titulo' => 'Valor Produtos'),
			array('nome' => 'Valor_Total', 'valor' => 'T013_Valor_Total', 'titulo' => 'Valor Total'),
			array('nome' => 'Valor_ICMS_ST', 'valor' => 'T013_Valor_ICMS_Substituicao', 'titulo' => 'Total ICMS ST'),
			array('nome' => 'Valor_Frete', 'valor' => 'T013_Valor_Frete', 'titulo' => 'Total Frete'),
			array('nome' => 'Valor_IPI', 'valor' => 'T013_Valor_Total_IPI', 'titulo' => 'IPI R$'),
			array('nome' => 'Valor_ICMS', 'valor' => 'T013_Valor_Total_ICMS', 'titulo' => 'ICMS R$'),

		);

		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);

		/**
         * Botões Almir
         */
        $botoes = array(
			array('titulo' => 'Incluir', 'id' => 'est004IncluirNotaFiscal_Btn',  'exibirCallback' => 'return isset($g["c029Ids"][188]);'),
			array('titulo' => 'Preencher Observações', 'id' => 'est004ParanaCompetitivo', 'exibirCallback' => 'return isset($g["c029Ids"][303]);'),
        );
		echo Botoes::processar($botoes);
		echo <<<EOT
		<div class="divHidden"><script type="text/javascript">if (typeof jQuery != 'undefined') {
			$('#est004ParanaCompetitivo').unbind('click').bind('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var ids = $('#{$g['divId']} .paranaCompetitivoSelecionar:checked').map(function() { return this.value; }).get();
				$('#{$g['divId']}').showLoading();
				$.getJSON('/est/est004/grid_func-ajax/paranaCompetitivoPrevia/?ajax=true&ids=' + encodeURIComponent(ids.join(',')) + '&extra=' + encodeURIComponent('{$extra_}') + '&callback=?', function(request) {
					$('#{$g['divId']}').hideLoading();
					if (!request.code) { dialogConfirm('Erro', request.data); return; }
					var r = request.data, texto = ids.length ? 'Foram selecionadas ' + request.ids.length + ' notas fiscais.' : 'Foram encontradas ' + request.ids.length + ' notas fiscais nos filtros atuais.';
					texto += '<br><br>Itens aptos: ' + request.linhas.filter(function(l) { return l.situacao === 'apta para preenchimento'; }).length + '.<br>Já existentes: ' + r.ja_existentes + '.<br>Itens com alíquota diferente de 4%: ' + r.aliquota_diferente + '.<br>Deseja preencher automaticamente as observações fiscais do Paraná Competitivo?';
					dialogConfirm('Preencher Observações', texto, {'Confirmar': function() {
						$(this).dialog('destroy').remove();
						var notas = request.ids || [], tamanhoLote = 25, indice = 0, etapa = 0;
						var totalEtapas = Math.ceil(notas.length / tamanhoLote), progressoId = 'est004ParanaCompetitivoProgresso';
						var resumo = {notas_analisadas: notas.length, notas_atualizadas: 0, observacoes_incluidas: 0, ja_existentes: 0, ncm_sem_flag: 0, aliquota_diferente: 0, erros: 0, mensagens: []};
						if (!notas.length) { dialogConfirm('Nenhum registro', 'Não há notas fiscais para processar.'); return; }
						dialogAlert('Paraná Competitivo', "<div id='" + progressoId + "'></div>");
						function atualizarProgresso(mensagem) {
							var percentual = Math.round((indice / notas.length) * 100);
							$('#' + progressoId).html('<b>Etapa ' + (etapa || 1) + ' de ' + totalEtapas + '</b><br>' + mensagem + '<br><br>Registros processados: ' + indice + ' de ' + notas.length + ' (' + percentual + '%).<br>Notas atualizadas: ' + resumo.notas_atualizadas + '. Observações incluídas: ' + resumo.observacoes_incluidas + '.');
						}
						function processarProximoLote() {
							if (indice >= notas.length) {
								var pendencias = resumo.erros ? '<br><br><b>Pendências: ' + resumo.erros + '.</b><br>' + resumo.mensagens.join('<br>') : '';
								atualizarProgresso('<b>Processamento concluído.</b>' + pendencias);
								divRefresh('{$g['divId']}', true);
								return;
							}
							var lote = notas.slice(indice, indice + tamanhoLote); etapa++;
							atualizarProgresso('Processando registros ' + (indice + 1) + ' a ' + (indice + lote.length) + '.');
							$.getJSON('/est/est004/grid_func-ajax/paranaCompetitivoProcessar/?ajax=true&ids=' + encodeURIComponent(lote.join(',')) + '&callback=?', function(resp) {
								if (!resp.code) { $('#' + progressoId).html('<b>Processamento interrompido na etapa ' + etapa + '.</b><br>' + resp.data); return; }
								var s = resp.data;
								['notas_atualizadas', 'observacoes_incluidas', 'ja_existentes', 'ncm_sem_flag', 'aliquota_diferente', 'erros'].forEach(function(campo) { resumo[campo] += parseInt(s[campo] || 0, 10); });
								if (s.mensagens && s.mensagens.length) { resumo.mensagens = resumo.mensagens.concat(s.mensagens); }
								indice += lote.length;
								setTimeout(processarProximoLote, 0);
							}).fail(function() {
								$('#' + progressoId).html('<b>Processamento interrompido na etapa ' + etapa + '.</b><br>Não foi possível obter resposta do servidor.');
							});
						}
						processarProximoLote();
					}, 'Cancelar': function() { $(this).dialog('destroy').remove(); }});
				}).fail(function() { $('#{$g['divId']}').hideLoading(); dialogConfirm('Erro', 'Não foi possível iniciar o processamento.'); });

				return false;
			});
		}</script></div>
EOT;
	
		//if (!personalizacaoTotais()) {
			/**
			* Totais (Versão Acima do GRID)
			*/
			//RETIRADO POIS ESTAVA FORA DO PADRÃO E NÃO ESTAVA FUNCIONANDO PARA ALGUNS CLIENTES POR CONTA DO C004_Id
			// $C004_Id = false;
	  //       preg_match("/T013_C004_Id=\'([^\'])+\'/", $extra, $matches);
	  //       if (isset($matches[1])) { $C004_Id = $matches[1]; }
			// O extra utilizados para o total é diferente do utilizado para montar o grid
// 			$extraTotais1 = <<<EOT
// 			    left join D024 on D024_Id=T013_D024_Id
// 			    left join D018 on D018_Id=D024_D018_Id
// 			    left join D006 on D006_Id=T013_D006_Id
// EOT;

			// $extraTotais1 .= ($C004_Id > 0 ? " WHERE T013_C004_Id='{$C004_Id}' AND T013_Flag_Cancelada!='S'" : "");
	  //       $extraTotais1 = $g['ui']->gridFiltroExtra(false, $gridFiltro, $extraTotais1);
			// $extraTotais = <<<EOT
			//     left join T014 on T014_T013_Id=T013_Id
			//     left join T012 on T012_Id=T014_T012_Id
			//     left join D009 on D009_Id=T014_D009_Id
			//     left join D049 on D049_Id=D009_D049_Id
			//     left join D001 on D001_Id=D049_D001_Id
			//     left join D024 on D024_Id=T013_D024_Id
			//     left join D018 on D018_Id=D024_D018_Id
			//     left join D006 on D006_Id=T014_D006_Id
   //              left join T225 on T225_Id=T014_T225_Id
   //              left join T224 on T224_Id=T225_T224_Id
   //              left join C007 as Comprador on Comprador.C007_Id=T224_C007_Id_Vendedor_Interno
			// 	left join C007 as Supervisor on Supervisor.C007_Id=Comprador.C007_C007_Id
// EOT;
			// $extraTotais .= ($C004_Id > 0 ? " WHERE T013_C004_Id='{$C004_Id}' AND T013_Flag_Cancelada!='S' and (IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', Comprador.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', (Supervisor.C007_Id='{$g['usuarioAtual']}' or Comprador.C007_Id='{$g['usuarioAtual']}') ,'1=1')) OR (Comprador.C007_Id IS NULL OR Comprador.C007_Id=0 OR Comprador.C007_Id=''))" : "");

	       /* $extraTotais = $g['ui']->gridFiltroExtra(false, $gridFiltro, $extraTotais);
			$valorTotal  = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(concat(T013_Valor_Total,T013_Id))", $from, $extraTotais)));
			$valorTotal1 = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(T013_Valor_Total_Produtos)", $from, $extraTotais)));
			$valorTotal2 = $g['sqlAuto']->pegarSqlCampo("count(T013_Id)", $from, $extraTotais);
			$totalICMSST = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(T013_Valor_ICMS_Substituicao)", $from, $extraTotais)));
			$totalFrete  = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(T013_Valor_Frete)", $from, $extraTotais)));
			$totalIPI    = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(T013_Valor_Total_IPI)", $from, $extraTotais)));
			$totalICMS   = gCorrigeNumero(($g['sqlAuto']->pegarSqlCampo("sum(T013_Valor_Total_ICMS)", $from, $extraTotais)));

			
			
			echo $totais = <<<EOT
				<div class="buttonsBottomTop">
					<span class="item" style="text-align:right">Notas Recebidas<br><input type="text" value="{$valorTotal2}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Valor Produtos<br><input type="text" value="{$valorTotal1}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Valor Total<br><input type="text" value="{$valorTotal}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Total ICMS ST<br><input type="text" value="{$totalICMSST}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Total Frete<br><input type="text" value="{$totalFrete}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Total IPI<br><input type="text" value="{$totalIPI}" class="whiteBg" id="" size="11" readonly></span>
					<span class="item" style="text-align:right">Total ICMS<br><input type="text" value="{$totalICMS}" class="whiteBg" id="" size="11" readonly></span>
					<div style="clear:both;"></div>
				</div>
EOT;
			$g['smarty']->assign('totais', $totais);

			

			
		}*/
			
		
		$novoIdTelaForm = uniqid();
		
		
		echo <<<EOT
		<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
			$('#{$g['divId']} .grid tbody tr').unbind('click').bind('click', function(data) {
				if (data.target.nodeName != 'TD' && data.target.nodeName != 'TR' && data.target.nodeName != 'FONT') { return true; }
				var id = $(this).closest('tr').attr('id').split('|');
				var id = id[0].replace(/T013_Id-/, '');
				abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,unique(), '', 'Editar Nota Fiscal: ' + $(this).closest('tr').find('td:eq(1)').text(), '/est/est004/content/est004ContentEditarNotaFiscal/', '&acaoId=' + id + '&tabela=T013', 'auto', false, false);
			});
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
        gBotaoAuditoria($from);
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }



