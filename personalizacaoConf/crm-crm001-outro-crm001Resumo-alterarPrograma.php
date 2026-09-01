<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /crm-crm001-outro-crm001Resumo/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

// Mostra informações gerênciais do CRM

		// recarregar a tela automaticamente      
		echo <<<EOT
			<div>
				<script type="text/javascript">
					setTimeout(function(){
						divRefresh('{$g['divId']}');
					}, 30000);
				</script>
EOT;

		// Total de pedidos ativos (em aberto)
		$qT005Ativos = mysql_query("select count(*) quantidade,
										   sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where (T005_Flag_Status=0
									 or T005_Flag_Status=14
									 or T005_Flag_Status=15
									 or T005_Flag_Status=13
									 or T005_Flag_Status=6
									 or T005_Flag_Status=1
									 or T005_Flag_Status=2
									 or T005_Flag_Status=3
									 or T005_Flag_Status=10)
									 and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			 and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Ativos = mysql_fetch_array($qT005Ativos);
		$T005AtivosQuantidade = $mT005Ativos['quantidade'];
		$T005AtivosValor = gCorrigeNumero($mT005Ativos['valor']);

		// Total de pedidos pendentes
		$qT005Pendente = mysql_query("select count(*) quantidade,
											 sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=0
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Pendente = mysql_fetch_array($qT005Pendente);
		$T005PendenteQuantidade = $mT005Pendente['quantidade'];
		$T005PendenteValor = gCorrigeNumero($mT005Pendente['valor']);

		// Total de pedidos aprovados
		$qT005Aprovado = mysql_query("select count(*) quantidade,
											 sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=14
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Aprovado = mysql_fetch_array($qT005Aprovado);
		$T005AprovadoQuantidade = $mT005Aprovado['quantidade'];
		$T005AprovadoValor = gCorrigeNumero($mT005Aprovado['valor']);

		// Total de pedidos negados
		$qT005Negado = mysql_query("select count(*) quantidade,
										   sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=15
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Negado = mysql_fetch_array($qT005Negado);
		$T005NegadoQuantidade = $mT005Negado['quantidade'];
		$T005NegadoValor = gCorrigeNumero($mT005Negado['valor']);

		// Total de pedidos em análise comercial
		$qT005AnaliseComercial = mysql_query("select count(*) quantidade,
											sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=13
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005AnaliseComercial = mysql_fetch_array($qT005AnaliseComercial);
		$T005AnaliseComercialQuantidade = $mT005AnaliseComercial['quantidade'];
		$T005AnaliseComercialValor = gCorrigeNumero($mT005AnaliseComercial['valor']);

		// Total de pedidos em análise comercial
		$qT005AnaliseFinanceiro = mysql_query("select count(*) quantidade,
											sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=6
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005AnaliseFinanceiro = mysql_fetch_array($qT005AnaliseFinanceiro);
		$T005AnaliseFinanceiroQuantidade = $mT005AnaliseFinanceiro['quantidade'];
		$T005AnaliseFinanceiroValor = gCorrigeNumero($mT005AnaliseFinanceiro['valor']);

		// Total de pedidos para impressão
		$qT005Imprimir = mysql_query("select count(*) quantidade,
											 sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=1
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Imprimir = mysql_fetch_array($qT005Imprimir);
		$T005ImprimirQuantidade = $mT005Imprimir['quantidade'];
		$T005ImprimirValor = gCorrigeNumero($mT005Imprimir['valor']);

		// Total de pedidos para separação
		$qT005Separando = mysql_query("select count(*) quantidade,
											  sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=2
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Separando = mysql_fetch_array($qT005Separando);
		$T005SeparandoQuantidade = $mT005Separando['quantidade'];
		$T005SeparandoValor = gCorrigeNumero($mT005Separando['valor']);

		// Total de pedidos para conferência
		$qT005Conferindo = mysql_query("select count(*) quantidade,
											   sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=10
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Conferindo = mysql_fetch_array($qT005Conferindo);
		$T005ConferindoQuantidade = $mT005Conferindo['quantidade'];
		$T005ConferindoValor = gCorrigeNumero($mT005Conferindo['valor']);

		// Total de pedidos para gerar NF
		$qT005GerarNF = mysql_query("select count(*) quantidade,
											sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=3
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005GerarNF = mysql_fetch_array($qT005GerarNF);
		$T005GerarNFQuantidade = $mT005GerarNF['quantidade'];
		$T005GerarNFValor = gCorrigeNumero($mT005GerarNF['valor']);

		// Total de pedidos para conferência
		$qT005AguardandoColeta = mysql_query("select count(*) quantidade,
											sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=4
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005AguardandoColeta = mysql_fetch_array($qT005AguardandoColeta);
		$T005AguardandoColetaQuantidade = $mT005AguardandoColeta['quantidade'];
		$T005AguardandoColetaValor = gCorrigeNumero($mT005AguardandoColeta['valor']);

		// Total de pedidos vencidos
		$qT005Vencidos = mysql_query("select count(*) quantidade,
											sum(T005_Valor_Total) valor
								   from T005 
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T005_Flag_Status=26
								    and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT005Vencidos = mysql_fetch_array($qT005Vencidos);
		$T005VencidosQuantidade = $mT005Vencidos['quantidade'];
		$T005VencidosValor = gCorrigeNumero($mT005Vencidos['valor']);

		// Total clientes cadastrados
		$qD024Clientes = mysql_query("select count(*) quantidade
								   from D024 
							  left join C007 as Vendedor on Vendedor.C007_Id=D024_C007_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=D024_C007_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where D024_Flag_Ativo='S'
									and D024_Flag_Cliente='S'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mD024Clientes = mysql_fetch_array($qD024Clientes);
		$D024ClientesQuantidade = $mD024Clientes['quantidade'];

		// Total clientes cadastrados
		$qD024Ativos = mysql_query("select count(*) quantidade
								   from D024 
							  left join C007 as Vendedor on Vendedor.C007_Id=D024_C007_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=D024_C007_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where D024_Flag_Ativo='S'
									and D024_Flag_Comercial='A'
									and D024_Flag_Cliente='S'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mD024Ativos = mysql_fetch_array($qD024Ativos);
		$D024AtivosQuantidade = $mD024Ativos['quantidade'];

		// Total clientes cadastrados
		$qD024Inativos = mysql_query("select count(*) quantidade
								   from D024 
							  left join C007 as Vendedor on Vendedor.C007_Id=D024_C007_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=D024_C007_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where D024_Flag_Ativo='S'
									and D024_Flag_Comercial!='A'
									and D024_Flag_Cliente='S'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mD024Inativos = mysql_fetch_array($qD024Inativos);
		$D024InativosQuantidade = $mD024Inativos['quantidade'];

		// Total clientes cadastrados
		$cMes=date("m",time());
		$cAno=date("Y",time());
		$Data_Inicio=date("Y-m-01", mktime(0, 0, 0, $cMes, 1, $cAno));        
		$Data_Fim=date("Y-m-t", mktime(0, 0, 0, $cMes, 1, $cAno));        
		$qT007Vendas = mysql_query("select T007_Data_Emissao,
										   count(*) quantidade,
										   sum(T007_Valor_Total) valor 
								   from T007
							  left join D006 on D006_Id=T007_D006_Id
							  left join D024 on D024_Id=T007_D024_Id
							  left join C007 as Vendedor on Vendedor.C007_Id=T007_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T007_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T007_Data_Emissao>='$Data_Inicio'
									and T007_Data_Emissao<='$Data_Fim'
									and T007_Flag_Cancelada!='S'
									and D006_Flag_Entrada_Saida='S'
									and D006_Flag_Venda_Compra_Outros='V'
									and D006_Flag_Devolucao!='S'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))
						  			group by T007_Data_Emissao
						  			order by T007_Data_Emissao");

		// Total clientes cadastrados
		$qT003Orcamentos = mysql_query("select count(*) quantidade,
											   sum(T003_Valor_Total) valor
								   from T003
							  left join C007 as Vendedor on Vendedor.C007_Id=T003_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T003_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
								  where T003_Data_Emissao>='$Data_Inicio'
									and T003_Data_Emissao<='$Data_Fim'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))");
		$mT003Orcamentos = mysql_fetch_array($qT003Orcamentos);

		// Total clientes cadastrados
		$qT003OrcamentosCancelados = mysql_query("select count(*) quantidade,
											   sum(T003_Valor_Total) valor,
											   D047_Descricao_Motivo
								   from T003
							  left join C007 as Vendedor on Vendedor.C007_Id=T003_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T003_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
							  left join D047 on D047_Id=T003_D047_Id
								  where T003_Data_Emissao>='$Data_Inicio'
									and T003_Data_Emissao<='$Data_Fim'
									and T003_Flag_Perdido='S'
						  			and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))
						  			group by D047_Id");
		$mT003OrcamentosCancelados = mysql_fetch_array($qT003OrcamentosCancelados);

		// Total de pedidos ativos por cliente
		$qT005AtivosClientes = mysql_query("select count(distinct T005_Id) pedidos,
											sum(T006_Valor_Total_Preco) totalProdutos,
											sum(T006_Valor_Preco_Unitario*if(T006_Quantidade_Estoque>0,if(T006_Quantidade_Estoque>T006_Quantidade,T006_Quantidade,T006_Quantidade_Estoque),0)) totalDisponivel,
											D024_Id,
											substr(D024_Nome_Empresa,1,25) D024_Nome_Empresa,
											D024_Cnpj
								   from T005 
							  left join T006 on T006_T005_Id=T005_Id
							  left join C007 as Vendedor on Vendedor.C007_Id=T005_C007_Id_Vendedor_Interno
							  left join C007 as Externo on Externo.C007_Id=T005_C007_Id_Vendedor_Externo
							  left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
							  left join D024 on D024_Id=T005_D024_Id
								  where (T005_Flag_Status=0
									 or T005_Flag_Status=14
									 or T005_Flag_Status=15)
									and IF('{$g['C007']['C007_Flag_Vendedor']}' = 'V', vendedor.C007_Id='{$g['usuarioAtual']}' or externo.C007_Id='{$g['usuarioAtual']}',IF('{$g['C007']['C007_Flag_Vendedor']}' = 'S', '1=1' ,'1=1'))
									and (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null)
									group by D024_Id
									order by sum(T006_Valor_Total_Preco) desc");

		echo mysql_error();

		$retorno = <<<EOT
		<style type="text/css">
		.td{
			font-weight: normal;
			font-size: 12px;
			font-family: arial, verdana;
			padding: 3px 3px 3px 3px;
		}
		</style>
		<table valign=top>
			<tr>
				<td valign=top>
					<table valign=top>
						<tr>
							<td valign=top>
								<table width=100% valign=top>
									<tr style='background:lightblue'>
										<td class='td' colspan=3 align='center' style='font-weight:bold'>
											PEDIDOS ATIVOS
										</td>
									</tr>
									<tr style='background:lightblue'>
										<td class='td'>
											Status
										</td>
										<td class='td' align='right'>
											Qtde
										</td>
										<td class='td' align='right'>
											Valor R$
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Pendentes
										</td>
										<td class='td' align='right'>
											$T005PendenteQuantidade
										</td>
										<td class='td' align='right'>
											$T005PendenteValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Aprovados
										</td>
										<td class='td' align='right'>
											$T005AprovadoQuantidade
										</td>
										<td class='td' align='right'>
											$T005AprovadoValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Negados
										</td>
										<td class='td' align='right'>
											$T005NegadoQuantidade
										</td>
										<td class='td' align='right'>
											$T005NegadoValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Análise comercial
										</td>
										<td class='td' align='right'>
											$T005AnaliseComercialQuantidade
										</td>
										<td class='td' align='right'>
											$T005AnaliseComercialValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Análise financeiro
										</td>
										<td class='td' align='right'>
											$T005AnaliseFinanceiroQuantidade
										</td>
										<td class='td' align='right'>
											$T005AnaliseFinanceiroValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Imprimir
										</td>
										<td class='td' align='right'>
											$T005ImprimirQuantidade
										</td>
										<td class='td' align='right'>
											$T005ImprimirValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Separando
										</td>
										<td class='td' align='right'>
											$T005SeparandoQuantidade
										</td>
										<td class='td' align='right'>
											$T005SeparandoValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Conferindo
										</td>
										<td class='td' align='right'>
											$T005ConferindoQuantidade
										</td>
										<td class='td' align='right'>
											$T005ConferindoValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Gerar NF
										</td>
										<td class='td' align='right'>
											$T005GerarNFQuantidade
										</td>
										<td class='td' align='right'>
											$T005GerarNFValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td' style='font-weight:bold'>
											&nbsp;
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T005AtivosQuantidade
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T005AtivosValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Aguardando coleta
										</td>
										<td class='td' align='right'>
											$T005AguardandoColetaQuantidade
										</td>
										<td class='td' align='right'>
											$T005AguardandoColetaValor
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Vencidos
										</td>
										<td class='td' align='right'>
											$T005VencidosQuantidade
										</td>
										<td class='td' align='right'>
											$T005VencidosValor
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td valign=top height=100%>
								<table width=100% valign=top>
									<tr style='background:lightblue'>
										<td class='td' colspan=3 align='center' style='font-weight:bold'>
											CLIENTES
										</td>
									</tr>
									<tr style='background:lightblue'>
										<td class='td'>
											Descrição
										</td>
										<td class='td' align='right'>
											Qtde
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Ativos
										</td>
										<td class='td' align='right'>
											$D024AtivosQuantidade
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td'>
											Inativos
										</td>
										<td class='td' align='right'>
											$D024InativosQuantidade
										</td>
									</tr>
									<tr style='background:white'>
										<td class='td' style='font-weight:bold'>
											&nbsp;
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$D024ClientesQuantidade
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table valign=top>
						<tr>
							<td valign=top>
								<table width=100% valign=top>
									<tr style='background:lightblue'>
										<td class='td' colspan=5 align='center' style='font-weight:bold'>
											VENDAS $cMes/$cAno
										</td>
									</tr>
									<tr style='background:lightblue'>
										<td class='td'>
											Dia
										</td>
										<td class='td' align='right'>
											Qtde
										</td>
										<td class='td' align='right'>
										   	Valor R$
										</td>
										<td class='td' align='right'>
										   	Médio R$
										</td>
									</tr>
EOT;
								   	$T007VendasQuantidadeTotal = 0;
								   	$T007VendasValorTotal = 0;

									while ($mT007Vendas = mysql_fetch_array($qT007Vendas)) {

											$T007VendasQuantidadeTotal += $mT007Vendas['quantidade'];
											$T007VendasValorTotal += $mT007Vendas['valor'];

											$T007VendasTicketMedio = gCorrigeNumero($mT007Vendas['valor'] / $mT007Vendas['quantidade']);
											$T007VendasData = gCorrigeData($mT007Vendas['T007_Data_Emissao']);
											$T007VendasQuantidade = $mT007Vendas['quantidade'];
											$T007VendasValor = gCorrigeNumero($mT007Vendas['valor']);

											$retorno .= <<<EOT
													<tr style='background:white'>
														<td class='td'>
															$T007VendasData
														</td>
														<td class='td' align='right'>
															$T007VendasQuantidade
														</td>
														<td class='td' align='right'>
															$T007VendasValor
														</td>
														<td class='td' align='right'>
															$T007VendasTicketMedio
														</td>
													</tr>
EOT;
									
									}

								   	$Aproveitamento = gCorrigeNumero(($T007VendasValorTotal/$mT003Orcamentos['valor'])*100);
									$T007VendasTicketMedioTotal = gCorrigeNumero($T007VendasValorTotal/$T007VendasQuantidadeTotal);
									$T007VendasValorTotal = gCorrigeNumero($T007VendasValorTotal);
								   	$T003OrcamentosTicketMedio = gCorrigeNumero($mT003Orcamentos['valor']/$mT003Orcamentos['quantidade']);
								   	$T003OrcamentosQuantidade = $mT003Orcamentos['quantidade'];
								   	$T003OrcamentosValor = gCorrigeNumero($mT003Orcamentos['valor']);

									$retorno .= <<<EOT
									<tr style='background:white;'>
										<td class='td' style='font-weight:bold'>
											Total vendas
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T007VendasQuantidadeTotal
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T007VendasValorTotal
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T007VendasTicketMedioTotal
										</td>
									</tr>
									<tr style='background:white;'>
										<td class='td' style='font-weight:bold'>
											Total orçamentos
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T003OrcamentosQuantidade
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T003OrcamentosValor
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T003OrcamentosTicketMedio
										</td>
									</tr>
									<tr style='background:white;'>
										<td class='td' style='font-weight:bold'>
											Aproveitamento
										</td>
										<td class='td' align='right' style='font-weight:bold' colspan=2>
											$Aproveitamento %
										</td>
										<td class='td' align='right' style='font-weight:bold' colspan=2>
											&nbsp;
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<table width=100% valign=top>
									<tr style='background:lightblue'>
										<td class='td' colspan=3 align='center' style='font-weight:bold'>
											ORÇAMENTOS CANCELADOS $cMes/$cAno
										</td>
									</tr>
									<tr style='background:lightblue'>
										<td class='td'>
											Motivo
										</td>
										<td class='td' align='right'>
											Qtde
										</td>
										<td class='td' align='right'>
										   	Valor R$
										</td>
									</tr>
EOT;
								   	$T003OrcamentosCanceladosQuantidadeTotal = 0;
								   	$T003OrcamentosCanceladosValorTotal = 0;

									while ($mT003OrcamentosCancelados = mysql_fetch_array($qT003OrcamentosCancelados)) {

											$T003OrcamentosCanceladosQuantidadeTotal += $mT003OrcamentosCancelados['quantidade'];
											$T003OrcamentosCanceladosValorTotal += $mT003OrcamentosCancelados['valor'];

											$T003OrcamentosCanceladosMotivo = $mT003OrcamentosCancelados['D047_Descricao_Motivo'];
											$T003OrcamentosCanceladosQuantidade = $mT003OrcamentosCancelados['quantidade'];
											$T003OrcamentosCanceladosValor = gCorrigeNumero($mT003OrcamentosCancelados['valor']);

											$retorno .= <<<EOT
													<tr style='background:white'>
														<td class='td'>
															$T003OrcamentosCanceladosMotivo
														</td>
														<td class='td' align='right'>
															$T003OrcamentosCanceladosQuantidade
														</td>
														<td class='td' align='right'>
															$T003OrcamentosCanceladosValor
														</td>
													</tr>
EOT;
		
									}

									$T003OrcamentosCanceladosValorTotal = gCorrigeNumero($T003OrcamentosCanceladosValorTotal);

									$retorno .= <<<EOT
									<tr style='background:white;'>
										<td class='td' style='font-weight:bold'>
											&nbsp;
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T003OrcamentosCanceladosQuantidadeTotal
										</td>
										<td class='td' align='right' style='font-weight:bold'>
											$T003OrcamentosCanceladosValorTotal
										</td>
									</tr>
								</table>
							</tr>
						</td>
					</table>
				</td>
				<td valign=top>
					<table valign=top>
						<tr>
							<td valign=top>
								<table width=100% valign=top>
									<tr style='background:lightblue'>
										<td class='td' colspan=6 align='center' style='font-weight:bold'>
											PEDIDOS PENDENTES POR CLIENTE
										</td>
									</tr>
									<tr style='background:lightblue'>
										<td class='td'>
											Código
										</td>
										<td class='td'>
											Cliente
										</td>
										<td class='td'>
											Cnpj
										</td>
										<td class='td' align='right'>
											Qtde
										</td>
										<td class='td' align='right'>
										   	Produtos R$
										</td>
										<td class='td' align='right'>
										   	Estoque R$
										</td>
									</tr>
EOT;
								   	$T005AtivosClientesQuantidadeTotal = 0;
								   	$T005AtivosClientesTotalProdutosTotal = 0;
								   	$T005AtivosClientesTotalDisponivelTotal =0;

									while($mT005AtivosClientes = mysql_fetch_array($qT005AtivosClientes)){
				
										$T005AtivosClientesQuantidadeTotal += $mT005AtivosClientes['pedidos'];
										$T005AtivosClientesTotalProdutosTotal += $mT005AtivosClientes['totalProdutos'];
										$T005AtivosClientesTotalDisponivelTotal += $mT005AtivosClientes['totalDisponivel'];

										$T005AtivosClientesQuantidade = $mT005AtivosClientes['pedidos'];
										$T005AtivosClientesTotalProdutos = gCorrigeNumero($mT005AtivosClientes['totalProdutos']);
										$T005AtivosClientesTotalDisponivel = gCorrigeNumero($mT005AtivosClientes['totalDisponivel']);

										$retorno .= <<<EOT
											<tr style='background:white'>
												<td class='td' align='center'>
													$mT005AtivosClientes[D024_Id]
												</td>
												<td class='td'>
													$mT005AtivosClientes[D024_Nome_Empresa]
												</td>
												<td class='td'>
													$mT005AtivosClientes[D024_Cnpj]
												</td>
												<td class='td' align='right'>
													$T005AtivosClientesQuantidade
												</td>
												<td class='td' align='right'>
													$T005AtivosClientesTotalProdutos
												</td>
												<td class='td' align='right'>
													$T005AtivosClientesTotalDisponivel
												</td>
											</tr>
EOT;
									}

									$T005AtivosClientesTotalProdutosTotal = gCorrigeNumero($T005AtivosClientesTotalProdutosTotal);
									$T005AtivosClientesTotalDisponivelTotal = gCorrigeNumero($T005AtivosClientesTotalDisponivelTotal);

									$retorno .= <<<EOT
										<tr style='background:white'>
											<td class='td'>
												&nbsp;
											</td>
											<td class='td'>
												&nbsp;
											</td>
											<td class='td'>
												&nbsp;
											</td>
											<td class='td' align='right' style='font-weight:bold'>
												$T005AtivosClientesQuantidadeTotal
											</td>
											<td class='td' align='right' style='font-weight:bold'>
												$T005AtivosClientesTotalProdutosTotal
											</td>
											<td class='td' align='right' style='font-weight:bold'>
												$T005AtivosClientesTotalDisponivelTotal
											</td>
										</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
EOT;

		echo $retorno;
