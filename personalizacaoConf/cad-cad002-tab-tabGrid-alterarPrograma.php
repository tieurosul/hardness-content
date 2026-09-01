<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad002-tab-tabGrid/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

		// Form (caso seja o mesmo form em multiplas tabs)
		//$formId = uniqid();
		//$g['smarty']->assign('formId', $formId);
	
		// Definição das Tabs
		$tabs = array();
		$tabsList = array(
			// Identificador Unico, Programa a ser desenhado na Tab, Titulo, Variaveis GET (opcional)
			array(uniqid(), '/cad/cad002/grid/lista/', 'Produtos'),
			array(uniqid(), '/cad/cad002/grid/cad002GridPrecos/', 'Precos'),
			array(uniqid(), '/cad/cad002/grid/cad002GridCustos/', 'Custos'),
			//array(uniqid(), '/cad/cad002/grid/cad002GridOutros/', 'Outros'),
			array(uniqid(), '/cad/cad002/content/cad002ContentListaFornecedor/', 'Tabelas'),
			array(uniqid(), '/cad/cad007/grid/cad007grid01/', 'Documentos'),
			array(uniqid(), '/cad/cad002/grid/cad002gridInativos/', 'Inativos'),
			//array(uniqid(), '/cad/cad002/grid/cad002gridCodigosDuplicados/', 'Duplicados'),
            array(uniqid(), '/cad/cad002/grid/cad002GridHistoricoGeral/', 'Histórico'),
            //array(uniqid(), '/cad/cad002/grid/cad002GridValidade/', 'Validade'),
			array(uniqid(), '/cad/cad002/content/cad002ImportarCSV/','Importar CSV'),
		);

		if($g['c029Ids'][275]){
			$tabsList[] = array(uniqid(), '/cad/cad002/content/cad002ContentAtivosImobilizados/','Ativos Imobilizado (CIAP)');
		}
		
		// Processa Tabs
		$g['conteudo']['pagina'] = gProcessaTabs($tabsList, $tabs);
		
		// Carrega Template
		$g['smarty']->assign('tabs', $tabs);
		$g['smarty']->display('tab.tpl');



