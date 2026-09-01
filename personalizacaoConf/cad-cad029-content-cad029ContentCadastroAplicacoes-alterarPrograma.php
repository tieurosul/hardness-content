<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad029-content-cad029ContentCadastroAplicacoes/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
/*
$D149=mysql_query("SELECT COUNT(*),D005_Id,D149_Id FROM D005 LEFT JOIN D149 ON D149_D005_Id=D005_Id GROUP BY D149_D005_Id HAVING COUNT(*) > 3 ");
while($mD149=mysql_fetch_array($D149)){
	$D005=mysql_query("SELECT * FROM D149 WHERE D149_D005_Id='{$mD149['D005_Id']}' ORDER BY D149_Id DESC;");
	$C004_Id=0;
	while($mD005 = mysql_fetch_array($D005)){
		if($C004_Id == $mD005['D149_C004_Id']){
			mysql_query("DELETE FROM D149 WHERE D149_Id='{$mD005['D149_Id']}'");
			//echo "D149_Id: ".$mD005['D149_Id']. " D005_Id: ".$mD005['D149_D005_Id']." C004: ".$mD005['D149_C004_Id']."<br />";
			continue;
		}
		$C004_Id=$mD005['D149_C004_Id'];
	}
}
die();
*/
/*
		* Divisão da Area em Conteúdos
		*/
		// Como será dividio o conteúdo? (veja em: /smarty/templates/content-*.tpl)
		$content1 = uniqid();
		$g['smarty']->assign('content1', $content1);
		$g['smarty']->display('content-full.tpl');
		// O que será carregado em cada conteúdo?
		$actions = array(
			// identificador, url, paramentros get (opcional)
			array($content1, '/cad/cad029/tab/cad029TabCadastroAplicacoes/', '&acaoId=' . urlencode($r_acaoId))
		);
		echo gProcessaAcoes($actions);

