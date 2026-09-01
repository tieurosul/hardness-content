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
$conf['triggers']['D009']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D009']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D009']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D009']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D009']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['D009']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

// Triggers
$conf['triggers']['D009']['insertAfter'] = <<<'EOT'
	/**
	* CASO SEJA UM INSERT NA D009, JÁ É FEITO O INSERT NA T098 COMO PRIMEIRO REGISTRO DO HISTÓRICO
	*/
	
	// Personalizacao chamado 44561
	mysqli_query("UPDATE D009 SET D009_Meses_MMF       = '12', 
							      D009_Meses_Compra    = '6', 
								  D009_Meses_Reposicao = '4' 
							WHERE D009_Id = '{$chaveValor}'");

	require_once('bibliotecas/classes/CAD002.php');
	$CAD002 = new CAD002();
	$retorno = $CAD002->insereRegistrosT098($chaveValor);
EOT;

$conf['triggers']['D009']['updateAfter'] = <<<'EOT'
	/**
	* IRÁ VERIFICAR OS REGISTROS DA T098 COM OS RECÉM ALTERADOS DA D009, CASO ALGUM SEJA
	* DIFERENTE FARÁ O INSERT NA T098 COM OS NOVOS VALORES
	*/
	require_once('bibliotecas/classes/CAD002.php');
	$CAD002 = new CAD002();

	if($g['C031']['desativarHistoricoPrecos'] == 'N'){
		// Selecionando todos os registros da T098
		$T098 = mysqli_query("SELECT * FROM T098 WHERE T098_D009_Id='{$chaveValor}' ORDER BY T098_Id DESC LIMIT 1");
		$mT098 = mysql_fetch_array($T098);
	}

	// Selecionando todos os registros da D009
	$D009 = mysqli_query("SELECT * FROM D009 WHERE D009_Id='{$chaveValor}'");
	$mD009 = mysql_fetch_array($D009);


	if ($g['updateBefore-D009_Valor_Preco_Tabela'] != $mD009['D009_Valor_Preco_Tabela']) {
		$T162_Extra = "Trigger PHP - Tabela D009";
		$camposAlterados = array();
		$camposAlterados['D009_Data_Atualizacao_Preco_Tabela'] = array($mD009['D009_Data_Atualizacao_Preco_Tabela'], date('Y-m-d'));
		$camposAlterados = mysql_real_escape_string(serialize($camposAlterados));

		mysqli_query("UPDATE D009 SET D009_Data_Atualizacao_Preco_Tabela = CURDATE() WHERE D009_Id = '{$chaveValor}'");
		mysqli_query("INSERT INTO T162 (T162_C007_Id, T162_Acao, T162_Query, T162_DataHora, T162_Tabela, T162_Registro_Id, T162_Registro, T162_IP) VALUES ('{$g['usuarioAtual']}', 'U', '{$T162_Extra}', '" . date('Y-m-d H:i:s') . "', 'D009', '{$chaveValor}', COMPRESS('{$camposAlterados}'), '{$_SERVER['REMOTE_ADDR']}');",$g['conexaoBancoAuditoria']);
	}

	//echo "SELECT * FROM D009 WHERE D009_Id='{$chaveValor}'";
	//echo "{$mT098['T098_D009_Valor_Preco_Tabela']} 	!= 	{$mD009['D009_Valor_Preco_Tabela']}";
	//die('updateAfter D009');

	if($g['C031']['desativarHistoricoPrecos'] == 'N'){
		// atribuindo zero aos valoes caso retornen null do banco
		$mT098['T098_D009_Valor_Preco_Tabela'] = (empty($mT098['T098_D009_Valor_Preco_Tabela'])) ? '0' : $mT098['T098_D009_Valor_Preco_Tabela'];
		$mT098['T098_D009_Valor_Custo_Tabela'] = (empty($mT098['T098_D009_Valor_Custo_Tabela'])) ? '0' : $mT098['T098_D009_Valor_Custo_Tabela'];
		$mT098['T098_D009_Aliquota_ICMS_Tabela'] = (empty($mT098['T098_D009_Aliquota_ICMS_Tabela'])) ? '0' : $mT098['T098_D009_Aliquota_ICMS_Tabela'];
		$mT098['T098_D009_Percentual_Desconto_Tabela'] = (empty($mT098['T098_D009_Percentual_Desconto_Tabela'])) ? '0' : $mT098['T098_D009_Percentual_Desconto_Tabela'];
		$mT098['T098_D009_IPV_1'] = (empty($mT098['T098_D009_IPV_1'])) ? '0' : $mT098['T098_D009_IPV_1'];
		$mT098['T098_D009_IPV_2'] = (empty($mT098['T098_D009_IPV_2'])) ? '0' : $mT098['T098_D009_IPV_2'];
		$mT098['T098_D009_IPV_3'] = (empty($mT098['T098_D009_IPV_3'])) ? '0' : $mT098['T098_D009_IPV_3'];
		$mT098['T098_D009_ICF_1'] = (empty($mT098['T098_D009_ICF_1'])) ? '0' : $mT098['T098_D009_ICF_1'];
		$mT098['T098_D009_ICF_2'] = (empty($mT098['T098_D009_ICF_2'])) ? '0' : $mT098['T098_D009_ICF_2'];
		$mT098['T098_D009_ICF_3'] = (empty($mT098['T098_D009_ICF_3'])) ? '0' : $mT098['T098_D009_ICF_3'];
		$mT098['T098_Flag_Promocao'] = (empty($mT098['T098_Flag_Promocao'])) ? 'N' : $mT098['T098_Flag_Promocao'];
		$mT098['T098_D009_Preco_1'] = (empty($mT098['T098_D009_Preco_1'])) ? '0' : $mT098['T098_D009_Preco_1'];
		$mT098['T098_D009_Preco_2'] = (empty($mT098['T098_D009_Preco_2'])) ? '0' : $mT098['T098_D009_Preco_2'];
		$mT098['T098_D009_Preco_3'] = (empty($mT098['T098_D009_Preco_3'])) ? '0' : $mT098['T098_D009_Preco_3'];


		$mD009['D009_Valor_Preco_Tabela'] = (empty($mD009['D009_Valor_Preco_Tabela'])) ? '0' : $mD009['D009_Valor_Preco_Tabela'];
		$mD009['D009_Valor_Custo_Tabela'] = (empty($mD009['D009_Valor_Custo_Tabela'])) ? '0' : $mD009['D009_Valor_Custo_Tabela'];
		$mD009['D009_Aliquota_ICMS_Tabela'] = (empty($mD009['D009_Aliquota_ICMS_Tabela'])) ? '0' : $mD009['D009_Aliquota_ICMS_Tabela'];
		$mD009['D009_Percentual_Desconto_Tabela'] = (empty($mD009['D009_Percentual_Desconto_Tabela'])) ? '0' : $mD009['D009_Percentual_Desconto_Tabela'];
		$mD009['D009_IPV_1'] = (empty($mD009['D009_IPV_1'])) ? '0' : $mD009['D009_IPV_1'];
		$mD009['D009_IPV_2'] = (empty($mD009['D009_IPV_2'])) ? '0' : $mD009['D009_IPV_2'];
		$mD009['D009_IPV_3'] = (empty($mD009['D009_IPV_3'])) ? '0' : $mD009['D009_IPV_3'];
		$mD009['D009_ICF_1'] = (empty($mD009['D009_ICF_1'])) ? '0' : $mD009['D009_ICF_1'];
		$mD009['D009_ICF_2'] = (empty($mD009['D009_ICF_2'])) ? '0' : $mD009['D009_ICF_2'];
		$mD009['D009_ICF_3'] = (empty($mD009['D009_ICF_3'])) ? '0' : $mD009['D009_ICF_3'];
		$mD009['D009_Flag_Promocao'] = (empty($mD009['D009_Flag_Promocao'])) ? 'N' : $mD009['D009_Flag_Promocao'];
		$mD009['D009_Preco_1'] = (empty($mD009['D009_Preco_1'])) ? '0' : $mD009['D009_Preco_1'];
		$mD009['D009_Preco_2'] = (empty($mD009['D009_Preco_2'])) ? '0' : $mD009['D009_Preco_2'];
		$mD009['D009_Preco_3'] = (empty($mD009['D009_Preco_3'])) ? '0' : $mD009['D009_Preco_3'];

		if(	($mT098['T098_D009_Valor_Preco_Tabela'] 			!= 	$mD009['D009_Valor_Preco_Tabela']) 			||
			($mT098['T098_D009_Valor_Custo_Tabela'] 			!= 	$mD009['D009_Valor_Custo_Tabela'])   		||
			($mT098['T098_D009_Aliquota_ICMS_Tabela'] 			!= 	$mD009['D009_Aliquota_ICMS_Tabela']) 		||
			($mT098['T098_D009_Percentual_Desconto_Tabela'] 	!= 	$mD009['D009_Percentual_Desconto_Tabela']) 	||
			($mT098['T098_D009_IPV_1'] 							!= 	$mD009['D009_IPV_1']) 						||
			($mT098['T098_D009_IPV_2'] 							!= 	$mD009['D009_IPV_2']) 						||
			($mT098['T098_D009_IPV_3'] 							!= 	$mD009['D009_IPV_3']) 						||
			($mT098['T098_D009_ICF_1'] 							!= 	$mD009['D009_ICF_1']) 						||
			($mT098['T098_D009_ICF_2'] 							!= 	$mD009['D009_ICF_2']) 						||
			($mT098['T098_D009_ICF_3'] 							!= 	$mD009['D009_ICF_3']) 						||
			($mT098['T098_Flag_Promocao'] 						!= 	$mD009['D009_Flag_Promocao']) 				||
			($mT098['T098_D009_Preco_1'] 						!= 	$mD009['D009_Preco_1']) 					||
			($mT098['T098_D009_Preco_2'] 						!= 	$mD009['D009_Preco_2']) 					||
			($mT098['T098_D009_Preco_3'] 						!= 	$mD009['D009_Preco_3'])						){
			  $retorno = $CAD002->insereRegistrosT098($chaveValor);
		}
	}

	mysqli_query("UPDATE D009 SET D009_Quantidade_Estoque_Fora=0 WHERE D009_D049_Id='{$mD009['D009_D049_Id']}' AND D009_Id != '{$chaveValor}'");

 	/*if ($mD009['D009_IPT_1']>0 or $mD009['D009_IPT_2']>0 or $mD009['D009_IPT_3']>0 or $mD009['D009_IPT_4']>0) {

        mysqli_query("update D009
                         set D009_Preco_1            =if({$mD009['D009_IPT_1']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_1']},D009_Preco_1),
                             D009_Preco_2            =if({$mD009['D009_IPT_2']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_2']},D009_Preco_2),
                             D009_Preco_3            =if({$mD009['D009_IPT_3']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_3']},D009_Preco_3),
                             D009_Preco_4            =if({$mD009['D009_IPT_4']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_4']},D009_Preco_4)
                       where D009_Id='{$mD009['D009_Id']}'");
	} */
	// Felipe Carrano - Solicitacao Lucas Lisot Via Skipe em 10/06/2025
 	if ($mD009['D009_IPT_1']>0 or $mD009['D009_IPT_2']>0 or $mD009['D009_IPT_3']>0 or $mD009['D009_IPT_4']>0) {
/* 		 if($g['usuarioAtual'] == 63){
			 echo "Entrou personalizacao trigger D009 / tetes para o crontab";
		 } */
        mysqli_query("update D009
                         set D009_Preco_1            =if({$mD009['D009_IPT_1']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_1']},D009_Preco_1),
                             D009_Preco_2            =if({$mD009['D009_IPT_2']} > 0, D009_Valor_Custo_Unitario*{$mD009['D009_IPT_2']},D009_Preco_2),
                             D009_Preco_3            =if({$mD009['D009_IPT_3']} > 0, D009_Preco_1*{$mD009['D009_IPT_3']},D009_Preco_3),
                             D009_Preco_4            =if({$mD009['D009_IPT_4']} > 0, D009_Preco_1*{$mD009['D009_IPT_4']},D009_Preco_4)
                       where D009_Id='{$mD009['D009_Id']}'");
	} 

	if($g['C031']['atualizaPrecoLojaAutomatico'] == 'S'){
		mysqli_query("UPDATE D009 
						 SET D009_Preco_Loja = D009_Preco_Loja_Real
					   WHERE D009_Id 		 = '{$mD009['D009_Id']}'
						 AND D009_Preco_Loja != D009_Preco_Loja_Real");
	}

	
EOT;





