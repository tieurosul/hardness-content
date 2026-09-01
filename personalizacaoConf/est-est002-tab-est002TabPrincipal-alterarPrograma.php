<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /est-est002-tab-est002TabPrincipal/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

		echo $r_acaoId;

		// Definição das Tabs
		$tabs = array();
		$tabsList = array();
		// Identificador Unico, Programa a ser desenhado na Tab, Titulo, Variaveis GET (opcional)
		if($g['C031']['modeloExpedicao'] == '1') {
			if ($g['C031']['expedicaoDeMercadoria'] == 'normal') {
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridImpressao', 'Impressão', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridImpressaoProduto/', 'Produto Imp', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridSeparacao/', 'Separação', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridSeparacaoProduto/', 'Produto Sep', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			} else {
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridImpressao', 'Aguardando Separação', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
				$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridSeparacao/', 'Em Separação', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			}
			$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridConferencia/', 'Conferência', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridConferenciaProduto/', 'Produto Conf', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		} else if($g['C031']['modeloExpedicao'] == '2') {
			$tabsList[] = array(uniqid(), '/est/est002/content/est002ContentSeparacaoComercial/', 'Separação Comercial', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			$tabsList[] = array(uniqid(), '/est/est002/content/est002ContentSeparacaoBalcao/', 'Separação Balcão', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridConferenciaComercial/', 'Conferência Comercial', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
			$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridConferenciaBalcao/', 'Conferência Balcão', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		}

		//Abas de emissão da nota fiscal
		$tabsList[] = array(uniqid(), '/ven/ven012/grid/ven012GridPrincipal/', 'Gerar Notas Fiscais', "&acaoId=" . urlencode($_REQUEST['acaoId']),'',true);
		//$tabsList[] = array(uniqid(), '/ven/ven012/grid/ven012GridFilaDeEnvioNovo/', 'Enviar NFe para Sefaz', "&acaoId=" . urlencode($_REQUEST['acaoId']),'',true);
		$tabsList[] = array(uniqid(), '/ven/ven012/tab/ven012GridFilaDeEnvioNovo/', 'Enviar NFe para Sefaz', "&acaoId=" . urlencode($_REQUEST['acaoId']),'',true);

		$tabsList[] = array(uniqid(), '/sistema/personalizacao/grid/bcd712131dba3a816781b73aee2f3bb1/', 'Agend. Entrega', '',false,true);

		//array(uniqid(), '/est/est002/grid/est002GridEmissaoNF/', 'Emissão NF', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true),
		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridAguardandoEntrega/', 'Aguardando Coleta', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);	
		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridColetados/', 'Coletados', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		

		if(isset($g['c029Ids'][242])){
			$tabsList[] = array(uniqid(), '/fis/fis012/content/fis012ContentPrincipal/', 'MDF-e');
		}
		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridAguardandoEntregaProduto/', 'Produto Aguard', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridEntregaInterna/', 'Entrega Interna', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//array(uniqid(), '/est/est002/content/est002ContentEstoque/', 'Estoque', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true),
		//array(uniqid(), '/est/est002/grid/est002GridAguardandoLiberacao/', 'Aguardando Liberação', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true),
		//array(uniqid(), '/est/est003/grid/est003GridNaoLiberado/', 'Não Liberado', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true),
		//$tabsList[] = array(uniqid(), '/est/est003/grid/est003GridAguardandoProdCompra', 'Aguardando Prod/Compra', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		$tabsList[] = array(uniqid(), '/est/est003/grid/est003GridDivergencias/', 'Divergências', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridEmprestimo/', 'Empréstimo', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est003/grid/est003GridListaBox/', 'Lista Box', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est003/content/est003Content/', 'Estoque', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridGeral/', 'Geral', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridControleProduto/', 'Controle do Produto', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		//$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridSeparacaoProduto/', 'Produtos em Separação', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridPedidosRetornados/', 'Pedidos Retornados', "&acaoId=" . urlencode($_REQUEST['acaoId']),false,true);
		if(isset($g['c029Ids'][38])){
			$tabsList[] = array(uniqid(), '/fin/fin005/grid/fin005GridPrincipal/', 'Boleto Bancário');
		}

		$tabsList[] = array(uniqid(), '/est/est002/grid/est002GridPortalPostal/', 'Correios');

		// Processa Tabs
		echo gProcessaTabs($tabsList, $tabs);

		// Carrega Template
		$g['smarty']->assign('tabs', $tabs);
		$g['smarty']->display('tab.tpl');








