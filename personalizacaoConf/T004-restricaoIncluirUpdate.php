<?php
/**
* restricaoIncluirUpdate
* 
* Código PHP para validar um campo de uma tabela na hora de Incluir ou Atualizar.
* Para ver variáveis e funções disponíveis dentro do código, veja:
* 		http://flycker/wiki/index.php?title=Restri%C3%A7%C3%B5es_de_Campos
* Dica: utilize unset() para remover uma restrição definida global.
*/

/*
Exemplo:
$conf['restricaoIncluirUpdate']['T004_Campo'] = <<<'EOT'
	// Código PHP de validação
	// $retorno = true; (OK)
	// $retorno = 'string'; (ERRO)
EOT;
*/

// restricaoTravar
$conf['restricaoTravar']['T004'] = <<<'EOT'
	$retorno = true;
	if ($operacao == 'alterar' || $operacao == 'excluir') {
		$sql = "SELECT T006_Id FROM T006 WHERE T006_T004_Id = '{$valorPk}'";
		$res = mysql_query($sql);
		$rows = mysql_numrows($res);
		if ($rows > 0) {
			$retorno = 'Este item já gerou pedido.';
		}
	} else if ($operacao == 'incluir') {
/* 		$sql = "SELECT T005_Id FROM T005 WHERE T005_T003_Id = '{$valorPkParent}'";
		$res = mysql_query($sql);
		$rows = mysql_numrows($res);
		if ($rows > 0) {
			$retorno = 'Este orçamento já gerou pedido.';
		} */
	}
	//Travas para os itens que possuem cotação relacionada
    /*if(($operacao == 'alterar' || $operacao == 'excluir') && $retorno == true) {
        $sql = mysql_query("SELECT T219_Id FROM T004 LEFT JOIN T219 ON T219_Id=T004_T219_Id WHERE T004_Id = '{$valorPk}'");
        $res = mysql_fetch_array($sql);
        if ($res['T219_Id'] > 0) {
            $retorno = 'Este item está vinculado a uma cotação.';
        }
    }*/

	//RESTRICAO AO EDITAR OS CAMPOS DE QUANTIDADE/QUANTIDADE CONVERSAO CASO O PROCESSO ESTEJA ATIVO
	if (($campo === 'T004_Quantidade') && ($operacao == 'alterar')) {
		$utilizaQuantidadeConversao = $g['C031']['utilizaQuantidadeConversao'] === 'S';
		if ($utilizaQuantidadeConversao) {
			$sql  = " SELECT D002A_Utiliza_Conversao ";
			$sql .=   " FROM T004 ";
			$sql .=        " LEFT JOIN D009  ON D009_Id = T004_D009_Id ";
			$sql .=        " LEFT JOIN D049  ON D049_Id = D009_D049_Id ";
			$sql .=        " LEFT JOIN D001  ON D001_Id = D049_D001_Id ";
			$sql .=        " LEFT JOIN D002  ON D002_Id = D001_D002_Id ";
			$sql .=        " LEFT JOIN D002A ON D002_Id = D002A_D002_Id ";
			$sql .=  " WHERE T004_Id = '$valorPk' ";
			$query  = mysql_query("$sql");
			$dados  = mysql_fetch_array($query);

			$usaConversao = $dados['D002A_Utiliza_Conversao'] === 'S';

			if ($usaConversao) {
				$retorno = 'Este produto utiliza conversão e não é possivel alterar sua quantidade.<br><br>Edite o campo da quantidade conversão.';
			}
		}
	}
	//RESTRICAO AO EDITAR OS CAMPOS DE QUANTIDADE/QUANTIDADE CONVERSAO CASO O PROCESSO ESTEJA ATIVO
EOT;
