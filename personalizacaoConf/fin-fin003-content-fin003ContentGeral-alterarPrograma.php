<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /fin-fin003-content-fin003ContentGeral/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

   		/*
		 * Divisão da Area em Conteúdos
		 */
		// Como será dividio o conteúdo? (veja em: /smarty/templates/content-*.tpl)
		$content1 = uniqid();
		$content2 = uniqid();
		$g['smarty']->assign('content1', $content1);
		$g['smarty']->assign('content2', $content2);
		$g['smarty']->assign('content1Height', '220px');
		$g['smarty']->display('content-2rows.tpl');		
		// O que será carregado em cada conteúdo?
		$actions = array(
			// identificador, url, paramentros get (opcional)
			array($content1, '/fin/fin003/tab/fin003TabContent1/', '&divReload=' . urlencode($content2)),
			array($content2, '/fin/fin003/tab/fin003TabContent2/', '&divReload=' . urlencode($content1)),
		);
		echo gProcessaAcoes($actions);
