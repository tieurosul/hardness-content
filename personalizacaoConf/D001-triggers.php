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
$conf['triggers']['D001']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D001']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D001']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D001']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D001']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D001']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/
// Personalizada a trigger para popular o D001A_D001_Id conforme o valor do D001_Flag_Tipo_Item
// Eurosul utiliza esse campo D001_Flag_Tipo_Item e o valor que é enviado para o SPED FISCAL é o D001A_D187_Id
$conf['triggers']['D001']['updateAfter'] = <<<'EOT'
	$D001 = mysql_query("SELECT D001_Flag_Tipo_Item,
								D001_Id,
								IFNULL(D001A_Id,0) as D001A_Id,
								IFNULL(D001A_D187_Id,0) as D001A_D187_Id
	  						  FROM D001 
					     LEFT JOIN D001A ON D001A_D001_Id = D001_Id
							 WHERE D001_Id = '{$chaveValor}'");
	$mD001 = mysql_fetch_array($D001);

	$D187 = mysql_query("SELECT D187_Id,
								D187_Codigo
						   FROM D187");

	while($mD187 = mysql_fetch_array($D187)){
		if($mD187['D187_Codigo'] == $mD001['D001_Flag_Tipo_Item']){
			$D187_Id = $mD187['D187_Id'];
		}
	}

	if($mD001['D001A_D187_Id'] != $D187_Id){
		if($mD001['D001A_Id'] > 0){
			mysql_query("UPDATE D001A
							 SET D001A_D187_Id = '{$D187_Id}'
						   WHERE D001A_Id = '{$mD001['D001A_Id']}'");
		}  else {
			mysqli_query("INSERT INTO D001A (D001A_D001_Id,
											 D001A_D187_Id)
									VALUES  ('{$mD001['D001_Id']}',
									         '{$D187_Id}')");
		}
	}
EOT;

