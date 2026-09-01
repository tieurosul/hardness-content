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
$conf['restricaoIncluirUpdate']['T066_Campo'] = <<<'EOT'
	// Código PHP de validação
	// $retorno = true; (OK)
	// $retorno = 'string'; (ERRO)
EOT;
*/

$conf['restricaoIncluirUpdate']['T066_Quantidade_Balanco'] = <<<'EOT'
	if($valor < 0){
		$retorno = "A quantidade de balanço não pode ser negativa";
	} else {
		$retorno = true;
	}
EOT;
