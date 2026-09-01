<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est002-grid-est002GridSeparacaoProduto/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


		/**
		* Definição do Grid (Tabela)
		*/			
		$grid = array(									
			array('colunaValor' => 'T006_Id', 'colunaTitulo' => 'Id', 'chavePrimaria' => true, 'colunaExibir' => false),
			array('colunaValor' => 'T005_Id', 'colunaTitulo' => 'Pedido'),
			array('colunaValor' => 'T006_Codigo_Produto', 'colunaTitulo' => 'Código', 'colunaAlinhar' => 'left', 'style' => 'font-weight:bold' ),
			array('colunaValor' => 'T006_Descricao_Produto', 'colunaTitulo' => 'Produto', 'style' => 'font-weight:bold'),
			array('colunaValor' => 'D082_Marca', 'colunaTitulo' => 'Marca'),
			array('colunaValor' => 'D037_Unidade', 'colunaTitulo' => 'UN', 'colunaAlinhar' => 'center'),
			array('colunaValor' => 'T006_Quantidade_Estoque', 'colunaTitulo' => 'Estoque', 'colunaAlinhar' => 'right'),
			array('colunaValor' => 'T006_T206_Id', 'colunaExibir' => false),
			array('colunaValor' => 'T006_Quantidade', 'colunaTitulo' => 'Qtd', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'style' => 'font-weight:bold'),
			array('colunaValor' => 'T006_Hora_Entrega', 'colunaTitulo' => 'Entrega', 'colunaAlinhar' => 'center', 'colunaExibir'=>false),
			array('colunaValor' => 'T006_Quantidade_Separacao', 'colunaTitulo' => 'Separado','colunaCasasDecimais' => 2, 'colunaAlinhar' => 'right', 'colunaExibir' => false), 
		);
			
		/**
		* Definição da SQL
		*/
		$from = 'T006';
		$extra = <<<EOT
			left join T005 on T006_T005_Id=T005_Id
			left join D009 on T006_D009_Id=D009_Id
			left join D049 on D009_D049_Id=D049_Id
			left join D001 on D049_D001_Id=D001_Id
			left join D082 on D049_D082_Id=D082_Id
			left join D037 on D001_D037_Id=D037_Id
			WHERE T005_C004_Id='{$g['empresaAtual']}' AND T005_Flag_Status = '2'
			GROUP BY T006_Id
EOT;

		$tipos_ = gGeraSelect('C008', 'C008_Tipo', 'C008_Tipo');
$tipos = array(
	array('title' => 'Todos', 'value' => '')
);
$tipos = array_merge($tipos, $tipos_);
		/**
		* Filtro
		*/
		$gridFiltro = array(			
			'D001_Descricao_Produto,D001_Descricao_Ingles' => array('titulo' => 'Português/Inglês', 'tipo' => 'texto'),
	'D001_Codigo_Produto' => array('titulo' => 'Código', 'tipo' => 'numero'),
	'D082_Marca' => array('titulo' => 'Marca', 'tipo' => 'texto'),
	'C008_Tipo' => array('titulo' => 'Tipo', 'tipo' => 'select', 'select' => $tipos),
	'D001_Flag_Pre_Cadastro' => array('titulo' => 'Pré-Cadastro (S/N)?', 'tipo' => 'texto'),
	'D009_Quantidade_Estoque' => array('titulo' => 'Qte.Estoque', 'tipo' => 'moeda'),
    'D001_Foto' => array('titulo' => 'IMPA/FANN', 'tipo' => 'numero'),
    'D009_Data_Alteracao_Preco' => array('titulo' => 'Dt Alteração', 'tipo' => 'data'),
	'D001_Flag_Pre_Cadastro' => array('titulo' => 'Pré-Cadastro', 'tipo' => 'select', 'select' => array(
		array('title' => 'Todos', 'value' => ''),
		array('title' => 'Sim', 'value' => 'S'),
		array('title' => 'Não', 'value' => 'N'),
	), 'naoGerarExtra' => false),
		);
		// Gera o filtro e acrescenta ao $extra
		$extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
		
		// Imprime o filtro do GRID (normalmente executado acima do GRID)
		echo $g['ui']->gridFiltroPrint($gridFiltro);

		$valorTotal = gCorrigeNumero($g['sqlAuto']->pegarSqlCampo("T006_Valor_Preco_Sem_Desconto_Unitario * T006_Quantidade", $from, $extra, false, true, true));

		if (!personalizacaoTotais()) {
			echo <<<EOT
			<div class="buttonsBottomTop">
<span class="item" style="text-align:right;margin-left:650px;">Total s/ IPI  <input type="text" value="{$valorTotal}" class="whiteBg" id="" size="11" readonly></span>
				<div style="clear:both;"></div>
			</div>
EOT;
		}
		
		/**
		 * Geração: Monta o SQL e retorna o dados
		 */
		list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);
		
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
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }
	




