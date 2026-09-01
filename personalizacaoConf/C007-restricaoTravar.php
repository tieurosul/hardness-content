<?php
/**
* restricaoTravar
* 
* Código PHP para validar se um registro de uma tabela pode: ser Editada, Incluida ou Removida.
* Para ver variáveis e funções disponíveis dentro do código, veja:
*		http://flycker/wiki/index.php?title=Restri%C3%A7%C3%B5es_de_Campos
* Dica: utilize unset() para remover uma restrição definida global.
*/

/*
Exemplo:
$conf['restricaoTravar']['C007'] = <<<'EOT'
	// Código PHP de validação
	// $retorno = true; (OK)
	// $retorno = 'string'; (ERRO)
EOT;
*/

$conf['triggers']['C007']['updateBefore'] = <<<'EOT'
	$sql = mysql_query("SELECT C007_Senha FROM C007 WHERE C007_Id = '{$chaveValor}'");
	$row = mysql_fetch_assoc($sql);
	$g['updateBefore-C007_Senha'] = $row['C007_Senha'];
EOT;

$conf['triggers']['C007']['updateAfter'] = <<<'EOT'

	$sql = mysql_query("SELECT C007_Senha FROM C007 WHERE C007_Id = '{$chaveValor}'");
	$row = mysql_fetch_assoc($sql);

	if($g['updateBefore-C007_Senha'] != $row['C007_Senha']){
		mysqli_query("UPDATE C007 SET C007_Data_Alteracao_Senha = NOW() WHERE C007_Id = '{$chaveValor}'");
	}
EOT;
