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
$conf['restricaoIncluirUpdate']['T005_Campo'] = <<<'EOT'
	// Código PHP de validação
	// $retorno = true; (OK)
	// $retorno = 'string'; (ERRO)
EOT;
*/


$conf['restricaoIncluirUpdate']['T005_Ordem_Compra'] = <<<'EOT'
	$retorno = rRegx('naoVazio', $valor);
EOT;

// restricaoTravar
$conf['restricaoTravar']['T005'] = <<<'EOT'
    $retorno = true;
    if ($operacao == 'excluir') {
        $sql = "SELECT T005_Id FROM T005 WHERE T005_Id = '{$valorPk}' AND (T005_Flag_Status>0 AND T005_Flag_Status!='' AND T005_Flag_Status IS NOT NULL AND T005_Flag_Status!=14 AND T005_Flag_Status!=15 AND T005_Flag_Status!=9 AND T005_Flag_Status!=6 AND T005_Flag_Status!=22)";
        $res = mysql_query($sql);
        $rows = mysql_numrows($res);
        if ($rows > 0) {
            $retorno = 'Este pedido já foi faturado.';
        }
    } else {
        $retorno = true;
    }
    if($campo == 'T005_Estoque_BOX' || $campo == 'T005_Percentual_Comissao_Representante' || $campo == 'T005_Observacao_3'){
        $retorno = true;        
    }
EOT;

