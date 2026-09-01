<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class IMP003 extends IMP003_ {

	// Felipe Kadanos - 25/02/2026 - Melhoria EUROSUL FORNECEDO - 46468
    // Ao vincular OC no item, seta locação para PENDENTE
    public function importarItensOC($T075_Id, $D024_Id)
    {
        global $g;
        $sqlT225 = mysql_query("SELECT T225_Id, T225_Descricao_Produto, T225_Quantidade_Dividir, T225_D037_Id, T225_D005_Id, D149_Imposto_Importacao, 
                                       D149_Importacao_IPI, D149_Importacao_PIS, D149_Importacao_COFINS, T225_D009_Id, T225_Codigo_Produto, T224_C004_Id, T225_Valor_Preco_Unitario 
                                FROM T225 
                                LEFT JOIN T224 ON T224_Id=T225_T224_Id 
                                LEFT JOIN D149 ON D149_D005_Id=T225_D005_Id AND D149_C004_Id=T224_C004_Id 
                                WHERE T224_D024_Id = '{$D024_Id}' AND T225_Quantidade_Dividir > 0 GROUP BY T225_Id");
        while($T225 = mysql_fetch_array($sqlT225)){
            $sql = <<<EOT
                INSERT INTO T055 (
                    T055_Descricao_Produto,
                    T055_Quantidade,
                    T055_D037_Id,
                    T055_D005_Id,
                    T055_Valor_Custo_Unitario,
                    T055_Flag_ME,
                    T055_Aliquota_Imposto_Importacao,
                    T055_Aliquota_IPI,
                    T055_Aliquota_PIS,
                    T055_Aliquota_COFINS,
                    T055_D009_Id,
                    T055_Codigo_Produto,
                    T055_T075_Id,
                    T055_C004_Id,
                    T055_T225_Id
                ) VALUES (
                    '{$T225['T225_Descricao_Produto']}',
                    '{$T225['T225_Quantidade_Dividir']}',
                    '{$T225['T225_D037_Id']}',
                    '{$T225['T225_D005_Id']}',
                    '{$T225['T225_Valor_Preco_Unitario']}',
                    'S',
                    '{$T225['D149_Imposto_Importacao']}',
                    '{$T225['D149_Importacao_IPI']}',
                    '{$T225['D149_Importacao_PIS']}',
                    '{$T225['D149_Importacao_COFINS']}',
                    '{$T225['T225_D009_Id']}',
                    '{$T225['T225_Codigo_Produto']}',
                    '{$T075_Id}',
                    '{$T225['T224_C004_Id']}',
                    '{$T225['T225_Id']}'
                );
EOT;
            mysql_query($sql);
            $erro = mysql_error();
            if ($erro) {
                return $erro;
            }

            // Seta a locação do item para a que possui a T066_D004_Id = 1882
            $T055_Id = $g['mysqlLastId'];
            $T066 = mysql_query("SELECT T066_Id FROM T066 WHERE T066_D004_Id = '1882' AND T066_D009_Id = '{$T225['T225_D009_Id']}'");
            $loc = mysql_fetch_array($T066);
            if ($loc['T066_Id'] > 0) {
                mysql_query("UPDATE T055 SET T055_T066_Id = '{$loc['T066_Id']}' WHERE T055_Id='{$T055_Id}'");
                if ($erro = mysql_error()) {
                    return "Erro ao atualizar locação (D004=1882) do item {$T055_Id}: {$erro}";
                }
            } else {
                // Se não encontrou com D004_Id = 1882, tenta buscar localização com D004_Local = 'PENDENTE'
                $T066_Pendente = mysql_query("SELECT T066_Id FROM T066 LEFT JOIN D004 ON D004_Id = T066_D004_Id WHERE T066_D009_Id = '{$T225['T225_D009_Id']}' AND D004_Local = 'PENDENTE'");
                $loc_pendente = mysql_fetch_array($T066_Pendente);
                if ($loc_pendente['T066_Id'] > 0) {
                    mysql_query("UPDATE T055 SET T055_T066_Id = '{$loc_pendente['T066_Id']}' WHERE T055_Id='{$T055_Id}'");
                    if ($erro = mysql_error()) {
                        return "Erro ao atualizar locação (PENDENTE) do item {$T055_Id}: {$erro}";
                    }
                } else {
                    // Se não encontrou, cria uma nova locação com D004_Local = 'PENDENTE'
                    $ins = "INSERT INTO T066 (T066_D004_Id, T066_D009_Id) VALUES ('1882', '{$T225['T225_D009_Id']}')";
                    mysqli_query($ins);
                    if ($erro = mysql_error()) {
                        return "Erro ao inserir locação (PENDENTE) do item {$T055_Id}: {$erro}\n";
                    }
                    mysql_query("UPDATE T055 SET T055_T066_Id = '{$g['mysqlLastId']}' WHERE T055_Id='{$T055_Id}'");
                    if ($erro = mysql_error()) {
                        return "Erro ao atualizar locação (PENDENTE) do item {$T055_Id}: {$erro}\n";
                    }
                }
            }

            mysql_query("UPDATE T225 SET T225_Quantidade_Dividido = T225_Quantidade_Dividido+T225_Quantidade_Dividir, T225_Quantidade_Dividir = 0 WHERE T225_Id = '{$T225['T225_Id']}'");
            $erro = mysql_error();
            if ($erro) {
                return $erro;
            }
        }
        return true;
    }
	
	public function gerarNotaFiscal($T075_Id){
		global $g,$T007_Id,$T007_Id_aux;
		
		$T007=mysql_query("select T007_Id,T007_Numero_Nota_Fiscal,
		                         T007_Data_Emissao 
		                    from T007 
		                   where T007_T075_Id='{$T075_Id}' 
		                     and T007_Flag_Cancelada!='S'");
		                     
		if (mysql_num_rows($T007)>0)
		{
			$cMsg_Faturado="Esta importação já foi faturada: ";
			while ($mT007=mysql_fetch_array($T007))
			{
					$mT007['T007_Data_Emissao']=gCorrigeData($mT007['T007_Data_Emissao']);
					$cMsg_Faturado.="NF: ".$mT007['T007_Numero_Nota_Fiscal']." no dia ".$mT007['T007_Data_Emissao'].", ";
					$T007_Id_aux = $mT007['T007_Id'];
					
			}
			return $cMsg_Faturado;
		}
        
		$T075 = mysql_query("select * 
		                       from T075 
		                  left join D006 on D006_Id=T075_D006_Id
		                  left join D118 on D118_Id=T075_D118_Id
		                  left join D018 on D018_UF=D118_Estado
		                      where T075_Id='{$T075_Id}'");
		$mT075 = mysql_fetch_array($T075);

		$D024 = mysql_query("select * 
		                       from D024 
  	                      left join D018 on D018_Id=D024_D018_Id
  	                      left join D020 on D020_Id=D024_D020_Id
  	                      left join D030 on D030_Id=D024_D030_Id
		                      WHERE D024_Id = '{$mT075['T075_D024_Id']}'");
		$mD024 = mysql_fetch_array($D024);
		
		if($mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'J')
		{
			$T007_Cliente_CNPJ_CPF="{$mD024['D024_Cnpj']}";
		}
		elseif($mD024['D024_Flag_Pessoa_Fisica_Juridica'] == 'I')
		{
			$T007_Cliente_CNPJ_CPF="";
		}
		else
		{
			$T007_Cliente_CNPJ_CPF="{$mD024['D024_Cpf']}";
		}
		
		
		$T055=mysql_query("select sum(T055_Valor_Total_Frete) T055_Valor_Total_Frete,
		                          sum(T055_Valor_Total_Custo*$mT075[T075_Cotacao_ME]) T055_Valor_Total_Custo_Reais,
		                          sum(T055_Valor_Total_Custo) T055_Valor_Total_Custo,
		                          sum(T055_Valor_Aduaneira) T055_Valor_Aduaneira,
		                          sum(T055_Valor_Imposto_Importacao) T055_Valor_Imposto_Importacao,
		                          sum(T055_Valor_IPI) T055_Valor_IPI,
		                          sum(T055_Valor_PIS) T055_Valor_PIS,
		                          sum(T055_Valor_COFINS) T055_Valor_COFINS,
		                          sum(T055_Valor_ICMS) T055_Valor_ICMS,
		                          sum(T055_Valor_ICMS_Presumido) T055_Valor_ICMS_Presumido,
		                          sum(T055_Valor_ICMS_Pagar) T055_Valor_ICMS_Pagar,
		                          sum(T055_Valor_Base_ICMS_Sem_Reducao) T055_Valor_Base_ICMS_Sem_Reducao,
		                          sum(T055_Valor_Base_ICMS_Normal) T055_Valor_Base_ICMS_Normal,
		                          sum(T055_Peso_Kg_Liquido_Unitario) T055_Peso_Kg_Liquido_Unitario,
		                          sum(T055_Valor_Total_Despesas) T055_Valor_Total_Despesas,
		                          sum(T055_Valor_Total_Despesas_Aduaneira) T055_Valor_Total_Despesas_Aduaneira,
		                          sum(T055_Valor_Total_Custo_Final) T055_Valor_Total_Custo_Final,
                                  sum(T055_Valor_Total_AFRMM) T055_Valor_Total_AFRMM,
                                  sum(T055_Valor_Total_Despesas_Acessorias_Nota_Fiscal) T055_Valor_Total_Despesas_Acessorias_Nota_Fiscal,
		                          sum(T055_Valor_Total_Preco_Nota_Fiscal) T055_Valor_Total_Preco_Nota_Fiscal
		                     from T055
		                    where T055_T075_Id='{$T075_Id}'");
							
		                    
	    $mT055=mysql_fetch_array($T055);
	    
	    $mT055['T055_Valor_Total_Custo_Reais'] = number_format($mT055['T055_Valor_Total_Custo_Reais'], 2, ',', '.');
	    $mT055['T055_Valor_Total_Custo'] = number_format($mT055['T055_Valor_Total_Custo'], 2, ',', '.');
	    $mT075['T075_Cotacao_ME'] = number_format($mT075['T075_Cotacao_ME'], 4, ',', '.');
	    $mT055['T055_Valor_Total_Frete'] = number_format($mT055['T055_Valor_Total_Frete'], 2, ',', '.');
	    $mT055['T055_Valor_Aduaneira'] = number_format($mT055['T055_Valor_Aduaneira'], 2, ',', '.');
	    $mT055['T055_Valor_Imposto_Importacao'] = number_format($mT055['T055_Valor_Imposto_Importacao'], 2, ',', '.');
	    $mT055['T055_Valor_IPI'] = number_format($mT055['T055_Valor_IPI'], 2, ',', '.');
	    $mT055['T055_Valor_PIS'] = number_format($mT055['T055_Valor_PIS'], 2, ',', '.');
	    $mT055['T055_Valor_COFINS'] = number_format($mT055['T055_Valor_COFINS'], 2, ',', '.');
        $mT055['T055_Valor_Total_AFRMM'] = number_format($mT055['T055_Valor_Total_AFRMM'], 2, ',', '.');
	    $mT055['T055_Valor_ICMS'] = number_format($mT055['T055_Valor_ICMS'], 2, ',', '.');
	    $mT055['T055_Valor_ICMS_Presumido'] = number_format($mT055['T055_Valor_ICMS_Presumido'], 2, ',', '.');
	    $mT055['T055_Valor_ICMS_Pagar'] = number_format($mT055['T055_Valor_ICMS_Pagar'], 2, ',', '.');
	    $mT055['T055_Peso_Kg_Liquido_Unitario'] = number_format($mT055['T055_Peso_Kg_Liquido_Unitario'], 2, ',', '.');
	    $mT055['T055_Valor_Total_Preco_Nota_Fiscal'] = number_format($mT055['T055_Valor_Total_Preco_Nota_Fiscal'], 2, ',', '.');
	    $mT055['T055_Valor_Total_Despesas_Aduaneira'] = number_format($mT055['T055_Valor_Total_Despesas_Aduaneira'], 2, ',', '.');
	    $mT055['T055_Valor_Total_Despesas'] = number_format($mT055['T055_Valor_Total_Despesas'], 2, ',', '.');
	    $mT055['T055_Valor_Total_Custo_Final'] = number_format($mT055['T055_Valor_Total_Custo_Final'], 2, ',', '.');
	    $mT055['T055_Valor_Base_ICMS_Sem_Reducao'] = number_format($mT055['T055_Valor_Base_ICMS_Sem_Reducao'], 2, ',', '.');
	    $mT075['T075_Aliquota_ICMS_Credito_Presumido'] = number_format($mT075['T075_Aliquota_ICMS_Credito_Presumido'], 2, ',', '.');
	    $mT075['T075_Percentual_Reducao_ICMS_Presumido'] = number_format($mT075['T075_Percentual_Reducao_ICMS_Presumido'], 2, ',', '.');
		
		if($mT075['D118_Flag_Tipo'] == 'N'){
			//caso o tipo da D.I no cadastro do porto seja Normal
			$baseICMS = "T055_Valor_Base_ICMS_Sem_Reducao";
		}else{
			//caso o tipo da D.I no cadastro do porto seja Simplificada
			$baseICMS = "T055_Valor_Base_ICMS_Normal";
		}
		$mT075['D006_Observacao']="II: {$mT055['T055_Valor_Imposto_Importacao']}, PIS: {$mT055['T055_Valor_PIS']}, Cofins: {$mT055['T055_Valor_COFINS']}, Siscomex: {$mT055['T055_Valor_Total_Despesas_Aduaneira']}, Frete: {$mT055['T055_Valor_Total_Frete']}, THC: {$mT055['T055_Valor_Aduaneira']}, AFRMM: {$mT055['T055_Valor_Total_AFRMM']}, Base ICMS: {$mT055[$baseICMS]}, Devido: {$mT055['T055_Valor_ICMS']}, Presumido: {$mT055['T055_Valor_ICMS_Presumido']}, Recolhido: {$mT055['T055_Valor_ICMS_Pagar']}. BASE DE CALCULO REDUZIDA EM {$mT075['T075_Percentual_Reducao_ICMS_Presumido']}%  CONF. ART. 81-A, INCISO I, DEC. 5.141/02. CREDITO PRESUMIDO DE {$mT075['T075_Aliquota_ICMS_Credito_Presumido']}% CONF. ART. 572-2, DEC.5.141/02. VALOR CREDITO PRESUMIDO R\$ {$mT055['T055_Valor_ICMS_Presumido']} ICMS RECOLHIDO R\$ {$mT055['T055_Valor_ICMS_Pagar']}, De acordo com o decreto 6891 Alt 60º artigo 617A, DI: {$mT075['T075_Numero_DI']} PO: {$mT075['T075_Ordem_Compra']}. {$mT075['T075_Observacao_NF']}";
		

        require_once("bibliotecas/classes/VEN012.php");
        $VEN012 = new VEN012();
        //retorna a proxima numeração e série da nota fiscal
        $proximoNumeroSerie = $VEN012->retornaProximaNF('6', $g['empresaAtual']);

        list($proximoNumero,$serie) = $proximoNumeroSerie;
        if($proximoNumero === false){
            $serie = str_replace(array('<b>','</b>'), '', $serie);
            return $serie;
        }

		$nNumero_Serie = '1';
        $T007_Flag_Finalidade_NFe = '1';
		
		$mD024['D020_Nome_Cidade'] = mysql_real_escape_string($mD024['D020_Nome_Cidade']);
		
		mysql_query("START TRANSACTION");
		
	    $cClausula  = " insert into T007 ( ";
	    $cClausula .= " T007_D006_Id, "; // 1
	    $cClausula .= " T007_D024_Id, "; // 2
	    $cClausula .= " T007_D022_Id, "; // 3
	    $cClausula .= " T007_D021_Id, "; // 4
	    $cClausula .= " T007_C004_Id, "; // 5
	    $cClausula .= " T007_D036_Id, "; // 6
	    $cClausula .= " T007_T134_Id, "; // 7
	    $cClausula .= " T007_Flag_Cancelada, "; // 8
	    $cClausula .= " T007_Flag_ACP, "; // 9
	    $cClausula .= " T007_Numero_Nota_Fiscal, "; // 10
	    $cClausula .= " T007_Ambiente_NFe, "; // 11
	    $cClausula .= " T007_Serie, "; // 12
	    $cClausula .= " T007_Numero_Nota_Devolvida, "; // 13
	    $cClausula .= " T007_Data_Emissao, "; // 14
	    $cClausula .= " T007_Data_Saida, "; // 15
	    $cClausula .= " T007_Nome_Transportadora, "; // 16
	    $cClausula .= " T007_Tipo_Transporte, "; // 17
	    $cClausula .= " T007_Observacao_Nota_Fiscal_2, "; // 18
	    $cClausula .= " T007_Flag_Frete, "; // 19
	    $cClausula .= " T007_Flag_IPI, "; // 20
	    $cClausula .= " T007_Obs_Classificacao_Fiscal, "; // 21
	    $cClausula .= " T007_Valor_Frete, "; // 22
	    $cClausula .= " T007_Valor_Seguro, "; // 23
	    $cClausula .= " T007_Percentual_Comissao_Representante, "; // 24
	    $cClausula .= " T007_Valor_Despesas_Acessorias, "; // 25
	    $cClausula .= " T007_Valor_Entrada, "; // 26
	    $cClausula .= " T007_Valor_Desconto, "; // 27
	    $cClausula .= " T007_C007_Id_Vendedor_Interno, "; // 28
	    $cClausula .= " T007_C007_Id_Vendedor_Externo, "; // 29
	    $cClausula .= " T007_C007_Id_Devolucao,	 "; // 30
	    $cClausula .= " T007_Cliente_Razao, "; // 31
	    $cClausula .= " T007_Cliente_CNPJ_CPF, "; // 32
	    $cClausula .= " T007_Cliente_Inscricao_Estadual, "; // 33
	    $cClausula .= " T007_Cliente_Endereco, "; // 34
	    $cClausula .= " T007_Cliente_Endereco_Numero, "; // 35
	    $cClausula .= " T007_Cliente_Bairro, "; // 36
	    $cClausula .= " T007_Cliente_CEP, "; // 37
	    $cClausula .= " T007_Cliente_Cidade, "; // 38
	    $cClausula .= " T007_Cliente_Codigo_Cidade_IBGE, "; // 39
	    $cClausula .= " T007_Cliente_Telefone, "; // 40
	    $cClausula .= " T007_Cliente_Estado, "; // 41
	    $cClausula .= " T007_Cliente_Codigo_Estado_IBGE, "; // 42
	    $cClausula .= " T007_Cliente_Pais, "; // 43
	    $cClausula .= " T007_Cliente_Codigo_Pais, "; // 44
	    $cClausula .= " T007_Hora_Saida, "; // 45
	    $cClausula .= " T007_Transportadora_Placa_Veiculo,"; // 46
	    $cClausula .= " T007_Estado_Veiculo, "; // 47
	    $cClausula .= " T007_Transportadora_CNPJ, "; // 48
	    $cClausula .= " T007_Transportadora_Inscricao_Estadual, "; // 49
	    $cClausula .= " T007_Transportadora_Endereco, "; // 50
	    $cClausula .= " T007_Transportadora_Cidade, "; // 51
	    $cClausula .= " T007_Transportadora_Estado, "; // 52
	    $cClausula .= " T007_Quantidade_Volumes, "; // 53
	    $cClausula .= " T007_Especie, "; // 54
	    $cClausula .= " T007_Marca, "; // 55
	    $cClausula .= " T007_Numero_Caixas, "; // 56
	    $cClausula .= " T007_Peso_Bruto, "; // 57
	    $cClausula .= " T007_Peso_Liquido, "; // 58
	    $cClausula .= " T007_Inscricao_Municipal, "; // 59
	    $cClausula .= " T007_Aliquota_ISS, "; // 60
	    $cClausula .= " T007_Empresa_Usuaria_Nome, "; // 61
	    $cClausula .= " T007_Empresa_Usuaria_Endereco, "; // 62
	    $cClausula .= " T007_Empresa_Usuaria_Numero, "; // 63
	    $cClausula .= " T007_Empresa_Usuaria_Bairro, "; // 64
	    $cClausula .= " T007_Empresa_Usuaria_CEP, "; // 65
	    $cClausula .= " T007_Empresa_Usuaria_Cidade, "; // 66
	    $cClausula .= " T007_Empresa_Usuaria_Estado, "; // 67
	    $cClausula .= " T007_Empresa_Usuaria_Uf, "; // 68
	    $cClausula .= " T007_Empresa_Usuaria_Codigo_Cidade_IBGE, "; // 69
	    $cClausula .= " T007_Empresa_Usuaria_Codigo_Estado_IBGE, "; // 70
	    $cClausula .= " T007_Empresa_Usuaria_Pais, "; // 71
	    $cClausula .= " T007_Empresa_Usuaria_Telefone, "; // 72
	    $cClausula .= " T007_Empresa_Usuaria_Fax, "; // 73
	    $cClausula .= " T007_Empresa_Usuaria_CNPJ, "; // 74
	    $cClausula .= " T007_Empresa_Usuaria_Inscricao_Estadual, "; // 75
	    $cClausula .= " T007_Empresa_Usuaria_CNAE_Fiscal, "; // 76
	    $cClausula .= " T007_Flag_Impresso, "; // 77
	    $cClausula .= " T007_Percentual_Desconto, "; // 78
	    $cClausula .= " T007_Valor_Percentual_Desconto, "; // 79
	    $cClausula .= " T007_Valor_Total_Base_ICMS, "; // 80
	    $cClausula .= " T007_Valor_Total_ICMS, "; // 81
	    $cClausula .= " T007_Valor_Total_Base_IPI, "; // 82
	    $cClausula .= " T007_Valor_Total_IPI, "; // 83
	    $cClausula .= " T007_Valor_ISS, "; // 84
	    $cClausula .= " T007_Valor_Total_Custo, "; // 85
	    $cClausula .= " T007_Valor_Total_Produtos, "; // 86
	    $cClausula .= " T007_Valor_Total, "; // 87
	    $cClausula .= " T007_IPV, "; // 88
	    $cClausula .= " T007_IPV_Cliente, "; // 89
	    $cClausula .= " T007_Flag_Entrada_Saida, "; // 90
	    $cClausula .= " T007_Flag_Finalidade_NFe, "; // 91
	    $cClausula .= " T007_Origem_Chave_Acesso, "; // 92
	    $cClausula .= " T007_Flag_Venda_Compra_Outros, "; // 93
	    $cClausula .= " T007_Flag_Expedicao, "; // 94
	    $cClausula .= " T007_Numero_Parcelas, "; // 95
	    $cClausula .= " T007_Numero_Dias_Entrada, "; // 96
	    $cClausula .= " T007_Numero_Dias, "; // 97
	    $cClausula .= " T007_Flag_Revenda_Consumidor, "; // 98
	    $cClausula .= " T007_Casas_Decimais_Quantidade, "; // 99
	    $cClausula .= " T007_Casas_Decimais_Preco, "; // 100
	    $cClausula .= " T007_Descricao_CFOP, "; // 101
	    $cClausula .= " T007_Codigo_CFOP, "; // 102
	    $cClausula .= " T007_Exportacao_UF_Embarque, "; // 103
	    $cClausula .= " T007_Exportacao_Local_Embarque, "; // 104
	    $cClausula .= " T007_Empresa_Usuaria_CRT, "; // 105
	    $cClausula .= " T007_Versao_NFe, "; // 106
	    $cClausula .= " T007_Observacao_Nota_Fiscal_1, ";            // 107
	    $cClausula .= " T007_T075_Id, ";            // 108
		$cClausula .= " T007_Flag_Destino_Operacao, "; // 109
        $cClausula .= " T007_Hora_Emissao, "; // 110
        $cClausula .= " T007_Fuso_Horario_Emissao, "; // 111
        $cClausula .= " T007_Fuso_Horario_Saida "; // 112
	    $cClausula .= " ) values ("; // 109
	    $cClausula .= " '{$mT075['T075_D006_Id']}', "; // CFOP // 1
	    $cClausula .= " '{$mT075['T075_D024_Id']}', "; // Fornecedor //2
	    $cClausula .= " '', "; // Transportadora // 3
	    $cClausula .= " '', "; // Representante //4
	    $cClausula .= " {$g['c004']['C004_Id']}, ";   // Empresa_Atual //5
	    $cClausula .= " '', "; // D036_Id - Tipo Transporte // 6
	    $cClausula .= " '', "; // T134_Id - Faixa simples //7
	    $cClausula .= " 'N', "; // Flag_Cancelada //8
	    $cClausula .= " '6', "; // Flag ACP //9
	    $cClausula .= " '{$proximoNumero}', "; // NUMERO DA NOTA FISCAL //10
	    $cClausula .= " '{$g['c004']['C004_Ambiente_NFe']}', "; // Ambiente NFe //11
	    $cClausula .= " '{$serie}', "; // Numero serie NFe //12
	    $cClausula .= " '', "; //-- NUMERO DA NOTA FISCAL DE DEVOLUCAO //13
	    $cClausula .= " curdate(), "; // DATA DE EMISSAO //14
	    $cClausula .= " curdate(), "; // Data Saida //15
	    $cClausula .= " '', "; // Nome Transportadora //16
	    $cClausula .= " '', "; // D036_Tipo_Transporte - Tipo Transporte //17
	    $cClausula .= " '', "; // OBSERVACAO DO CLIENTE QUE SAI NO CORPO DA NOTA FISCAL //18
	    $cClausula .= " '', "; // Flag Frete //19
	    $cClausula .= " '', "; // Flag IPI //20
	    $cClausula .= " '', "; // Observacao Fiscal //21
	    $cClausula .= " '', "; // Valor Frete //22
	    $cClausula .= " '', "; // Valor Seguro //23
	    $cClausula .= " '', "; // Percentual Comissao //24 
        $cClausula .= " '{$mT055['T055_Valor_Total_Despesas_Acessorias_Nota_Fiscal']}', "; // Valor Despesas Acessorias //25
	    $cClausula .= " '', "; // Valor Entrada //26
	    $cClausula .= " '', "; // VALOR DO DESCONTO //27
	    $cClausula .= " '', "; // ID DO USUARIO VENDEDOR INTERNO //28
	    $cClausula .= " '0', "; // ID DO USUARIO VENDEDOR EXTERNO //29
	    $cClausula .= " '0', "; // ID DO USUARIO QUE FEZ A  DEVOLUCAO //30
	    $cClausula .= " '{$mD024['D024_Nome_Empresa']}', "; // D024_Nome_Empresa //31
	    $cClausula .= " '{$T007_Cliente_CNPJ_CPF}', "; // CNPJ ou CPF //32
	    $cClausula .= " '{$mD024['D024_Inscricao_Estadual']}', "; //33
	    $cClausula .= " '{$mD024['D024_Endereco']}', "; //34
	    $cClausula .= " '{$mD024['D024_Numero']}', "; //35
	    $cClausula .= " '{$mD024['D024_Bairro']}', "; //36
	    $cClausula .= " '{$mD024['D024_Cep']}', "; //37
	    $cClausula .= " '{$mD024['D020_Nome_Cidade']}', "; //38
	    $cClausula .= " '{$mD024['D020_Codigo_IBGE']}', "; //39
	    $cClausula .= " '{$mD024['D024_DDD_Telefone_1']}{$mD024['D024_Telefone_1']}', "; //40
	    $cClausula .= " '{$mD024['D018_UF']}', "; // 41
	    $cClausula .= " '{$mD024['D018_Codigo_IBGE']}', "; //42
	    $cClausula .= " '{$mD024['D030_Nome_Pais']}', "; // 43
	    $cClausula .= " '{$mD024['D030_Codigo_Pais']}', "; //44
	    $cClausula .= " '', "; // Hora Saida //45
	    $cClausula .= " '', "; // Placa do veiculo de transpote //46
	    $cClausula .= " '', "; // UF do veiculo de transpote //47
	    $cClausula .= " '', ";  // D022_CNPJ //48
	    $cClausula .= " '', "; // D022_Inscricao_Estadual //49
	    $cClausula .= " '', "; // D022_Endereco // 50
	    $cClausula .= " '', ";  // D022 D020_Nome_Cidade //51
	    $cClausula .= " '', "; // D022 D018_UF //52
	    $cClausula .= " '', "; // QUANTIDADE DE VOLUMES  //53
	    $cClausula .= " '', "; // Especie Volumes //54
	    $cClausula .= " '', "; // Marca //55
	    $cClausula .= " '', "; // Numero Caixas //56
	    $cClausula .= " '{$mT075['T075_Peso_Bruto']}', "; // Peso Bruto //57
	    $cClausula .= " '{$mT075['T075_Peso_Liquido']}', "; // Peso Liquido //58
	    $cClausula .= " '', ";  //59
	    $cClausula .= " '0', "; // Aliquota ISS //60
	    $cClausula .= " '{$g['C004']['C004_Nome_Empresa']}', "; //61
	    $cClausula .= " '{$g['C004']['C004_Logradouro']}', "; //62
	    $cClausula .= " '{$g['C004']['C004_Numero']}', "; //63
	    $cClausula .= " '{$g['C004']['C004_Bairro']}', "; //64
	    $cClausula .= " '{$g['C004']['C004_Cep']}', "; // 65 
	    $cClausula .= " '{$g['C004']['D020_Nome_Cidade']}', "; //66
	    $cClausula .= " '{$g['C004']['D018_Estado']}', "; //67
	    $cClausula .= " '{$g['C004']['D018_UF']}', "; //68
	    $cClausula .= " '{$g['C004']['D020_Codigo_IBGE']}', "; //69
	    $cClausula .= " '{$g['C004']['D018_Codigo_IBGE']}', "; //70
	    $cClausula .= " 'BR', "; //71
	    $cClausula .= " '{$g['C004']['C004_Telefone']}', "; //72
	    $cClausula .= " '{$g['C004']['C004_Fax']}', "; //73
	    $cClausula .= " '{$g['C004']['C004_Cnpj']}', "; //74
	    $cClausula .= " '{$g['C004']['C004_Inscricao_Estadual']}', "; //75
	    $cClausula .= " '{$g['C004']['C004_CNAE_Fiscal']}', "; //76 
	    $cClausula .= " 'N', "; //77
	    $cClausula .= " '0', "; // Percentual Desconto //78
	    $cClausula .= " '0', "; // Valor Percentual Desconto //79
	    $cClausula .= " '', "; // Valor Base ICMS //80
	    $cClausula .= " '', "; // Valor ICMS //81
	    $cClausula .= " '', "; // Valor Base IPI //82
	    $cClausula .= " '', "; // Valor IPI //83
	    $cClausula .= " '0', "; // Valor ISS //84
	    $cClausula .= " '0', "; // Valor Total Custo //85 
	    $cClausula .= " '', "; // Valor Total Produtos //86
	    $cClausula .= " '', "; // Valor Total //87 
	    $cClausula .= " '0', "; // IPV //88
	    $cClausula .= " '0', "; // IPV Cliente //89
	    $cClausula .= " 'E', "; // Flag Entrada Saida //90
	    $cClausula .= " '$T007_Flag_Finalidade_NFe', "; // Finalidade NFE //91
	    $cClausula .= " '', "; // Chave Acesso da Nota Geradora //92
	    $cClausula .= " '{$mT075['D006_Flag_Venda_Compra_Outros']}', "; // Flag Venda / Compras / Outros //93
	    $cClausula .= " '1', "; // Flag Expedicao // 94
	    $cClausula .= " '', "; // Numero de Parcelas // 95
	    $cClausula .= " '', "; // Numero dias na entrada (Prazo) //96
	    $cClausula .= " '', "; // Numero Dias //97
	    $cClausula .= " '', "; // Flag Revenda Consumidor //98
	    $cClausula .= " '', "; // Casas decimais quantidade //99
	    $cClausula .= " '{$g['C004']['C004_Casas_Decimais_Preco']}', "; // Casas decimais preco //100
	    $cClausula .= " '', "; // Descricao CFOP //101
	    $cClausula .= " '', "; // Codigo CFOP //102
	    $cClausula .= " '', "; // Exportacao UF Embarque //103                     
	    $cClausula .= " '', "; // Exportacao Local Embarque  //104
	    $cClausula .= " {$g['C004']['C004_Codigo_Regime_Tributario']}, "; //105
	    $cClausula .= " '{$g['C004']['C004_Versao_NFe']}', "; // Versao NFe   // 106
	    $cClausula .= " '{$mT075['D006_Observacao']}', "; // Observacao NFE //107
	    $cClausula .= " '{$T075_Id}', "; // Id DI //108
		$cClausula .= " '3', "; // Flag_Destino_Operacao //109
        $cClausula .= " current_time(), "; // Hora Emissao //110
        $cClausula .= "'".date('P')."', "; // Fuso horario emissao // 111
        $cClausula .= "'".date('P')."' "; // Fuso horario saida // 112
	    $cClausula .= " )";
		mysql_query($cClausula);
		$cErro=mysql_error();
		if (!empty($cErro))
		{
			mysql_query("ROLLBACK");
			return "Erro: " . $cErro . "<br />" . $cClausula;
		}

		/**
         * Verificar se todos itens tem locação
         */
        $T055 = mysql_query("SELECT T066_D004_Id,
                                    T066_Id,
                                    D001_Codigo_Produto,
                                    T055_T066_Id
                            FROM T055
                            LEFT JOIN D009 ON D009_Id=T055_D009_Id
                            LEFT JOIN T066 ON T055_T066_Id=T006_Id
                            WHERE T055_T075_Id='{$T075_Id}'
                            GROUP BY T055_Id");
        $erro = '';
        while ($mT055 = mysql_fetch_array($T055)) {
            if ($mT055['T066_Id']<=0 || $mT055['T066_D004_Id']<=0){
                $erro .= "O produto {$mT055['D001_Codigo_Produto']} está sem locação.<br>";
                continue;
            }
        }
        if (!empty($erro)){                
            mysql_query("ROLLBACK");
            return $erro;
        }
		
		$T007_Id = $g['mysqlLastId'];
		
		$T055 = mysql_query("select * 
		                       from T055 
		                  left join D005 on D005_Id=T055_D005_Id
		                  left join D088 on D088_Id=D005_D088_Id
		                  left join D037 on D037_Id=T055_D037_Id
		                      where T055_T075_Id='{$T075_Id}'");
		while ($mT055 = mysql_fetch_array($T055))
		{
			
			if($mT075['D118_Estado']=="PR" || $mT075['D118_Estado']=="PARANA"){
				$icms = $mT055['T055_Valor_ICMS_Pagar'];
			}else{
				$icms = $mT055['T055_Valor_ICMS'];
			}

    	    $mT055['T055_Valor_Custo_Unitario']=$mT055['T055_Valor_Custo_Unitario']*$mT075['T075_Cotacao_ME'];
    	    
		    if ($mT055['T055_Aliquota_IPI']>0)
		    {
		        $mT055['D088_Codigo_ST']='00';
			}
			else
			{
		        $mT055['D088_Codigo_ST']='03';
			}
		    $mT055['T055_Descricao_Produto'] = mysql_real_escape_string($mT055['T055_Descricao_Produto']);

		    $T066 = mysql_query("select T055_T066_Id, T066_D004_Id from T055 lef join T066 on T066_Id=T055_T066_Id where T055_Id='{$mT055['T055_Id']}'");
            $mT066=mysql_fetch_array($T066);
            if (mysql_num_rows($T066)>1){
                $mT066[T066_Id]=0;
                $mT066[T066_D004_Id]=0;
            }

		    $cClausula="insert into T008 (";
		    $cClausula.="T008_C004_Id,"; // 1
		    $cClausula.="T008_T007_Id,"; // 2
		    $cClausula.="T008_D006_Id,"; // 3
		    $cClausula.="T008_D037_Id,"; // 4
		    $cClausula.="T008_D059_Id,"; // 5
		    $cClausula.="T008_Unidade,"; // 6
		    $cClausula.="T008_Codigo_CFOP,"; // 7
		    $cClausula.="T008_Aliquota_IPI,"; // 8
		    $cClausula.="T008_Percentual_Reducao_IPI,"; // 9
		    $cClausula.="T008_Aliquota_ICMS,"; // 10
		    $cClausula.="T008_Percentual_Reducao_ICMS,"; // 11
		    $cClausula.="T008_Peso_Unitario,"; // 12
		    $cClausula.="T008_D005_Id,"; // 13
		    $cClausula.="T008_Flag_Situacao_Tributaria,"; // 14
		    $cClausula.="T008_Flag_Preco_Especial,"; // 15
		    $cClausula.="T008_Classificacao_Fiscal,"; // 16
		    $cClausula.="T008_Situacao_Tributaria_IPI, "; // 17
		    $cClausula.="T008_Quantidade,"; // 18
		    $cClausula.="T008_Codigo_Produto,"; // 19
		    $cClausula.="T008_Codigo_Barras,"; // 20
		    $cClausula.="T008_Codigo_Substituto,"; // 21
		    $cClausula.="T008_D009_Id,"; // 22
		    $cClausula.="T008_Descricao_Produto,"; // 23
		    $cClausula.="T008_Valor_Custo_Unitario,"; // 24
		    $cClausula.="T008_Valor_Preco_Sem_Desconto_Unitario,"; // 25
		    $cClausula.="T008_Flag_ST,"; // 26
		    $cClausula.="T008_ST_VA,"; // 27
		    $cClausula.="T008_ICMS_Inter_Estadual,"; // 28
		    $cClausula.="T008_ST_ICMS_Interno,"; // 29
		    $cClausula.="T008_Flag_Isento_PIS,"; // 30
		    $cClausula.="T008_Aliquota_PIS,"; // 31
		    $cClausula.="T008_Situacao_Tributaria_PIS,"; // 32
		    $cClausula.="T008_Flag_Isento_COFINS,"; // 33
		    $cClausula.="T008_Aliquota_COFINS,"; // 34
		    $cClausula.="T008_Situacao_Tributaria_COFINS, "; // 35
		    $cClausula.="T008_Valor_Total_Base_Imposto_Importacao, "; // 36
		    $cClausula.="T008_Valor_Total_Imposto_Importacao, "; // 37
		    $cClausula.="T008_Valor_Total_Despesas_Aduaneira, "; // 38
		    $cClausula.="T008_Valor_Total_IOF, "; // 39
		    $cClausula.="T008_Numero_Documento_Importacao, "; // 40
		    $cClausula.="T008_Data_Registro_Documento_Importacao, "; // 41
		    $cClausula.="T008_Local_Desembaraco, "; // 42
		    $cClausula.="T008_Data_Desembaraco, "; // 43
		    $cClausula.="T008_UF_Desembaraco, "; // 44
		    $cClausula.="T008_Codigo_Exportador, "; // 45
		    $cClausula.="T008_Numero_Adicao, "; // 46
		    $cClausula.="T008_Numero_Sequencia_Adicao, "; // 47
		    $cClausula.="T008_Codigo_Fabricante, "; // 48
		    $cClausula.="T008_Valor_Desconto_Item_Adicao, "; // 49
		    $cClausula.="T008_Numero_Pedido_Compra, "; // 50
		    $cClausula.="T008_Item_Pedido_Compra, "; // 51
		    $cClausula.="T008_Percentual_Base_Operacao_Propria, "; // 52
		    $cClausula.="T008_Aliquota_ICMS_Credito, "; // 53
		    $cClausula.="T008_Valor_ICMS_Credito, "; // 54
		    $cClausula.="T008_Valor_ICMS_Substituicao_Destino, "; // 55
		    $cClausula.="T008_Valor_Base_ICMS_Substituicao_Destino, "; // 56
		    $cClausula.="T008_Valor_ICMS_Substituicao_Retido, "; // 57
		    $cClausula.="T008_Valor_Base_ICMS_Substituicao_Retido, "; // 58
		    $cClausula.="T008_Percentual_Reducao_ICMS_ST, "; // 59
		    $cClausula.="T008_Modalidade_ICMS, "; // 60
		    $cClausula.="T008_Modalidade_ICMS_Substituicao, "; // 61
		    $cClausula.="T008_Flag_Calculo_Manual, "; // 62
		    $cClausula.="T008_UF_ICMS_Substituicao_Devido, "; // 63
		    $cClausula.="T008_Valor_Base_ICMS_Impresso, "; // 64
		    $cClausula.="T008_Valor_ICMS, "; // 65
		    $cClausula.="T008_Valor_Base_IPI, "; // 66
		    $cClausula.="T008_Valor_IPI, "; // 67
		    $cClausula.="T008_Valor_Base_PIS, "; // 68
		    $cClausula.="T008_Valor_PIS, "; // 69
		    $cClausula.="T008_Valor_Base_COFINS, "; // 70
		    $cClausula.="T008_Valor_COFINS, "; // 71
		    $cClausula.="T008_Valor_Base_ICMS, "; // 72
			$cClausula.="T008_Flag_Transporte_DI, "; // 73
            $cClausula.="T008_Flag_Tipo_Intermedio_DI, "; // 74
            $cClausula.="T008_CNPJ_Importador, "; // 75
            $cClausula.="T008_UF_Importador, "; // 76
            $cClausula.="T008_Numero_Drawback_Importacao, "; // 77
            $cClausula.="T008_Valor_Preco_Original, "; // 78
            $cClausula.="T008_T066_Id, "; // 79
            $cClausula.="T008_D004_Id, "; // 80
            $cClausula.="T008_T055_Id "; // 81
			
		    $cClausula.=") values ("; 
 		    $cClausula.="'{$g['c004']['C004_Id']}',"; // Empresa Atual  // 1
		    $cClausula.="'{$T007_Id}',"; // T007_Id // 2
		    $cClausula.="'{$mT075['T075_D006_Id']}',"; // D006_Id // 3
		    $cClausula.="'{$mT055['T055_D037_Id']}',"; // D037_Id // 4
		    $cClausula.="'{$mT075['D006_D059_Id']}',"; // D059_Id // 5
		    $cClausula.="'{$mT055['D037_Unidade']}',"; // D037_Id // 6
		    $cClausula.="'{$mT075['D006_Codigo_CFOP']}',"; // D006_Codigo_CFOP // 7
		    $cClausula.="'{$mT055['T055_Aliquota_IPI']}',"; // Aliquota IPI // 8
		    $cClausula.="'0',"; // Percentual Reducao IPI // 9
		    $cClausula.="'{$mT075['T075_Aliquota_ICMS']}',"; // Aliquota ICMS // 10
		    $cClausula.="'{$mT075['T075_Percentual_Reducao_ICMS']}',"; // Percentual Reducao ICMs // 11
		    $cClausula.="'{$mT055['T055_Peso_Kg_Liquido_Unitario']}',"; // Peso Unitario // 12
		    $cClausula.="'{$mT055['T055_D005_Id']}',"; // D005_Id // 13
		    $cClausula.="'100',"; // Flag situacao tributaria ICMS // 14
		    $cClausula.="'',"; // Flag Preco Especial // 15
		    $cClausula.="'{$mT055['D005_Classificacao_Fiscal']}',"; // NCM // 16
		    $cClausula.="'{$mT055['D088_Codigo_ST']}', "; // Situacao tributaria IPI // 17                                               
		    $cClausula.="'{$mT055['T055_Quantidade']}',"; // Quantidade // 18
		    $cClausula.="'{$mT055['T055_Codigo_Produto']}',"; // Codigo Produto // 19
			$cClausula.="'',"; // Codigo Barras // 20
		    $cClausula.="'',"; // Codigo Substituto // 21
		    $cClausula.="'{$mT055['T055_D009_Id']}',"; // D009_Id // 22
		    $cClausula.="'{$mT055['T055_Descricao_Produto']}',"; // Descricao Produto // 23
		    $cClausula.="'{$mT055['T055_Valor_Custo_Final_Unitario']}',"; // Valor Custo Unitario // 24
		    $cClausula.="'{$mT055['T055_Valor_Preco_Unitario_Nota_Fiscal']}',"; // Valor Preco sem desconto unitario // 25
		    $cClausula.="'N',"; // Flag ST // 26
		    $cClausula.="'N',"; // ST VA // 27
		    $cClausula.="'0',"; // ICMS Inter Estadual // 28
		    $cClausula.="'0',"; // ST ICMS Interno // 29
		    $cClausula.="'{$mT075['D006_Flag_Isento_PIS']}',"; // Flag Isento PIS // 30
		    $cClausula.="'{$mT055['T055_Aliquota_PIS']}',"; // Aliquota PIS // 31
		    $cClausula.="'{$mT075['D006_Situacao_Tributaria_PIS']}',"; // Situacao Tributaria PIS // 32
		    $cClausula.="'{$mT075['D006_Flag_Isento_COFINS']}',"; // Flag Isento COFINS // 33
		    $cClausula.="'{$mT055['T055_Aliquota_COFINS']}',"; // Aliquota COFINS // 34
		    $cClausula.="'{$mT075['D006_Situacao_Tributaria_COFINS']}', "; // Situacao Tributaria COFINS // 35
		    $cClausula.="'{$mT055['T055_Valor_Base_Imposto_Importacao']}', "; // Base II // 36
		    $cClausula.="'{$mT055['T055_Valor_Imposto_Importacao']}', "; // Valor II // 37
		    $cClausula.="'', "; // Despesas Aduaneira // 38
		    $cClausula.="'', "; // Valor IOF // 39
		    $cClausula.="'{$mT075['T075_Numero_DI']}', "; // Numero DI // 40
		    $cClausula.="'{$mT075['T075_Data_Emissao_DI']}', "; // Data registro DI // 41
		    $cClausula.="'{$mT075['D118_Nome']}', "; // Local Desembaraco // 42
		    $cClausula.="'{$mT075['T075_Data_Emissao_DI']}', "; // Data Desenbaraco // 43
		    $cClausula.="'{$mT075['D018_UF']}', "; // UF Desenbaraco // 44
		    $cClausula.="'{$mT075['T075_D024_Id']}', "; // Codigo Exportador // 45
		    $cClausula.="'{$mT055['T055_Valor_Adicao']}', "; // Numero Adicao // 46
		    $cClausula.="'1', "; // Numero sequencia adicao // 47
		    $cClausula.="'{$mT075['T075_D024_Id']}', "; // Codigo Fabricante // 48
		    $cClausula.="'', "; // Valor desconto Item adicao // 49
		    $cClausula.="'', "; // Numero Pedido compra // 50
		    $cClausula.="'', "; // Item pedido compra // 51
		    $cClausula.="'', "; // Percentual Base Operacao Propria // 52
		    $cClausula.="'', "; // Aliquota ICMS Credito // 53
		    $cClausula.="'', "; // Valor ICMS Credito // 54
		    $cClausula.="'', "; // Valor ICMS substituicao destino // 55
		    $cClausula.="'', "; // Valor Base ICMS substituicao destino // 56
		    $cClausula.="'', "; // Valor ICMS subsituicao Retido // 57
		    $cClausula.="'', "; // Valor Base ICMS Substituicao retido // 58
		    $cClausula.="'', "; // Percentual Reducao ICMS ST // 59
		    $cClausula.="'', "; // Modalidade ICMS // 60
		    $cClausula.="'', "; // Modalidade ICMS Substituicao // 61
		    $cClausula.="'S', "; // Flag Calculo Manual // 62
		    $cClausula.="'', "; // UF ICMS Substituticao Devido // 63
		    $cClausula.="'{$mT055[$baseICMS]}', "; // 64
		    $cClausula.="'{$icms}', "; // 65
		    $cClausula.="'{$mT055['T055_Valor_Base_IPI']}', "; // 66
		    $cClausula.="'{$mT055['T055_Valor_IPI']}', "; // 67
		    $cClausula.="'{$mT055['T055_Valor_Base_PIS']}', "; // 68
		    $cClausula.="'{$mT055['T055_Valor_PIS']}', "; // 69
		    $cClausula.="'{$mT055['T055_Valor_Base_COFINS']}', "; // 70
		    $cClausula.="'{$mT055['T055_Valor_COFINS']}', "; // 71
		    $cClausula.="'{$mT055['T055_Valor_Base_ICMS_Normal']}', "; // 72
			$cClausula.="'{$mT075['T075_Flag_Transporte_DI']}', "; // 73
            $cClausula.="'{$mT075['T075_Flag_Tipo_Intermedio_DI']}', "; // 74
            $cClausula.="'{$mT075['T075_CNPJ_Importador']}', "; // 75
            $cClausula.="'{$mT075['T075_UF_Importador']}', "; // 76
            $cClausula.="'{$mT075['T075_Numero_Drawback_Importacao']}', "; // 77
            $cClausula.="'{$mT055['T055_Valor_Preco_Unitario_Nota_Fiscal']}', "; // Valor Preco sem desconto unitario // 78
            $cClausula.="'{$mT066['T055_T066_Id']}', "; // 79
            $cClausula.="'{$mT066['T066_D004_Id']}', "; // 80
            $cClausula.="'{$mT055['T055_Id']}' "; // 81
		    $cClausula.=")";

			mysql_query($cClausula);
			$cErro=mysql_error();
			if (!empty($cErro))
			{
				mysql_query("ROLLBACK");
				return "Erro: " . $cErro . "<br />" . $cClausula;
			}

		}
		
		mysql_query("update T007 set T007_Numero_Nota_fiscal='' where T007_Id='$T007_Id'");
		
		mysql_query("call T007_Gravar_Totalizacao('$T007_Id')");

		// Cadastra os fornecedores dos produtos
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        $T008 = mysql_query("SELECT D001_Id, T007_D024_Id FROM T008 
                            LEFT JOIN D009 ON D009_Id=T008_D009_Id
                            LEFT JOIN D049 ON D049_Id=D009_D049_Id
                            LEFT JOIN D001 ON D001_Id=D049_D001_Id
                            LEFT JOIN T007 ON T007_Id=T008_T007_Id
                            LEFT JOIN T241 ON T241_D001_Id=D001_Id AND T241_D024_Id=T007_D024_Id
                            WHERE T008_T007_Id = '{$T007_Id}'
                            AND T241_Id IS NULL
                            GROUP BY D001_Id");
        while ($mT008 = mysql_fetch_array($T008)) {
            $CAD002->cadastrarFornecedorProduto($mT008['D001_Id'], $mT008['T007_D024_Id']);
        }
		
		mysql_query("COMMIT");
		mysql_query("UNLOCK ALL");
		
		return true;
	}

	/**
	* procuraCadastroItem
	*
	* @param string $dadoItem
	* @return array
	*/
	public function procuraCadastroItem($dadoItem){

		global $g;
		// Funcao personalizada por empresa
		// Recebe a descricao do item que vem na DI        
		$posicao = strpos($dadoItem, "CODIGO EUROSUL:");
		if($posicao === false){
			return false;
		}        

		$item   = substr($dadoItem, $posicao);
		$item   = explode(':', $item,-1);
		$codigo = explode('-', $item[1]);
		$codigo = trim($codigo[0]);
		$codigo = str_pad($codigo, 6, "0", STR_PAD_LEFT);

		$D009 = mysql_query("SELECT D009_Id,
									D001_Codigo_Produto,
									D001_Descricao_Produto,
									D001_Peso_Unitario_Kg,
									D037_Id,
									D001A_Altura,
									D001A_Largura,
									D001A_Comprimento
							   FROM D009
						  LEFT JOIN D049 ON D049_Id = D009_D049_Id
						  LEFT JOIN D001 ON D001_Id = D049_D001_Id
						  LEFT JOIN D037 ON D037_Id = D001_D037_Id
						  LEFT JOIN D001A ON D001_Id = D001A_D001_Id
							  WHERE D001_Codigo_Produto = '{$codigo}'
							    AND D009_C004_Id = '{$g['empresaAtual']}'");

		if(mysql_num_rows($D009) <=0){
			return $dadoItem;
		} else{
			$mD009 = mysql_fetch_array($D009);
		}

		$array = array();
		$array['D009_Id']                = $mD009['D009_Id'];
		$array['D001_Codigo_Produto']    = $mD009['D001_Codigo_Produto'];
		$array['D001_Descricao_Produto'] = $mD009['D001_Descricao_Produto'];
		$array['D001_Peso_Unitario_Kg']  = $mD009['D001_Peso_Unitario_Kg'];
		$array['D037_Id']                = $mD009['D037_Id'];
		$array['D001A_Altura']           = $mD009['D001A_Altura'];
	    $array['D001A_Largura']          = $mD009['D001A_Largura'];
		$array['D001A_Comprimento']      = $mD009['D001A_Comprimento'];
		
		return $array;
		
	}  

	/**
     * imp007Finalizar
     *
     * @param String $T075_Id
     * @return String/bool
     */     
	 // Melhoria lançar numero lote
    public function imp007Finalizar($T075_Id)
    {
        global $g;
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");

        $T075  = mysql_query("SELECT T075_Data_Estoque FROM T075 WHERE T075_Id='{$T075_Id}'");
        $mT075 = mysql_fetch_array($T075);
        if ($mT075['T075_Data_Estoque'] != null && $mT075['T075_Data_Estoque'] != '0000-00-00') {
            return "Esse processo já foi realizado";
        }
        
        $T055 = mysql_query("SELECT T066_D004_Id,
                                    T066_Id,
                                    D001_Codigo_Produto,
                                    D001A_Flag_Validade,
                                    T055A_Data_Validade,
                                    T055_T066_Id,
                                    T066A_Data_Validade,
                                    T066_Quantidade_Estoque,
                                    T055A_Id,
									T055A_Largura,
									T055A_Altura,
									T055A_Comprimento
                               FROM T055
                          LEFT JOIN D009 ON D009_Id=T055_D009_Id
                          LEFT JOIN D049 ON D049_Id=D009_D049_Id
                          LEFT JOIN D001 ON D001_Id=D049_D001_Id
                          LEFT JOIN D001A ON D001_Id=D001A_D001_Id
                          LEFT JOIN T055A ON T055_Id=T055A_T055_Id
                          LEFT JOIN T066 ON T055_T066_Id=T066_Id
                          LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                              WHERE T055_T075_Id='{$T075_Id}'
                           GROUP BY T055_Id");

        $mensagemProduto      = '';
        while ($mT055 = mysql_fetch_array($T055)) {

            $erroProdutos = '';

            if (($mT055['T055_T066_Id']<=0 || $mT055['T066_D004_Id'] <=0)){
                $erroProdutos .= "- Está sem locação preenchida.\n";
            }
            if($mT055['D001A_Flag_Validade'] == 'S' && ($mT055['T055A_Data_Validade'] == '0000-00-00' || empty($mT055['T055A_Data_Validade']))){
                $erroProdutos .= "- Está sem data de validade e produto tem controle de validade.\n";
            }

			if($g['C031']['incluirLocacaoAutomaticaItemDiComValidade'] == 'N' && $mT055['T055_T066_Id'] > 0 && $mT055['T066_Quantidade_Estoque'] > 0 && ($mT055['T055A_Data_Validade'] != '0000-00-00' || empty($mT055['T055A_Data_Validade'])) && $mT055['T066A_Data_Validade'] != $mT055['T055A_Data_Validade'] && $mT055['T055A_Id'] > 0){
                $erroProdutos .= "- A locação selecionada possui estoque e data de validade diferente da DI, verifique ou cadastre uma nova locação.\n";
            }

			if(($mT055['T055A_Largura'] <= 0 || empty($mT055['T055A_Largura'])) || ($mT055['T055A_Altura'] <= 0 || empty($mT055['T055A_Altura'])) || ($mT055['T055A_Comprimento'] <= 0 || empty($mT055['T055A_Comprimento']))){

				$dimensaoProdutos = '';
				if($mT055['T055A_Altura'] <= 0 || empty($mT055['T055A_Altura'])){
					$dimensaoProdutos .= " Altura";
				}

				if($mT055['T055A_Largura'] <= 0 || empty($mT055['T055A_Largura'])){
					$dimensaoProdutos .= (!empty($dimensaoProdutos)) ? " / Largura" : "Largura"; 
				}

				if($mT055['T055A_Comprimento'] <= 0 || empty($mT055['T055A_Comprimento'])){
					$dimensaoProdutos .= (!empty($dimensaoProdutos)) ? " / Comprimento" : "Comprimento"; 
				}

				$erroProdutos .= "Faltando dimensão: ". $dimensaoProdutos . ".\n";
            }



            if(!empty($erroProdutos)){
                $mensagemProduto .= "CÓDIGO ".$mT055['D001_Codigo_Produto'].":\n".$erroProdutos."\n";
            }   
        }

        if (!empty($mensagemProduto)){                
            mysql_query("ROLLBACK");
            return $mensagemProduto;
        }

        /**
         * SELECIONANDO TODOS OS ITENS DA DI
         */
        $T055 = mysql_query("SELECT T075_Id,
                                    T055_Id,
                                    T055_Quantidade,
                                    T055_D009_Id,
                                    T055_D006_Id,
                                    T075_D024_Id,
                                    T066_D004_Id,
                                    T066_Id,
                                    D049_Id,
                                    D001_Flag_Produto_Serie,
                                    D001_Id,
                                    IFNULL(D001A_Id,0) AS D001A_Id,
                                    T001_Id,
                                    T055_T066_Id,
									T055A_Id,
                                    T055A_Data_Validade,
                                    T055A_Largura,
                                    T055A_Altura,
                                    T055A_Comprimento,
									T066A_Data_Validade,
                                    T066_Quantidade_Estoque,
                                    T055A_Flag_Credita_Estoque
                            FROM T055 
                            LEFT JOIN T075 ON T075_Id='{$T075_Id}' 
                            LEFT JOIN D009 ON D009_Id=T055_D009_Id
                            LEFT JOIN D049 ON D049_Id=D009_D049_Id
                            LEFT JOIN D001 ON D001_Id=D049_D001_Id
                            LEFT JOIN D001A ON D001_Id=D001A_D001_Id
                            LEFT JOIN T066 ON T066_Id=T055_T066_Id
							LEFT JOIN T066A ON T066_Id=T066A_T066_Id
                            LEFT JOIN T001 ON T001_T055_Id=T055_Id AND T001_Flag_Operacao!='C' AND T001_Descricao_Operacao like 'IMP-%'
                            LEFT JOIN T055A ON T055_Id = T055A_T055_Id
                            WHERE T055_T075_Id='{$T075_Id}'
                            GROUP BY T055_Id");
                
        if (mysql_num_rows($T055) == 0) {
            return "Não existem produtos nessa DI";
        }
        
        // LOOPING PARA INSERIR NO HISTÓRICO DO PRODUTO
        while ($mT055 = mysql_fetch_array($T055)) {
            if ($mT055['T001_Id']>0){
                continue;
            }

 			//if($g['C031']['incluirLocacaoAutomaticaItemDiComValidade'] == 'S' && $mT055['T055_T066_Id'] > 0 && $mT055['T066_Quantidade_Estoque'] > 0 && ($mT055['T055A_Data_Validade'] != '0000-00-00' || empty($mT055['T055A_Data_Validade'])) && $mT055['T066A_Data_Validade'] != $mT055['T055A_Data_Validade'] && $mT055['T055A_Id'] > 0){
            //Melhoria solicitada pelo Lucas em 04/09/2025, sempre vai lançar uma nova locação para gravar o lote
            if($mT055['T055_T066_Id'] > 0 && $mT055['T066_Quantidade_Estoque'] > 0){
                //Lança um novo T066 com o mesmo D004 que vai entrar com nova data de validade
                mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$mT055['T055_D009_Id']}', '{$mT055['T066_D004_Id']}')");

                $mT055['T055_T066_Id'] = $g['mysqlLastId'];
                mysqli_query("UPDATE T055 SET T055_T066_Id = '{$mT055['T055_T066_Id']}' WHERE T055_Id = '{$mT055['T055_Id']}'");
            } 

            //$mT055['T055_T066_Id'] = $g['mysqlLastId'];
            mysqli_query("UPDATE T055 SET T055_T066_Id = '{$mT055['T055_T066_Id']}' WHERE T055_Id = '{$mT055['T055_Id']}'");

            $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
            $numeroLote = date("YmdHi") . rand(1000, 9999);
            if(mysql_num_rows($T066A) > 0){
                mysql_query("UPDATE T066A SET T066A_Data_Validade = '{$mT055['T055A_Data_Validade']}', T066A_Numero_Lote = '{$numeroLote}'  WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
                $erro = mysql_error();
                if (!empty($erro)) {
                    mysql_query("ROLLBACK");
                    return "3 " . $erro;
                }

            } else {
                mysql_query("INSERT INTO T066A (T066A_T066_Id,T066A_Data_Validade, T066A_Numero_Lote) VALUES  ('{$mT055['T055_T066_Id']}','{$mT055['T055A_Data_Validade']}', '{$numeroLote}')");
                $erro = mysql_error();
                if (!empty($erro)) {
                    mysql_query("ROLLBACK");
                    return "3 " . $erro;
                }
            }

            $creditaEstoque = ($mT055['T055A_Flag_Credita_Estoque'] == 'N') ? 'N' : 'E';

            $sql = <<<EOT
            INSERT INTO T001 (
                T001_Descricao_Operacao,
                T001_Flag_Operacao,
                T001_Quantidade,
                T001_Data_Lancamento,
                T001_T055_Id,
                T001_D009_Id,
                T001_D006_Id,
                T001_D024_Id,
                T001_D004_Id,
                T001_T066_Id
            ) VALUES (
                'IMP-{$T075_Id}',
                '{$creditaEstoque}',
                '{$mT055['T055_Quantidade']}',
                CURDATE(),
                '{$mT055['T055_Id']}',
                '{$mT055['T055_D009_Id']}',
                '{$mT055['T055_D006_Id']}',
                '{$mT055['T075_D024_Id']}',
                '{$mT055['T066_D004_Id']}',
                '{$mT055['T055_T066_Id']}'
            );
EOT;
            mysql_query($sql);
            $erro = mysql_error();
            if ($erro) {
                mysql_query("ROLLBACK");
                return "1 " . $erro;
            }
            
            $T006 = mysql_query("UPDATE T006 SET T006_T075_Id='' WHERE T006_T075_Id='{$T075_Id}' and T006_D009_Id='{$mT055['T055_D009_Id']}'");
            $erro = mysql_error();
            if ($erro) {
                mysql_query("ROLLBACK");
                return "2 " . $erro;
            }

            // $retorno = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id'],100);
            $retornoProcessa = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id'],20);
            if($retornoProcessa !== true){
                registrarLog(array("D009", $mT055['T055_D009_Id'], $g['empresaAtual'], " Erro ao reprocessar histórico - IMP003 - ATUALIZAR CUSTO:", $retornoProcessa, "S",  3));
                $retorno = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id']);

                if ($retorno != true) {
                    mysql_query("ROLLBACK");
                    return "Erro reprocessa histórico: " . $retorno;
                }
            }
            
            if ($mT055['D001_Flag_Produto_Serie'] == 'S') {
                
                $quantidade = $mT055['T055_Quantidade'];
                
                for ($i = 1; $i <= $quantidade; $i++) {
                    
                    $D144 = <<<EOT
                        INSERT INTO D144 (
                            D144_D049_Id,
                            D144_T055_Id
                        ) VALUES (
                            '{$mT055['D049_Id']}',
                            '{$mT055['T055_Id']}'
                        );
EOT;
                    $retorno = mysql_query($D144);
                    if ($retorno != true) {
                        mysql_query("ROLLBACK");
                        return "Erro ao inserir o número de série.";
                    }
                }
            }

            /*if($mT055['T055A_Data_Validade'] != '0000-00-00' && !empty($mT055['T055A_Data_Validade'])){
                //VERIFICA SE JA EXISTE REGISTRO NA TABELA ESTENDIDA
                $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
                if(mysql_num_rows($T066A) > 0){
                    mysql_query("UPDATE T066A SET T066A_Data_Validade = '{$mT055['T055A_Data_Validade']}' WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
                    $erro = mysql_error();
                    if (!empty($erro)) {
                        mysql_query("ROLLBACK");
                        return "3 " . $erro;
                    }

                } else {
                    mysql_query("INSERT INTO T066A (T066A_T066_Id,T066A_Data_Validade) VALUES  ('{$mT055['T055_T066_Id']}','{$mT055['T055A_Data_Validade']}')");
                    $erro = mysql_error();
                    if (!empty($erro)) {
                        mysql_query("ROLLBACK");
                        return "3 " . $erro;
                    }
                }
            } */

            if($mT055['D001A_Id'] <= 0){
                mysql_query("INSERT INTO D001A (D001A_D001_Id,
                                                D001A_Largura,
                                                D001A_Altura,
                                                D001A_Comprimento) 
                                        VALUES ('{$mT055['D001_Id']}',
                                                '{$mT055['T055A_Largura']}',
                                                '{$mT055['T055A_Altura']}',
                                                '{$mT055['T055A_Comprimento']}')");
            } else {
                mysqli_query("UPDATE D001A 
                                 SET D001A_Largura     = IF('{$mT055['T055A_Largura']}' > 0, '{$mT055['T055A_Largura']}', D001A_Largura),
                                     D001A_Altura      = IF('{$mT055['T055A_Altura']}' > 0, '{$mT055['T055A_Altura']}', D001A_Altura),
                                     D001A_Comprimento = IF('{$mT055['T055A_Comprimento']}' > 0, '{$mT055['T055A_Comprimento']}', D001A_Comprimento)
                               WHERE D001A_Id = '{$mT055['D001A_Id']}'");

            }

            if($mT055['T055A_Altura'] > 0 && $mT055['T055A_Comprimento'] > 0 && $mT055['T055A_Largura'] > 0){
                $cubagem = $mT055['T055A_Altura'] * $mT055['T055A_Largura'] * $mT055['T055A_Comprimento'];

                mysqli_query("UPDATE D001 
                                 SET D001_Cubagem_Unitaria = '{$cubagem}' 
                               WHERE D001_Id = '{$mT055['D001_Id']}'");
            }
        }

 		$T075 = mysql_query("UPDATE T075 SET T075_Data_Estoque=CURDATE(), 
                                             T075_C007_Id_Finalizou_Estoque = '{$g['usuarioAtual']}'
                                       WHERE T075_Id='{$T075_Id}'");        
		$erro = mysql_error();
        if ($erro) {
            mysql_query("ROLLBACK");
            return "4 " . $erro;
        }

        mysql_query("COMMIT");
        return true;
    }

	/**
     * imp007Finalizar
     *
     * @param String $T075_Id
     * @return String/bool
     */     
    public function xxxximp007Finalizar($T075_Id)
    {
        global $g;
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        
        mysql_query("SET AUTOCOMMIT=0");
        mysql_query("START TRANSACTION");

        $T075  = mysql_query("SELECT T075_Data_Estoque FROM T075 WHERE T075_Id='{$T075_Id}'");
        $mT075 = mysql_fetch_array($T075);
        if ($mT075['T075_Data_Estoque'] != null && $mT075['T075_Data_Estoque'] != '0000-00-00') {
            return "Esse processo já foi realizado";
        }
        
        $T055 = mysql_query("SELECT T066_D004_Id,
                                    T066_Id,
                                    D001_Codigo_Produto,
                                    D001A_Flag_Validade,
                                    T055A_Data_Validade,
                                    T055_T066_Id,
                                    T066A_Data_Validade,
                                    T066_Quantidade_Estoque,
                                    T055A_Id,
									T055A_Largura,
									T055A_Altura,
									T055A_Comprimento
                               FROM T055
                          LEFT JOIN D009 ON D009_Id=T055_D009_Id
                          LEFT JOIN D049 ON D049_Id=D009_D049_Id
                          LEFT JOIN D001 ON D001_Id=D049_D001_Id
                          LEFT JOIN D001A ON D001_Id=D001A_D001_Id
                          LEFT JOIN T055A ON T055_Id=T055A_T055_Id
                          LEFT JOIN T066 ON T055_T066_Id=T066_Id
                          LEFT JOIN T066A ON T066_Id = T066A_T066_Id
                              WHERE T055_T075_Id='{$T075_Id}'
                           GROUP BY T055_Id");

        $mensagemProduto      = '';
        while ($mT055 = mysql_fetch_array($T055)) {

            $erroProdutos = '';

            if (($mT055['T055_T066_Id']<=0 || $mT055['T066_D004_Id'] <=0)){
                $erroProdutos .= "- Está sem locação preenchida.\n";
            }
            if($mT055['D001A_Flag_Validade'] == 'S' && ($mT055['T055A_Data_Validade'] == '0000-00-00' || empty($mT055['T055A_Data_Validade']))){
                $erroProdutos .= "- Está sem data de validade e produto tem controle de validade.\n";
            }

			if($g['C031']['incluirLocacaoAutomaticaItemDiComValidade'] == 'N' && $mT055['T055_T066_Id'] > 0 && $mT055['T066_Quantidade_Estoque'] > 0 && ($mT055['T055A_Data_Validade'] != '0000-00-00' || empty($mT055['T055A_Data_Validade'])) && $mT055['T066A_Data_Validade'] != $mT055['T055A_Data_Validade'] && $mT055['T055A_Id'] > 0){
                $erroProdutos .= "- A locação selecionada possui estoque e data de validade diferente da DI, verifique ou cadastre uma nova locação.\n";
            }

			if(($mT055['T055A_Largura'] <= 0 || empty($mT055['T055A_Largura'])) || ($mT055['T055A_Altura'] <= 0 || empty($mT055['T055A_Altura'])) || ($mT055['T055A_Comprimento'] <= 0 || empty($mT055['T055A_Comprimento']))){

				$dimensaoProdutos = '';
				if($mT055['T055A_Altura'] <= 0 || empty($mT055['T055A_Altura'])){
					$dimensaoProdutos .= " Altura";
				}

				if($mT055['T055A_Largura'] <= 0 || empty($mT055['T055A_Largura'])){
					$dimensaoProdutos .= (!empty($dimensaoProdutos)) ? " / Largura" : "Largura"; 
				}

				if($mT055['T055A_Comprimento'] <= 0 || empty($mT055['T055A_Comprimento'])){
					$dimensaoProdutos .= (!empty($dimensaoProdutos)) ? " / Comprimento" : "Comprimento"; 
				}

				$erroProdutos .= "Faltando dimensão: ". $dimensaoProdutos . ".\n";
            }



            if(!empty($erroProdutos)){
                $mensagemProduto .= "CÓDIGO ".$mT055['D001_Codigo_Produto'].":\n".$erroProdutos."\n";
            }   
        }

        if (!empty($mensagemProduto)){                
            mysql_query("ROLLBACK");
            return $mensagemProduto;
        }

        /**
         * SELECIONANDO TODOS OS ITENS DA DI
         */
        $T055 = mysql_query("SELECT T075_Id,
                                    T055_Id,
                                    T055_Quantidade,
                                    T055_D009_Id,
                                    T055_D006_Id,
                                    T075_D024_Id,
                                    T066_D004_Id,
                                    T066_Id,
                                    D049_Id,
                                    D001_Flag_Produto_Serie,
                                    D001_Id,
                                    IFNULL(D001A_Id,0) AS D001A_Id,
                                    T001_Id,
                                    T055_T066_Id,
									T055A_Id,
                                    T055A_Data_Validade,
                                    T055A_Largura,
                                    T055A_Altura,
                                    T055A_Comprimento,
									T066A_Data_Validade,
                                    T066_Quantidade_Estoque
                            FROM T055 
                            LEFT JOIN T075 ON T075_Id='{$T075_Id}' 
                            LEFT JOIN D009 ON D009_Id=T055_D009_Id
                            LEFT JOIN D049 ON D049_Id=D009_D049_Id
                            LEFT JOIN D001 ON D001_Id=D049_D001_Id
                            LEFT JOIN D001A ON D001_Id=D001A_D001_Id
                            LEFT JOIN T066 ON T066_Id=T055_T066_Id
							LEFT JOIN T066A ON T066_Id=T066A_T066_Id
                            LEFT JOIN T001 ON T001_T055_Id=T055_Id AND T001_Flag_Operacao!='C' AND T001_Descricao_Operacao like 'IMP-%'
                            LEFT JOIN T055A ON T055_Id = T055A_T055_Id
                            WHERE T055_T075_Id='{$T075_Id}'
                            GROUP BY T055_Id");
                
        if (mysql_num_rows($T055) == 0) {
            return "Não existem produtos nessa DI";
        }
        
        // LOOPING PARA INSERIR NO HISTÓRICO DO PRODUTO
        while ($mT055 = mysql_fetch_array($T055)) {
            if ($mT055['T001_Id']>0){
                continue;
            }

			if($g['C031']['incluirLocacaoAutomaticaItemDiComValidade'] == 'S' && $mT055['T055_T066_Id'] > 0 && $mT055['T066_Quantidade_Estoque'] > 0 && ($mT055['T055A_Data_Validade'] != '0000-00-00' || empty($mT055['T055A_Data_Validade'])) && $mT055['T066A_Data_Validade'] != $mT055['T055A_Data_Validade'] && $mT055['T055A_Id'] > 0){
                //Lança um novo T066 com o mesmo D004 que vai entrar com nova data de validade
                mysql_query("INSERT INTO T066 (T066_D009_Id, T066_D004_Id) VALUES ('{$mT055['T055_D009_Id']}', '{$mT055['T066_D004_Id']}')");

                $mT055['T055_T066_Id'] = $g['mysqlLastId'];
                mysqli_query("UPDATE T055 SET T055_T066_Id = '{$mT055['T055_T066_Id']}' WHERE T055_Id = '{$mT055['T055_Id']}'");
            }

            $sql = <<<EOT
            INSERT INTO T001 (
                T001_Descricao_Operacao,
                T001_Flag_Operacao,
                T001_Quantidade,
                T001_Data_Lancamento,
                T001_T055_Id,
                T001_D009_Id,
                T001_D006_Id,
                T001_D024_Id,
                T001_D004_Id,
                T001_T066_Id
            ) VALUES (
                'IMP-{$T075_Id}',
                'E',
                '{$mT055['T055_Quantidade']}',
                CURDATE(),
                '{$mT055['T055_Id']}',
                '{$mT055['T055_D009_Id']}',
                '{$mT055['T055_D006_Id']}',
                '{$mT055['T075_D024_Id']}',
                '{$mT055['T066_D004_Id']}',
                '{$mT055['T055_T066_Id']}'
            );
EOT;
            mysql_query($sql);
            $erro = mysql_error();
            if ($erro) {
                mysql_query("ROLLBACK");
                return "1 " . $erro;
            }
            
            $T006 = mysql_query("UPDATE T006 SET T006_T075_Id='' WHERE T006_T075_Id='{$T075_Id}' and T006_D009_Id='{$mT055['T055_D009_Id']}'");
            $erro = mysql_error();
            if ($erro) {
                mysql_query("ROLLBACK");
                return "2 " . $erro;
            }

            // $retorno = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id'],100);
            $retornoProcessa = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id'],20);
            if($retornoProcessa !== true){
                registrarLog(array("D009", $mT055['T055_D009_Id'], $g['empresaAtual'], " Erro ao reprocessar histórico - IMP003 - ATUALIZAR CUSTO:", $retornoProcessa, "S",  3));
                $retorno = $CAD002->D001_reprocessa_historico(0, $mT055['T055_D009_Id']);

                if ($retorno != true) {
                    mysql_query("ROLLBACK");
                    return "Erro reprocessa histórico: " . $retorno;
                }
            }
            
            if ($mT055['D001_Flag_Produto_Serie'] == 'S') {
                
                $quantidade = $mT055['T055_Quantidade'];
                
                for ($i = 1; $i <= $quantidade; $i++) {
                    
                    $D144 = <<<EOT
                        INSERT INTO D144 (
                            D144_D049_Id,
                            D144_T055_Id
                        ) VALUES (
                            '{$mT055['D049_Id']}',
                            '{$mT055['T055_Id']}'
                        );
EOT;
                    $retorno = mysql_query($D144);
                    if ($retorno != true) {
                        mysql_query("ROLLBACK");
                        return "Erro ao inserir o número de série.";
                    }
                }
            }

            if($mT055['T055A_Data_Validade'] != '0000-00-00' && !empty($mT055['T055A_Data_Validade'])){
                //VERIFICA SE JA EXISTE REGISTRO NA TABELA ESTENDIDA
                $T066A = mysql_query("SELECT T066A_Id FROM T066A WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
                if(mysql_num_rows($T066A) > 0){
                    mysql_query("UPDATE T066A SET T066A_Data_Validade = '{$mT055['T055A_Data_Validade']}' WHERE T066A_T066_Id = '{$mT055['T055_T066_Id']}'");
                    $erro = mysql_error();
                    if (!empty($erro)) {
                        mysql_query("ROLLBACK");
                        return "3 " . $erro;
                    }

                } else {
                    mysql_query("INSERT INTO T066A (T066A_T066_Id,T066A_Data_Validade) VALUES  ('{$mT055['T055_T066_Id']}','{$mT055['T055A_Data_Validade']}')");
                    $erro = mysql_error();
                    if (!empty($erro)) {
                        mysql_query("ROLLBACK");
                        return "3 " . $erro;
                    }
                }
            }

            if($mT055['D001A_Id'] <= 0){
                mysql_query("INSERT INTO D001A (D001A_D001_Id,
                                                D001A_Largura,
                                                D001A_Altura,
                                                D001A_Comprimento) 
                                        VALUES ('{$mT055['D001_Id']}',
                                                '{$mT055['T055A_Largura']}',
                                                '{$mT055['T055A_Altura']}',
                                                '{$mT055['T055A_Comprimento']}')");
            } else {
                mysqli_query("UPDATE D001A 
                                 SET D001A_Largura     = IF('{$mT055['T055A_Largura']}' > 0, '{$mT055['T055A_Largura']}', D001A_Largura),
                                     D001A_Altura      = IF('{$mT055['T055A_Altura']}' > 0, '{$mT055['T055A_Altura']}', D001A_Altura),
                                     D001A_Comprimento = IF('{$mT055['T055A_Comprimento']}' > 0, '{$mT055['T055A_Comprimento']}', D001A_Comprimento)
                               WHERE D001A_Id = '{$mT055['D001A_Id']}'");

            }

            if($mT055['T055A_Altura'] > 0 && $mT055['T055A_Comprimento'] > 0 && $mT055['T055A_Largura'] > 0){
                $cubagem = $mT055['T055A_Altura'] * $mT055['T055A_Largura'] * $mT055['T055A_Comprimento'];

                mysqli_query("UPDATE D001 
                                 SET D001_Cubagem_Unitaria = '{$cubagem}' 
                               WHERE D001_Id = '{$mT055['D001_Id']}'");
            }
        }

 		$T075 = mysql_query("UPDATE T075 SET T075_Data_Estoque=CURDATE(), 
                                             T075_C007_Id_Finalizou_Estoque = '{$g['usuarioAtual']}'
                                       WHERE T075_Id='{$T075_Id}'");        
		$erro = mysql_error();
		
        if ($erro) {
            mysql_query("ROLLBACK");
            return "4 " . $erro;
        }

        mysql_query("COMMIT");
        return true;
    }	
	
}































