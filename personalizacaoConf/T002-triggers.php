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
$conf['triggers']['T002']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T002']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T002']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T002']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T002']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['T002']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

$conf['triggers']['T002']['updateAfter'] = <<<'EOT'
mysqli_query("call T002_Gravar_Totalizacao('$chaveValor')");
mysqli_query("call T002_Baixar_Titulo('$chaveValor','".date('Y-m-d')."')");
require_once('bibliotecas/classes/FIN009.php');
$FIN009 = new FIN009();
$sql = mysqli_query("SELECT T002_D014_Id, T002_Data_Vencimento FROM T002 WHERE T002_Id = '{$chaveValor}'");
$row = mysql_fetch_array($sql);
$mes = date("n",strtotime($row['T002_Data_Vencimento']));
$ano = date("Y",strtotime($row['T002_Data_Vencimento']));
$periodo = array('mes' => $mes, 'mes_final' => $mes, 'ano_atual' => $ano, 'ano' => $ano, 'ano_final' => $ano);
//$FIN009->FIN009RecculaProjecao($row['T002_D014_Id'], $periodo);

require_once('bibliotecas/classes/VEN001.php');
$VEN001 = new VEN001();

$retorno = $VEN001->calcularComissaoTotal($chaveValor, 'T002');
$upT002 = mysqli_query("UPDATE T002 SET T002_Valor_Comissao_Representante = '{$retorno['valorComissaoRepresentante']}',
										T002_Valor_Total_Comissao_Externo = '{$retorno['valorComissaoExterno']}',
										T002_Valor_Total_Comissao_Usuario = '{$retorno['valorComissaoUsuario']}'
						WHERE T002_Id = '{$chaveValor}'");
EOT;
