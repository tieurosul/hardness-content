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
$conf['triggers']['T001']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T001']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T001']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T001']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T001']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T001']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

$conf['triggers']['T001']['insertAfter'] = <<<'EOT'
	// Código PHP
	$T066A = mysql_query("SELECT T066A_Numero_Lote 
						    FROM T001
					   LEFT JOIN T066 ON T066_Id = T001_T066_Id
					   LEFT JOIN T066A ON T066_Id = T066A_T066_Id
					       WHERE T001_Id = '{$chaveValor}'");
	
	$mT066A = mysql_fetch_array($T066A);

	$T001A = mysql_query("SELECT T001A_Id FROM T001A WHERE T001A_T001_Id = '{$chaveValor}'");
	if(mysql_num_rows($T001A) > 0){
		mysqli_query("UPDATE T001A SET T001A_Numero_Lote = '{$mT066A['T066A_Numero_Lote']}' WHERE T001A_T001_Id = '{$chaveValor}'");
	} else {
		mysqli_query("INSERT INTO T001A (T001A_T001_Id, T001A_Numero_Lote) VALUES ('{$chaveValor}','{$mT066A['T066A_Numero_Lote']}' )");
	}
EOT;
