<?php
namespace hardness;
/**
* Este código PHP irá substituir o callback: gJuntarCodigos()
*/

function _gJuntarCodigos ($tabela, $antigo, $novo, $tabelasBloqueio = array(), $tabelasRestricao = array(), $executarTriggersPHP = true)
{
	// Normalizar
	$antigo = (int) $antigo;
	$novo = (int) $novo;
	$tabela = strtoupper($tabela);

	// Verificações
	if ($antigo <= 0 ||  $novo <= 0) {
		return 'Os dois códigos devem ser preenchidos.';
	}
	if ($antigo == $novo) {
		return 'Os códigos não podem ser o mesmo.';
	}
	//mysql_query("SET @disable_trigger = 1;");
	// Precorre todas as tabelas
	$resShowTables = mysql_query('SHOW TABLES');
	if (!$resShowTables) { return 'Erro interno 1'; }
	while ($showTable = mysql_fetch_array($resShowTables)) {
		// Para cada tabela, verifica se existe algum campo da chave para mudar
		$showTable = strtoupper($showTable[0]);
		if (strpos(" $showTable ",'DIM0') || strpos(" $showTable ",'FAC0')){
			continue;
		}
                log("JUNTAR CODIGOS - desc: DESC {$showTable} '%{$tabela}_Id%'");
		$resDesc = mysqli_query("DESC {$showTable} '%{$tabela}_Id%'");
		if (!$resDesc) { return 'Erro interno 22 ' ."DESC {$showTable} '%{$tabela}_Id%' " . mysql_error(); }
		while ($desc = mysql_fetch_array($resDesc)) {
			$desc = $desc[0];
			if ($desc == "{$tabela}_Id") { continue; }
			// Se for uma tabela de bloqueio, tem que verificar se existe algum registro
			if (in_array($showTable, $tabelasBloqueio)) {
                log("JUNTAR CODIGOS - select: SELECT {$desc} FROM {$showTable} WHERE {$desc} = '{$antigo}'");
				$resCount = mysql_query("SELECT {$desc} FROM {$showTable} WHERE {$desc} = '{$antigo}'");
				if (!$resCount) { return 'Erro interno 3'; }
				if (mysql_num_rows($resCount) > 0) {
					return "BLOQUEADO-{$showTable}";
				}
			} else {
				$sql = "UPDATE {$showTable} SET {$desc} = '{$novo}' WHERE {$desc} = '{$antigo}'";
                log("JUNTAR CODIGOS - update: ".$sql);
				$res = mysqli_query($sql);
				if (!$res && in_array($showTable, $tabelasRestricao)) { 
                log("JUNTAR CODIGOS - delete: DELETE FROM {$showTable} WHERE {$desc} = '{$antigo}'");
					mysql_query("DELETE FROM {$showTable} WHERE {$desc} = '{$antigo}'", $executarTriggersPHP);
				} else if (!$res) {
					return 'Erro interno 4 Erro: ' . mysql_error();
 				}
			}
		}
	}
	// Apaga o registro antigo
	$sql = "DELETE FROM {$tabela} WHERE {$tabela}_Id = {$antigo}";
	$res = mysql_query($sql, $executarTriggersPHP);
	if (!$res) { return 'Erro interno 5'; }
	//mysql_query("SET @disable_trigger = NULL;");

	// Fim
	return true;
}
