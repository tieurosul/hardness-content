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
$conf['triggers']['T004']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T004']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T004']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T004']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T004']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T004']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

$conf['triggers']['T004']['insertAfter'] .= <<<'EOT'

	mysqli_query("UPDATE D009A SET D009A_Data_Ultimo_Orcamento = NOW() WHERE D009A_D009_Id = '{$mT004['T004_D009_Id']}'");

EOT;




