<?php
namespace hardness;

if($r_die == 'true'){
	apc_delete('cacheN001-e211_novo');
}

require_once('bibliotecas/classes/NOTIFICACOES.php');
$NOTIFICACOES = new NOTIFICACOES();

// Loop empresas
$resEmp = mysql_query("SELECT C004_Id FROM C004");
while ($rowEmp = mysql_fetch_assoc($resEmp)) {
	reprocessarEmpresaAtual($rowEmp['C004_Id']);
	
	$NOTIFICACOES->atualizarNotificacoesT006();
	$NOTIFICACOES->atualizarNotificacoesT005();
	$NOTIFICACOES->atualizarNotificacoesT007();
	$NOTIFICACOES->atualizarNotificacoesT003();
	$NOTIFICACOES->atualizarNotificacoesT218();
	$NOTIFICACOES->atualizarNotificacoesT219();
	$NOTIFICACOES->atualizarNotificacoesT224();
	$NOTIFICACOES->atualizarNotificacoesT225();
	$NOTIFICACOES->atualizarNotificacoesT013();
	$NOTIFICACOES->atualizarNotificacoesT004();
	$NOTIFICACOES->atualizarNotificacoesT014();
	$NOTIFICACOES->atualizarNotificacoesT047();
	$NOTIFICACOES->atualizarNotificacoesT245();
	$NOTIFICACOES->atualizarNotificacoesT048();
	$NOTIFICACOES->atualizarNotificacoesT256();
}



