<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class NOTIFICACOES extends NOTIFICACOES_ {
	// defina os métodos para sobreescrever
	  public function retornoAgenda() {
        return '';
    }
	
    public function orcamentoRetornoContato() {
		  return '';
    }

    public function produtosEstoqueVazio(){
      return '';
    }
}



