<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-api001-outro-API001OutroLinksApi/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/
	global $g;
	
	$sqlC007 = mysql_query("SELECT C007_Id,C007_Login,C007_Senha,md5(concat(C007_Login,':',C007_Senha)) as AUTH FROM C007 WHERE C007_Id='{$g['usuarioAtual']}'");
	$C007 = mysql_fetch_assoc($sqlC007);
	$dominio = $_SERVER['HTTP_HOST'];
	$autenticacao = $C007['AUTH'];
	$dataInicial = date('Y') . '-01-01';
	$dataFinal = date('Y') . '-12-31';

	$metodos = array(
		array("metodo" => "Clientes","link" =>"{$dominio}/api/rel/get/clientes?API_AUTH={$autenticacao}"),
		array("metodo" => "Empresas","link" =>"{$dominio}/api/rel/empresas?API_AUTH={$autenticacao}"),
		array("metodo" => "Transportadoras","link" =>"{$dominio}/api/rel/transportadoras?API_AUTH={$autenticacao}"),
		array("metodo" => "Produtos","link" =>"{$dominio}/api/rel/get/produtos?API_AUTH={$autenticacao}"),
		array("metodo" => "Portadoras à Receber","link" =>"{$dominio}/api/rel/get/portadoresReceber?API_AUTH={$autenticacao}"),
		array("metodo" => "Contas","link" =>"{$dominio}/api/rel/get/contas?API_AUTH={$autenticacao}"),
		array("metodo" => "Centro de Custo","link" =>"{$dominio}/api/rel/get/centroDeCustos?API_AUTH={$autenticacao}"),
		array("metodo" => "Portadores à Pagar","link" =>"{$dominio}/api/rel/get/portadoresPagar?API_AUTH={$autenticacao}"),
		array("metodo" => "Orçamentos","link" =>"{$dominio}/api/rel/get/orcamentos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Produtos Orçamentos","link" =>"{$dominio}/api/rel/get/orcamentosProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Pedidos","link" =>"{$dominio}/api/rel/get/pedidos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		// array("metodo" => "Pedidos Detalhes","link" =>"{$dominio}/api/rel/pedidosDetalhes?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Pedidos Expedição","link" =>"{$dominio}/api/rel/pedidosExpedicao?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Produtos Pedidos","link" =>"{$dominio}/api/rel/get/pedidosProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Saida","link" =>"{$dominio}/api/rel/get/notasSaida?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Saida Detalhes","link" =>"{$dominio}/api/rel/get/notasSaidaDetalhes?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Saida Produtos","link" =>"{$dominio}/api/rel/get/notasSaidaProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Cotações","link" =>"{$dominio}/api/rel/cotacoes?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Produtos Cotações","link" =>"{$dominio}/api/rel/get/cotacoesProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Ordens de Compra","link" =>"{$dominio}/api/rel/get/ordensDeCompra?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Produtos OC","link" =>"{$dominio}/api/rel/get/ordensDeCompraProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Conhecimentos","link" =>"{$dominio}/api/rel/conhecimentos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Entrada","link" =>"{$dominio}/api/rel/get/notasEntrada?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Entrada Produtos","link" =>"{$dominio}/api/rel/get/notasEntradaProdutos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "NF's Entrada Tributos","link" =>"{$dominio}/api/rel/get/notasEntradaProdutosTributos?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Contas à Receber","link" =>"{$dominio}/api/rel/get/contasReceber?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Contas à Receber Detalhes","link" =>"{$dominio}/api/rel/get/contasReceberDetalhes?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Contas à Pagar","link" =>"{$dominio}/api/rel/get/contasPagar?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Contas à Pagar Detalhes","link" =>"{$dominio}/api/rel/get/contasPagarDetalhes?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Lançamentos Bancários","link" =>"{$dominio}/api/rel/get/lancamentosBancarios?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Tipos CFOP","link" =>"{$dominio}/api/rel/get/tiposCFOP?API_AUTH={$autenticacao}"),
		array("metodo" => "CFOP's","link" =>"{$dominio}/api/rel/get/CFOP?API_AUTH={$autenticacao}"),
		array("metodo" => "Usuários","link" =>"{$dominio}/api/rel/usuarios?API_AUTH={$autenticacao}"),
		array("metodo" => "Vendedores Internos","link" =>"{$dominio}/api/rel/get/vendedoresInternos?API_AUTH={$autenticacao}"),
		array("metodo" => "Supervisores","link" =>"{$dominio}/api/rel/supervisores?API_AUTH={$autenticacao}"),
		array("metodo" => "Vendedores Externos","link" =>"{$dominio}/api/rel/vendedoresExternos?API_AUTH={$autenticacao}"),
		array("metodo" => "Supervisores 2","link" =>"{$dominio}/api/rel/supervisores2?API_AUTH={$autenticacao}"),
		array("metodo" => "Vendedores Substitutos","link" =>"{$dominio}/api/rel/vendedoresSubstitutos?API_AUTH={$autenticacao}"),
		array("metodo" => "Compradores Internos","link" =>"{$dominio}/api/rel/compradoresInternos?API_AUTH={$autenticacao}"),
		array("metodo" => "Compradores Externos","link" =>"{$dominio}/api/rel/compradoresExternos?API_AUTH={$autenticacao}"),
		array("metodo" => "Estoque","link" =>"{$dominio}/api/rel/estoque?API_AUTH={$autenticacao}"),
    	array("metodo" => "RMA 2.0 Cabeçalho","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=1ba4c6f2070371ca6a8772a9fe86232b&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
    	array("metodo" => "RMA 2.0 Produtos","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=89c9f2a1e76faff63b9aa1f924bb6e74&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
    	array("metodo" => "RMA 2.0 Custos","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=dcb297f56c4a6c960b010c09702c6373&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "RMA's","link" =>"{$dominio}/api/rel/get/RMA?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Custos RMA","link" =>"{$dominio}/api/rel/custoRMA?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Atividades","link" =>"{$dominio}/api/rel/atividades?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Assuntos das atividades","link" =>"{$dominio}/api/rel/assuntosAtividades?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Produtos Importação","link" =>"{$dominio}/api/rel/get/produtoImportacao?API_AUTH={$autenticacao}&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Validade Produtos","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=a6fa68e3d1ff6bdd12d81fdb41df38a9&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Relatório Análise de Crédito","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=843b0d384234b99c9e43f0006db6cee8&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Tempo de faturar gerar nota","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=2ff45326c0b96770f898a64eb2c1dae8&DATAINICIAL={$dataInicial}&DATAFINAL={$dataFinal}"),
		array("metodo" => "Documentos por produto","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=824245fd6ccee1a834453f851befa82b"),
		array("metodo" => "Grid validade produto","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=aec0528e5ec4f5ff1b1f40803352995b"),
		array("metodo" => "CertificadosProdutosMiguel","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=e4aeeaeb22179c9f3812a59b7643e896"),
		array("metodo" => "Contatos Clientes","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=4caa1db659ef75c58f3cb9572a344830"),
		array("metodo" => "NovaNotasFiscaisProdutos","link" =>"{$dominio}/api/rel/get/AppApi?API_AUTH={$autenticacao}&codigoApp=5ee104deaa6e48a484c8cc26633aedea"),
	);


echo <<<EOT
	<style>
		table a:link {
		color: #666;
		font-weight: bold;
		text-decoration:none;
		}
		table#t1 a:visited {
			color: #999999;
			font-weight:bold;
			text-decoration:none;
		}
		table#t1 a:active,
		table#t1 a:hover {
			color: #bd5a35;
			text-decoration:underline;
		}
		table#t1 {
			width: 98%;
			font-family:Arial, Helvetica, sans-serif;
			color:#666;
			font-size:12px;
			background:#eaebec;
			margin:10px;
			border:#ccc 1px solid;

			-moz-border-radius:3px;
			-webkit-border-radius:3px;
			border-radius:3px;
		}
		table#t1 th {
			padding:21px 25px 22px 25px;
			border-top:1px solid #fafafa;
			border-bottom:1px solid #e0e0e0;

			background: #ededed;
			background: -webkit-gradient(linear, left top, left bottom, from(#ededed), to(#ebebeb));
			background: -moz-linear-gradient(top,  #ededed,  #ebebeb);
		}
		table#t1 th:first-child{
			text-align: left;
			padding-left:20px;
		}
		table#t1 tr:first-child th:first-child{
			-moz-border-radius-topleft:3px;
			-webkit-border-top-left-radius:3px;
			border-top-left-radius:3px;
		}
		table#t1 tr:first-child th:last-child{
			-moz-border-radius-topright:3px;
			-webkit-border-top-right-radius:3px;
			border-top-right-radius:3px;
		}
		table#t1 tr{
			text-align: left;
			padding-left:10px;
		}
		table#t1 tr td:first-child{
			text-align: left;
			padding-left:20px;
			border-left: 0;
		}
		table#t1 tr td {
			padding:10px;
			border-top: 1px solid #ffffff;
			border-bottom:1px solid #e0e0e0;
			border-left: 1px solid #e0e0e0;

			background: #fafafa;
			background: -webkit-gradient(linear, left top, left bottom, from(#fbfbfb), to(#fafafa));
			background: -moz-linear-gradient(top,  #fbfbfb,  #fafafa);
		}
		table#t1 tr.even td{
			background: #f6f6f6;
			background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#f6f6f6));
			background: -moz-linear-gradient(top,  #f8f8f8,  #f6f6f6);
		}
		table#t1 tr:last-child td{
			border-bottom:0;
		}
		table#t1 tr:last-child td:first-child{
			-moz-border-radius-bottomleft:3px;
			-webkit-border-bottom-left-radius:3px;
			border-bottom-left-radius:3px;
		}
		table#t1 tr:last-child td:last-child{
			-moz-border-radius-bottomright:3px;
			-webkit-border-bottom-right-radius:3px;
			border-bottom-right-radius:3px;
		}
		table#t1 tr:hover td{
			background: #f2f2f2;
			background: -webkit-gradient(linear, left top, left bottom, from(#f2f2f2), to(#f0f0f0));
			background: -moz-linear-gradient(top,  #f2f2f2,  #f0f0f0);
		}
	</style>

	<table cellspacing='0' id="t1">
		<tr>
			<th>Entidade</th>
			<th style="width:0px;"></th>
			<th>Rota GET</th>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td><font color='red'>***Troque os valores de DATAINICIAL e DATAFINAL pela data escolhida no formato AAAA-MM-DD.</font></td>
		</tr>

	<script>
	function copyToClipboard(element) 
	{
		var temp = $("<input>");
		$("body").append(temp);
		temp.val($('rota' + element).text()).select();
		document.execCommand("copy");
		temp.remove();
		$('tag').html('<u>Copiar</u>');
		$('#'+element+'x').html('<tag><u>Copiar</u><br /><font style="color:green">copiado</font></tag>');
	}
	</script>
EOT;

EOT;
	$count=0;
	foreach($metodos as $key=>$metodo){
		$count++;
		echo <<<EOT
			<tr  style="cursor:pointer;" onclick="copyToClipboard('{$count}');">
				<td>{$metodo['metodo']}</td>
				<td id="{$count}x"><tag><u>Copiar</u></tag></td>
				<td id="{$count}"><rota{$count}>{$metodo['link']}</rota{$count}></td>
			</tr>
EOT;
	}

	echo <<<EOT
	</table>
EOT;













