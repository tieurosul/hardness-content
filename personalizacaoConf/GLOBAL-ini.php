<?php
/**
* Variáveis de Configurações do Sistema
* 
* Modifica variáveis utilizadas internamente pelo sistema.
*/

/*
Exemplo:
$conf['ini']['formatoOrcamentoEmail'] = 'ambos'; // 'html' ou 'pdf' ou 'ambos'
EOT;
*/

$conf['ini']['etiquetasProdutoMetodo'] = array('windows', '');		// metodos de impressão: array('windows', '') ou array('lpr', 'nome_da_impressora')
$conf['ini']['etiquetasProdutoTipoCodBarra'] = 'D001';				// qual campo utilizar para o código de barra (D001: D001_Codigo_Produto / D009: D009_Id / D049: D049_Id)
$conf['ini']['etiquetasProdutoModelos'] = array(3);					// um array de D138_Id's dos modelos de etiquetas (caso hava mais de um, será pedido para selecionar)
$conf['ini']['etiquetasProdutoNomeEmpresa'] = 'EUROSUL';			// nome da empresa na logo (caso o modelo utiliza)

$conf['camposValorPadrao']['primeiroVencimentoNum'] = '28';

