<?php
/**
* triggers
* 
* Execução de código PHP antes/após INSERT/UPDATE/DELETE
* A chave primária sempre estará disponível (exceto em beforeInsert) pela variavél: $chaveValor 
* IMPORTANTE: coloque um '\' na função mysql_query caso use ela: mysqli_query() para utilizar a original do mysql
*/

/*
Exemplos:
$conf['triggers']['T140']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T140']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T140']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T140']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T140']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T140']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

$conf['triggers']['T140']['insertAfter'] .= <<<'EOT'
	// Código PHP
	$row = mysql_fetch_assoc(mysqli_query("SELECT T140_Data_Hora_Upload FROM T140 WHERE T140_Id = '{$chaveValor}'"));
	if ($row['T140_Data_Hora_Upload'] == '0000-00-00 00:00:00') {
		mysqli_query("UPDATE T140 SET T140_Data_Hora_Upload = NOW() WHERE T140_Id = '{$chaveValor}'");
	}
EOT;

$conf['triggers']['T140']['updateBefore'] .= <<<'EOT'
	// Código PHP
	global $g;
	$row = mysql_fetch_assoc(mysqli_query("SELECT T140_Flag_Marcado FROM T140 WHERE T140_Id = '{$chaveValor}'"));
	$g['updtBefore-T140_Flag_Marcado'] = $row['T140_Flag_Marcado'];
EOT;

$conf['triggers']['T140']['updateAfter'] .= <<<'EOT'
	// Código PHP
	global $g;
	$row = mysql_fetch_assoc(mysqli_query("SELECT T140_Flag_Marcado FROM T140 WHERE T140_Id = '{$chaveValor}'"));
	if ($row['T140_Flag_Marcado'] == $g['updtBefore-T140_Flag_Marcado']) {
		mysqli_query("UPDATE T140 SET T140_Data_Hora_Alteracao = NOW() WHERE T140_Id = '{$chaveValor}'");
	}
EOT;
