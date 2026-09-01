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
$conf['triggers']['T005']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T005']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T005']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T005']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T005']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T005']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

$conf['triggers']['T005']['updateBefore'] .= <<<'EOT'
	global $g;

	$sqlBefore = "SELECT T005_Flag_Status FROM T005 WHERE T005_Id = '{$chaveValor}'";
	$resBefore = mysql_query($sqlBefore);
	$rowBefore = mysql_fetch_assoc($resBefore);
	
	log("T005_Flag_Status Before: {$rowBefore['T005_Flag_Status']}");
	$g['updateBefore-T005_Flag_Status'] = $rowBefore['T005_Flag_Status'];
EOT;

$conf['triggers']['T005']['updateAfter'] .= <<<'EOT'
	global $g;
	
	$sqlAfter = "SELECT T005_Flag_Status FROM T005 WHERE T005_Id = '{$chaveValor}'";
	$resAfter = mysql_query($sqlAfter);
	$rowAfter = mysql_fetch_assoc($resAfter);

	$statusBefore = $g['updateBefore-T005_Flag_Status'];

	if (mysql_num_rows($resAfter) > 0) {
        if ($rowAfter['T005_Flag_Status'] == 6) {
			$updt = "UPDATE T005A SET T005A_Data_Hora_Analise_Credito = CURRENT_TIMESTAMP() WHERE T005A_T005_Id = '{$chaveValor}'";
			log("Atualiza Data Hora Analise Credito {$updt}");
			mysqli_query($updt);
		}
		// if ((empty($statusBefore) || $statusBefore != $rowAfter['T005_Flag_Status']) && in_array($rowAfter['T005_Flag_Status'], ['',0,1,2,3,4,5,10])) {
		if ((empty($statusBefore) || $statusBefore != $rowAfter['T005_Flag_Status']) && in_array($rowAfter['T005_Flag_Status'], [1,2,3,4,5,10])) {
			$updtTempo = ($rowAfter['T005_Flag_Status'] == 3) 
				? ", T005A_Dt_Hr_Etapa_Gerar_Nota = CURRENT_TIMESTAMP(), T005A_Tempo_Seg_Expedicao = TIMESTAMPDIFF(SECOND, T005A_Dt_Hr_Etapa_Impressao, T005A_Dt_Hr_Etapa_Gerar_Nota)" 
				: "";

			$updt = "UPDATE T005A SET T005A_Data_Hora_Etapa_Expedicao = CURRENT_TIMESTAMP() {$updtTempo} WHERE T005A_T005_Id = '{$chaveValor}'";
			log("Atualiza Data Hora Etapas Expedição {$updt}");
			mysqli_query($updt);
		}
	}
EOT;





