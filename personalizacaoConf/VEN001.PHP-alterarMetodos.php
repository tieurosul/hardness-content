<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class VEN001 extends VEN001_ {
	// defina os métodos para sobreescreverqu

    // Felipe Kadanos - 27/10/2025
    // Personalizado a pedido do Miguel para adicionar opção de imprimir descrição em ingles
    // 23/02/2024 - Felipe Carrano
    // impressao com alterações de melhoria solicitada pelo lucas
	public function montarEmailPdf($T003_Id, $imprimirHtml = false, $moeda = '1', $descIngles = false)
    {
        global $g, $confUsuario;

        // Ajuste do tamanho da fonte para casos que a geração do PDF está ficando com fonte muito pequena
        $fonteMinima = 13 + $g['C031']['vendasZoomFonteImprimirPDF'];
        $fonteMedia  = 18 + $g['C031']['vendasZoomFonteImprimirPDF'];
        $fonteGrande = 20 + $g['C031']['vendasZoomFonteImprimirPDF'];

        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();        
        
        if (!$imprimirHtml) {
            // Arquivo
            $dir = $g['pathDados'] . 'tmp/';
            is_dir($dir) OR mkdir($dir, 0777, true);
            $filename             = "orcamento-{$T003_Id}.pdf";
            $file                 = $dir . $filename;
            $fileFull             = $g['pathRaiz'] . '/' . $file;
            $retorno['Anexos']    = array(
                $file
            );
            $retorno['AnexosWeb'] = array(
                '/hardness3/' . $file
            );
            
            $T003 = <<<EOT
                SELECT * FROM T003 
                LEFT JOIN D013 ON D013_Id=T003_D013_Id
                LEFT JOIN D024 ON D024_Id=T003_D024_Id
                WHERE T003_Id='{$T003_Id}';
EOT;
            
            $T003 = mysql_query($T003);
            $T003 = mysql_fetch_array($T003);
            
            // Array de retorno para criação do e-mail
            $retorno['Para']    = (!empty($T003['D013_E_Mail'])) ? $T003['D013_E_Mail'] : $T003['D024_E_Mail'];
            $retorno['Assunto'] = "Orçamento Nº[{$T003_Id}]";
            
            $retorno['Corpo'] = "<img src='{$caminhoImagem}' style='margin-left:10px'><font size='3'><br /><br /><br /><br /></font><font style='font-size:16px'>Segue orçamento de venda número <b>$T003_Id</b> em anexo.</font><font size='3'><br /><br /></font>";
            
            $r_link = '/ven/ven001/form_func-ajax/ven001ImprimirOrcamento/?ajax=true&gerarHtml=1&T003_Id=' . $T003_Id . '&moeda=' . $moeda . '&descIngles=' . $descIngles; // .'&alcadaUpdate='.$alcadaUpdate;
            $r_link = str_replace(' ', '+', $r_link);
            shell_exec("xvfb-run --server-num " . mt_rand(0, 98) . " -s \"-screen 0 640x480x16\" wkhtmltopdf --minimum-font-size {$fonteMinima} --quiet --margin-left 1 --margin-right 1 --margin-top 1 --margin-bottom 1 \"{$g['urlRaiz']}{$r_link}&noEmp=true&p={$g['empresaAtual']}&u={$g['usuarioAtual']}&db={$confUsuario['dbDatabase']}\" \"{$fileFull}\"");
            log("xvfb-run --server-num " . mt_rand(0, 98) . " -s \"-screen 0 640x480x16\" wkhtmltopdf --minimum-font-size 13 --quiet --margin-left 1 --margin-right 1 --margin-top 1 --margin-bottom 1 \"{$g['urlRaiz']}{$r_link}&noEmp=true&p={$g['empresaAtual']}&u={$g['usuarioAtual']}&db={$confUsuario['dbDatabase']}\" \"{$fileFull}\"");
            
            return $retorno;
        }
        
        error_reporting(E_ALL);
				/**
				* SELECT's
				*/
				$C004 = mysql_query("SELECT * FROM C004 WHERE C004_Id = '{$g['empresaAtual']}'");
				$resC004 = mysql_fetch_array($C004);
				$footer = $resC004['C004_Observacao_Rodape_Orcamento'];
				
				$T003_Id = mysql_real_escape_string($T003_Id);
				$sql = "SELECT *, T003_Prazos(T003_Id) as formaPagamento FROM T003 
						   LEFT JOIN D013 ON D013_Id=T003_D013_Id 
						   LEFT JOIN D024 ON D024_Id=T003_D024_Id 
						   LEFT JOIN D020 ON D020_Id=D024_D020_Id
						   LEFT JOIN D018 ON D018_Id=D024_D018_Id 
						   LEFT JOIN T003A ON T003_Id=T003A_T003_Id
						   WHERE T003_Id='{$T003_Id}'";
				log($sql);
				$T003 = mysql_query($sql);
				$T003 = mysql_fetch_array($T003);
		
				$sql = <<<EOT
					 SELECT * FROM T004 
					LEFT JOIN D009 ON D009_Id=T004_D009_Id 
					LEFT JOIN D049 ON D049_Id=D009_D049_Id
					LEFT JOIN D001 ON D001_Id=D049_D001_Id
					LEFT JOIN T144 ON T144_D001_Id=D001_Id AND T144_Flag_Tipo != 'F'
		            LEFT JOIN D037 ON D037_Id=T004_D037_Id
		            LEFT JOIN D005 ON D005_Id=T004_D005_Id
					LEFT JOIN T004A ON T004_Id=T004A_T004_Id
					LEFT JOIN T066 ON T066_Id=T004A_T066_Id
					LEFT JOIN T066A ON T066_Id=T066A_T066_Id
					LEFT JOIN D001A ON D001_Id=D001A_D001_Id
					LEFT JOIN D006 ON D006_Id=T004_D006_Id
					WHERE T004_T003_Id = '{$T003_Id}' 
					GROUP BY T004_Id
					ORDER BY T004_Item
EOT;
                // Lucas pediu para a ordenação ser pelo T004_Item apenas, abaixo order by antigo
       			//ORDER BY T004_Item_Pedido_Compra,T004_Id

				$T004_sql = mysql_query($sql);				
				
				$sql = "SELECT D024_E_Mail FROM D024 WHERE D024_Id='{$T003['T003_D024_Id']}'";
				$D024 = mysql_query($sql);
				$D024 = mysql_fetch_array($D024);	
				
				$sql = "SELECT * FROM D013 WHERE D013_Id='{$T003['T003_D013_Id']}'";
				$D013 = mysql_query($sql);
				$D013 = mysql_fetch_array($D013);

                $dadosEnd = [
                    "cep" => $T003['D024_Cep'],
                    "rua" => $T003['D024_Endereco'],
                    "numero" => $T003['D024_Numero'],
                    "complemento" => $T003['D024_Complemento'],
                    "bairro" => $T003['D024_Bairro'],
                    "uf" => $T003['D018_UF'],
                    "cidade" => $T003['D020_Nome_Cidade'],
                ];

                if ($T003['T003_D148_Id_Entrega'] > 0) {
                    $sql = "SELECT * FROM D148 
                                LEFT JOIN D020 ON D020_Id = D148_D020_Id
                                LEFT JOIN D018 ON D018_Id = D148_D018_Id
                                LEFT JOIN D030 ON D030_Id = D148_D030_Id
                            WHERE D148_Id = '{$T003['T003_D148_Id_Entrega']}'";
                    $end = mysql_fetch_assoc(mysqli_query($sql));
                            
                    $dadosEnd = [
                        "cep" => $end['D148_Cep'],
                        "rua" => $end['D148_Rua'],
                        "numero" => $end['D148_Numero'],
                        "complemento" => $end['D148_Complemento'],
                        "bairro" => $end['D148_Bairro'],
                        "uf" => $end['D018_UF'],
                        "cidade" => $end['D020_Nome_Cidade'],
                    ];
                }
				
				// Array de retorno para criação do e-mail
				$array = array();
				$array['Para'] = (!empty($T003['D013_E_Mail'])) ? $T003['D013_E_Mail'] : $D024['D024_E_Mail'];
				$array['Assunto'] = "Orçamento Nº[{$T003_Id}]";
							
				$html = '';
				/**
				* PDF - STYLE
				*/
				$html .= <<<EOT
				<style>
					body {
						margin: 10mm 10mm 10mm 10mm;
						font-family: Roboto, Arial, sans-serif, Verdana;
					}
					.header .text {
						font-size: 15px;
					}
					.header .text2 {
						font-size: 25px;
						font-weight: bold;
					}
					.titulo_superior {
						font-size: 10px;
					}
					.tdGeral {
						font-size: 10px;
					}
					.itens {
						background: #4F4D4D;
					}
					.itens td {
						font-size: 10px;
						background: white;
					}
					.itens th {
						background: #adadad;
						font-size: 12px;
					}
					.textIncluso {
						font-size: 14px;
						color: red;
						background-color: #E1ECF1;
					}
					
				</style>
EOT;
				
				/**
				* PDF - HEADER
				*/
				
 				if(!empty($g['c004']['C004_Logo_Marca'])){
					$pathinfo = pathinfo($g['c004']['C004_Logo_Marca']);
					$caminhoImagem = "{$confUsuario['urlRaiz']}{$g['pathWeb']}{$g['pathDados']}imagens/logo/{$g['empresaAtual']}.2.{$pathinfo['extension']}";
					log("qual é o caminho ".$caminhoImagem);
					file_put_contents('/tmp/abc', $caminhoImagem);
					$caminhoImagem = "<img src=\"{$caminhoImagem}\" width=\"900px\">";
				}else{
					$caminhoImagem="";
				} 

				// (TODO) passar para o arq. conf
				$caminhoImagem = str_replace('201.22.57.137:8282', 'eurosul', $caminhoImagem);
				$caminhoImagem = str_replace('201.22.57.137:8181', 'sigma', $caminhoImagem);
				
				if(!empty($T003['D024_Cnpj'])){
					$cpfCnpj = $T003['D024_Cnpj'];
				}else{
					$cpfCnpj = $T003['D024_Cpf'];
				}
				$tel="";
				if(!empty($D013['D013_Telefone_1'])){
					$tel = '('.$D013['D013_DDD_Telefone_1'].') '.$D013['D013_Telefone_1'];
				}else if(!empty($T003['D024_Telefone_1'])){
					$tel = '('.$T003['D024_DDD_Telefone_1'].') '.$T003['D024_Telefone_1'];
				}
				$T003['D024_Nome_Empresa']=gLimpaAcentos($T003['D024_Nome_Empresa']);
				$T003['D024_Nome_Empresa']=substr($T003['D024_Nome_Empresa'],0,60);
				$dataEmissao = gCorrigeData($T003['T003_Data_Emissao']);
				// $html .= <<<EOT
					// <table class="header">
						// <tr>
							// <td width="150" align="center">{$caminhoImagem}</td>
							// <td width="40"></td>
							// <td align="left" class="text" width="330">
								// <b>{$T003['D024_Id']} - {$T003['D024_Nome_Empresa']}</b><br>
								// CNPJ: $cpfCnpj<br>
								// <b>Att: {$D013['D013_Nome_Contato']} - {$tel}</b><br>
								// {$T003['D024_Endereco']} - {$T003['D024_Numero']} <br>
								// {$T003['D024_Bairro']} - {$T003['D024_Cep']} - {$T003['D020_Nome_Cidade']} - {$T003['D018_UF']}
							// </td>
							// <td align="center" width="150" class="text2">
								// <br>
								// ORÇAMENTO<br>Nº {$T003['T003_Id']}<br>{$dataEmissao}
							// </td>
						// </tr>
					// </table>
					// <br />
// EOT;
				$sqlC004 = "SELECT * FROM C004 left join D018 on D018_Id=C004_D018_Id left join D020 on D020_Id=C004_D020_Id WHERE C004_Id='{$g['empresaAtual']}'";
				$resultC004 = mysql_query($sqlC004);
				$linhaC004 = mysql_fetch_array($resultC004);
				
				$sqlC007 = "SELECT * FROM C007 WHERE C007_Id='{$T003['T003_C007_Id_Vendedor_Interno']}'";
				$resultC007 = mysql_query($sqlC007);
				$linhaC007 = mysql_fetch_array($resultC007);
				
				if(($T003['D024_Cnpj'] != "") || (!empty($T003['D024_Cnpj']))){
					$cpfCnpj = "<b>CNPJ:</b> ".$T003['D024_Cnpj'];
				}else{
					$cpfCnpj = "<b>CPF:</b> ".$T003['D024_Cpf'];
				}
				$T003_Data_Emissao = gCorrigeData($T003['T003_Data_Emissao']);
				$html .=<<<EOT
				<table width="100%">
					<tr>
						<td align="center">{$caminhoImagem}</td>
					</tr>
				</table>
				<table width="100%" align="center">
					<tr>
						<td align="center"><font style="font-size:28px;color:#4F4D4D">Orçamento - Proforma: $T003_Id</font></td>
					</tr>
				</table>
				<br>
				<table class="header" width="100%">
					<tr>
						<td width="33%" valign=top style="border-right: 1px solid #4f4d4d;">
							<table align="left" style="font-size:14px;" cellpadding="0" valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Fornecedor</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Nome_Empresa']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Logradouro']}, {$linhaC004['C004_Numero']} - {$linhaC004['C004_Bairro']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>CEP:</b> {$linhaC004['C004_Cep']} {$linhaC004['D020_Nome_Cidade']} - {$linhaC004['D018_UF']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>CNPJ:</b> {$linhaC004['C004_Cnpj']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Incr. Estadual:</b> {$linhaC004['C004_Inscricao_Estadual']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Site']}</td>
								</tr>
							</table>
						</td>
						<td width="40%" valign=top style="border-right: 1px solid #4f4d4d;">
							<table align="left" style="font-size:14px;"  cellpadding="0"  valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Cliente</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral">{$T003['D024_Id']} - {$T003['D024_Nome_Empresa']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$cpfCnpj}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Contato: </b>{$D013['D013_Nome_Contato']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>E-Mail: </b>{$D013['D013_E_Mail']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Tel. </b>{$tel}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Cep: </b>{$dadosEnd['cep']}</td>
								</tr>
                                <tr>
									<td class="tdGeral"><b>Rua: </b>{$dadosEnd['rua']}ㅤ<b>Num: </b>{$dadosEnd['numero']}</td>
								</tr>
                                <tr>
									<td class="tdGeral"><b>Bairro: </b>{$dadosEnd['bairro']}</td>
								</tr>
                                <tr>
									<td class="tdGeral"><b>UF: </b>{$dadosEnd['uf']}ㅤ<b>Cidade: </b>{$dadosEnd['cidade']}</td>
								</tr>
							</table>
						</td>
						<td width="27%" valign=top>
							<table align="left" style="font-size:14px;" cellpadding="0"  valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Vendedor</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Nº Orçamento:</b> $T003_Id</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Data:</b> {$T003_Data_Emissao}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Vendedor:</b> {$linhaC007['C007_Primeiro_Nome']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Dpto:</b> {$linhaC007['C007_Cargo']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Tel.</b> {$linhaC007['C007_Telefone']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>E-mail:</b> {$linhaC007['C007_Email']}</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
EOT;

				/**
				* PDF - TABELA PRODUTOS
				*/
				$html .= <<<EOT
				<div style="height:20px; width:100%;"></div>
					<table class="itens" cellspacing="2" cellpadding="2">
						<tr>
							<th align="center" width="3%">It</th>
							<th align="center" width="5%">Código</th>
							<th align="center" width="30%">Descrição</th>
							<th align="center" width="5%">Quant</th>
							<th align="center" width="3%">Und</th>
							<th align="center" width="7%">NCM</th>
							<th align="center" width="3%">CFOP</th>
EOT;
			//Verifica se existe algum produto com ST
			$sqlVerificaST = mysql_query("SELECT * FROM T004 WHERE T004_T003_Id = '{$T003_Id}' AND T004_Flag_ST='S' GROUP BY T004_Id");
			if(mysql_num_rows($sqlVerificaST) > 999999){
				$html .= <<<EOT
							<th align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font> IPI R\$</th>
							<th align="center" width="4%">ICM %</th>
							<th align="center" width="3%">IPI</th>
							<th align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font> IPI R\$</th>
							<th align="center" width="8%">Valor<br>ST R$</th>
							<th align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI R\$</th>
EOT;
			}else{
				$html .= <<<EOT
							<th align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font><br> IPI e ST R\$</th>
							<th align="center" width="4%">ICM %</th>
							<th align="center" width="3%">IPI %</th>
							<th align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font><br> IPI e ST R\$</th>
							<th align="center" width="8%">Valor<br>ST R$</th>
							<th align="center" width="8%">Valor<br>FCP ST R$</th>
							<th align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI e ST R\$</th>
EOT;
			}
				$html .= <<<EOT
							<th align="center" width="8%">Link</th>
						</tr>
EOT;
				$count = 1;
				while($T004 = mysql_fetch_array($T004_sql)){
					//$codigo = !empty($T004['T004_Codigo_Substituto']) ? $T004['T004_Codigo_Substituto'] : $T004['T004_Codigo_Produto'];
					$codigo = (!empty($T004['T004_Codigo_Substituto'])) ? "<b>".$T004['T004_Codigo_Substituto']."</b><br>". $T004['T004_Codigo_Produto'] : $T004['T004_Codigo_Produto'];
					$qtd = gCorrigeNumero($T004['T004_Quantidade']);
					$unitario = gCorrigeNumero($T004['T004_Valor_Preco_Unitario']);
					$valorST = gCorrigeNumero($T004['T004_Valor_ST']);
					$valorFCPST = gCorrigeNumero($T004['T004A_Valor_FCP_ST']);
					$total = $T004['T004_Valor_Total_Preco']+$T004['T004_Valor_IPI']+$T004['T004_Valor_ST'] + $T004['T004A_Valor_FCP_ST'];
                    $unitarioComIPIeST = gCorrigeNumero($total/round($T004['T004_Quantidade'],0));
                    $total = gCorrigeNumero($total);
					$ipi = gCorrigeNumero($T004['T004_Aliquota_IPI']);
					$pis = number_format($T004['T004_Aliquota_PIS'],2,',','.');
					$cofins = number_format($T004['T004_Aliquota_COFINS'],2,',','.');
					$AliqPis = $T004['T004_Aliquota_PIS'];
					$AliqCofins = $T004['T004_Aliquota_COFINS'];
					$icms = number_format($T004['T004_Aliquota_ICMS'],0);
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
					}
					$dataValidade = gCorrigeData($T004['D049_Data_Validade']);
                    if(!empty($dataValidade) && ($dataValidade!="null")){
                        $dataValidade = " Validade: ".$dataValidade;
                    }
					$dataValidadeLocacao = gCorrigeData($T004['T066A_Data_Validade']);
                    if(!empty($dataValidadeLocacao) && ($dataValidadeLocacao!="null")){						
                        $dataValidade = " Validade: ".$dataValidadeLocacao;
                    }

					$codigoIMPA = $T004['D001A_Codigo_IMPA'];
					if(!empty($codigoIMPA)){
						$codigoIMPA = (!empty($dataValidade)) ? " - IMPA: ".$T004['D001A_Codigo_IMPA'] : " IMPA: ".$T004['D001A_Codigo_IMPA'];						
					}

                    $pesoEstimado += ($T004['D001_Peso_Unitario_Kg']*$T004['T004_Quantidade']);
                    $cubagemEstimado += ($T004['D001_Cubagem_Unitaria']*$T004['T004_Quantidade']);;
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						log("qual a URL ".$T004["T144_Url"]);
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
						log($link);
					}

                    $observacaoItem = '';
                    if (!empty($T004['T004_Observacao'])) {
                        $observacaoItem = '<font style="background:#FCF9AE"><i>' . $T004['T004_Observacao'] . '</i></font><br>';
                    }
					//if (!empty($T004["T144_Url"]))
					//	$corpo.='<a href="'.$T004["T144_Url"].'" target=_blank style="text-decoration:none;font-weight:bold;color:#000">Ver Produto e documentos</a>';
					$T004['D001_Especificacoes'] = ($T004['D001_Especificacoes'] != '') ? "<br /><i>".$T004['D001_Especificacoes']."</i>" : '';
					log("Especificacoes: ".$T004['D001_Especificacoes']);

                    $desc = ($descIngles && !empty($T004['D001_Descricao_Ingles'])) ? "<br><i>{$T004['D001_Descricao_Ingles']}</i><br>" : '';

					$html .= <<<EOT
					<tr>
						<td align="center">
							{$T004['T004_Item']}
						</td>
						<td align="center">
							{$codigo}
						</td>
						<td align="left">
							<b>{$T004['T004_Descricao_Produto']}</b><br/> {$desc} <b> <i>{$observacaoItem}</i>{$T004['D001_Especificacoes']}<br>{$dataValidade} {$codigoIMPA}</b>
						</td>
						<td align="right">
							<b>{$qtd}</b>
						</td>
						<td>
							{$T004['D037_Unidade']}
						</td>
						<td align="center">
							{$T004['D005_Classificacao_Fiscal']}
						</td>
						<td align="center">
							{$T004['D006_Codigo_CFOP']}
						</td>
						<td align="right">
							<b>{$unitario}</b>
						</td>
						<td align="center">
							{$icms}
						</td>
						<td align="center">
							{$ipi}
						</td>
						<td align="right">
							<b>{$unitarioComIPIeST}</b>
						</td> 
						<td align="right">
							{$valorST}
						</td>
						<td align="right">
							{$valorFCPST}
						</td>
						<td align="right">
							<b>{$total}</b>
						</td>
						<td align="center">
							{$link}
						</td>
					</tr>
EOT;
					$count = $count + 1;
				}
				$html .= '</table>';
				
			
				/**
				* PDF - TABELA SERVIÇOS
				*/
				$sql = <<<EOT
				SELECT * FROM T145 
				WHERE T145_T003_Id='{$T003_Id}'
EOT;
				$result = mysql_query($sql);
				
				$valorTotalOrcamento = $T003['T003_Valor_Total'];
				if (mysql_num_rows($result) > 0) {
					$html .= <<<EOT
						<br>
						<span align="left">Seguem abaixo os serviços do pedido:</span>
						<br>
						<table class="itens" cellspacing="0" cellpadding="2">
							<tr class="titulo">
								<td align="center" width="10%">Código</td>
								<td align="center" width="60%">Descrição</td>
								<td align="right" width="10%">Qte</td>
								<td align="right" width="20%">Preço</td>
							</tr>
EOT;
					$valorTotal = 0;
					while($T145 = mysql_fetch_array($result)){
						$titulo = $T145['T145_Titulo'] . " - " . $T145['T145_Descricao'];
						$preco = gCorrigeNumero($T145['T145_Valor_Unitario']);
						$valorTotal += ($T145['T145_Quantidade'] * $T145['T145_Valor_Unitario']);
						$html .= <<<EOT
						<tr>
							<td align="center">
								{$T145['T145_Id']}
							</td>
							<td align="left">
								{$titulo}
							</td>
							<td align="right">
								{$T145['T145_Quantidade']}
							</td>
							<td align="right">
								{$preco}
							</td>
						</tr>
EOT;
							
					}
					$html .= '</table>';
		       		$html .= '<span align="left">Valor total dos Serviços: </b>' . gCorrigeNumero($valorTotal) . '</b></span>';
					$valorTotalOrcamento += $valorTotal;
				}
			
				switch ($T003['T003_Flag_Frete']) {
					case '':
						$T003_Frete = "";
						break;
					case '0':
						$T003_Frete = "Frete por conta do Fornecedor Remetente";
						break;
					case '1':
						$T003_Frete = "Frete por conta do Cliente Destinatário";
						break;
					case '2':
						$T003_Frete = "Terceiros";
						break;
					case '9':
						$T003_Frete = "Sem frete";
						break;
					default:
						$T003_Frete = "Não informado";
						break;
				}
				
				/**
				* PDF - TOTAIS
				*/
				$valorTotalProd = gCorrigeNumero($T003['T003_Valor_Total_Produtos']);
				$ipi = gCorrigeNumero($T003['T003_Valor_Total_IPI']);
				$valorTotal = gCorrigeNumero($T003['T003_Valor_Total']);
				$T003_Valor_Frete = gCorrigeNumero($T003['T003_Valor_Frete']);
				$T003['T003_Valor_Total_ICMS'] = gCorrigeNumero($T003['T003_Valor_Total_ICMS']);
				$T003['T003_Valor_ST'] = gCorrigeNumero($T003['T003_Valor_ST']);
				$T003['T003A_Valor_FCP_ST'] = gCorrigeNumero($T003['T003A_Valor_FCP_ST']);

				$Valor_PIS = gCorrigeNumero($T003['T003_Valor_Total']*($AliqPis/100));
				$Valor_COFINS = gCorrigeNumero($T003['T003_Valor_Total']*($AliqCofins/100));
				$T003['T003_Observacao']=str_replace(chr(10),'<br>',$T003['T003_Observacao']);
			 	if($pesoEstimado<=0){
                    $pesoEstimado = "";
                }else{
                    $pesoEstimado = gCorrigeNumero($pesoEstimado,3)." kg";
                }
               
                if($cubagemEstimado<=0){
                    $cubagemEstimado = "";
                }else{
                    $cubagemEstimado = gCorrigeNumero($cubagemEstimado,3)." m³";
                }

				$html .= <<<EOT
					<br><br />
					<table width='100%' cellspacing="0" cellpadding="0">
						<tr>
							<td align="left" style="font-size:10px" width="105">Prazo de Entrega: </td><td align="left" style="font-size:10px" width="230"><b>{$T003['T003_Prazo_Entrega']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total Produtos: </td><td  align="left" style="font-size:10px" width="100"><b>R$ {$valorTotalProd}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Frete: </td><td align="left" style="font-size:10px"><b>{$T003_Frete}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total Frete: </td><td align="left" style="font-size:10px"><b>R$ {$T003_Valor_Frete}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Forma pagamento: </td><td align="left" style="font-size:10px"><b> {$T003['formaPagamento']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total IPI: </td><td align="left" style="font-size:10px"><b>R$ {$ipi}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Validade proposta: </td><td align="left" style="font-size:10px"><b>{$T003['T003_Validade_Proposta']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total ST: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003_Valor_ST']}</b></td>
                        </tr>
                        <tr>
							<td align="left" style="font-size:10px" width="105">Peso estimado: </td><td align="left" style="font-size:10px"><b>{$pesoEstimado}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total FCP ST: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003A_Valor_FCP_ST']}</b></td>
						</tr>
						<tr>
                            <td align="left" style="font-size:10px" width="105">Cubagem estimada: </td><td align="left" style="font-size:10px"><b>{$cubagemEstimado}</b></td>
                            <td align="left" style="font-size:18px ;background: #adadad;border-left: 1px solid #4f4d4d;border-top: 1px solid #4f4d4d;border-bottom: 1px solid #4f4d4d" width="105"><b>Valor total: </td><td  align="left" style="font-size:18px;background: #adadad;border-right: 1px solid #4f4d4d;border-top: 1px solid #4f4d4d;border-bottom: 1px solid #4f4d4d">R$ {$valorTotal}</b></td>
						</tr>
						<tr>
							<td align="center" width="100%" colspan="4" style="color:black;font-size:20px"> </td>
						</tr>
						<tr>
							<td align="left" width="100%" colspan="4"> </td>
						</tr>
					</table>
EOT;

            $html .= <<<EOT
					<br>
                    <table style="border-collapse: collapse; border-spacing: 10px;">
                        <tr bgcolor=white style="font-size:10px;">
                            <td align=center style="background: #adadad;border: 1px solid #4f4d4d;">
                                <b>Parcela</b><br>
                            </td>
                            <td align=center style="background: #adadad;border: 1px solid #4f4d4d;">
                                <b>Vencimento</b><br style="background: #adadad;border: 1px solid #4f4d4d;">
                            </td>
                            <td align=center style="background: #adadad;border: 1px solid #4f4d4d;">
                                <b>Valor</b><br >
                            </td>
                            <td align=center style="background: #adadad;border: 1px solid #4f4d4d;">
                                <b>Portador</b><br>
                            </td>
                        </tr>
EOT; 

                $T089 = mysql_query("SELECT T089_Numero_Parcela,
                                            T089_Prazos_Dias,
                                            T089_Data_Vencimento,
                                            T089_Valor_Parcela,
                                            D027_Portador
                                       FROM T089
                                  LEFT JOIN D027 ON D027_Id = T089_D027_Id
                                      WHERE T089_T003_Id = '{$T003_Id}'");
                
                while($mT089 = mysql_fetch_array($T089)){

                    $valorParcela  = gCorrigeNumero($mT089['T089_Valor_Parcela']);
                    $dataParcela   = gCorrigeData($mT089['T089_Data_Vencimento']);
                    $portador      = $mT089['D027_Portador'];
                    $numeroParcela = $mT089['T089_Numero_Parcela'];

                    $html .= <<<EOT
                        <tr bgcolor=white style="font-size:10px">
                            <td align=center style="border: 1px solid #4f4d4d; padding: 5px;">
                                $numeroParcela
                            </td>
                            <td align=center style="border: 1px solid #4f4d4d; padding: 5px;">
                                $dataParcela
                            </td>
                            <td align=right style="border: 1px solid #4f4d4d; padding: 5px;">
                                $valorParcela
                            </td>
                            <td align=center style="border: 1px solid #4f4d4d; padding: 5px;">
                                $portador
                            </td>
                        <tr>
EOT;
			}


 			$html .= <<<EOT
					</table>
					<table width='100%' cellspacing="0" cellpadding="0">
					<td align="center" width="100%" colspan="4" style="color:black;font-size:20px"><br />NÃO ACEITAMOS DEVOLUÇÃO APÓS 10 DIAS DA NOTA FISCAL.<br></td>
						</tr>
						<tr>
							<td align="left" width="100%" colspan="4"> </td>
						</tr>
					</table>
EOT; 

			if(!empty($T003['T003_Observacao'])){

				$html .= <<<EOT
				<table>
					<tr>
						<td width="455" align="left" style="font-size:10px;background: #adadad;border: 1px solid #4f4d4d;"><span align="left">Observação: <br><b>{$T003['T003_Observacao']}</b></span></td>
						<td width="190" align="left"></td>
						<td>   
							<table  cellspacing="0" cellpadding="0">
								<tr>
									<td align="left" style="font-size:10px" width="146">Valor ICMS: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003_Valor_Total_ICMS']}</b></td>
								</tr>
								<tr>
									<td align="left" style="font-size:10px" width="146">Percentual PIS: </td><td  align="left" style="font-size:10px"><b>R$ {$Valor_PIS} - {$pis}%</b></td>
								</tr>
								<tr>
									<td align="left" style="font-size:10px" width="146">Percentual COFINS: </td><td  align="left" style="font-size:10px"><b>R$ {$Valor_COFINS} - {$cofins}%</b></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
EOT;

			}

			$D006 = mysql_query("SELECT D006_Observacao FROM T003 LEFT JOIN T004 ON T004_T003_Id = T003_Id LEFT JOIN D006 ON T004_D006_Id = D006_Id WHERE T003_Id = '{$T003_Id}' group by D006_Id");
			
			if(mysql_num_rows($D006) > 0 ){
				while ($mD006 = mysql_fetch_array($D006)) {

					if(!empty($mD006['D006_Observacao'])){

						$Observacao_Fiscal .= $mD006['D006_Observacao'] . ';';

						// Váriaveis dentro da Observação Fiscal
						foreach ($T003 as $key => $value) {
							$key = preg_replace('#^[^_]+_#', '', $key);
							$key = strtoupper($key);
							if (preg_match('#^([0-9])+\.([0-9])+$#', $value)) {
								$value = gCorrigeNumero($value);
							}
							$Observacao_Fiscal = str_replace('[' . strtoupper($key) . ']', $value, $Observacao_Fiscal);
						}
					}
				}
			}

            $enderecoEntrega = '';
            if($T003['T003_D148_Id_Entrega'] > 0){
                    $D148 = mysql_query("SELECT D148_Rua,
                                                D148_Numero,
                                                D148_Cep,
                                                D148_Bairro,
                                                D018_UF,
                                                D020_Nome_Cidade
                                           FROM D148
                                      LEFT JOIN D020 ON D020_Id = D148_D020_Id
                                      LEFT JOIN D018 ON D018_Id = D148_D018_Id
                                          WHERE D148_Id = '{$T003['T003_D148_Id_Entrega']}'");

                $mD148 = mysql_fetch_array($D148);

                $enderecoEntrega = $mD148['D148_Rua']. ' - '.$mD148['D148_Numero'] . '<br>BAIRRO: '. $mD148['D148_Bairro'] .'<br>CEP: '. $mD148['D148_Cep'] . '<br>'. $mD148['D020_Nome_Cidade']. '/'.$mD148['D018_UF'];
            }

			if(!empty($Observacao_Fiscal) || !empty($enderecoEntrega)){

			    $html .= <<<EOT
   					    <table>
						    <tr>
EOT;
                    if(!empty($Observacao_Fiscal)){
                        $html .= <<<EOT
                            <td width="455" align="left" style="font-size:10px;background: #adadad;border: 1px solid #4f4d4d;"><span align="left">Observação Fiscal: <br><b>{$Observacao_Fiscal}</b></span></td>
EOT;
                    }

                    if(!empty($enderecoEntrega)){
                        $html .= <<<EOT
                            <td width="455" align="left" style="font-size:10px;background: #adadad;border: 1px solid #4f4d4d;"><span align="left">Endereço de entrega: <br><b>{$enderecoEntrega}</b></span></td>
EOT;
                    }
                $html .= <<<EOT
                            </tr>
                        </table>
                    <br>
EOT;
			}

            if (!empty($T003["T003_Observacao"])) {
                //$html .= '<br><span align="left">Observação: <br><b>'.str_replace(chr(10),'<br>',$T003['T003_Observacao']).'</b></span>';
            }
            // Assinatura
            // retirada da assinatura 27/07/2022, pedido Lucas no projeto Eurosul
            /*if (!empty($g['c007']['C007_Email_Assinatura']) && trim($g['c007']['C007_Email_Assinatura']) != '<br>') {
                $assinatura = gCorrigeAssinatura($g['c007']['C007_Email_Assinatura']);
                $html .= "<br /><br /><span align=\"left\" style=\"font-size: 18px;\">{$assinatura}</span>";
            }*/		

            if(!empty($g['C004']['C004_Observacao_Rodape_Orcamento'])){
                $html .=  "<span style='width: 100%'>{$g['C004']['C004_Observacao_Rodape_Orcamento']}</span>";
            }
		
        return $html;
    }

    // ultima funcao que esta rodando da impressao antes das alterações de melhoria (prazo, endereco de entrada)
	public function xxxmontarEmailPdf($T003_Id, $imprimirHtml = false, $moeda = '1')
    {
        global $g, $confUsuario;

        // Ajuste do tamanho da fonte para casos que a geração do PDF está ficando com fonte muito pequena
        $fonteMinima = 13 + $g['C031']['vendasZoomFonteImprimirPDF'];
        $fonteMedia  = 18 + $g['C031']['vendasZoomFonteImprimirPDF'];
        $fonteGrande = 20 + $g['C031']['vendasZoomFonteImprimirPDF'];

        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();        
        
        if (!$imprimirHtml) {
            // Arquivo
            $dir = $g['pathDados'] . 'tmp/';
            is_dir($dir) OR mkdir($dir, 0777, true);
            $filename             = "orcamento-{$T003_Id}.pdf";
            $file                 = $dir . $filename;
            $fileFull             = $g['pathRaiz'] . '/' . $file;
            $retorno['Anexos']    = array(
                $file
            );
            $retorno['AnexosWeb'] = array(
                '/hardness3/' . $file
            );
            
            $T003 = <<<EOT
                SELECT * FROM T003 
                LEFT JOIN D013 ON D013_Id=T003_D013_Id
                LEFT JOIN D024 ON D024_Id=T003_D024_Id
                WHERE T003_Id='{$T003_Id}';
EOT;
            
            $T003 = mysql_query($T003);
            $T003 = mysql_fetch_array($T003);
            
            // Array de retorno para criação do e-mail
            $retorno['Para']    = (!empty($T003['D013_E_Mail'])) ? $T003['D013_E_Mail'] : $T003['D024_E_Mail'];
            $retorno['Assunto'] = "Orçamento Nº[{$T003_Id}]";
            
            $retorno['Corpo'] = "<img src='{$caminhoImagem}' style='margin-left:10px'><font size='3'><br /><br /><br /><br /></font><font style='font-size:16px'>Segue orçamento de venda número <b>$T003_Id</b> em anexo.</font><font size='3'><br /><br /></font>";
            
            $r_link = '/ven/ven001/form_func-ajax/ven001ImprimirOrcamento/?ajax=true&gerarHtml=1&T003_Id=' . $T003_Id . '&moeda=' . $moeda; // .'&alcadaUpdate='.$alcadaUpdate;
            $r_link = str_replace(' ', '+', $r_link);
            shell_exec("xvfb-run --server-num " . mt_rand(0, 98) . " -s \"-screen 0 640x480x16\" wkhtmltopdf --minimum-font-size {$fonteMinima} --quiet --margin-left 1 --margin-right 1 --margin-top 1 --margin-bottom 1 \"{$g['urlRaiz']}{$r_link}&noEmp=true&p={$g['empresaAtual']}&u={$g['usuarioAtual']}&db={$confUsuario['dbDatabase']}\" \"{$fileFull}\"");
            log("xvfb-run --server-num " . mt_rand(0, 98) . " -s \"-screen 0 640x480x16\" wkhtmltopdf --minimum-font-size 13 --quiet --margin-left 1 --margin-right 1 --margin-top 1 --margin-bottom 1 \"{$g['urlRaiz']}{$r_link}&noEmp=true&p={$g['empresaAtual']}&u={$g['usuarioAtual']}&db={$confUsuario['dbDatabase']}\" \"{$fileFull}\"");
            
            return $retorno;
        }
        
        error_reporting(E_ALL);
				/**
				* SELECT's
				*/
				$C004 = mysql_query("SELECT * FROM C004 WHERE C004_Id = '{$g['empresaAtual']}'");
				$resC004 = mysql_fetch_array($C004);
				$footer = $resC004['C004_Observacao_Rodape_Orcamento'];
				
				$T003_Id = mysql_real_escape_string($T003_Id);
				$sql = "SELECT *, T003_Prazos(T003_Id) as formaPagamento FROM T003 
						   LEFT JOIN D013 ON D013_Id=T003_D013_Id 
						   LEFT JOIN D024 ON D024_Id=T003_D024_Id 
						   LEFT JOIN D020 ON D020_Id=D024_D020_Id
						   LEFT JOIN D018 ON D018_Id=D024_D018_Id 
						   LEFT JOIN T003A ON T003_Id=T003A_T003_Id
						   WHERE T003_Id='{$T003_Id}'";
				log($sql);
				$T003 = mysql_query($sql);
				$T003 = mysql_fetch_array($T003);
		
				$sql = <<<EOT
					 SELECT * FROM T004 
					LEFT JOIN D009 ON D009_Id=T004_D009_Id 
					LEFT JOIN D049 ON D049_Id=D009_D049_Id
					LEFT JOIN D001 ON D001_Id=D049_D001_Id
					LEFT JOIN T144 ON T144_D001_Id=D001_Id
		            LEFT JOIN D037 ON D037_Id=T004_D037_Id
		            LEFT JOIN D005 ON D005_Id=T004_D005_Id
					LEFT JOIN T004A ON T004_Id=T004A_T004_Id
					LEFT JOIN T066 ON T066_Id=T004A_T066_Id
					LEFT JOIN T066A ON T066_Id=T066A_T066_Id
					LEFT JOIN D001A ON D001_Id=D001A_D001_Id
					LEFT JOIN D006 ON D006_Id=T004_D006_Id
					WHERE T004_T003_Id = '{$T003_Id}' 
					GROUP BY T004_Id
					ORDER BY T004_Item
EOT;
                // Lucas pediu para a ordenação ser pelo T004_Item apenas, abaixo order by antigo
       			//ORDER BY T004_Item_Pedido_Compra,T004_Id

				$T004_sql = mysql_query($sql);				
				
				$sql = "SELECT D024_E_Mail FROM D024 WHERE D024_Id='{$T003['T003_D024_Id']}'";
				$D024 = mysql_query($sql);
				$D024 = mysql_fetch_array($D024);	
				
				$sql = "SELECT * FROM D013 WHERE D013_Id='{$T003['T003_D013_Id']}'";
				$D013 = mysql_query($sql);
				$D013 = mysql_fetch_array($D013);
				
				// Array de retorno para criação do e-mail
				$array = array();
				$array['Para'] = (!empty($T003['D013_E_Mail'])) ? $T003['D013_E_Mail'] : $D024['D024_E_Mail'];
				$array['Assunto'] = "Orçamento Nº[{$T003_Id}]";
							
				$html = '';
				/**
				* PDF - STYLE
				*/
				$html .= <<<EOT
				<style>
					body {
						margin: 10mm 10mm 10mm 10mm;
						font-family: Roboto, Arial, sans-serif, Verdana;
					}
					.header .text {
						font-size: 15px;
					}
					.header .text2 {
						font-size: 25px;
						font-weight: bold;
					}
					.titulo_superior {
						font-size: 10px;
					}
					.tdGeral {
						font-size: 10px;
					}
					.itens {
						background: #4F4D4D;
					}
					.itens td {
						font-size: 10px;
						background: white;
					}
					.itens th {
						background: #adadad;
						font-size: 12px;
					}
					.textIncluso {
						font-size: 14px;
						color: red;
						background-color: #E1ECF1;
					}
					
				</style>
EOT;
				
				/**
				* PDF - HEADER
				*/
				
 				if(!empty($g['c004']['C004_Logo_Marca'])){
					$pathinfo = pathinfo($g['c004']['C004_Logo_Marca']);
					$caminhoImagem = "{$confUsuario['urlRaiz']}{$g['pathWeb']}{$g['pathDados']}imagens/logo/{$g['empresaAtual']}.2.{$pathinfo['extension']}";
					log("qual é o caminho ".$caminhoImagem);
					file_put_contents('/tmp/abc', $caminhoImagem);
					$caminhoImagem = "<img src=\"{$caminhoImagem}\" width=\"900px\">";
				}else{
					$caminhoImagem="";
				} 

				// (TODO) passar para o arq. conf
				$caminhoImagem = str_replace('201.22.57.137:8282', 'eurosul', $caminhoImagem);
				$caminhoImagem = str_replace('201.22.57.137:8181', 'sigma', $caminhoImagem);
				
				if(!empty($T003['D024_Cnpj'])){
					$cpfCnpj = $T003['D024_Cnpj'];
				}else{
					$cpfCnpj = $T003['D024_Cpf'];
				}
				$tel="";
				if(!empty($D013['D013_Telefone_1'])){
					$tel = '('.$D013['D013_DDD_Telefone_1'].') '.$D013['D013_Telefone_1'];
				}else if(!empty($T003['D024_Telefone_1'])){
					$tel = '('.$T003['D024_DDD_Telefone_1'].') '.$T003['D024_Telefone_1'];
				}
				$T003['D024_Nome_Empresa']=gLimpaAcentos($T003['D024_Nome_Empresa']);
				$T003['D024_Nome_Empresa']=substr($T003['D024_Nome_Empresa'],0,60);
				$dataEmissao = gCorrigeData($T003['T003_Data_Emissao']);
				// $html .= <<<EOT
					// <table class="header">
						// <tr>
							// <td width="150" align="center">{$caminhoImagem}</td>
							// <td width="40"></td>
							// <td align="left" class="text" width="330">
								// <b>{$T003['D024_Id']} - {$T003['D024_Nome_Empresa']}</b><br>
								// CNPJ: $cpfCnpj<br>
								// <b>Att: {$D013['D013_Nome_Contato']} - {$tel}</b><br>
								// {$T003['D024_Endereco']} - {$T003['D024_Numero']} <br>
								// {$T003['D024_Bairro']} - {$T003['D024_Cep']} - {$T003['D020_Nome_Cidade']} - {$T003['D018_UF']}
							// </td>
							// <td align="center" width="150" class="text2">
								// <br>
								// ORÇAMENTO<br>Nº {$T003['T003_Id']}<br>{$dataEmissao}
							// </td>
						// </tr>
					// </table>
					// <br />
// EOT;
				$sqlC004 = "SELECT * FROM C004 left join D018 on D018_Id=C004_D018_Id left join D020 on D020_Id=C004_D020_Id WHERE C004_Id='{$g['empresaAtual']}'";
				$resultC004 = mysql_query($sqlC004);
				$linhaC004 = mysql_fetch_array($resultC004);
				
				$sqlC007 = "SELECT * FROM C007 WHERE C007_Id='{$T003['T003_C007_Id_Vendedor_Interno']}'";
				$resultC007 = mysql_query($sqlC007);
				$linhaC007 = mysql_fetch_array($resultC007);
				
				if(($T003['D024_Cnpj'] != "") || (!empty($T003['D024_Cnpj']))){
					$cpfCnpj = "<b>CNPJ:</b> ".$T003['D024_Cnpj'];
				}else{
					$cpfCnpj = "<b>CPF:</b> ".$T003['D024_Cpf'];
				}
				$T003_Data_Emissao = gCorrigeData($T003['T003_Data_Emissao']);
				$html .=<<<EOT
				<table width="100%">
					<tr>
						<td align="center">{$caminhoImagem}</td>
					</tr>
				</table>
				<table width="100%" align="center">
					<tr>
						<td align="center"><font style="font-size:28px;color:#4F4D4D">Orçamento - Proforma: $T003_Id</font></td>
					</tr>
				</table>
				<br>
				<table class="header" width="100%">
					<tr>
						<td width="33%" valign=top style="border-right: 1px solid #4f4d4d;">
							<table align="left" style="font-size:14px;" cellpadding="0" valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Fornecedor</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Nome_Empresa']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Logradouro']}, {$linhaC004['C004_Numero']} - {$linhaC004['C004_Bairro']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>CEP:</b> {$linhaC004['C004_Cep']} {$linhaC004['D020_Nome_Cidade']} - {$linhaC004['D018_UF']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>CNPJ:</b> {$linhaC004['C004_Cnpj']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Incr. Estadual:</b> {$linhaC004['C004_Inscricao_Estadual']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$linhaC004['C004_Site']}</td>
								</tr>
							</table>
						</td>
						<td width="40%" valign=top style="border-right: 1px solid #4f4d4d;">
							<table align="left" style="font-size:14px;"  cellpadding="0"  valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Cliente</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral">{$T003['D024_Id']} - {$T003['D024_Nome_Empresa']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$cpfCnpj}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Contato: </b>{$D013['D013_Nome_Contato']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>E-Mail: </b>{$D013['D013_E_Mail']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Tel. </b>{$tel}</td>
								</tr>
								<tr>
									<td class="tdGeral"> </td>
								</tr>
							</table>
						</td>
						<td width="27%" valign=top>
							<table align="left" style="font-size:14px;" cellpadding="0"  valign="top">
								<tr>
									<td class="titulo_superior"><b><u>Vendedor</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Nº Orçamento:</b> $T003_Id</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Data:</b> {$T003_Data_Emissao}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Vendedor:</b> {$linhaC007['C007_Primeiro_Nome']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Dpto:</b> {$linhaC007['C007_Cargo']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Tel.</b> {$linhaC007['C007_Telefone']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>E-mail:</b> {$linhaC007['C007_Email']}</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
EOT;

				/**
				* PDF - TABELA PRODUTOS
				*/
				$html .= <<<EOT
				<div style="height:20px; width:100%;"></div>
					<table class="itens" cellspacing="2" cellpadding="2">
						<tr>
							<th align="center" width="3%">It</th>
							<th align="center" width="5%">Código</th>
							<th align="center" width="30%">Descrição</th>
							<th align="center" width="5%">Quant</th>
							<th align="center" width="3%">Und</th>
							<th align="center" width="7%">NCM</th>
							<th align="center" width="3%">CFOP</th>
EOT;
			//Verifica se existe algum produto com ST
			$sqlVerificaST = mysql_query("SELECT * FROM T004 WHERE T004_T003_Id = '{$T003_Id}' AND T004_Flag_ST='S' GROUP BY T004_Id");
			if(mysql_num_rows($sqlVerificaST) > 999999){
				$html .= <<<EOT
							<th align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font> IPI R\$</th>
							<th align="center" width="4%">ICM %</th>
							<th align="center" width="3%">IPI</th>
							<th align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font> IPI R\$</th>
							<th align="center" width="8%">Valor<br>ST R$</th>
							<th align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI R\$</th>
EOT;
			}else{
				$html .= <<<EOT
							<th align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font><br> IPI e ST R\$</th>
							<th align="center" width="4%">ICM %</th>
							<th align="center" width="3%">IPI %</th>
							<th align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font><br> IPI e ST R\$</th>
							<th align="center" width="8%">Valor<br>ST R$</th>
							<th align="center" width="8%">Valor<br>FCP ST R$</th>
							<th align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI e ST R\$</th>
EOT;
			}
				$html .= <<<EOT
							<th align="center" width="8%">Link</th>
						</tr>
EOT;
				$count = 1;
				while($T004 = mysql_fetch_array($T004_sql)){
					//$codigo = !empty($T004['T004_Codigo_Substituto']) ? $T004['T004_Codigo_Substituto'] : $T004['T004_Codigo_Produto'];
					$codigo = (!empty($T004['T004_Codigo_Substituto'])) ? "<b>".$T004['T004_Codigo_Substituto']."</b><br>". $T004['T004_Codigo_Produto'] : $T004['T004_Codigo_Produto'];
					$qtd = gCorrigeNumero($T004['T004_Quantidade']);
					$unitario = gCorrigeNumero($T004['T004_Valor_Preco_Unitario']);
					$valorST = gCorrigeNumero($T004['T004_Valor_ST']);
					$valorFCPST = gCorrigeNumero($T004['T004A_Valor_FCP_ST']);
					$total = $T004['T004_Valor_Total_Preco']+$T004['T004_Valor_IPI']+$T004['T004_Valor_ST'] + $T004['T004A_Valor_FCP_ST'];
                    $unitarioComIPIeST = gCorrigeNumero($total/round($T004['T004_Quantidade'],0));
                    $total = gCorrigeNumero($total);
					$ipi = gCorrigeNumero($T004['T004_Aliquota_IPI']);
					$pis = number_format($T004['T004_Aliquota_PIS'],2,',','.');
					$cofins = number_format($T004['T004_Aliquota_COFINS'],2,',','.');
					$AliqPis = $T004['T004_Aliquota_PIS'];
					$AliqCofins = $T004['T004_Aliquota_COFINS'];
					$icms = number_format($T004['T004_Aliquota_ICMS'],0);
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
					}
					$dataValidade = gCorrigeData($T004['D049_Data_Validade']);
                    if(!empty($dataValidade) && ($dataValidade!="null")){
                        $dataValidade = " Validade: ".$dataValidade;
                    }
					$dataValidadeLocacao = gCorrigeData($T004['T066A_Data_Validade']);
                    if(!empty($dataValidadeLocacao) && ($dataValidadeLocacao!="null")){						
                        $dataValidade = " Validade: ".$dataValidadeLocacao;
                    }

					$codigoIMPA = $T004['D001A_Codigo_IMPA'];
					if(!empty($codigoIMPA)){
						$codigoIMPA = (!empty($dataValidade)) ? " - IMPA: ".$T004['D001A_Codigo_IMPA'] : " IMPA: ".$T004['D001A_Codigo_IMPA'];						
					}

                    $pesoEstimado += ($T004['D001_Peso_Unitario_Kg']*$T004['T004_Quantidade']);
                    $cubagemEstimado += ($T004['D001_Cubagem_Unitaria']*$T004['T004_Quantidade']);;
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						log("qual a URL ".$T004["T144_Url"]);
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
						log($link);
					}
					//if (!empty($T004["T144_Url"]))
					//	$corpo.='<a href="'.$T004["T144_Url"].'" target=_blank style="text-decoration:none;font-weight:bold;color:#000">Ver Produto e documentos</a>';
					$T004['D001_Especificacoes'] = ($T004['D001_Especificacoes'] != '') ? "<br /><i>".$T004['D001_Especificacoes']."</i>" : '';
					log("Especificacoes: ".$T004['D001_Especificacoes']);
					$html .= <<<EOT
					<tr>
						<td align="center">
							{$T004['T004_Item']}
						</td>
						<td align="center">
							{$codigo}
						</td>
						<td align="left">
							<b>{$T004['T004_Descricao_Produto']}<br /> <i>{$T004['T004_Observacao']}</i>{$T004['D001_Especificacoes']}<br>{$dataValidade} {$codigoIMPA}</b>
						</td>
						<td align="right">
							<b>{$qtd}</b>
						</td>
						<td>
							{$T004['D037_Unidade']}
						</td>
						<td align="center">
							{$T004['D005_Classificacao_Fiscal']}
						</td>
						<td align="center">
							{$T004['D006_Codigo_CFOP']}
						</td>
						<td align="right">
							<b>{$unitario}</b>
						</td>
						<td align="center">
							{$icms}
						</td>
						<td align="center">
							{$ipi}
						</td>
						<td align="right">
							<b>{$unitarioComIPIeST}</b>
						</td> 
						<td align="right">
							{$valorST}
						</td>
						<td align="right">
							{$valorFCPST}
						</td>
						<td align="right">
							<b>{$total}</b>
						</td>
						<td align="center">
							{$link}
						</td>
					</tr>
EOT;
					$count = $count + 1;
				}
				$html .= '</table>';
				
			
				/**
				* PDF - TABELA SERVIÇOS
				*/
				$sql = <<<EOT
				SELECT * FROM T145 
				WHERE T145_T003_Id='{$T003_Id}'
EOT;
				$result = mysql_query($sql);
				
				$valorTotalOrcamento = $T003['T003_Valor_Total'];
				if (mysql_num_rows($result) > 0) {
					$html .= <<<EOT
						<br>
						<span align="left">Seguem abaixo os serviços do pedido:</span>
						<br>
						<table class="itens" cellspacing="0" cellpadding="2">
							<tr class="titulo">
								<td align="center" width="10%">Código</td>
								<td align="center" width="60%">Descrição</td>
								<td align="right" width="10%">Qte</td>
								<td align="right" width="20%">Preço</td>
							</tr>
EOT;
					$valorTotal = 0;
					while($T145 = mysql_fetch_array($result)){
						$titulo = $T145['T145_Titulo'] . " - " . $T145['T145_Descricao'];
						$preco = gCorrigeNumero($T145['T145_Valor_Unitario']);
						$valorTotal += ($T145['T145_Quantidade'] * $T145['T145_Valor_Unitario']);
						$html .= <<<EOT
						<tr>
							<td align="center">
								{$T145['T145_Id']}
							</td>
							<td align="left">
								{$titulo}
							</td>
							<td align="right">
								{$T145['T145_Quantidade']}
							</td>
							<td align="right">
								{$preco}
							</td>
						</tr>
EOT;
							
					}
					$html .= '</table>';
		       		$html .= '<span align="left">Valor total dos Serviços: </b>' . gCorrigeNumero($valorTotal) . '</b></span>';
					$valorTotalOrcamento += $valorTotal;
				}
			
				switch ($T003['T003_Flag_Frete']) {
					case '':
						$T003_Frete = "";
						break;
					case '0':
						$T003_Frete = "Por conta do emitente";
						break;
					case '1':
						$T003_Frete = "Por conta do destinatário";
						break;
					case '2':
						$T003_Frete = "Terceiros";
						break;
					case '9':
						$T003_Frete = "Sem frete";
						break;
					default:
						$T003_Frete = "Não informado";
						break;
				}
				
				/**
				* PDF - TOTAIS
				*/
				$valorTotalProd = gCorrigeNumero($T003['T003_Valor_Total_Produtos']);
				$ipi = gCorrigeNumero($T003['T003_Valor_Total_IPI']);
				$valorTotal = gCorrigeNumero($T003['T003_Valor_Total']);
				$T003_Valor_Frete = gCorrigeNumero($T003['T003_Valor_Frete']);
				$T003['T003_Valor_Total_ICMS'] = gCorrigeNumero($T003['T003_Valor_Total_ICMS']);
				$T003['T003_Valor_ST'] = gCorrigeNumero($T003['T003_Valor_ST']);
				$T003['T003A_Valor_FCP_ST'] = gCorrigeNumero($T003['T003A_Valor_FCP_ST']);

				$Valor_PIS = gCorrigeNumero($T003['T003_Valor_Total']*($AliqPis/100));
				$Valor_COFINS = gCorrigeNumero($T003['T003_Valor_Total']*($AliqCofins/100));
				$T003['T003_Observacao']=str_replace(chr(10),'<br>',$T003['T003_Observacao']);
			 	if($pesoEstimado<=0){
                    $pesoEstimado = "";
                }else{
                    $pesoEstimado = gCorrigeNumero($pesoEstimado,3)." kg";
                }
               
                if($cubagemEstimado<=0){
                    $cubagemEstimado = "";
                }else{
                    $cubagemEstimado = gCorrigeNumero($cubagemEstimado,3)." m³";
                }
				$html .= <<<EOT
					<br><br />
					<table width='100%' cellspacing="0" cellpadding="0">
						<tr>
							<td align="left" style="font-size:10px" width="105">Prazo de Entrega: </td><td align="left" style="font-size:10px" width="230"><b>{$T003['T003_Prazo_Entrega']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total Produtos: </td><td  align="left" style="font-size:10px" width="100"><b>R$ {$valorTotalProd}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Frete: </td><td align="left" style="font-size:10px"><b>{$T003_Frete}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total Frete: </td><td align="left" style="font-size:10px"><b>R$ {$T003_Valor_Frete}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Forma pagamento: </td><td align="left" style="font-size:10px"><b> {$T003['formaPagamento']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total IPI: </td><td align="left" style="font-size:10px"><b>R$ {$ipi}</b></td>
						</tr>
						<tr>
							<td align="left" style="font-size:10px" width="105">Validade proposta: </td><td align="left" style="font-size:10px"><b>{$T003['T003_Validade_Proposta']}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total ST: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003_Valor_ST']}</b></td>
                        </tr>
                        <tr>
							<td align="left" style="font-size:10px" width="105">Peso estimado: </td><td align="left" style="font-size:10px"><b>{$pesoEstimado}</b></td>
							<td align="left" style="font-size:10px" width="105">Valor total FCP ST: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003A_Valor_FCP_ST']}</b></td>
						</tr>
						<tr>
                            <td align="left" style="font-size:10px" width="105">Cubagem estimada: </td><td align="left" style="font-size:10px"><b>{$cubagemEstimado}</b></td>
                            <td align="left" style="font-size:18px ;background: #adadad;border-left: 1px solid #4f4d4d;border-top: 1px solid #4f4d4d;border-bottom: 1px solid #4f4d4d" width="105"><b>Valor total: </td><td  align="left" style="font-size:18px;background: #adadad;border-right: 1px solid #4f4d4d;border-top: 1px solid #4f4d4d;border-bottom: 1px solid #4f4d4d">R$ {$valorTotal}</b></td>
						</tr>
						<tr>
							<td align="center" width="100%" colspan="4" style="color:black;font-size:20px"><br />NÃO ACEITAMOS DEVOLUÇÃO APÓS 10 DIAS DA NOTA FISCAL.<br></td>
						</tr>
						<tr>
							<td align="left" width="100%" colspan="4"> </td>
						</tr>
					</table>
EOT;
			if(!empty($T003['T003_Observacao'])){

				$html .= <<<EOT
				<table>
					<tr>
						<td width="455" align="left" style="font-size:10px;background: #adadad;border: 1px solid #4f4d4d;"><span align="left">Observação: <br><b>{$T003['T003_Observacao']}</b></span></td>
						<td width="190" align="left"></td>
						<td>   
							<table  cellspacing="0" cellpadding="0">
								<tr>
									<td align="left" style="font-size:10px" width="146">Valor ICMS: </td><td  align="left" style="font-size:10px"><b>R$ {$T003['T003_Valor_Total_ICMS']}</b></td>
								</tr>
								<tr>
									<td align="left" style="font-size:10px" width="146">Percentual PIS: </td><td  align="left" style="font-size:10px"><b>R$ {$Valor_PIS} - {$pis}%</b></td>
								</tr>
								<tr>
									<td align="left" style="font-size:10px" width="146">Percentual COFINS: </td><td  align="left" style="font-size:10px"><b>R$ {$Valor_COFINS} - {$cofins}%</b></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
EOT;

			}

			$D006 = mysql_query("SELECT D006_Observacao FROM T003 LEFT JOIN T004 ON T004_T003_Id = T003_Id LEFT JOIN D006 ON T004_D006_Id = D006_Id WHERE T003_Id = '{$T003_Id}' group by D006_Id");
			
			if(mysql_num_rows($D006) > 0 ){
				while ($mD006 = mysql_fetch_array($D006)) {

					if(!empty($mD006['D006_Observacao'])){

						$Observacao_Fiscal .= $mD006['D006_Observacao'] . ';';

						// Váriaveis dentro da Observação Fiscal
						foreach ($T003 as $key => $value) {
							$key = preg_replace('#^[^_]+_#', '', $key);
							$key = strtoupper($key);
							if (preg_match('#^([0-9])+\.([0-9])+$#', $value)) {
								$value = gCorrigeNumero($value);
							}
							$Observacao_Fiscal = str_replace('[' . strtoupper($key) . ']', $value, $Observacao_Fiscal);
						}
					}
				}
			}

			if(!empty($Observacao_Fiscal)){
			$html .= <<<EOT
   					<table>
						<tr>
					  		<td width="455" align="left" style="font-size:10px;background: #adadad;border: 1px solid #4f4d4d;"><span align="left">Observação Fiscal: <br><b>{$Observacao_Fiscal}</b></span></td>
					 	</tr>
					</table>
				<br>
EOT;
			}


				if (!empty($T003["T003_Observacao"])) {
					//$html .= '<br><span align="left">Observação: <br><b>'.str_replace(chr(10),'<br>',$T003['T003_Observacao']).'</b></span>';
				}
				// Assinatura
				// retirada da assinatura 27/07/2022, pedido Lucas no projeto Eurosul
				/*if (!empty($g['c007']['C007_Email_Assinatura']) && trim($g['c007']['C007_Email_Assinatura']) != '<br>') {
					$assinatura = gCorrigeAssinatura($g['c007']['C007_Email_Assinatura']);
					$html .= "<br /><br /><span align=\"left\" style=\"font-size: 18px;\">{$assinatura}</span>";
				}*/		
		
        return $html;
    }
	
	public function retornaAliquotas($D009_Id, $T003_Id = false, $T005_Id = false, $T007_Id = false, $T004_Id = false, $T006_Id = false, $T008_Id = false, $CFOP = false, $D024_Id_Enviado = false){
		
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        global $g;
        
        $mProduto = array();
        
        if ($T003_Id > 0) {
            $cClausula = " SELECT * ";
            $cClausula .= " FROM T003 ";
            $cClausula .= " LEFT JOIN D024 ON D024_Id=T003_D024_Id ";
            $cClausula .= " LEFT JOIN D018 ON D018_Id=D024_D018_Id ";
            $cClausula .= " LEFT JOIN D053 ON D053_D018_Id=D024_D018_Id AND D053_C004_Id=T003_C004_Id ";
            $cClausula .= " LEFT JOIN C004 ON C004_Id=T003_C004_Id ";
            $cClausula .= " LEFT JOIN D006 ON D006_Id=T003_D006_Id ";
            $cClausula .= " WHERE T003_Id='{$T003_Id}' ";
            
            $Cab   = mysql_query($cClausula);
            $cErro = mysql_error();
            
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            $mCab = mysql_fetch_array($Cab);
            
            $D024_Id                 = $mCab['T003_D024_Id'];
            $C004_Id                 = $mCab['T003_C004_Id'];
            $D018_Id                 = $mCab['D024_D018_Id'];
            $Flag_Acp                = $mCab['T003_Flag_Acp'];
            $Flag_Revenda_Consumidor = $mCab['D024_Flag_Revenda_Consumidor'];
            $Flag_Contribuinte       = $mCab['T003_Flag_Contribuinte'];
            $Percentual_Comissao     = $mCab['T003_Percentual_Comissao'];
            $Aliquota_ICMS_Credito   = "";
            //$D024_Flag_ICMS_Substituicao_Tributaria_Substituto =  $mCab['D024_Flag_ICMS_Substituicao_Tributaria_Substituto'];
            
            $cClausula = " SELECT * FROM T004 WHERE T004_Id='{$T004_Id}'";
            $sqlT004   = mysql_query($cClausula);
            $cErro     = mysql_error();
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            
            $T004                = mysql_fetch_array($sqlT004);
            $D006_Id             = $T004['T004_D006_Id'];
            $Flag_Origem_Produto = $T004['T004_Flag_Origem_Produto'];
            
        } else if ($T005_Id > 0) {
            $cClausula = " SELECT * ";
            $cClausula .= " FROM T005 ";
            $cClausula .= " LEFT JOIN D024 ON D024_Id=T005_D024_Id ";
            $cClausula .= " LEFT JOIN D018 ON D018_Id=D024_D018_Id ";
            $cClausula .= " LEFT JOIN D053 ON D053_D018_Id=D024_D018_Id AND D053_C004_Id=T005_C004_Id ";
            $cClausula .= " LEFT JOIN C004 ON C004_Id=T005_C004_Id ";
            $cClausula .= " LEFT JOIN D006 ON D006_Id=T005_D006_Id ";
            $cClausula .= " WHERE T005_Id='$T005_Id' ";
            
            $Cab   = mysql_query($cClausula);
            $cErro = mysql_error();
            
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            $mCab = mysql_fetch_array($Cab);
            
            $D024_Id                 = $mCab['T005_D024_Id'];
            $C004_Id                 = $mCab['T005_C004_Id'];
            $D018_Id                 = $mCab['D024_D018_Id'];
            $Flag_Acp                = $mCab['T005_Flag_ACP'];
            $Flag_Revenda_Consumidor = $mCab['D024_Flag_Revenda_Consumidor'];
            $Flag_Contribuinte       = $mCab['D024_Flag_Contribuinte'];
            $Percentual_Comissao     = $mCab['T005_Percentual_Comissao'];
            $Aliquota_ICMS_Credito   = "";
            //$D024_Flag_ICMS_Substituicao_Tributaria_Substituto =  $mCab['D024_Flag_ICMS_Substituicao_Tributaria_Substituto'];
            
            $sqlT006 = mysql_query("SELECT * FROM T006 WHERE T006_Id = '{$T006_Id}'");
            $T006    = mysql_fetch_array($sqlT006);
            $cErro   = mysql_error();
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }

            $Flag_Origem_Produto = $T006['T006_Flag_Origem_Produto'];
            $D006_Id             = $T006['T006_D006_Id'];
            
        } else if ($T007_Id > 0) {
            
            $cClausula = " SELECT * ";
            $cClausula .= " FROM T007 ";
            $cClausula .= " LEFT JOIN D024 ON D024_Id=T007_D024_Id ";
            $cClausula .= " LEFT JOIN D018 ON D018_Id=D024_D018_Id ";
            $cClausula .= " LEFT JOIN D053 ON D053_D018_Id=D024_D018_Id AND D053_C004_Id=T007_C004_Id ";
            $cClausula .= " LEFT JOIN C004 ON C004_Id=T007_C004_Id ";
            $cClausula .= " LEFT JOIN T134 ON T134_Id=T007_T134_Id ";
            $cClausula .= " LEFT JOIN D006 ON D006_Id=T007_D006_Id ";
            $cClausula .= " WHERE T007_Id='$T007_Id' ";
            
            $Cab   = mysql_query($cClausula);
            $cErro = mysql_error();
            
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            $mCab = mysql_fetch_array($Cab);
            
            $D024_Id                 = $mCab['T007_D024_Id'];
            $C004_Id                 = $mCab['T007_C004_Id'];
            $D018_Id                 = $mCab['D024_D018_Id'];
            $Flag_Acp                = $mCab['T007_Flag_ACP'];
            $Flag_Revenda_Consumidor = $mCab['D024_Flag_Revenda_Consumidor'];
            $Flag_Contribuinte       = $mCab['D024_Flag_Contribuinte'];
            $Percentual_Comissao     = $mCab['T007_Percentual_Comissao_Representante'];
            $Aliquota_ICMS_Credito   = $mCab['T134_ICMS'];
            //$D024_Flag_ICMS_Substituicao_Tributaria_Substituto =  $mCab['D024_Flag_ICMS_Substituicao_Tributaria_Substituto'];
            
			if($mCab['D006_Codigo_Beneficio_Fiscal'] == 'S'){
                $Codigo_Beneficio_Fiscal  = $mCab['D024_Codigo_Beneficio_Fiscal'];
            }
            
            if(empty($Codigo_Beneficio_Fiscal) && !empty($mCab['D006_Valor_Codigo_Beneficio_Fiscal'])){
                $Codigo_Beneficio_Fiscal  = $mCab['D006_Valor_Codigo_Beneficio_Fiscal'];
            }

            log("Benef Fiscal = ".$Codigo_Beneficio_Fiscal);
			
            $sqlT008 = mysql_query("SELECT *,substring(T008_Flag_Situacao_Tributaria,1,1) as T008_Flag_Origem_Produto FROM T008 WHERE T008_Id = '{$T008_Id}'");
            $cErro   = mysql_error();
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            $T008 = mysql_fetch_array($sqlT008);
            
            $Flag_Origem_Produto = $T008['T008_Flag_Origem_Produto'];
            $D006_Id = $T008['T008_D006_Id'];
            
        } else {
            // Nenhum dado recebido
        }
        if($CFOP > 0) {
            $D006_Id = $CFOP;
        }
       
        if(empty($D024_Id) && !empty($D024_Id_Enviado)){
            $D024_Id = $D024_Id_Enviado;
            $sqlD009 = mysql_query("SELECT * FROM D009 WHERE D009_Id = '{$D009_Id}'");
            $mD009 = mysql_fetch_array($sqlD009);
            $C004_Id = $mD009['D009_C004_Id'];

            $cClausula = " SELECT * FROM D024 ";
            $cClausula .= " LEFT JOIN D018 ON D018_Id=D024_D018_Id ";
            $cClausula .= " LEFT JOIN D053 ON D053_D018_Id=D024_D018_Id AND D053_C004_Id=$C004_Id ";
            $cClausula .= " LEFT JOIN C004 ON C004_Id=$C004_Id ";
            $cClausula .= " WHERE D024_Id='$D024_Id' ";
            $Cab   = mysql_query($cClausula);
            $cErro = mysql_error();
            
            if (!empty($cErro)) {
                return "{$cErro}<br />{$cClausula}";
            }
            $mCab = mysql_fetch_array($Cab);

            $D018_Id                 = $mCab['D024_D018_Id'];
            $Flag_Revenda_Consumidor = $mCab['D024_Flag_Revenda_Consumidor'];
            $Flag_Contribuinte       = $mCab['D024_Flag_Contribuinte'];
            $Flag_Acp                = 6;
        }


        $cClausula = " SELECT * ";
        $cClausula .= " FROM D006 ";
        $cClausula .= " LEFT JOIN D059 ON D059_Id=D006_D059_Id ";
        $cClausula .= " LEFT JOIN D088 ON D088_Id=D006_D088_Id ";
        $cClausula .= " WHERE D006_Id='{$D006_Id}'";
        $D006 = mysql_query($cClausula);
        if (!empty($cErro)) {
            return "$cErro<br />$cClausula";
        }
        $mD006 = mysql_fetch_array($D006);
        
        $cClausula = " SELECT ";
        $cClausula .= " *, ";
        $cClausula .= " D009_Id, ";
        $cClausula .= " D001_Id, ";
        $cClausula .= " D049_Id, ";
        $cClausula .= " D001_Codigo_Produto, ";
        $cClausula .= " D001_Descricao_Produto, ";
        $cClausula .= " D001_D037_Id, ";
        $cClausula .= " D009_Valor_Custo_Unitario, ";
        $cClausula .= " D009_Flag_Preco_Tabelado, ";
        $cClausula .= " D041_Valor_Preco_Consumidor, ";
        $cClausula .= " D041_Valor_Base, ";
        $cClausula .= " D041_Valor_Preco_Revenda, ";
        $cClausula .= " D024_Horario_Entrega, ";
        $cClausula .= " D001_Peso_Unitario_Kg, ";
        $cClausula .= " D001_Peso_Unitario_Kg, ";
        $cClausula .= " D001_Peso_Total_Kg, ";
        $cClausula .= " D001_Qte_Embalagem, ";
        $cClausula .= " D037_Unidade, ";
        $cClausula .= " D037_Id, ";
        $cClausula .= " D001_Flag_Tipo, ";
        $cClausula .= " D049_Origem_Mercadoria, ";
        $cClausula .= " IFNULL(D009_Origem_Mercadoria, '') as D009_Origem_Mercadoria, ";
        $cClausula .= " D009_IPI_3('$C004_Id',D009_Id,'$D024_Id','$D006_Id','1','$Flag_Acp') as Aliquota_IPI, ";
        $cClausula .= " D009_IPI_3('$C004_Id',D009_Id,'$D024_Id','$D006_Id','2','$Flag_Acp') as Percentual_Reducao_IPI, ";
        $cClausula .= " D009_IPV_ICMS('$C004_Id','$D024_Id',D009_Id,'$D006_Id','$D018_Id','$Flag_Revenda_Consumidor','$Percentual_Comissao','$Flag_Acp',2) as Aliquota_ICMS, ";
        $cClausula .= " D009_IPV_ICMS('$C004_Id','$D024_Id',D009_Id,'$D006_Id','$D018_Id','$Flag_Revenda_Consumidor','$Percentual_Comissao','$Flag_Acp',3) as Percentual_Reducao_ICMS, ";
        $cClausula .= " D005_Id, ";
        /* [REFORMA TRIBUTÁRIA] - Gabriel Cegantini 24/11/2025
        ================================================================================ */
        $cClausula .= " D005_Situacao_Tributaria_CBS_IBS, ";
        $cClausula .= " D005_Classificacao_Tributaria_CBS_IBS, ";
        $cClausula .= " D005_Situacao_Tributaria_CBS_IBS_Regular, ";
        $cClausula .= " D005_Classificacao_Tributaria_CBS_IBS_Regular, ";
        /* ================================================================================ */
        $cClausula .= " D049_Flag_Substituicao_Tributaria, ";
        $cClausula .= " D049_Flag_Nacional_Importado, ";
        $cClausula .= " D005_Flag_ST, ";
        $cClausula .= " D005_Classificacao_Fiscal, ";
        $cClausula .= " D088_Codigo_ST, ";
        $cClausula .= " D005_Aliquota_PIS, ";
        $cClausula .= " D005_Aliquota_COFINS, ";
        $cClausula .= " D149_Valor_VA, ";
        $cClausula .= " D009_Valor_Custo_ST_Unitario, ";
        $cClausula .= " D009_Valor_Custo_ST_Medio_Unitario ";
        $cClausula .= " FROM D001 as D1";
        $cClausula .= " LEFT JOIN D049 ON D001_Id=D049_D001_Id ";
        $cClausula .= " LEFT JOIN D009 ON D049_Id=D009_D049_Id ";
        $cClausula .= " LEFT JOIN D041 ON D041_D049_Id=D049_Id ";
        $cClausula .= " LEFT JOIN D037 ON D037_Id=D001_D037_Id ";
        $cClausula .= " LEFT JOIN D024 ON D024_Id={$D024_Id} ";
        $cClausula .= " LEFT JOIN D018 ON D018_Id=D024_D018_Id ";
        $cClausula .= " LEFT JOIN D005 ON D005_Id=D001_D005_Id OR D005_Id = (SELECT T025_D005_Id FROM D001 LEFT JOIN T025 ON T025_D015_Id=D001_D015_Id AND T025_D002_Id=D001_D002_Id WHERE D001_Id=D1.D001_Id LIMIT 1)";
        $cClausula .= " LEFT JOIN D149 ON D005_Id=D149_D005_Id AND D149_C004_Id={$C004_Id} ";
        $cClausula .= " LEFT JOIN D088 ON D088_Id=D149_D088_Id ";
        $cClausula .= " WHERE D009_Id='$D009_Id' GROUP BY D009_Id";
        $Produto  = mysql_query($cClausula);
        $mProduto = mysql_fetch_array($Produto);
        require_once("bibliotecas/classes/CAD002.php");
        $CAD002 = new CAD002();
        log($cClausula);
        
        if($mProduto['D009_Origem_Mercadoria'] != ''){
            $mProduto['D049_Origem_Mercadoria'] = $mProduto['D009_Origem_Mercadoria'];
        }

        if (!empty($Flag_Origem_Produto)) {
            $mProduto['D049_Origem_Mercadoria'] = $Flag_Origem_Produto;
        }
        
        //Verifica as exceções de valor de MVA da NCM
        /*
        if (!empty($mProduto['D149_Regras_MVA'])) {
            preg_match_all("/(MVA|MVAA) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_MVA);
            $valoresRegras = $D149_Regras_MVA[3];
            if (in_array($mProduto['D018_UF'], $D149_Regras_MVA[2])) {
                $regra                     = array_search($mProduto['D018_UF'], $D149_Regras_MVA[2]);
                $mProduto['D149_Valor_VA'] = str_replace(",", ".", $valoresRegras[$regra]);
            }
        }*/        

        $Aliquota_ICMS           = $CAD002->retornaICMS($C004_Id, $D024_Id, $mProduto['D009_Id'], $D006_Id, $D018_Id, $Flag_Revenda_Consumidor, $Percentual_Comissao, $Flag_Acp, '2');
        $Percentual_Reducao_ICMS = $CAD002->retornaICMS($C004_Id, $D024_Id, $mProduto['D009_Id'], $D006_Id, $D018_Id, $Flag_Revenda_Consumidor, $Percentual_Comissao, $Flag_Acp, '3');


        // Quando a CFOP é isenta de ICMS, o sistema vai gerar o valor do ICMS nos campos com extenção "Oculto", para que estes valores possam ser utilizados na observação da nota
        // Os parametros finais = 22 ou 33 é para retornar as aliquotas mesmo que a CFOP seja isenta
        // Marcelo de Paula - 18-09-2014
        $Aliquota_ICMS_Oculto    = $CAD002->retornaICMS($C004_Id, $D024_Id, $mProduto['D009_Id'], $D006_Id, $D018_Id, $Flag_Revenda_Consumidor, $Percentual_Comissao, $Flag_Acp, '22');
        $Percentual_Reducao_ICMS_Oculto = $CAD002->retornaICMS($C004_Id, $D024_Id, $mProduto['D009_Id'], $D006_Id, $D018_Id, $Flag_Revenda_Consumidor, $Percentual_Comissao, $Flag_Acp, '33');

        $mProduto['Aliquota_ICMS']           = $Aliquota_ICMS;
        $mProduto['Percentual_Reducao_ICMS'] = $Percentual_Reducao_ICMS;
		
		//IF Personalizado - Caso o estado esteja preechido no campo UF ST Venda, o sistema inverte a flag ST do D149	
        /* Comentado 24/08/2021, solicitacao da CHA e Eurosul
		if(!empty($mProduto['D149_UF_ST'])){
            $pos = strpos($mProduto['D149_UF_ST'], $mProduto['D018_UF']);
            if ($pos !== false) {
				$mProduto['D149_Flag_ST'] = ($mProduto['D149_Flag_ST'] == 'N' || $mProduto['D149_Flag_ST'] == '') ? "S" : "N";
            }
        }
        */
        
        // Só entra aqui se na tabela ICMS (D053) o D053_ST_ICMS_Inter_Estadual estiver 0. 
        // Esta validação do ICMS é a mesma do programa CAD002->retornaICMS, se for alterada la precisa alterar aqui também (10/11/2021)
        if($mCab['D053_ST_ICMS_Inter_Estadual'] <= 0){

            if((($mProduto['D049_Origem_Mercadoria'] == '1' || $mProduto['D049_Origem_Mercadoria'] == '2' || $mProduto['D049_Origem_Mercadoria'] == '3') AND $mCab['D024_D018_Id'] != $mCab['C004_D018_Id'] AND $mD006['D006_Flag_Isento_ICMS'] != 'S') AND ($mCab['D024_Inscricao_Estadual'] == '' OR $mCab['D024_Inscricao_Estadual'] == 'ISENTO' OR $mCab['D024_Flag_Pessoa_Fisica_Juridica'] == 'F')) {
                $mCab['D053_ST_ICMS_Inter_Estadual'] = $mCab['D053_Aliquota_ICMS_Importado'];
            } else if((($mProduto['D049_Origem_Mercadoria'] == '1' || $mProduto['D049_Origem_Mercadoria'] == '2' || $mProduto['D049_Origem_Mercadoria'] == '3') AND $mCab['D024_D018_Id'] != $mCab['C004_D018_Id'] AND $mD006['D006_Flag_Isento_ICMS'] != 'S') AND (($mCab['D024_Inscricao_Estadual'] != '' AND $mCab['D024_Inscricao_Estadual'] != 'ISENTO') OR $mCab['D024_Flag_Pessoa_Fisica_Juridica'] == 'J')) {
                $mCab['D053_ST_ICMS_Inter_Estadual'] = $mCab['D053_Aliquota_ICMS_Importado_2'];
            } else if ($mCab['D024_Inscricao_Estadual'] == '' OR $mCab['D024_Inscricao_Estadual'] == 'ISENTO') {
                $mCab['D053_ST_ICMS_Inter_Estadual'] = $mCab['D053_Aliquota_ICMS_1'];
            } else if (($mCab['D024_Inscricao_Estadual'] != '' AND $mCab['D024_Inscricao_Estadual'] != 'ISENTO') OR $mCab['D024_Flag_Pessoa_Fisica_Juridica'] == 'J') {
                $mCab['D053_ST_ICMS_Inter_Estadual'] = $mCab['D053_Aliquota_ICMS_2'];
            } 
        }

	    //Verifica as exceções de valor de MVA e ICMS da NCM
	    if (!empty($mProduto['D149_Regras_MVA'])) {
	        preg_match_all("/(MVA|MVAA) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_MVA);
	        $valoresRegras = $D149_Regras_MVA[3];
	        if (in_array($mCab['D018_UF'], $D149_Regras_MVA[2])) {
	            $regra = array_search($mProduto['D018_UF'], $D149_Regras_MVA[2]);
	            if (!empty($valoresRegras[$regra])) {
	                $mProduto['D149_Valor_VA'] = str_replace(",", ".", $valoresRegras[$regra]);
	            }
	        }
	        if ($mProduto['Aliquota_ICMS']>0) {	
		        log("/(ICM|ICMS) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims {$mProduto['D149_Regras_MVA']}, {$D149_Regras_ICM}");
		        preg_match_all("/(ICM|ICMS) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_ICM);
		        $valoresRegras = $D149_Regras_ICM[3];
		        if (in_array($mCab['D018_UF'], $D149_Regras_ICM[2])) {
		            $regra = array_search($mProduto['D018_UF'], $D149_Regras_ICM[2]);
		            if (!empty($valoresRegras[$regra])) {
		                $mProduto['Aliquota_ICMS'] = str_replace(",", ".", $valoresRegras[$regra]);
		            }
		        } else if (in_array('XX', $D149_Regras_ICM[2])) {
		            $regra = array_search('XX', $D149_Regras_ICM[2]);
		            if (!empty($valoresRegras[$regra])) {
		                $mProduto['Aliquota_ICMS'] = str_replace(",", ".", $valoresRegras[$regra]);
		            }
		        }
		    }
	        log("/(ICMIE|ICMSIE) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims {$mProduto['D149_Regras_MVA']}, {$D149_Regras_ICM}");
	        preg_match_all("/(ICMIE|ICMSIE) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_ICM);
	        $valoresRegras = $D149_Regras_ICM[3];
	        if (in_array($mCab['D018_UF'], $D149_Regras_ICM[2])) {
	            $regra = array_search($mProduto['D018_UF'], $D149_Regras_ICM[2]);
	            if (!empty($valoresRegras[$regra])) {
	                $mCab['D053_ST_ICMS_Inter_Estadual'] = str_replace(",", ".", $valoresRegras[$regra]);
	            }
	        } else if (in_array('XX', $D149_Regras_ICM[2])) {
	            $regra = array_search('XX', $D149_Regras_ICM[2]);
	            if (!empty($valoresRegras[$regra])) {
	                $mCab['D053_ST_ICMS_Inter_Estadual'] = str_replace(",", ".", $valoresRegras[$regra]);
	            }
	        }
	        log("/(ICMINT|ICMSINT) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims {$mProduto['D149_Regras_MVA']}, {$D149_Regras_ICM}");
	        preg_match_all("/(ICMINT|ICMSINT) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_ICM);
	        $valoresRegras = $D149_Regras_ICM[3];
	        if (in_array($mCab['D018_UF'], $D149_Regras_ICM[2])) {
	            $regra = array_search($mProduto['D018_UF'], $D149_Regras_ICM[2]);
	            if (!empty($valoresRegras[$regra])) {
	                $mCab['D053_ST_ICMS_Interno'] = str_replace(",", ".", $valoresRegras[$regra]);
	            }
	        } else if (in_array('XX', $D149_Regras_ICM[2])) {
	            $regra = array_search('XX', $D149_Regras_ICM[2]);
	            if (!empty($valoresRegras[$regra])) {
	                $mCab['D053_ST_ICMS_Interno'] = str_replace(",", ".", $valoresRegras[$regra]);
	            }
	        }
	        
	    }
        
        $D053_ST_ICMS_Inter_Estadual = $mCab['D053_ST_ICMS_Inter_Estadual'];
        $D053_ST_ICMS_Interno        = $mCab['D053_ST_ICMS_Interno'];

        if ($mD006['D006_ST_ICMS_Inter_Estadual_MVA'] > 0)
            $mCab['D053_ST_ICMS_Inter_Estadual'] = $mD006['D006_ST_ICMS_Inter_Estadual_MVA'];
        if ($mD006['D006_ST_ICMS_Interno_MVA'] > 0)
            $mCab['D053_ST_ICMS_Interno'] = $mD006['D006_ST_ICMS_Interno_MVA'];
        
        if ($mD006['D006_ST_MVA'] > 0)
            $mProduto['D149_Valor_VA'] = $mD006['D006_ST_MVA'];
		

		// Esse IF já existe no final do método. Foi copiado emergencialmente aqui para que utilize esta regra para definir a alíquota de ICMS interno para calcular o MVA ajustado corretamente.
		if (($mProduto['D049_Origem_Mercadoria'] == '1' || $mProduto['D049_Origem_Mercadoria'] == '2' || $mProduto['D049_Origem_Mercadoria'] == '3') && $mCab['D024_D018_Id'] != $mCab['C004_D018_Id'] && $mD006['D006_Flag_Isento_ICMS'] != 'S') {
            $mCab['D053_ST_ICMS_Inter_Estadual'] = $mProduto['Aliquota_ICMS'] ;
            $Aliquota_ICMS_Oculto = $mProduto['Aliquota_ICMS'] ;
        }

        if ($mProduto['D149_Valor_VA'] > 0 and $mD006['D006_Flag_Ajustar_MVA']=='S') {
            $Variavel_A                = 1 + ($mProduto['D149_Valor_VA'] / 100);
			log("Variavel: ".$Variavel_A);
            $Variavel_B                = (1 - ($mCab['D053_ST_ICMS_Inter_Estadual'] / 100)) / (1 - ($mCab['D053_ST_ICMS_Interno'] / 100));
			log("Varialvel B: ".$Variavel_B ."InterEst: ".$mCab['D053_ST_ICMS_Inter_Estadual']." Interno: ".$mCab['D053_ST_ICMS_Interno']);
            $Variavel_AB               = (($Variavel_A * $Variavel_B) - 1) * 100;
			log("Variavel AB: ".$Variavel_AB);
            $mProduto['D149_Valor_VA'] = $Variavel_AB;
        }

        $mCab['D053_ST_ICMS_Inter_Estadual'] = $D053_ST_ICMS_Inter_Estadual;
        $mCab['D053_ST_ICMS_Interno']        = $D053_ST_ICMS_Interno;
        
        if ($mD006['D006_ST_ICMS_Inter_Estadual'] > 0)
            $mCab['D053_ST_ICMS_Inter_Estadual'] = $mD006['D006_ST_ICMS_Inter_Estadual'];
        if ($mD006['D006_ST_ICMS_Interno'] > 0)
            $mCab['D053_ST_ICMS_Interno'] = $mD006['D006_ST_ICMS_Interno'];
        
        if ($mD006['D006_Flag_ST_VA'] == "N") {
            $mProduto['D149_Valor_VA'] = 0;
        }
        if ((($Flag_Acp != '1') AND ($Flag_Acp != '6')) OR ($mD006['D006_Flag_Substituicao_Tributaria'] != 'S')) {
            $mProduto['D149_Flag_ST']  = "N";
            $mProduto['D149_Valor_VA'] = 0;
        }
		log("D006_Flag_Substituicao_Tributaria: ".$mD006['D006_Flag_Substituicao_Tributaria']." D149_Flag_ST: ".$mProduto['D149_Flag_ST']." Valor_VA: ".$mProduto['D149_Valor_VA']." D149_Flag_ST: ".$mProduto['D149_Flag_ST']);

        if ($mD006['D006_Flag_Substituicao_Tributaria'] == 'S' && $mProduto['D149_Flag_ST'] == 'S' || ($mProduto['D149_Valor_VA'] > 0 && $mProduto['D149_Flag_ST']=='N')){
            $Flag_ST = 'S';
        } else {
            $Flag_ST                               = 'N';
            //$mCab['D053_ST_ICMS_Interno']          = 0;
            //$mCab['D053_ST_ICMS_Inter_Estadual']   = 0;
            //$mD006['D006_ST_ICMS_Interno_Reducao'] = 0;
            //$mProduto['D149_Valor_VA']             = 0;
        }

		log("Flag ST: ".$Flag_ST);

        //Aplica a redução de MVA
        if ($mD006['D006_Reducao_MVA'] > 0) {
            $mProduto['D149_Valor_VA'] = $mProduto['D149_Valor_VA'] - (($mD006['D006_Reducao_MVA'] * $mProduto['D149_Valor_VA']) / 100);
        }
        
        $Flag_Situacao_Tributaria = '0' . $mD006['D059_Codigo_ST'];
        
        
        // Verificando a Situação tributária
        if ((!empty($mProduto['D049_Origem_Mercadoria']) OR $mProduto['D049_Origem_Mercadoria'] == '0') AND $mProduto['D049_Origem_Mercadoria'] != '') {
            $Flag_Situacao_Tributaria = $mProduto['D049_Origem_Mercadoria'] . $mD006['D059_Codigo_ST'];
            
        } else if (!empty($mProduto['D049_Flag_Nacional_Importado'])) {
            if ($mProduto['D049_Flag_Nacional_Importado'] == "N") {
                $Flag_Situacao_Tributaria = '0' . $mD006['D059_Codigo_ST'];
            } else {
                $Flag_Situacao_Tributaria = '1' . $mD006['D059_Codigo_ST'];
            }
        } else if (!empty($mProduto['D001_Flag_Tipo'])) {
            if ($mProduto['D001_Flag_Tipo'] == "PRODUZIDO") {
                $Flag_Situacao_Tributaria = '1' . $mD006['D059_Codigo_ST'];
            } else {
                $Flag_Situacao_Tributaria = '0' . $mD006['D059_Codigo_ST'];
            }
        }
		
		if (($mProduto['D049_Origem_Mercadoria'] == '1' || $mProduto['D049_Origem_Mercadoria'] == '2' || $mProduto['D049_Origem_Mercadoria'] == '3') && $mCab['D024_D018_Id'] != $mCab['C004_D018_Id'] && $mD006['D006_Flag_Isento_ICMS'] != 'S') {
            if (!empty($mProduto['D149_Regras_MVA'])) {    
                if ($mProduto['Aliquota_ICMS']>0) { 
                    log("/(ICMIMP|ICMSIMP) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims {$mProduto['D149_Regras_MVA']}, {$D149_Regras_ICM}");
                    preg_match_all("/(ICMIMP|ICMSIMP) +([a-z]{2}) *[^[0-9]]? *(([0-9]|\,|\.)+)( |\n|\r|)/ims", $mProduto['D149_Regras_MVA'], $D149_Regras_ICM);
                    $valoresRegras = $D149_Regras_ICM[3];
                    if (in_array($mCab['D018_UF'], $D149_Regras_ICM[2])) {
                        $regra = array_search($mProduto['D018_UF'], $D149_Regras_ICM[2]);
                        if (!empty($valoresRegras[$regra])) {
                            $mProduto['Aliquota_ICMS'] = str_replace(",", ".", $valoresRegras[$regra]);
                        }
                    } else if (in_array('XX', $D149_Regras_ICM[2])) {
                        $regra = array_search('XX', $D149_Regras_ICM[2]);
                        if (!empty($valoresRegras[$regra])) {
                            $mProduto['Aliquota_ICMS'] = str_replace(",", ".", $valoresRegras[$regra]);
                        }
                    }
                }
            }    
            $mCab['D053_ST_ICMS_Inter_Estadual'] = $mProduto['Aliquota_ICMS'] ;
            $Aliquota_ICMS_Oculto = $mProduto['Aliquota_ICMS'] ;
        }

        if($mD006['D006_Flag_Calcula_FCP'] == 'S' || $mD006['D006_Flag_Calcula_FCP_ST'] == 'S' || $mD006['D006_Flag_Calcula_FCP_Retido'] == 'S'){
            $percentualFCP = $mCab['D053_Percentual_FCP'];
        }else{
            $percentualFCP = 0;
        }

        if($mD006['D006_Somar_FCP_ICMS_Interno'] == 'S'){
            $mCab['D053_ST_ICMS_Interno'] = $mCab['D053_ST_ICMS_Interno'] + $mCab['D053_Percentual_FCP'];
        }

        if($mD006['D006_Flag_Isento_ICMS'] == 'D' && ($mD006['D006_D059_Id'] == '5' || $mD006['D006_D059_Id'] == '6' || $mD006['D006_D059_Id'] == '7')){
            $mProduto['Aliquota_ICMS'] = 0;
        }

        if ($mD006['D006_Aliquota_PIS'] > 0) {
            $mCab['C004_Percentual_PIS'] = $mD006['D006_Aliquota_PIS'];
        }
       
        if ($mD006['D006_Aliquota_COFINS'] > 0) {
            $mCab['C004_Percentual_COFINS'] = $mD006['D006_Aliquota_COFINS'];
        }
        
        if ($mProduto['D149_Aliquota_PIS'] > 0) {
            $mCab['C004_Percentual_PIS'] = $mProduto['D149_Aliquota_PIS'];
        }
        
        if ($mProduto['D149_Aliquota_COFINS'] > 0) {
            $mCab['C004_Percentual_COFINS'] = $mProduto['D149_Aliquota_COFINS'];
        }

        if ($mProduto['D149_Aliquota_IRPJ'] > 0) {
            $mCab['C004_Percentual_IRPJ'] = $mProduto['D149_Aliquota_IRPJ'];
        }
        
        if ($mProduto['D149_Aliquota_CSLL'] > 0) {
            $mCab['C004_Percentual_CSLL'] = $mProduto['D149_Aliquota_CSLL'];
        }

		// Verifica se tem reducao na aliquota do IPI
        if($mProduto['Aliquota_IPI'] > 0 && $mD006['D006_Reducao_Aliquota_IPI'] > 0){
            $mProduto['Aliquota_IPI'] = $mProduto['Aliquota_IPI'] * ((100 - $mD006['D006_Reducao_Aliquota_IPI']) / 100);
        }
        
        // Buscado dados dos tributos da lei da transparência
        
        // Buscando o percentual de tributos (lei transparências)
        $D149a  = mysql_query("SELECT D149_Percentual_Trib_Transparencia FROM D149 WHERE D149_Id='{$mProduto['D149_Id']}'");
        $mD149a = mysql_fetch_array($D149a);
        
        $D006a  = mysql_query("SELECT D006_Percentual_Trib_Transparencia FROM D006 WHERE D006_Id='{$D006_Id}'");
        $mD006a = mysql_fetch_array($D006a);
        
        if (!empty($mD149a['D149_Percentual_Trib_Transparencia']) AND $mD149a['D149_Percentual_Trib_Transparencia'] > 0) {
            
            $Percentual_Trib_Transparencia = $mD149a['D149_Percentual_Trib_Transparencia'];
            
        } else if (!empty($mD006a['D006_Percentual_Trib_Transparencia']) AND $mD006a['D006_Percentual_Trib_Transparencia'] > 0) {
            
            $Percentual_Trib_Transparencia = $mD006a['D006_Percentual_Trib_Transparencia'];
            
        } else {
            
            $Percentual_Trib_Transparencia = 0;
        }
        
        // Definindo o array de retorno
        $array = array();
		log("Flag ST:". $Flag_ST);
        $array['D005_Id']                        = $mProduto['D005_Id'];
        $array['D149_Id']                        = $mProduto['D149_Id'];
        $array['Classificacao_Fiscal']           = $mProduto['D005_Classificacao_Fiscal'];
        $array['D006_Id']                        = $D006_Id;
        $array['Aliquota_IPI']                   = $mProduto['Aliquota_IPI'];
        $array['Aliquota_ICMS']                  = $mProduto['Aliquota_ICMS'];
        $array['Aliquota_ICMS_Oculto']           = $Aliquota_ICMS_Oculto;
        $array['Percentual_Reducao_ICMS']        = $mProduto['Percentual_Reducao_ICMS'];
        $array['Percentual_Reducao_ICMS_Oculto'] = $Percentual_Reducao_ICMS_Oculto;
        $array['Percentual_Reducao_ICMS_ST']     = $mD006['D006_ST_ICMS_Interno_Reducao'];
        $array['ST_VA']                          = $mProduto['D149_Valor_VA'];
        $array['Flag_ST']                        = $Flag_ST;
        $array['Flag_Situacao_Tributaria']       = $Flag_Situacao_Tributaria;
        $array['ICMS_Inter_Estadual']            = $mCab['D053_ST_ICMS_Inter_Estadual'];
        $array['ST_ICMS_Interno']                = $mCab['D053_ST_ICMS_Interno'];
        $array['ST_ICMS_Inter_Estadual_MVA']     = $mD006['D006_ST_ICMS_Inter_Estadual_MVA'];
        $array['ST_ICMS_Interno_MVA']            = $mD006['D006_ST_ICMS_Interno_MVA'];
        $array['Flag_Isento_PIS']                = $mD006['D006_Flag_Isento_PIS'];
        $array['Aliquota_PIS']                   = $mCab['C004_Percentual_PIS'];
        $array['Situacao_Tributaria_PIS']        = $mD006['D006_Situacao_Tributaria_PIS'];
        $array['Flag_Isento_COFINS']             = $mD006['D006_Flag_Isento_COFINS'];
        $array['Aliquota_COFINS']                = $mCab['C004_Percentual_COFINS'];
        $array['Situacao_Tributaria_COFINS']     = $mD006['D006_Situacao_Tributaria_COFINS'];
        $array['Situacao_Tributaria_IPI']        = $mD006['D088_Codigo_ST'];
        $array['D059_Id']                        = $mD006['D059_Id'];
        $array['D082_Id']                        = $mProduto['D049_D082_Id'];
        $array['Valor_Custo_Unitario']           = $mProduto['D009_Valor_Custo_Unitario'];
        $array['D001_Peso_Unitario_Kg']          = $mProduto['D001_Peso_Unitario_Kg'];
        $array['D001_Peso_Unitario_Bruto']       = $mProduto['D001_Peso_Unitario_Bruto'];
        $array['Percentual_Reducao_IPI']         = $mProduto['Percentual_Reducao_IPI'];
        $array['Aliquota_ICMS_Credito']          = $Aliquota_ICMS_Credito;
        $array['Percentual_Trib_Transparencia']  = $Percentual_Trib_Transparencia;
        $array['Flag_Pre_Cadastro']              = $mProduto['D001_Flag_Pre_Cadastro'];
        $array['Valor_Custo_ST_Unitario']        = $mProduto['D009_Valor_Custo_ST_Unitario'];
        $array['Valor_Custo_ST_Medio_Unitario']  = $mProduto['D009_Valor_Custo_ST_Medio_Unitario'];
        $array['Motivo_Desoneracao_ICMS']        = $mD006['D006_Motivo_Desoneracao_ICMS'];
        $array['IPV_Sem_Lucro']                  = $mCab['C004_IPV_Padrao_Sem_Lucro'];
        $array['Aliquota_IRPJ']                  = $mCab['C004_Percentual_IRPJ'];
        $array['Flag_Isento_IRPJ']               = $mD006['D006_Flag_Isento_IRPJ'];
        $array['Aliquota_CSLL']                  = $mCab['C004_Percentual_CSLL'];
        $array['Flag_Isento_CSLL']               = $mD006['D006_Flag_Isento_CSLL'];
		
		$array['Percentual_Partilha_ICMS_Destino'] = $mD006['D006_Percentual_Partilha_ICMS_Destino'];
        $array['Percentual_Partilha_ICMS_Origem']  = $mD006['D006_Percentual_Partilha_ICMS_Origem'];
        $array['Codigo_CEST']                      = $mProduto['D149_Codigo_CEST'];
        $array['Percentual_FCP']                   = $percentualFCP;
        $array['Codigo_Enq_Legal_IPI']             = $mD006['D006_Codigo_Enq_Legal_IPI'];
		$array['D006_Codigo_Beneficio_Fiscal']     = $Codigo_Beneficio_Fiscal;


        /* ================================================================================ */
        /* [REFORMA TRIBUTÁRIA] - Gabriel Cegantini 24/11/2025
        ================================================================================ */
        // Pega as aliquotas da reforma tributaria baseada na classificação tributária CBS/IBS selecionada dentro da "NCM - Vale para todas as empresas" e retorna para serem salvas
        require_once('bibliotecas/classes/ReformaTributaria.php');
        $reformaTributaria = new ReformaTributaria();

        /* Sistema de prioridade, se estiver preenchido na CFOP, prioriza a situação fiscal da CFOP,
        se não, utiliza a situação fiscal da NCM */
        if (!empty($mD006['D006_Situacao_Tributaria_CBS_IBS']) AND !empty($mD006['D006_Classificacao_Tributaria_CBS_IBS'])) {
            // Pega as alíquotas e seta situação fiscal da: Tributação normal
            $aliquotasReformaTributaria = $reformaTributaria->getAliquotas($mD006['D006_Classificacao_Tributaria_CBS_IBS'] ?? "");
            $array['Situacao_Tributaria_CBS_IBS']       = $mD006['D006_Situacao_Tributaria_CBS_IBS'];
            $array['Classificacao_Tributaria_CBS_IBS']  = $mD006['D006_Classificacao_Tributaria_CBS_IBS'];
            
            // Pega as alíquotas e seta situação fiscal da: Tributação regular
            $aliquotasReformaTributariaRegular = $reformaTributaria->getAliquotas($mD006['D006_Classificacao_Tributaria_CBS_IBS_Regular'] ?? "");
            $array['Situacao_Tributaria_CBS_IBS_Regular']       = $mD006['D006_Situacao_Tributaria_CBS_IBS_Regular'];
            $array['Classificacao_Tributaria_CBS_IBS_Regular']  = $mD006['D006_Classificacao_Tributaria_CBS_IBS_Regular'];
        } else {
            // Pega as alíquotas e seta situação fiscal da: Tributação normal
            $aliquotasReformaTributaria = $reformaTributaria->getAliquotas($mProduto['D005_Classificacao_Tributaria_CBS_IBS'] ?? "");
            $array['Situacao_Tributaria_CBS_IBS']       = $mProduto['D005_Situacao_Tributaria_CBS_IBS'];
            $array['Classificacao_Tributaria_CBS_IBS']  = $mProduto['D005_Classificacao_Tributaria_CBS_IBS'];

            // Pega as alíquotas e seta situação fiscal da: Tributação regular
            $aliquotasReformaTributariaRegular = $reformaTributaria->getAliquotas($mProduto['D005_Classificacao_Tributaria_CBS_IBS_Regular'] ?? "");
            $array['Situacao_Tributaria_CBS_IBS_Regular']       = $mProduto['D005_Situacao_Tributaria_CBS_IBS_Regular'];
            $array['Classificacao_Tributaria_CBS_IBS_Regular']  = $mProduto['D005_Classificacao_Tributaria_CBS_IBS_Regular'];
        }

        // Monta a tributação normal da reforma
        $array['Aliquota_CBS']                                  = $aliquotasReformaTributaria['aliquotaCbs'];
        $array['Aliquota_IBS_UF']                               = $aliquotasReformaTributaria['aliquotaIbsUf'];
        $array['Aliquota_IBS_Municipal']                        = $aliquotasReformaTributaria['aliquotaIbsMunicipal'];
        $array['Percentual_Reducao_CBS']                        = $aliquotasReformaTributaria['percentualReducaoCbs'];
        $array['Percentual_Reducao_IBS_UF']                     = $aliquotasReformaTributaria['percentualReducaoIbsUf'];
        $array['Percentual_Reducao_IBS_Municipal']              = $aliquotasReformaTributaria['percentualReducaoIbsMunicipal'];
        $array['Percentual_Diferimento_CBS']                    = $aliquotasReformaTributaria['percentualDiferimentoCbs'];
        $array['Percentual_Diferimento_IBS_UF']                 = $aliquotasReformaTributaria['percentualDiferimentoIbsUf'];
        $array['Percentual_Diferimento_IBS_Municipal']          = $aliquotasReformaTributaria['percentualDiferimentoIbsMunicipal'];

        // Monta a tributação regular da reforma
        $array['Aliquota_CBS_Regular']                          = $aliquotasReformaTributariaRegular['aliquotaCbs'];
        $array['Aliquota_IBS_UF_Regular']                       = $aliquotasReformaTributariaRegular['aliquotaIbsUf'];
        $array['Aliquota_IBS_Municipal_Regular']                = $aliquotasReformaTributariaRegular['aliquotaIbsMunicipal'];
        $array['Percentual_Reducao_CBS_Regular']                = $aliquotasReformaTributariaRegular['percentualReducaoCbs'];
        $array['Percentual_Reducao_IBS_UF_Regular']             = $aliquotasReformaTributariaRegular['percentualReducaoIbsUf'];
        $array['Percentual_Reducao_IBS_Municipal_Regular']      = $aliquotasReformaTributariaRegular['percentualReducaoIbsMunicipal'];
        /* ================================================================================ */


        return $array;
    
	}
	
	
	
	public function xretornaCFOP($D024_Id, $D009_Id = null, $C004_Id = null, $Destino = null, $Origem = null, $retornaLog = false, $OrigemPedido = null, $C004_Id_Estoque=null)
    {
        global $g;
        $tipo;
        $C004_Id = ($C004_Id == null) ? $g['empresaAtual'] : $C004_Id;
        if ($D009_Id) {
            $tipo    = 'I';
            $retorno = mysql_query("SELECT                                
                                    D024_Flag_Contribuinte,
                                    D024_Flag_Pessoa_Fisica_Juridica,
                                    D024_Flag_Revenda_Consumidor,
                                    D024_D006_Id,
                                    D018_UF,    
                                    D018_Id,                            
                                    D053_Flag_ST,
                                    D001_Flag_Tipo,
                                    D001_Peso_Unitario_Kg,
                                    D049_Flag_Nacional_Importado,
                                    D049_Origem_Mercadoria,
                                    D149_Flag_ST,
                                    D149_UF_ST,
                                    D005_Id,
                                    D005_Classificacao_Fiscal,
                                    C004_Codigo_Regime_Tributario,
                                    C004_D018_Id,
                                    C004_Id,
                                    D024_Codigo_Regime_Tributario
                                FROM D024
                                    LEFT JOIN C004 ON C004_Id={$C004_Id}
                                    LEFT JOIN D018 ON D018_Id=D024_D018_Id
                                    LEFT JOIN D053 ON D053_D018_Id=D018_Id
                                    LEFT JOIN D009 ON D009_Id={$D009_Id}
                                    LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                    LEFT JOIN D001 ON D001_Id=D049_D001_Id
                                    LEFT JOIN D005 ON D005_Id=D001_D005_Id
                                    LEFT JOIN D149 ON D005_Id=D149_D005_Id AND D149_C004_Id={$C004_Id}
                                WHERE 
                                    D024_Id={$D024_Id} AND D053_C004_Id={$C004_Id}
            ");
            
        } else {
            $tipo    = 'C';
            $retorno = mysql_query("SELECT    
                                    D024_Id,                            
                                    D024_Flag_Contribuinte,
                                    D024_Flag_Pessoa_Fisica_Juridica,
                                    D024_Flag_Revenda_Consumidor,
                                    D024_D006_Id,
                                    D018_UF,
                                    D018_Id,                                
                                    D053_Flag_ST,
                                    C004_Codigo_Regime_Tributario,
                                    C004_D018_Id,
                                    C004_Id,
                                    D024_Codigo_Regime_Tributario
                                FROM D024
                                    LEFT JOIN C004 ON C004_Id={$C004_Id}
                                    LEFT JOIN D018 ON D018_Id=D024_D018_Id
                                    LEFT JOIN D053 ON D053_D018_Id=D018_Id
                                WHERE 
                                    D024_Id={$D024_Id} AND D053_C004_Id={$C004_Id}
            
            ");
            
        }
        $registro = mysql_fetch_array($retorno);
        
        if ($Origem != NULL) {
            $registro['D049_Origem_Mercadoria'] = $Origem;
        }

        if ($OrigemPedido != null && $OrigemPedido > 0) {
            if($C004_Id != $OrigemPedido){
                $D137_Origem_Pedido = 'E';
            } else {
                $D137_Origem_Pedido = 'I';
            }
        } else {
            $D137_Origem_Pedido = '';
        }
        
        $D137_C004_Id = $registro['C004_Id'];
        
        $D137_C004_Codigo_Regime_Tributario = $registro['C004_Codigo_Regime_Tributario'];
        $D137_D024_Codigo_Regime_Tributario = $registro['D024_Codigo_Regime_Tributario'];
        
        $D137_Flag_Revenda_Consumidor = $registro['D024_Flag_Revenda_Consumidor'];
        
        // o valor do tipo "não contribuinte" que no cad cliente é 0 foi substituido por 9 
        $D137_D024_Flag_Contribuinte = $registro['D024_Flag_Contribuinte'];
        if ($D137_D024_Flag_Contribuinte == 0){
            $D137_D024_Flag_Contribuinte = 9;
        }
        
        if ($registro['D018_Id'] == $registro['C004_D018_Id']){
            $D137_Dentro_Fora_Estado = 'D';
        } else {
            $D137_Dentro_Fora_Estado = 'F';
        }

        $D137_UF_Tem_ST = $registro['D053_Flag_ST'];

		log("D149_Flag_ST: " . $registro['D149_Flag_ST']);
		if ($registro['D149_Flag_ST']) {
            $D137_NCM_Tem_ST = $registro['D149_Flag_ST'];
        } else {
            $D137_NCM_Tem_ST = "";
        }
		log("alaor: ".$D137_NCM_Tem_ST);
		//IF Personalizado - Caso o estado esteja preechido no campo UF ST Venda, o sistema inverte a flag ST do D149	
		// If comentado dia 18/02/2019 pois o cliente pediu para retirar essa personalização
		/*if(!empty($registro['D149_UF_ST'])){
            $pos = strpos($registro['D149_UF_ST'], $registro['D018_UF']);
            if ($pos !== false) {
				$D137_NCM_Tem_ST = ($D137_NCM_Tem_ST == 'N' || $D137_NCM_Tem_ST == '') ? "S" : "N";
            }
        }*/

        $D137_D005_Id = $registro['D005_Id'];

        if ($registro['D024_Flag_Pessoa_Fisica_Juridica'] == "F"){
            $D137_Pessoa_Fisica_Juridica = "PF";
        } elseif ($registro['D024_Flag_Pessoa_Fisica_Juridica'] == "J") {
            $D137_Pessoa_Fisica_Juridica = "PJ";
        } elseif ($registro['D024_Flag_Pessoa_Fisica_Juridica'] == "I") {
            $D137_Pessoa_Fisica_Juridica = "PI";
        }
        
        if ($g['C031']['multimarcas'] == 'S') {
            if ($registro['D049_Flag_Nacional_Importado'] == "N") {
                $D137_Produzido_Adquirido = "A";
            } else if ($registro['D049_Flag_Nacional_Importado'] == "I") {
                $D137_Produzido_Adquirido = "P";
            } else {
                $D137_Produzido_Adquirido = "";
            }
        } else {
            if ($registro['D001_Flag_Tipo'] == "COMPRADO") {
                $D137_Produzido_Adquirido = "A";
            } else if ($registro['D001_Flag_Tipo'] == "PRODUZIDO") {
                $D137_Produzido_Adquirido = "P";
            } else {
                $D137_Produzido_Adquirido = "";
            }
        }

        if($g['C031']['venderEstoqueMatriz'] == 'S'){
            if($C004_Id_Estoque == $g['empresaAtual']){
                $D137_Estoque_Origem = "D";
            } else {
                $D137_Estoque_Origem = "F";
            }
        }
        
        $retorno = mysql_query("SELECT *, D137_Quantidade_Regras(D137_Id) as qtdeRegras FROM D137 LEFT JOIN D006 ON D006_Id=D137_D006_Id WHERE D137_Flag_Inativar='N' AND D137_Flag_Cabecalho_Item='$tipo'");
        $validas;
        $valida;
        $err = "";
        $log = "Início Log D009_Id: {$D009_Id} <br />";
        $arrayDemo = Array();
        while ($regras = mysql_fetch_assoc($retorno)) {
            $iconeAbrirJanela = <<<EOT
                if([[permissaoRegras]]){

                    abrirJanela(false, divIdRootAberto(), '[[divId]]', unique(), '', 'Editar Regra', '/fis/fis001/content/fis001content03/', '&acaoId=' + encodeURIComponent('{$regras['D137_Id']}') + '&tabela=D137', [600,550]);
                }
EOT;
            $log .= "<br ></EOT>Entrou while D137_Id: {$regras['D137_Id']} - {$regras['D006_Codigo_CFOP']} - {$regras['D006_Descricao']}<br />";
            $demonstrativoDadosRegra = "<h3><font color=\"green\">Regra {$regras['D137_Id']} ({$regras['qtdeRegras']}): {$regras['D006_Id']}-{$regras['D006_Codigo_CFOP']} - {$regras['D006_Descricao']}</font><span class=\"gear\" style=\"float: right; cursor: pointer; width: 16px; height: 16px; right: 0; position: relative;\" onclick=\"{$iconeAbrirJanela} event.preventDefault(); return false;\" ></span></h3><div>";
            
            if (trim($regras['D137_Empresas_Destinatario']) != "") {
                $log .= "ENTROU IF D137_Empresas_Destinatario != ''<br />";
                $demonstrativoRegra .= "Cliente específicado na regra<br />";
                
                $pos = strpos($regras['D137_Empresas_Destinatario'], $D024_Id . ',');
                if ($pos === false) {
                    $log .= " Entrou CONTINUE D137_Empresas_Destinatario D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Cliente não específicado na regra</div>"
                    );
                    $err .= '0';
                    continue;
                }
            }

            if ($registro['D024_D006_Id'] > 0 AND $regras['D137_Flag_Preferencial_Sobre_D024_D006_Id'] != "S") {
                $log .= "Entrou IF D024_D006_Id > AND D137_Flag_Preferencial_Sobre_D024_D006_Id != S  D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Cliente possuí CFOP preferencial<br />";
                $err .= '0';
                continue;
            }
            
            if (trim($regras['D137_Empresas']) != "") {
                $log .= "Entrou IF D137_Empresas != ''<br />";
                $demonstrativoRegra .= "Empresa específicada na regra<br />";
               
                $pos = strpos($regras['D137_Empresas'], $D137_C004_Id . ',');
                if ($pos === false) {
                    $log .= " Entrou CONTINUE D137_Empresas D137_Id='{$regras['D137_Id']}' D137_C004_Id: {$D137_C004_Id}<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Empresa não específicada na regra</div>"
                    );
                    $err .= '0';
                    continue;
                }
            }
            if ($regras['D137_C004_Id'] > 0) {
                $log = "Entrou IF D137_C004_Id > 0 D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Regra específica da empresa<br />";

                if ($regras['D137_C004_Id'] != $D137_C004_Id) {
                    $log .= "Entrou CONTINUE D137_C004_Id: {$regras['D137_C004_Id']} != D137_C004_Id: {$D137_C004_Id} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Regra específica para outra empresa</div>"
                    );
                    $err .= '0';
                    continue;
                }
            }
            //if (trim($regras['D137_Flag_Revenda_Consumidor']) != "") {
            //    $log .= "Entrou D137_Flag_Revenda_Consumidor != '' D137_Id='{$regras['D137_Id']}'<br />";
            //    $demonstrativoRegra .= "Cliente revendedor/consumidor<br />";

            //    $pos = strpos($regras['D137_Flag_Revenda_Consumidor'], $D137_Flag_Revenda_Consumidor . ',');
            //    if ($pos === false) {
            //        $log .= "Entrou CONTINUE D137_Flag_Revenda_Consumidor. D137_Id='{$regras['D137_Id']}'<br />";
            //        $demonstrativoRegra = "";
            //        $arrayDemo[] = array(
            //            'order' => $regras['qtdeRegras'],
            //            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Cliente não revendedor/consumidor</div>"
            //        );
            //        $err .= '0';
            //        continue;
            //    }
            //}
            if ($regras['D137_C004_Codigo_Regime_Tributario'] != "0") {
                $log .= "Entrou D137_C004_Codigo_Regime_Tributario != 0 D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Regime tributário da empresa específicado na regra<br />";
                
                if ($regras['D137_C004_Codigo_Regime_Tributario'] != $D137_C004_Codigo_Regime_Tributario) {
                    
                    $log .= "Entrou CONTINUE D137_C004_Codigo_Regime_Tributario: {$regras['D137_C004_Codigo_Regime_Tributario']} != D137_C004_Codigo_Regime_Tributario: {$D137_C004_Codigo_Regime_Tributario} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Regime tributário da empresa diferente da regra</div>"
                    );
                    $err .= '1';
                    continue;
                }
            }
            if (!empty($regras['D137_D024_Codigo_Regime_Tributario'])) {
                $log .= "Entrou D137_D024_Codigo_Regime_Tributario != 0 D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Regime tributário do destinatário específicado na regra<br />";
                log("Regras D137_D024_Codigo_Regime_Tributario: ".$regras['D137_D024_Codigo_Regime_Tributario'].' D137_D024_Codigo_Regime_Tributario'.$D137_D024_Codigo_Regime_Tributario);
                if ($regras['D137_D024_Codigo_Regime_Tributario'] != $D137_D024_Codigo_Regime_Tributario) {
                    
                    $log .= "Entrou CONTINUE D137_D024_Codigo_Regime_Tributario: {$regras['D137_D024_Codigo_Regime_Tributario']} != D137_D024_Codigo_Regime_Tributario: {$D137_D024_Codigo_Regime_Tributario} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Regime tributário do destinatário diferente da regra</div>"
                    );
                    $err .= '1';
                    continue;
                }
            }
            if ($regras['D137_D024_Flag_Contribuinte'] != "0") {
                $log .= "Entrou D137_D024_Flag_Contribuinte != 0 D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Tipo contribuinte do cliente na regra<br />";

                if ($regras['D137_D024_Flag_Contribuinte'] != $D137_D024_Flag_Contribuinte) {
                    $log .= "Entrou CONTINUE D137_D024_Flag_Contribuinte: {$regras['D137_D024_Flag_Contribuinte']} != D137_D024_Flag_Contribuinte: {$D137_D024_Flag_Contribuinte} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Tipo contribuinte do cliente diferente da regra</div>"
                    );
                    $err .= '2';
                    continue;
                }
            }
            if ($regras['D137_Dentro_Fora_Estado'] != "") {
                $clienteEstado = ($D137_Dentro_Fora_Estado == 'D') ? 'dentro' : 'fora';
                $regraEstado = ($regras['D137_Dentro_Fora_Estado'] == 'D') ? 'dentro' : 'fora';
               
                $log .= "Entrou D137_Dentro_Fora_Estado != '' D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Cliente de {$clienteEstado} do estado<br />";
                if ($regras['D137_Dentro_Fora_Estado'] != $D137_Dentro_Fora_Estado) {
                    $log .= "Entrou CONTINUE D137_Dentro_Fora_Estado: {$regras['D137_Dentro_Fora_Estado']} != D137_Dentro_Fora_Estado: {$D137_Dentro_Fora_Estado} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Cliente de {$clienteEstado} do estado, regra para {$regraEstado}</div>"
                    );
                    $err .= '3';
                    continue;
                }
            }
            if (trim($regras['D137_Estados_Especificos']) != "") {
                $log .= "Entrou D137_Estados_Especificos != '' D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Estado do cliente está na regra<br />";

                $pos = strpos($regras['D137_Estados_Especificos'], $registro['D018_UF']);
                if ($pos === false) {
                    $log .= "Entrou CONTINUE D137_Estados_Especificos: {$regras['D137_Estados_Especificos']} != D137_Estados_Especificos: {$D137_Estados_Especificos} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Estado do cliente não está na regra</div>"
                    );
                    $err .= '4';
                    continue;
                }
            }
            if ($regras['D137_UF_Tem_ST'] != "") {
                $UFClienteST = ($D137_UF_Tem_ST == 'S') ? 'possuí' : 'não possuí';
                $UFRegraST = ($regras['D137_UF_Tem_ST'] == 'S') ? 'possuí' : 'não possuí';

                $log .= "Entrou D137_UF_Tem_ST != '' D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Estado do cliente {$UFClienteST} ST<br />";
                if ($regras['D137_UF_Tem_ST'] != $D137_UF_Tem_ST) {
                    $log .= "Entrou CONTINUE D137_UF_Tem_ST: {$regras['D137_UF_Tem_ST']} != D137_UF_Tem_ST: {$D137_UF_Tem_ST} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Estado do cliente {$UFClienteST} ST e a regra {$UFRegraST}</div>"
                    );
                    $err .= '5';
                    continue;
                }
            }
            if ($regras['D137_Pessoa_Fisica_Juridica'] != "") {
                if($D137_Pessoa_Fisica_Juridica == 'PF') {
                    $pessoaCliente = 'física';
                } else if($D137_Pessoa_Fisica_Juridica == 'PJ') {
                    $pessoaCliente = 'jurídica';
                } else {
                    $pessoaCliente = 'internacional';
                }

                if($regras['D137_Pessoa_Fisica_Juridica'] == 'PF') {
                    $pessoaRegra = 'física';
                } else if($regras['D137_Pessoa_Fisica_Juridica'] == 'PJ') {
                    $pessoaRegra = 'jurídica';
                } else {
                    $pessoaRegra = 'internacional';
                }

                $log .= "Entrou D137_Pessoa_Fisica_Juridica != '' D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Cliente é pessoa {$pessoaCliente}<br />";
                if ($regras['D137_Pessoa_Fisica_Juridica'] != $D137_Pessoa_Fisica_Juridica) {
                    $log .= "Entrou CONTINUE D137_Pessoa_Fisica_Juridica: {$regras['D137_Pessoa_Fisica_Juridica']} != D137_Pessoa_Fisica_Juridica: {$D137_Pessoa_Fisica_Juridica} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Cliente é pessoa {$pessoaCliente} e a regra é para pessoa {$pessoaRegra}</div>"
                    );
                    $err .= '6';
                    continue;
                }
            }

			log("D137 1: ".$regras['D137_Id']);
			// as proximas duas regras se aplicam apenas se for item
            if ($regras['D137_Flag_Cabecalho_Item'] == 'I') {
                $log .= "Entrou IF D137_Flag_Cabecalho_Item='I'<br />";
                if ($regras['D137_NCM_Tem_ST'] != "") {
                    $NCMST = ($D137_NCM_Tem_ST == 'S') ? 'possuí ST' : 'não possuí ST';
                    $regraST = ($regras['D137_NCM_Tem_ST'] == 'S') ? 'possuí ST' : 'não possuí ST';

                    $log .= "Entrou D137_NCM_Tem_ST != '' D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra .= "NCM do produto {$NCMST}<br />";
                    if ($regras['D137_NCM_Tem_ST'] != $D137_NCM_Tem_ST) {
                        $log .= "Entrou CONTINUE D137_NCM_Tem_ST: {$regras['D137_NCM_Tem_ST']} != D137_NCM_Tem_ST: {$D137_NCM_Tem_ST} D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra = "";
                        $arrayDemo[] = array(
                            'order' => $regras['qtdeRegras'],
                            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."NCM do produto {$NCMST} e a regra {$regraST}</div>"
                        );
                        $err .= '7';
                        continue;
                    }
                }
                if ($regras['D137_Flag_UF_Convenio_NCM_ST'] == "S") {
                    if($registro['D149_UF_ST'] != ""){
                        $log .= "Entrou D137_Flag_UF_Convenio_NCM_ST != '' D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra .= "Estado do cliente possuí convenio NCM/UF ST<br />";
                        $pos = strpos($registro['D149_UF_ST'], $registro['D018_UF']);
                        if ($pos === false) {
                            $log .= "Entrou CONTINUE D149_UF_ST D137_Id='{$regras['D137_Id']}'<br />";
                            $demonstrativoRegra = "";
                            $arrayDemo[] = array(
                                'order' => $regras['qtdeRegras'],
                                'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Estado do cliente não específicado no campo UF ST da NCM</div>"
                            );
                            $err .= '10';
                            continue;
                        }
                    }
                }
                //if ($regras['D137_Flag_UF_Convenio_NCM_ST'] == "N") {
                //    $log .= "Entrou D137_Flag_UF_Convenio_NCM_ST== 'N' D137_Id='{$regras['D137_Id']}'<br />";
                //    $demonstrativoRegra .= "Estado do cliente não possuí convenio NCM/UF ST<br />";
//
                //    $pos = strpos($regras['D149_UF_ST'], $registro['D018_UF']);
                //    if ($pos === true) {
                //        $log .= "Entrou CONTINUE D149_UF_ST: {$regras['D149_UF_ST']} != D149_UF_ST: {$D149_UF_ST} D137_Id='{$regras['D137_Id']}'<br />";
                //        $demonstrativoRegra = "";
                //        $arrayDemo[] = array(
                //            'order' => $regras['qtdeRegras'],
                //            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Estado do cliente está específicado no campo UF ST da NCM</div>"
                //        );
                //        $err .= '10';
                //        continue;
                //    }
                //}
                //if ($regras['D137_Produzido_Adquirido'] != "") {
                //    $tipoProduto = ($D137_Produzido_Adquirido == 'P') ? 'produzido' : 'adquirido';
                //    $tipoRegra = ($regras['D137_Produzido_Adquirido'] == 'P') ? 'produzidos' : 'adquiridos';
//
                //    $log .= "Entrou D137_Produzido_Adquirido != '' D137_Id='{$regras['D137_Id']}'<br />";
                //    $demonstrativoRegra .= "O produto é {$tipoProduto}<br />";
                //    if ($regras['D137_Produzido_Adquirido'] != $D137_Produzido_Adquirido) {
                //        $log .= "Entrou CONTINUE D137_Produzido_Adquirido: {$regras['D137_Produzido_Adquirido']} != D137_Produzido_Adquirido: {$D137_Produzido_Adquirido} D137_Id='{$regras['D137_Id']}'<br />";
                //        $demonstrativoRegra = "";
                //        $arrayDemo[] = array(
                //            'order' => $regras['qtdeRegras'],
                //            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."O produto é {$tipoProduto} e a regra é para produtos {$tipoRegra}</div>"
                //        );
                //        $err .= '8';
                //        continue;
                //    }
                //}
                if ($regras['D137_Peso_Minimo'] > 0 OR $regras['D137_Peso_Maximo'] > 0) {
                    $log .= "Entrou D137_Peso_Minimo >= '0' OR {$regras['D137_Peso_Maximo']}>0 D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra .= "O peso do produto está de acordo com o específicado<br />";
                    
                    if ($registro['D001_Peso_Unitario_Kg'] < $regras['D137_Peso_Minimo'] OR $registro['D001_Peso_Unitario_Kg'] > $regras['D137_Peso_Maximo']) {
                        $log .= "Entrou CONTINUE D001_Peso_Unitario_Kg: {$regras['D001_Peso_Unitario_Kg']} != D001_Peso_Unitario_Kg: {$D001_Peso_Unitario_Kg} D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra = "";
                        $arrayDemo[] = array(
                            'order' => $regras['qtdeRegras'],
                            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."O peso do produto ultrapassa os valores da regra</div>"
                        );
                        $err .= '8';
                        continue;
                    }
                }
                if (trim($regras['D137_NCM']) != "") {
                    $log .= "Entrou D137_NCM != '' D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra .= "NCM do produto específicada na regra<br />";

                    $pos = strpos($regras['D137_NCM'], $registro['D005_Classificacao_Fiscal']);
                    if ($pos === false) {
                        $log .= "Entrou CONTINUE D137_NCM D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra = "";
                        $arrayDemo[] = array(
                            'order' => $regras['qtdeRegras'],
                            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."NCM do produto não foi específicada na regra</div>"
                        );
                        $err .= '4';
                        continue;
                    }
                }
                if ($regras['D137_Flag_Destino_Produto'] != "") {
                    $log .= "Entrou D137_Flag_Destino_Produto != '' D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra .= "O destino do produto é o mesmo da regra<br />";

                    if ($regras['D137_Flag_Destino_Produto'] != $Destino) {
                        
                        $log .= "Entrou CONTINUE D137_Flag_Destino_Produto: {$regras['D137_Flag_Destino_Produto']} != D137_Flag_Destino_Produto: {$Destino} D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra = "";
                        $arrayDemo[] = array(
                            'order' => $regras['qtdeRegras'],
                            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."O destino do produto é diferente da regra</div>"
                        );
                        $err .= '9';
                        continue;
                    }
                }

                if ($regras['D137_Flag_Origem_Pedido'] != "") {
                    $log .= "Entrou D137_Flag_Origem_Pedido != '' D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra .= "A origem do pedido é a mesma da regra<br />";

                    if ($regras['D137_Flag_Origem_Pedido'] != $D137_Origem_Pedido) {
                        
                        $log .= "Entrou CONTINUE D137_Flag_Origem_Pedido: {$regras['D137_Flag_Origem_Pedido']} != D137_Flag_Origem_Pedido: {$D137_Origem_Pedido} D137_Id='{$regras['D137_Id']}'<br />";
                        $demonstrativoRegra = "";
                        $arrayDemo[] = array(
                            'order' => $regras['qtdeRegras'],
                            'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."A origem do pedido é diferente da regra</div>"
                        );
                        $err .= '9';
                        continue;
                    }
                }
            }
            if ($regras['D137_D005_Id'] > 0) {
                $log .= "Entrou D137_D005_Id >0 D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "NCM do produto específicada na regra<br />";
                
                if ($regras['D137_D005_Id'] != $D137_D005_Id) {
                    $log .= "Entrou CONTINUE D137_D005_Id: {$regras['D137_D005_Id']} != D137_D005_Id: {$D137_D005_Id} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."NCM do produto diferente da regra</div>"
                    );
                    $err .= '9';
                    continue;
                }
            }

            if ($regras['D137_Origem_Estoque'] != ""){
                $log.="Entrou D137_Origem_Estoque != '' D137_Id='{$regras['D137_Id']}'<br />";
                $demonstrativoRegra .= "Origem do estoque específicada na regra<br />";

                if ($regras['D137_Origem_Estoque']!=$D137_Estoque_Origem){
                    $log.="Entrou CONTINUE D137_Origem_Estoque: {$regras['D137_Origem_Estoque']} != D137_Origem_Estoque: {$D137_Estoque_Origem} D137_Id='{$regras['D137_Id']}'<br />";
                    $demonstrativoRegra = "";
                    $arrayDemo[] = array(
                        'order' => $regras['qtdeRegras'],
                        'texto' => str_replace('green', 'red', $demonstrativoDadosRegra)."Origem do estoque diferente da regra</div>"
                    );
                    $err.='9';
                    continue;                   
                }
            }

            $arrayDemo[] = array(
                'D006_Id' => $regras['D137_D006_Id'],
                'order' => $regras['qtdeRegras']+100,
                'texto' => $demonstrativoDadosRegra.$demonstrativoRegra."</div>"
            );
            
            $log .= "Aplicou essa regra<br />";
            $validas[] = $regras['D137_Id'];
            $valida    = $regras['D137_D006_Id'];
        }
        $log = $log . " <br />TOTAL REGRAS ENCONTRADAS: " . count($validas);
        log($log);

        usort($arrayDemo, function($a, $b) {
            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? 1 : -1;
        });
                    
        
        if (count($validas) == 0) {
            if ($registro['D024_D006_Id'] > 0) {
                $valida = $registro['D024_D006_Id'];
            }
            $valida = false;
        } else if(count($validas) > 1){
            $qtde_regras = 0;
            $valida      = 0;
            for ($i = 0; $i < count($validas); $i++) {
                $D137  = mysql_query("SELECT D137_Quantidade_Regras('{$validas[$i]}') as qtde_regras FROM D137 limit 1");
                $mD137 = mysql_fetch_array($D137);
                if ($mD137['qtde_regras'] > $qtde_regras) {
                    $D137_1      = mysql_query("SELECT D137_D006_Id FROM D137 WHERE D137_Id='{$validas[$i]}'");
                    $mD137_1     = mysql_fetch_array($D137_1);
                    $qtde_regras = $mD137['qtde_regras'];
                    $valida      = $mD137_1['D137_D006_Id'];
                }
            }
        }
    
        foreach($arrayDemo as $key => $value){
            if(!empty($value['D006_Id']) && $value['D006_Id'] == $valida){
                array_unshift($arrayDemo, $arrayDemo[$key]);
                unset($arrayDemo[$key+1]);
            }
        }
        $demonstrativo = "<script>$(function() {\$(\"#demonstrativoCFOP\").accordion({collapsible: true, autoHeight: false, icons: false}); });</script><div id=\"demonstrativoCFOP\">";
        foreach ($arrayDemo as $key => $value) {
            $demonstrativo .= $value['texto'];
        }
    
        $demonstrativo .= "</div><br /><b>TOTAL REGRAS VÁLIDAS: " . count($validas) . "</b>";
        $demonstrativo .= "</div><br /><b>ID CFOP UTILIZADO: " . $valida . "</b>";
        $demonstrativo .= "<b style=\"float: right\">".date("d/m/Y H:i:s")."</b><br />";
        if($retornaLog == true){
            return $demonstrativo;
        } else {
            return $valida;
        }
    }

  /**
     * retornaltimoPrecoVenda
     *
     * @param String $D024_Id
     * @param String $D009_Id
     * @return String
     */
    public function xxxretornaltimoPrecoVenda($D024_Id, $D009_Id, $T003_Id = 0, $T005_Id = 0)
    {
        global $g;

		return "Histórico desativado";
        
    }

    public function triggerWherePesquisaGrid($pesquisa_item) {
        global $g;

        // Essa função substitui a clausula where montada no pesquisaGrid na busca de produtos em orçamentos e pedidos
        // Wheres fixos que são montados no pesquisaGrid
        // Vendas01 = "WHERE (REPLACE(REPLACE(D001_Codigo_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-','') OR REPLACE(REPLACE(D083_Codigo_Produto_Fornecedor,' ',''),'-','') LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-',''))";
        // Vendas02 = "WHERE ((REPLACE(REPLACE(D001_Codigo_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ',''),'-','') AND REPLACE(REPLACE(D001_Codigo_Produto,' ',''),'-','') NOT LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-','')) OR (REPLACE(REPLACE(D083_Codigo_Produto_Fornecedor,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ',''),'-','') AND REPLACE(REPLACE(D083_Codigo_Produto_Fornecedor,' ',''),'-','') NOT LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-','')))";
        // Vendas03 = "WHERE (REPLACE(REPLACE(D001_Descricao_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-','') OR REPLACE(REPLACE(D001_Descricao_Ingles,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-','') OR REPLACE(REPLACE(D082_Marca,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-',''))";
        // Vendas04 = "WHERE D001_Codigo_Barras = '{$pesquisa_item}'";

        $caseVendas = array();
        $caseVendas['vendas01'] = '';
        $caseVendas['vendas02'] = '';
        $caseVendas['vendas03'] = "WHERE (REPLACE(REPLACE(D001_Descricao_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-','') OR REPLACE(REPLACE(D001_Descricao_Ingles,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-','') OR REPLACE(REPLACE(D082_Marca,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ','%'),'-','') OR REPLACE(REPLACE(D001A_Apelido_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('%{$pesquisa_item}%',' ',''),'-','') OR REPLACE(REPLACE(D001A_Codigo_IMPA,' ',''),'-','') LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-','') OR REPLACE(REPLACE(D001_Codigo_Produto,' ',''),'-','') LIKE REPLACE(REPLACE('{$pesquisa_item}%',' ',''),'-',''))";
        $caseVendas['vendas04'] = '';

        return $caseVendas;
    }

        /**
     * montarEmail
     *
     * @param String $T003_Id
     * @return Array
     */
    public function montarEmail($T003_Id)
    {
        global $g, $confUsuario;
        $T003_Id = mysql_real_escape_string($T003_Id);
        $sql     = "SELECT * ,
                          T003_Prazos(T003_Id) formaPagamento, 
                          T003_Prazos_Portador(T003_Id) formaPagamentoPortador
                     FROM T003 
                LEFT JOIN D013 ON D013_Id=T003_D013_Id 
                LEFT JOIN D024 ON D024_Id=T003_D024_Id 
                LEFT JOIN D020 ON D020_Id=D024_D020_Id
                LEFT JOIN D018 ON D018_Id=D024_D018_Id 
                LEFT JOIN T003A ON T003_Id = T003A_T003_Id
                    WHERE T003_Id='{$T003_Id}'";
        $T003    = mysql_query($sql);
        $T003    = mysql_fetch_array($T003);
        
        $sql = <<<EOT
            SELECT * FROM T004 
            LEFT JOIN D009 ON D009_Id = T004_D009_Id 
            LEFT JOIN D049 ON D049_Id = D009_D049_Id
            LEFT JOIN D001 ON D001_Id = D049_D001_Id
            LEFT JOIN T144 ON T144_D001_Id = D001_Id
            LEFT JOIN D037 ON D037_Id=T004_D037_Id
            LEFT JOIN D005 ON D005_Id=T004_D005_Id
            LEFT JOIN T004A ON T004_Id=T004A_T004_Id
            WHERE T004_T003_Id = '{$T003_Id}' 
            GROUP BY T004_Id
            ORDER BY T004_Item_Pedido_Compra,T004_Id
EOT;
        
        $T004_sql = mysql_query($sql);
        
        $sql  = "SELECT D024_E_Mail FROM D024 WHERE D024_Id='{$T003['T003_D024_Id']}'";
        $D024 = mysql_query($sql);
        $D024 = mysql_fetch_array($D024);
        
        $sql  = "SELECT D013_Nome_Contato, D013_Telefone_1, D013_DDD_Telefone_1 FROM D013 WHERE D013_Id='{$T003['T003_D013_Id']}'";
        $D013 = mysql_query($sql);
        $D013 = mysql_fetch_array($D013);
        
        $array = array();
        
        $array['Para']    = (!empty($T003['D013_E_Mail'])) ? $T003['D013_E_Mail'] : $D024['D024_E_Mail'];
        $array['Assunto'] = "Orçamento Nº[{$T003_Id}]";
        
        $tabela = 'font-size: 12px;';
        $tabela .= 'margin: 10px 25px 10px 0;';
        $tabela .= 'width: 800px;';
        $tabela .= 'text-align: left;';
        $tabela .= 'border:1px;';
        
        $th = 'font-size: 13px;';
        $th .= 'font-weight: bold;';
        $th .= 'color: black;';
        $th .= 'background: #4F4D4D;';
        $th .= 'padding: 4px 4px;';
        $th .= 'border-bottom: 0px solid #6678b1;';
        
        $td = 'border-bottom: 0px solid #ccc;';
        $td .= 'color: #000000;';
        $td .= 'background: #fff;';
        $td .= 'padding: 4px 2px 4px 2px;';
        
        if (!empty($g['c004']['C004_Logo_Marca'])) {
            $pathinfo      = pathinfo($g['c004']['C004_Logo_Marca']);
            $caminhoImagem = "{$confUsuario['urlRaiz']}{$g['pathWeb']}{$g['pathDados']}imagens/logo/{$g['empresaAtual']}.{$pathinfo['extension']}";
        } else {
            $caminhoImagem = "";
        }
        if (!empty($T003['D024_Cnpj'])) {
            $cpfCnpj = $T003['D024_Cnpj'];
        } else {
            $cpfCnpj = $T003['D024_Cpf'];
        }
        $tel = "";
        if (!empty($D013['D013_Telefone_1'])) {
            $tel = '(' . $D013['D013_DDD_Telefone_1'] . ') ' . $D013['D013_Telefone_1'];
        } else if (!empty($T003['D024_Telefone_1'])) {
            $tel = '(' . $T003['D024_DDD_Telefone_1'] . ') ' . $T003['D024_Telefone_1'];
        }
        $T003['D024_Nome_Empresa'] = substr($T003['D024_Nome_Empresa'], 0, 60);
        $T003_Data_Emissao         = gcorrigeData($T003['T003_Data_Emissao']);
        
        // TABELA DO CABEÇALHO DO LAYOUT
        $corpo = <<<EOT
            <div style="margin-left:25px;margin-top:10px;font-family:Sans-Serif;font-size:14px;color:black">
                <table width=800px border=0>
                    <tr>
                        <td width=10px><img src="{$caminhoImagem}" style="margin-left:10px"></td>
                        <td style='padding: 0px 0px 0px 30px'>
                            <font style='font-size:11px;font-weight:normal'>
                                <b>{$T003['D024_Id']} - {$T003['D024_Nome_Empresa']}</b><br />
                                CNPJ: $cpfCnpj<br />
                                <b>Att: {$D013['D013_Nome_Contato']} - {$tel}</b><br />
                                {$T003['D024_Endereco']} - {$T003['D024_Numero']} <br />
                                {$T003['D024_Bairro']} - {$T003['D024_Cep']} - {$T003['D020_Nome_Cidade']} - {$T003['D018_UF']}
                            </font>
                        </td>
                        <td width=130px align=center>
                            ORÇAMENTO<br>Nº {$T003['T003_Id']} <br> {$T003_Data_Emissao}
                        </td>
                    </tr>
                </table>
EOT;
                /* <table style="{$tabela}" bgcolor=gray cellspacing=1px>
                    <tbody>
                        <tr>
                            <td style="{$th}" rowspan=2>Item</td>
                            <td style="{$th}" rowspan=2>Código</td>
                            <td style="{$th}" rowspan=2>Descrição</td>
                            <td style="{$th}" align="center" width=30 rowspan=2>Und</td>
                            <td style="{$th}" rowspan=2 align="center">NCM</td>
                            <td style="{$th}" align="right" width=30 rowspan=2>Qte</td>
                            <td style="{$th}" align="right" width=80 rowspan=2>Unitário R\$</td>
                            <td style="{$th}" align="right" width=60 rowspan=2>Total R\$</td>
                            <td style="{$th}" align="center" width=30 rowspan=2>IPI</td>
                            <td style="{$th}" align="center" width=30 rowspan=2>ICM</td>
                            <td style="{$th}" align="right" width=30 rowspan=2>ST</td>
                            <td style="{$th}" rowspan=2>Link</td>
                        </tr>
                        <tr>
                        </tr>
EOT;
        // LOOPING PARA MONTAR A TABELA DOS PRODUTOS
        $count = 1;
        while ($T004 = mysql_fetch_array($T004_sql)) {
            if (!empty($T004['T004_Codigo_Substituto']))
                $codigo = $T004['T004_Codigo_Substituto'];
            else {
                $codigo = $T004['T004_Codigo_Produto'];
            }
            $T004_Quantidade               = gCorrigeNumero($T004['T004_Quantidade']);
            //$somaT004_Valor_Preco_Unitario = gCorrigeNumero($T004['T004_Valor_Preco_Unitario'] + ($T004['T004_Valor_IPI'] / $T004['T004_Quantidade']));
            $somaT004_Valor_Preco_Unitario = gCorrigeNumero($T004['T004_Valor_Preco_Unitario']);
            //$somaT004_Valor_Total_Preco    = gCorrigeNumero($T004['T004_Valor_Total_Preco'] + $T004['T004_Valor_IPI']);
            $somaT004_Valor_Total_Preco    = gCorrigeNumero($T004['T004_Valor_Total_Preco']);
            $T004_Aliquota_IPIFormatado    = number_format($T004['T004_Aliquota_IPI'], 0);
            $T004_Aliquota_ICMSFormatado   = number_format($T004['T004_Aliquota_ICMS'], 0);
            $T004_Valor_ST                 = gCorrigeNumero($T004['T004_Valor_ST'] + $T004['T004A_Valor_FCP_ST']);

            $corpo .= <<<EOT
                        <tr>
                            <td style="{$td}" align="center">{$count}</td>
                            <td style="{$td}" align="center">{$codigo}</td>
                            <td style="{$td};font-weight:bold" width=350px>
                                {$T004['T004_Descricao_Produto']}<br />
                                <span style="font-weight:normal; font-size:11px; font-style:italic;">
                                    {$T004['T004_Observacao']}
                                </span>
                            </td>
                            <td style="{$td}" align="center";">
                                {$T004['D037_Unidade']}
                            </td>
                            <td style="{$td}"; font-size:11px;" align="right">
                                {$T004['D005_Classificacao_Fiscal']}
                             </td>
                            <td style="{$td}" align="right";">
                                {$T004_Quantidade}
                            </td>
                            <td style="{$td};font-weight:bold" align="right";">
                                {$somaT004_Valor_Preco_Unitario}
                            </td>
                            <td style="{$td};font-weight:bold" align="right";">
                                {$somaT004_Valor_Total_Preco}
                            </td>
                            <td style="{$td}" align="center";font-size:12px;font-weight:normal;">
                                {$T004_Aliquota_IPIFormatado}%
                            </td>
                            <td style="{$td}" align="center";font-size:12px">
                                {$T004_Aliquota_ICMSFormatado}%
                            </td>
                            <td style="{$td}" align="right";font-size:12px">
                                {$T004_Valor_ST}
                            </td>
                            <td style="{$td}";padding:0px 2px 0px 2px" width=80px>
EOT;
            if (!empty($T004["T144_Url"])) {
                $T144Url = $T004["T144_Url"];
                $corpo .= <<<EOT
                                <a href={$T144Url} target=_blank style="text-decoration:none;font-weight:bold;color:#000"><u>Ver Produto e documentos</u></a>
EOT;
            }
            $corpo .= <<<EOT

                            </td>
                        </tr>
EOT;
            $count = $count + 1;
        }
        $corpo .= <<<EOT
                    <tbody>
                </table> */
                

                $sql = <<<EOT
					 SELECT * FROM T004 
					LEFT JOIN D009 ON D009_Id=T004_D009_Id 
					LEFT JOIN D049 ON D049_Id=D009_D049_Id
					LEFT JOIN D001 ON D001_Id=D049_D001_Id
					LEFT JOIN T144 ON T144_D001_Id=D001_Id AND T144_Flag_Tipo != 'F'
		            LEFT JOIN D037 ON D037_Id=T004_D037_Id
		            LEFT JOIN D005 ON D005_Id=T004_D005_Id
					LEFT JOIN T004A ON T004_Id=T004A_T004_Id
					LEFT JOIN T066 ON T066_Id=T004A_T066_Id
					LEFT JOIN T066A ON T066_Id=T066A_T066_Id
					LEFT JOIN D001A ON D001_Id=D001A_D001_Id
					LEFT JOIN D006 ON D006_Id=T004_D006_Id
					WHERE T004_T003_Id = '{$T003_Id}' 
					GROUP BY T004_Id
					ORDER BY T004_Item
EOT;
            $T004_sql = mysql_query($sql);	
                
    	/**
				* PDF - TABELA PRODUTOS
				*/
				$corpo .= <<<EOT
				
					<table style="{$tabela}" bgcolor="#adadad" cellspacing=1px>
						<tr>
							<th tyle="{$th}" align="center" width="3%">It</th>
							<th tyle="{$th}" align="center" width="5%">Código</th>
							<th tyle="{$th}" align="center" width="40%">Descrição</th>
							<th tyle="{$th}" align="center" width="5%">Quant</th>
							<th tyle="{$th}" align="center" width="3%">Und</th>
							<th tyle="{$th}" align="center" width="7%">NCM</th>
							<th tyle="{$th}" align="center" width="3%">CFOP</th>
EOT;
			//Verifica se existe algum produto com ST
			$sqlVerificaST = mysql_query("SELECT * FROM T004 WHERE T004_T003_Id = '{$T003_Id}' AND T004_Flag_ST='S' GROUP BY T004_Id");
			if(mysql_num_rows($sqlVerificaST) > 999999){
				$corpo .= <<<EOT
							<th tyle="{$th}" align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font> IPI R\$</th>
							<th tyle="{$th}" align="center" width="4%">ICM %</th>
							<th tyle="{$th}" align="center" width="3%">IPI</th>
							<th tyle="{$th}" align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font> IPI R\$</th>
							<th tyle="{$th}" align="center" width="8%">Valor<br>ST R$</th>
							<th tyle="{$th}" align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI R\$</th>
EOT;
			}else{
				$corpo .= <<<EOT
							<th tyle="{$th}" align="center" width="8%">Unit <font style="color:blue;"><b>sem</b></font><br> IPI e ST R\$</th>
							<th tyle="{$th}" align="center" width="4%">ICM %</th>
							<th tyle="{$th}" align="center" width="3%">IPI %</th>
							<th tyle="{$th}" align="center" width="8%">Unit <font style="color:blue;"><b>com</b></font><br> IPI e ST R\$</th>
							<th tyle="{$th}" align="center" width="8%">Valor<br>ST R$</th>
							<th tyle="{$th}" align="center" width="8%">Valor<br>FCP ST R$</th>
							<th tyle="{$th}" align="center" width="8%">Total <font style="color:blue;"><b>com</b></font> IPI e ST R\$</th>
EOT;
			}
				$corpo .= <<<EOT
							<th tyle="{$th}" width="8%">Link</th>
						</tr>
EOT;
				$count = 1;
				while($T004 = mysql_fetch_array($T004_sql)){
					//$codigo = !empty($T004['T004_Codigo_Substituto']) ? $T004['T004_Codigo_Substituto'] : $T004['T004_Codigo_Produto'];
					$codigo = (!empty($T004['T004_Codigo_Substituto'])) ? "<b>".$T004['T004_Codigo_Substituto']."</b><br>". $T004['T004_Codigo_Produto'] : $T004['T004_Codigo_Produto'];
					$qtd = gCorrigeNumero($T004['T004_Quantidade']);
					$unitario = gCorrigeNumero($T004['T004_Valor_Preco_Unitario']);
					$valorST = gCorrigeNumero($T004['T004_Valor_ST']);
					$valorFCPST = gCorrigeNumero($T004['T004A_Valor_FCP_ST']);
					$total = $T004['T004_Valor_Total_Preco']+$T004['T004_Valor_IPI']+$T004['T004_Valor_ST'] + $T004['T004A_Valor_FCP_ST'];
                    $unitarioComIPIeST = gCorrigeNumero($total/round($T004['T004_Quantidade'],0));
                    $total = gCorrigeNumero($total);
					$ipi = gCorrigeNumero($T004['T004_Aliquota_IPI']);
					$pis = number_format($T004['T004_Aliquota_PIS'],2,',','.');
					$cofins = number_format($T004['T004_Aliquota_COFINS'],2,',','.');
					$AliqPis = $T004['T004_Aliquota_PIS'];
					$AliqCofins = $T004['T004_Aliquota_COFINS'];
					$icms = number_format($T004['T004_Aliquota_ICMS'],0);
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
					}
					$dataValidade = gCorrigeData($T004['D049_Data_Validade']);
                    if(!empty($dataValidade) && ($dataValidade!="null")){
                        $dataValidade = " Validade: ".$dataValidade;
                    }
					$dataValidadeLocacao = gCorrigeData($T004['T066A_Data_Validade']);
                    if(!empty($dataValidadeLocacao) && ($dataValidadeLocacao!="null")){						
                        $dataValidade = " Validade: ".$dataValidadeLocacao;
                    }

					$codigoIMPA = $T004['D001A_Codigo_IMPA'];
					if(!empty($codigoIMPA)){
						$codigoIMPA = (!empty($dataValidade)) ? " - IMPA: ".$T004['D001A_Codigo_IMPA'] : " IMPA: ".$T004['D001A_Codigo_IMPA'];						
					}

                    $pesoEstimado += ($T004['D001_Peso_Unitario_Kg']*$T004['T004_Quantidade']);
                    $cubagemEstimado += ($T004['D001_Cubagem_Unitaria']*$T004['T004_Quantidade']);;
					$link = '';
					if ($T004["T144_Url"]) {
						$T004["T144_Url"] = strpos($T004["T144_Url"], 'http') !== false ? $T004["T144_Url"] : 'http://' . $T004["T144_Url"];
						log("qual a URL ".$T004["T144_Url"]);
						$link = "<a href=\"{$T004["T144_Url"]}\">ver produto e documentos</a>";
						log($link);
					}

                    $observacaoItem = '';
                    if (!empty($T004['T004_Observacao'])) {
                        $observacaoItem = '<font style="background:#FCF9AE"><i>' . $T004['T004_Observacao'] . '</i></font><br>';
                    }
					//if (!empty($T004["T144_Url"]))
					//	$corpo.='<a href="'.$T004["T144_Url"].'" target=_blank style="text-decoration:none;font-weight:bold;color:#000">Ver Produto e documentos</a>';
					$T004['D001_Especificacoes'] = ($T004['D001_Especificacoes'] != '') ? "<br /><i>".$T004['D001_Especificacoes']."</i>" : '';
					log("Especificacoes: ".$T004['D001_Especificacoes']);

                    $desc = ($descIngles && !empty($T004['D001_Descricao_Ingles'])) ? "<br><i>{$T004['D001_Descricao_Ingles']}</i><br>" : '';

					$corpo .= <<<EOT
					<tr>
						<td style="{$td}">
							{$T004['T004_Item']}
						</td>
						<td style="{$td}">
							{$codigo}
						</td>
						<td style="{$td} font-size:10px;">
							<b>{$T004['T004_Descricao_Produto']}</b><br/> {$desc} <b> <i>{$observacaoItem}</i>{$T004['D001_Especificacoes']}<br>{$dataValidade} {$codigoIMPA}</b>
						</td>
						<td style="{$td}">
							<b>{$qtd}</b>
						</td>
						<td style="{$td}">
							{$T004['D037_Unidade']}
						</td>
						<td style="{$td}">
							{$T004['D005_Classificacao_Fiscal']}
						</td>
						<td style="{$td}">
							{$T004['D006_Codigo_CFOP']}
						</td>
						<td style="{$td}">
							<b>{$unitario}</b>
						</td>
						<td style="{$td}">
							{$icms}
						</td>
						<td style="{$td}">
							{$ipi}
						</td>
						<td style="{$td}">
							<b>{$unitarioComIPIeST}</b>
						</td> 
						<td style="{$td}">
							{$valorST}
						</td>
						<td style="{$td}">
							{$valorFCPST}
						</td>
						<td style="{$td}">
							<b>{$total}</b>
						</td>
						<td style="{$td}" align="center">
							{$link}
						</td>
					</tr>
EOT;
					$count = $count + 1;
				}
				$corpo .= '</table>';
                
    $corpo .= <<<EOT
            </div>
EOT;
        $sql                 = <<<EOT
        SELECT * FROM T145 
        WHERE T145_T003_Id='{$T003_Id}'
EOT;
        $result              = mysql_query($sql);
        $valorTotalOrcamento = $T003['T003_Valor_Total'];
        // CASO TENHA SERVIÇOS, MONTA A TABELA DOS SERVIÇOS DO ORÇAMENTO
        if (mysql_num_rows($result) > 0) {
            $valorTotal = 0;
            $corpo .= <<<EOT
            <div style="border:2px solid #E0EEEE; padding-top:10px;padding-bottom:10px; margin-left:22px;">
                <div style="margin-left:25px;font-family:Sans-Serif;font-weight:bold"><br /><br />Seguem abaixo os serviços do pedido</div>
                <table style="{$tabela}">
                    <tbody>
                        <tr>
                            <th style="{$th}">Código</th>
                            <td style="{$th}">Descrição</td>
                            <td style="{$th}" align="right">Qte</td>
                            <td style="{$th}" align="right">Preço</td>
                        </tr>
EOT;
            while ($T145 = mysql_fetch_array($result)) {
                $T145_Valor_Unitario = gCorrigeNumero($T145['T145_Valor_Unitario']);
                $corpo .= <<<EOT
                        <tr>
                            <td style="{$td}">
                                {$T145['T145_Id']}
                            </td>
                            <td style="{$td}">
                                {$T145['T145_Titulo']} - {$T145['T145_Descricao']}
                            </td>
                            <td style="{$td} text-align:right;">
                                {$T145['T145_Quantidade']}
                            </td>
                            <td style="{$td}text-align:right;">
                                {$T145_Valor_Unitario}
                            </td>
                        </tr>
EOT;
                $valorTotal += ($T145['T145_Quantidade'] * $T145['T145_Valor_Unitario']);
            }
            $valorTotal = gCorrigeNumero($valorTotal);
            $corpo .= <<<EOT
                    </tbody>
                </table>
                <div style="margin-left:25px;font-family:Sans-Serif;"><b>Valor total dos Serviços: </b> '$valorTotal'</div>
EOT;
            $valorTotalOrcamento += $valorTotal;
        }
        $T003_Valor_Total_Produtos = gCorrigeNumero($T003['T003_Valor_Total_Produtos']);
        $T003_Valor_Total_IPI      = gCorrigeNumero($T003['T003_Valor_Total_IPI']);
        $T003_Valor_ST             = gCorrigeNumero($T003['T003_Valor_ST'] + $T003['T003A_Valor_FCP_ST']);
        $T003_Valor_Frete          = gCorrigeNumero($T003['T003_Valor_Frete']);
        $valorTotalOrcamento       = gCorrigeNumero($valorTotalOrcamento);

        $tiposFrete = array('0' => 'Por conta do emitente',
                            '1' => 'Por conta do destinatário',
                            '2' => 'Terceiros',
                            '9' => 'Sem frete');
        $T003_Frete = $tiposFrete["{$T003['T003_Flag_Frete']}"];

        if (empty($T003_Frete)) {
            $T003_Frete = "Não informado";
        }

        
        //MONTA A TABELA COM OS TOTALIZADORES DO ORÇAMENTO
        $corpo .= <<<EOT
                <div style="margin-left:25px">
                    <table border=0 style="font-family:Sans-Serif;color:black;font-size:12px">
                        <tr>
                            <td>
                                Prazo de Entrega: 
                            </td>
                            <td style="font-weight:bold" colspan=2 width=290px>
                                {$T003['T003_Prazo_Entrega']}
                            </td>
                            <td>
                                Valor total Produtos: 
                            </td>
                            <td style="font-weight:bold" align="right" width=90px>
                                R\$ {$T003_Valor_Total_Produtos}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Frete: 
                            </td>
                            <td style="font-weight:bold" colspan=2>
                                {$T003_Frete}
                            </td>
                            <td>
                                Valor total IPI: 
                            </td>
                            <td style="font-weight:bold" align="right">
                                R\$ {$T003_Valor_Total_IPI}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Forma pagamento: 
                            </td>
                            <td style="font-weight:bold" colspan=2>
                                {$T003['formaPagamentoPortador']}
                            </td>
                            <td>
                                Valor total ST + FCP ST: 
                            </td>
                            <td style="font-weight:bold;" align="right">
                                R\$ {$T003_Valor_ST}
                            </td>

                        </tr>
                        <tr>
                            <td>
                                Prazo pagamento: 
                            </td>
                            <td style="font-weight:bold" colspan=2>
                                {$T003['formaPagamento']}
                            </td>
                            <td>
                                Valor Total Frete: 
                            </td>
                            <td style="font-weight:bold;" align="right">
                                R\$ {$T003_Valor_Frete}
                            </td>
                        <tr>
                        <tr>
                            <td>
                                Validade proposta: 
                            </td>
                            <td style="font-weight:bold" colspan=2>
                                {$T003['T003_Validade_Proposta']}
                            </td>
                            <td>
                                Valor total Orçamento: 
                            </td>
                            <td style="font-weight:bold;font-size:14px;" align="right">
                                R\$ {$valorTotalOrcamento}
                            </td>
                        <tr>
                            <td colspan=3> </td>
                        </tr>
EOT;
        if (!empty($T003["T003_Observacao"])) {
            $T003_Observacao = str_replace(chr(10), '<br>', $T003['T003_Observacao']);
            $corpo .= <<<EOT
                        <tr>
                            <td valign=top>
                                Observação: 
                            </td>
                            <td style="font-weight:bold" colspan=5>
                                {$T003_Observacao}
                            </td>
                        </tr>
EOT;
        }
        $corpo .= <<<EOT
                    </table>
                </div>
EOT;
        
        $array['Corpo']                 = $corpo;
        $array['D024_Id']               = $T003['T003_D024_Id'];
        $array['T139_Valor_Total']      = $T003['T003_Valor_Total'];
        $sql                            = "SELECT COUNT(*) FROM T004 WHERE T004_T003_Id='{$T003_Id}'";
        $registros                      = mysql_query($sql);
        $registros                      = mysql_fetch_array($registros);
        $array['T139_Numero_Registros'] = $registros[0];
        
        return $array;
    }
    
    // Miguel - 01/07/2026
    // Personalizado para trocar usuario do pedido
    public function gerarPedido($T003_Id) {
        global $g, $T005_Id;
        $T003_Id = (isset($T003_Id)) ? $T003_Id : false;

        //$T003_D024_Id;
        //$T003_Data_Emissao;
        // inserir um novo registro na T005 levando: T005_T003_Id, e os dois acima
        if ($T003_Id) {
            mysql_query("SET AUTOCOMMIT=0");
            mysql_query("START TRANSACTION");

            // Verificando se os T006 vinculados ao T004 possuem um T005
            $T004=mysql_query("SELECT T006_Id,
                                      T006_T005_Id,
                                      T006_T004_Id 
                                 FROM T004 
                            LEFT JOIN T006 ON T006_T004_Id=T004_Id 
                                WHERE T004_T003_Id ='{$T003_Id}'");

            while ($mT004 = mysql_fetch_array($T004)) {
                if($mT004['T006_Id'] > 0){
                    $T005=mysql_query("SELECT T005_Id 
                                         FROM T005 
                                        WHERE T005_Id='{$mT004['T006_T005_Id']}'");

                    $obs="T006_T004_Id: ".$mT004['T006_T004_Id'];
                    if(mysql_num_rows($T005)==0){
                        mysql_query("UPDATE T006 set T006_Observacao='{$obs}', T006_T004_Id='' WHERE T006_Id='{$mT004['T006_Id']}'");
                    }
                }
            }
            
            $sql2   = "SELECT SUM(T004_Quantidade_Confirmada) FROM T004 LEFT JOIN T006 ON T006_T004_Id=T004_Id WHERE T004_T003_Id ='$T003_Id' AND T006_T004_Id IS NULL";
            $array2 = mysql_query($sql2);
            $linha  = mysql_fetch_array($array2);

            $sql3   = "SELECT SUM(T145_Quantidade_Confirmada) FROM T145 LEFT JOIN T147 ON T147_T145_Id=T145_Id WHERE T145_T003_Id ='$T003_Id' AND T147_T145_Id IS NULL";
            $array3 = mysql_query($sql3);
            $linha2 = mysql_fetch_array($array3);

            $sqlT138    = "SELECT T138_Id FROM T138 WHERE T138_T003_Id='{$T003_Id}' AND T138_T005_Id IS NULL";
            $resultT138 = mysql_query($sqlT138);
            $linhaT138  = mysql_num_rows($resultT138);

            if ($linha[0] <= 0 && $linha2[0] <= 0 && $linhaT138 <= 0) {
                return "Ã‰ necessÃ¡rio confirmar a quantidade de pelo menos um item, serviÃ§o ou troca.";
            }

            $T089  = mysql_query("SELECT SUM(T089_Valor_Parcela) as totalParcelas, 
                                         T003_Valor_Total, 
                                         T003_Flag_Acp 
                                    FROM T003 
                               LEFT JOIN T089 ON T089_T003_Id = T003_Id 
                                   WHERE T003_Id='{$T003_Id}'");

            $mT089 = mysql_fetch_array($T089);

            $obrigatorioGerarParcelaOrcamento = $g['C031']['obrigatorioGerarParcelaOrcamento'];
            if ($mT089['T003_Flag_Acp'] != 4 && number_format($mT089['totalParcelas'], 2) != number_format($mT089['T003_Valor_Total'], 2) && $obrigatorioGerarParcelaOrcamento == 'S') {
                return "O valor total das parcelas nÃ£o confere com o total do orÃ§amento, verifique a forma de pagamento.";
            }

            // Verifica se o banco vinculado aos portadores da forma de pagamento estÃ¡ ativo
            $T089 = mysql_query("SELECT IFNULL(D007_Flag_Ativo,0) as D007_Flag_Ativo, 
                                        IFNULL(D027_D007_Id,0) as D027_D007_Id,D027_Portador 
                                   FROM T089
                              LEFT JOIN D027 ON D027_Id=T089_D027_Id
                              LEFT JOIN D007 ON D007_Id=D027_D007_Id
                                  WHERE T089_T003_Id='{$T003_Id}'");

            while($mT089=mysql_fetch_array($T089)){
                if($mT089['D027_D007_Id'] > 0 && $mT089['D007_Flag_Ativo'] != 'S'){
                    return "O portador \"".$mT089['D027_Portador']."\" estÃ¡ com a conta bancÃ¡ria inativa. Por favor verifique com o setor financeiro";
                }
            }

            //Verifica se hÃ¡ itens do orÃ§amento sem CFOP, NCM ou com origem em branco
            $retornoRestricao = $this->verificaDadosOrcamento($T003_Id);
            if($retornoRestricao !== true){
                return $retornoRestricao;
            }

            $permitirVenda = $g['C031']['permitirVendaSemEstoque'];
            $permitirVenda = (!empty($permitirVenda) ? $permitirVenda : 'N');

            $sqlEstoque  = mysql_query("SELECT T004_Id 
                                          FROM T004 
                                     LEFT JOIN T055 ON T055_D009_Id=T004_D009_Id AND T055_T075_Id=T004_T075_Id
                                     LEFT JOIN D009 ON D009_Id=T004_D009_Id 
                                     LEFT JOIN T006 ON T006_T004_Id=T004_Id 
                                     left join D006 on D006_Id=T004_D006_Id
                                         WHERE T004_T003_Id='{$T003_Id}' 
                                           AND (T004_Quantidade_Confirmada>IF(IFNULL(T004_T075_Id,0)>0,IFNULL(T055_Quantidade,0),IF(IFNULL(T004_T206_Id,0)>0,D009_Quantidade_DF(T004_D009_Id,T004_T206_Id, 0),IFNULL(D009_Quantidade_Estoque_Liquido,0))))
                                           AND T006_Id IS NULL 
                                           AND '{$permitirVenda}'='N'
                                           AND D006_Flag_Estoque ='D'");

            $rowsEstoque = mysql_num_rows($sqlEstoque);
            if ($rowsEstoque > 0) {
                $msg = ($rowsEstoque > 1 ? "HÃ¡ itens sem estoque suficiente, favor verificar" : "Existe item sem estoque suficiente, favor verificar");
                return $msg;
            }

            $sqlEstoque  = mysql_query("SELECT T004_Id 
                                          FROM T004 
                                     LEFT JOIN D009 ON D009_Id=T004_D009_Id 
                                     LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                     LEFT JOIN D001 ON D001_Id=D049_D001_Id
                                     LEFT JOIN T006 ON T006_T004_Id=T004_Id 
                                         WHERE T004_T003_Id='{$T003_Id}' 
                                           AND D001_Flag_Pre_Cadastro='S'
                                           AND (T004_T219_Id IS NULL OR T004_T219_Id <= 0)
                                           AND T004_Valor_Custo_Unitario <= 0
                                           AND T006_Id IS NULL
                                           AND T004_Quantidade_Confirmada>0");

            $rowsEstoque = mysql_num_rows($sqlEstoque);
            if ($rowsEstoque > 0 && $g['C031']['permitePedidoProdutoPreCadastro'] == 'N') {
                $msg = ($rowsEstoque > 1 ? "HÃ¡ itens PRÃ‰-CADASTRO que nÃ£o possuem retorno de cotaÃ§Ã£o de compra, favor verificar" : "Existe item PRÃ‰-CADASTRO que nÃ£o possui retorno de cotaÃ§Ã£o de compra, favor verificar");
                return $msg;
            }

            // 22/01/2025 - Felipe Carrano
            // ValidaÃ§Ã£o por seguranÃ§a porque em alguns casos estava zerando o T004_Percentual_Desconto e ajustando o T004_Valor_Preco_Unitario
            // Ainda nÃ£o foi encontrado o problema
            $validaDesconto = $this->validaDescontoAplicado($T003_Id);
            if($validaDesconto !== true){
                return  $validaDesconto;
            }

            // Utilizado no APP  SOLICITA ATUALIZACAO PREÃ‡O COTAÃ‡ÃƒO NO ORÃ‡AMENTO
            $validaPrazoPreco = $this->validaPrazoPrecoCotacao($T003_Id);
            if($validaPrazoPreco !== true){
                return  $validaPrazoPreco;
            }

            $sql = "SELECT T003_Flag_Frete,
                           T003_D013_Id,
                           T003_D024_Id,
                           T003_D021_Id,
                           T003_C004_Id,
                           T003_D006_Id,
                           T003_C007_Id,
                           T003_D022_Id,
                           D020_D022_Id,
                           T003_D116_Id,
                           T003_D036_Id,
                           T003_T134_Id,
                           T003_D027_Id,
                           T003_C004_Id_Estoque,
                           T003_D148_Id_Entrega,
                           T003_D148_Id_Cobranca,
                           T003A_D148_Id_Redespacho,
                           T003_C007_Id_Vendedor_Interno,
                           T003_C007_Id_Vendedor_Externo,
                           D024_Flag_Transporta_Diferentes_UF,
                           T003_Flag_Status_Liberacao,
                           T003_Ordem_Compra,
                           T003_Observacao,
                           T003_Observacao_Comercial,
                           T003_Flag_Acp,
                           D024_Flag_Revenda_Consumidor,
                           T003_Observacao_Pagamento,
                           T003_Pgto_Parcelas,
                           T003_Pgto_Primeiro_Vcto_Tipo,
                           T003_Pgto_Primeiro_Vcto_Valor,
                           T003_Pgto_Dias_Vcto_Tipo,
                           T003_Pgto_Dias_Vcto_Valor,
                           T003_Pgto_Portador,
                           T003_Pgto_Data,
                           T003_Pgto_C007_Id,
                           T003_Liberou_Alcada_C007_Id,
                           T003_Liberou_Alcada_Data,
                           T003_Liberou_Alcada_IPV,
                           T003_Liberou_Alcada_Valor_Total,
                           T003_Liberou_Alcada_Prazo_Medio,
                           T003_Liberou_Alcada_Desconto,
                           T003_ST_ICMS_Interno,
                           T003_ICMS_Inter_Estadual,
                           T003_Flag_Lista_Preco,
                           T003_Valor_Frete,
                           T003_Valor_Frete_Calculado,
                           T003_Flag_Expedicao,
                           T003_Percentual_Comissao,
                           T003_Valor_Total_Comissao,
                           T003_Percentual_Comissao_Representante,
                           T003_Valor_Comissao_Representante,
                           T003_Percentual_Comissao_Externo,
                           T003_Valor_Total_Comissao_Externo,
                           T003_Percentual_Comissao_Usuario,
                           T003_Valor_Total_Comissao_Usuario,
                           T003_Percentual_Comissao_Manual,
                           T003_Valor_Desconto,
                           T003_Valor_Desconto_Manual,
                           T003_Total_Cubagem,
                           T003_Flag_Destino_Produto,
                           T003_Cliente_Area,
                           T003_Casas_Decimais_Preco,
                           T003_Valor_Despesas_Acessorias,
                           T003_Flag_Frete_Primeira_Parcela,
                           T003_Flag_ST_Primeira_Parcela,
                           T003_Flag_IPI_Primeira_Parcela,
                           T003_Pgto_Primeiro_Vcto_Valor_Data,
                           T003_Valor_Total,
                           T003A_Flag_Ratear_Frete_Custo,
                           T003A_Liberou_Alcada_Frete,
                           T003A_Flag_Conversao_Unidade,
                           D024_Flag_Comercial, 
                           D024_Flag_Pre_Cadastro,
                           T003A_Data_Entrega
                      FROM T003 
                 LEFT JOIN D006 ON D006_Id=T003_D006_Id 
                 LEFT JOIN D024 ON D024_Id=T003_D024_Id 
                 LEFT JOIN D020 ON D020_Id=D024_D020_Id 
                 LEFT JOIN D022 ON D022_Id=T003_D022_Id
                 LEFT JOIN T003A ON T003_Id=T003A_T003_Id
                     WHERE T003_Id='{$T003_Id}'";

            $array = mysql_query($sql);
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return '1 Erro: ' . $error;
            }

            $orcamento = mysql_fetch_assoc($array);

            if ($orcamento['D024_Flag_Pre_Cadastro'] == 'S' && $g['C031']['permitePedidoClientePreCadastro'] == 'N') {
                $msg = "O Cliente esta em PRÃ‰-CADASTRO verificar.";
                return $msg;
            }
            
            if($orcamento['T003_Flag_Frete'] != 9 AND $g['C031']['permiteTransportarDiferentesUF'] == 'N' AND $orcamento['D024_Flag_Transporta_Diferentes_UF'] == 'N'){
                $permiteTransporte = mysql_query("SELECT UFFaturamento.D018_UF as faturamento,  
                                                         UFEntrega.D018_UF as entrega,
                                                         D022_Id
                                                    FROM T003
                                                    LEFT JOIN D022 ON D022_Id=T003_D022_Id
                                                    LEFT JOIN D018 AS UFFaturamento ON UFFaturamento.D018_Id=D022_D018_Id
                                                    LEFT JOIN D148 ON D148_Id=T003_D148_Id_Entrega
                                                    LEFT JOIN D018 AS UFEntrega ON UFEntrega.D018_Id=D148_D018_Id
                                                    WHERE T003_Id='$T003_Id'");
                $mPermiteTransporte = mysql_fetch_array($permiteTransporte);
                if($mPermiteTransporte['faturamento'] != $mPermiteTransporte['entrega']){
                    mysql_query("ROLLBACK");
                    return "UF ".$mPermiteTransporte['faturamento']." da transportadora Ã© diferente da UF ".$mPermiteTransporte['entrega']." do cliente, favor verificar.";
                }
            }

            $T005_Data_Entrega = $g['conf']->camposValorPadrao('T005_Data_Entrega');
            $T005_Data_Entrega = (!empty($T005_Data_Entrega)) ? $T005_Data_Entrega : $orcamento['T003A_Data_Entrega'];

            $totalizarPeso = ($g['C031']['totalizarPesoVenda'] == 'S') ? 'S' : 'N';

            //if ($orcamento['D024_Flag_Pessoa_Fisica_Juridica'] == "J") {
            //    if (empty($orcamento['D024_Cnpj'])) {
            //        return "CNPJ da empresa em branco";
            //    }
            //} else if ($orcamento['D024_Flag_Pessoa_Fisica_Juridica'] == "F" && $orcamento['T003_Flag_Acp'] != 4) {
            //    if (empty($orcamento['D024_Cpf'])) {
            //        return "Cpf do cliente em branco";
            //    }
            //}

            //Ajuste flag status liberaÃ§Ã£o
            //Caso o orÃ§amento nÃ£o gerou parcela, mudamos a flag para 1 (nÃ£o passou por liberaÃ§Ã£o), pois no pedido se for gerado parcela, Ã© preciso passar por anÃ¡lise
            $sqlT089 = mysql_query("SELECT T089_Id FROM T089 WHERE T089_T003_Id = '{$T003_Id}'");
            if(mysql_num_rows($sqlT089) <= 0){
                //mysql_query("ROLLBACK");
                $orcamento['T003_Flag_Status_Liberacao'] = '1';
            }

            // funcao personalizada por cliente
            $excecoesValidaT003 = $this->excecoesValidaT003($orcamento);
            if($excecoesValidaT003 !== true){
                mysql_query("ROLLBACK");
                return $excecoesValidaT003;
            }

            $sql = "INSERT INTO T005 (T005_T003_Id,
                                      T005_D013_Id,
                                      T005_D024_Id,
                                      T005_D021_Id,
                                      T005_C004_Id,
                                      T005_D006_Id,
                                      T005_C007_Id,
                                      T005_C007_Id_Vendedor_Interno,
                                      T005_C007_Id_Vendedor_Externo,
                                      T005_Ordem_Compra,
                                      T005_Observacao,
                                      T005_Observacao_Comercial,
                                      T005_Observacao_Nota_Fiscal,
                                      T005_Flag_ACP,
                                      T005_Flag_Status,
                                      T005_Data_Emissao,
                                      T005_Hora_Emissao,
                                      T005_Flag_Situacao,
                                      T005_Flag_Revenda_Consumidor,
                                      T005_D022_Id,
                                      T005_D022_Id_Default,
                                      T005_Observacao_Pagamento,
                                      T005_Pgto_Parcelas,
                                      T005_Pgto_Primeiro_Vcto_Tipo,
                                      T005_Pgto_Primeiro_Vcto_Valor,
                                      T005_Pgto_Dias_Vcto_Tipo,
                                      T005_Pgto_Dias_Vcto_Valor,
                                      T005_Pgto_Portador,
                                      T005_Pgto_Data,
                                      T005_Pgto_C007_Id,
                                      T005_D116_Id,
                                      T005_Liberou_Alcada_C007_Id,
                                      T005_Liberou_Alcada_Data,
                                      T005_Liberou_Alcada_IPV,
                                      T005_Liberou_Alcada_Valor_Total,
                                      T005_Liberou_Alcada_Prazo_Medio,
                                      T005_Liberou_Alcada_Desconto,
                                      T005_ST_ICMS_Interno,
                                      T005_ICMS_Inter_Estadual,
                                      T005_Flag_Frete,
                                      T005_Flag_Lista_Preco,
                                      T005_Valor_Frete,
                                      T005_Valor_Frete_Calculado,
                                      T005_Flag_Expedicao,
                                      T005_Percentual_Comissao,
                                      T005_Valor_Total_Comissao,
                                      T005_Percentual_Comissao_Representante,
                                      T005_Valor_Comissao_Representante,
                                      T005_Percentual_Comissao_Externo,
                                      T005_Valor_Total_Comissao_Externo,
                                      T005_Percentual_Comissao_Usuario,
                                      T005_Valor_Total_Comissao_Usuario,
                                      T005_Percentual_Comissao_Manual,
                                      T005_Valor_Desconto,
                                      T005_Valor_Desconto_Manual,
                                      T005_Flag_Almoxarifado,
                                      T005_Total_Cubagem,
                                      T005_Flag_Destino_Produto,
                                      T005_D036_Id,
                                      T005_D148_Id_Entrega,
                                      T005_D148_Id_Cobranca,
                                      T005_Flag_Status_Liberacao,
                                      T005_Cliente_Area,
                                      T005_Casas_Decimais_Preco,
                                      T005_Data_Entrega,
                                      T005_Flag_Totalizar_Peso,
                                      T005_C004_Id_Estoque,
                                      T005_Valor_Despesas_Acessorias,
                                      T005_D027_Id,
                                      T005_Flag_Frete_Primeira_Parcela,
                                      T005_Flag_ST_Primeira_Parcela,
                                      T005_T134_Id,
                                      T005_Flag_IPI_Primeira_Parcela,
                                      T005_Pgto_Primeiro_Vcto_Valor_Data
                                      ) VALUES (
                                       '" . mysql_real_escape_string($T003_Id) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D013_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D024_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D021_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_C004_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D006_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_C007_Id']) . "',
                                       '" . mysql_real_escape_string($g['usuarioAtual']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_C007_Id_Vendedor_Externo']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Ordem_Compra']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Observacao']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Observacao_Comercial']) . "',
                                       '',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Acp']) . "',
                                       '0',
                                       CURDATE(),
                                       CURTIME(),
                                       '1',
                                       '" . mysql_real_escape_string($orcamento['D024_Flag_Revenda_Consumidor']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D022_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['D020_D022_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Observacao_Pagamento']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Parcelas']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Primeiro_Vcto_Tipo']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Primeiro_Vcto_Valor']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Dias_Vcto_Tipo']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Dias_Vcto_Valor']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Portador']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Data']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_C007_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D116_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_C007_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_Data']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_IPV']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_Valor_Total']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_Prazo_Medio']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Liberou_Alcada_Desconto']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_ST_ICMS_Interno']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_ICMS_Inter_Estadual']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Frete']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Lista_Preco']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Frete']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Frete_Calculado']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Expedicao']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Percentual_Comissao']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Total_Comissao']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Percentual_Comissao_Representante']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Comissao_Representante']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Percentual_Comissao_Externo']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Total_Comissao_Externo']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Percentual_Comissao_Usuario']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Total_Comissao_Usuario']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Percentual_Comissao_Manual']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Desconto']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Desconto_Manual']) . "',
                                       'N',
                                       '" . mysql_real_escape_string($orcamento['T003_Total_Cubagem']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Destino_Produto']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D036_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D148_Id_Entrega']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D148_Id_Cobranca']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Status_Liberacao']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Cliente_Area']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Casas_Decimais_Preco']) . "',
                                       '{$T005_Data_Entrega}',
                                       '{$totalizarPeso}',
                                       '" . mysql_real_escape_string($orcamento['T003_C004_Id_Estoque']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Valor_Despesas_Acessorias']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_D027_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_Frete_Primeira_Parcela']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_ST_Primeira_Parcela']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_T134_Id']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Flag_IPI_Primeira_Parcela']) . "',
                                       '" . mysql_real_escape_string($orcamento['T003_Pgto_Primeiro_Vcto_Valor_Data']) . "'
                                     );";
            log($sql);
            $retorno = mysql_query($sql);
            $erro    = mysql_error();
            if (!empty($erro)) {
                mysql_query("ROLLBACK");
                return '2 Erro: ' . $erro;
            }
            $T005_Id = $g['mysqlLastId'];

            //INSERE REGISTRO NA TABELA ESTENDIDA
            mysql_query("INSERT INTO T005A(T005A_T005_Id,
                                           T005A_Flag_Ratear_Frete_Custo,
                                           T005A_Liberou_Alcada_Frete,
                                           T005A_Flag_Conversao_Unidade,
                                           T005A_D148_Id_Redespacho) 
                                  VALUES ('{$T005_Id}',
                                          '{$orcamento['T003A_Flag_Ratear_Frete_Custo']}',
                                          '{$orcamento['T003A_Liberou_Alcada_Frete']}',
                                          '{$orcamento['T003A_Flag_Conversao_Unidade']}',
                                          '{$orcamento['T003A_D148_Id_Redespacho']}')");

            //Insere registro do pedido na tarefa
            //require_once('bibliotecas/classes/ETC017.php');
            //$ETC017 = new ETC017();
            //$ETC017->inserirRegistroTarefa($T005_Id, 'T005');

            //Insere registros no histÃ³rico do pedido (T178)
            mysql_query("insert into T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'Gerou Pedido','')");
            //Define o OrÃ§amento como Fechado
            mysql_query("UPDATE T003 SET T003_Flag_Perdido='F' WHERE T003_Id={$T003_Id}");

            /* PersonalizaÃ§Ã£o: NÃ£o inserir as parcelas, apenas gravar a forma de pagamento na T005
            //selecionar da T089 e inserir na T090 (pagamento)
            $query7 = "SELECT * FROM T089 WHERE T089_T003_Id='$T003_Id'";
            $result9 = mysql_query($query7);
            while($row = mysql_fetch_array($result9)){
            $sql90 = <<<EOT
            INSERT INTO T090 (T090_T005_Id, T090_D027_Id, T090_Numero_Parcela, T090_Prazos_Dias, T090_Data_Vencimento, T090_Valor_Parcela) 
            VALUES
            ('{$T005_Id}', '{$row['T089_D027_Id']}', '{$row['T089_Numero_Parcela']}', '{$row['T089_Prazos_Dias']}', '{$row['T089_Data_Vencimento']}', '{$row['T089_Valor_Parcela']}')
            EOT;
            $retorno .= $sql90."<br ></EOT>";
            $result = mysql_query($sql90);
            if (mysql_error()) {
            mysql_query("ROLLBACK");
            return '22: Erro Parcelas: ' . mysql_error();
            }
            }*/

            //Insere os documentos do orçamento no pedido.
            $sqlDocumentos    = "SELECT * FROM T188 WHERE T188_T003_Id='{$T003_Id}'";
            $resultDocumentos = mysql_query($sqlDocumentos);
            while ($linhaDocumentos = mysql_fetch_array($resultDocumentos)) {
                // Os campos do documento podem conter aspas simples (por exemplo, na
                // descrição). Escape-os antes de montar o INSERT para não invalidar
                // a consulta nem alterar os valores gravados.
                $T167_T005_Id           = mysql_real_escape_string($T005_Id);
                $T167_C007_Id           = mysql_real_escape_string($linhaDocumentos['T188_C007_Id']);
                $T167_Nome_Arquivo      = mysql_real_escape_string($linhaDocumentos['T188_Nome_Arquivo']);
                $T167_Descricao         = mysql_real_escape_string($linhaDocumentos['T188_Descricao']);
                $T167_Data_Hora_Upload  = mysql_real_escape_string($linhaDocumentos['T188_Data_Hora_Upload']);
                $sql            = <<<EOT
                INSERT INTO T167 
                (T167_T005_Id, T167_C007_Id, T167_Nome_Arquivo, T167_Descricao, T167_Data_Hora_Upload)
                VALUES
                ('{$T167_T005_Id}','{$T167_C007_Id}','{$T167_Nome_Arquivo}','{$T167_Descricao}','{$T167_Data_Hora_Upload}')
EOT;
                $resultCopiaDoc = mysql_query($sql);
                if (mysql_error()) {
                    mysql_query("ROLLBACK");
                    return '19 Erro: ' . mysql_error();
                }

                //----Copia o arquivo da pasta de orçamentos para pasta de pedidos
                //Pega a extensão do arquivo
                $pathinfo              = pathinfo($linhaDocumentos['T188_Nome_Arquivo']);
                $pathinfo['extension'] = empty($pathinfo['extension']) ? '.tmp' : $pathinfo['extension'];

                //Pega o caminho do arquivo no orçamento
                $dirOrigem = "{$g['pathDados']}orcamento";
                is_dir($dirOrigem) OR mkdir($dirOrigem, 0777, true);
                $fileOrigem = $dirOrigem . '/' . $linhaDocumentos['T188_Id'] . '.' . $pathinfo['extension'];

                $dirDestino = "{$g['pathDados']}pedido";
                is_dir($dirDestino) OR mkdir($dirDestino, 0777, true);
                $fileDestino = $dirDestino . '/' . $g['mysqlLastId'] . '.' . $pathinfo['extension'];
                copy($fileOrigem, $fileDestino);
            }

            // neste momento insere apenas os serviÃ§os (T145) que nao estÃ£o associados a produtos (avulsos)
            $sql    = "SELECT * FROM T145 WHERE T145_T003_Id='{$T003_Id}' AND T145_T004_Id IS NULL";
            $result = mysql_query($sql);
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return '3 Erro: ' . $error;
            }

            while ($orcamentoSer = mysql_fetch_array($result)) {

                $sql     = "INSERT INTO T147 (T147_T005_Id, 
                                              T147_T145_Id, 
                                              T147_D110_Id,
                                              T147_D112_Id,
                                              T147_Titulo, 
                                              T147_Descricao, 
                                              T147_Tempo_Estimado, 
                                              T147_Quantidade,
                                              T147_Valor_Unitario,
                                              T147_Valor_Total,
                                              T147_Hora_Inicio,
                                              T147_Hora_Fim,
                                              T147_Status,
                                              T147_D116_Id) 
                                              VALUES 
                                              ('" . mysql_real_escape_string($T005_Id) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Id']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_D110_Id']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_D112_Id']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Titulo']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Descricao']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Tempo_Estimado']) . "',
                                                                     '1',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Valor_Unitario']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Valor_Unitario']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Hora_Inicio']) . "',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_Hora_Fim']) . "',
                                                                     'P',
                                                                     '" . mysql_real_escape_string($orcamentoSer['T145_D116_Id']) . "'
                                                                     );";
                $retorno = mysql_query($sql); 
                $error = mysql_error();             
                if (!empty($error)) {
                    mysql_query("ROLLBACK");
                    return '4 Erro: ' . $error;
                }
                $T147_Id = $g['mysqlLastId'];
                
                //selecionar todos da T146 onde T146_T145_Id = T145_Id
                //mais um looping para cada T146 inserir um registro na T148
                
                $sql     = "SELECT * FROM T146 WHERE T146_T145_Id=" . $orcamentoSer['T145_Id'];
                $result2 = mysql_query($sql);
                $error = mysql_error();
                if (!empty($error)) {
                    mysql_query("ROLLBACK");
                    return '5 Erro: ' . $error;
                }
                
                while ($processo = mysql_fetch_assoc($result2)) {
                    $sql     = "INSERT INTO T148 (T148_Id,
                                                  T148_T147_Id,
                                                  T148_Titulo,
                                                  T148_Descricao)
                                                  VALUES
                                                  ('',
                                                   '" . mysql_real_escape_string($T147_Id) . "',
                                                   '" . mysql_real_escape_string($processo['T146_Titulo']) . "',
                                                   '" . mysql_real_escape_string($processo['T146_Descricao']) . "'
                                                   );";
                    $retorno = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '11.2 Erro: ' . mysql_error();
                    }
                }
                $sql     = "SELECT * FROM T152 WHERE T152_T145_Id=" . $orcamentoSer['T145_Id'];
                $result3 = mysql_query($sql);
                $error = mysql_error();
                if (!empty($error)) {
                    mysql_query("ROLLBACK");
                    return '5 Erro: ' . $error;
                }

                while ($material = mysql_fetch_assoc($result3)) {
                    $sql = "INSERT INTO T149(T149_Id,
                                                T149_D009_Id,
                                                T149_D037_Id,
                                                T149_T147_Id,
                                                T149_Quantidade)
                                                VALUES
                                                ('',
                                                 '" . mysql_real_escape_string($material['T152_D009_Id']) . "',
                                                 '" . mysql_real_escape_string($material['T152_D037_Id']) . "',
                                                 '" . mysql_real_escape_string($T147_Id) . "',
                                                 '" . mysql_real_escape_string($material['T152_Quantidade']) . "'
                                                 );";
                    $retorno = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '7 Erro: ' . mysql_error();
                    }
                }
            }

            //selecionar todos da T004 onde t004_T003_Id = $T003
            //inserir na t006 todos da T004 selecionados
            $sql = <<<EOT
                SELECT T004_D009_Id,
                       T004_Id,
                       D009_Id,
                       D009_Quantidade_Estoque_Liquido,
                       T004_Valor_Preco_Original,
                       T004_Valor_Preco_Sem_Desconto_Unitario,
                       T004_Codigo_Substituto,
                       T004_Item,
                       T004_D037_Id,
                       T004_D005_Id,
                       T004_D006_Id,
                       D005_Classificacao_Fiscal,
                       T004_Codigo_Produto,
                       T004_Marca,
                       T004_Descricao_Produto,
                       T004_Quantidade_Confirmada,
                       T004_Valor_Preco_Bruto,
                       T004_Valor_Custo_Unitario,
                       T004_Valor_Preco_Unitario,
                       T004_Valor_Preco_Original_Bruto,
                       T004_Percentual_Desconto_Bruto,
                       T004_IPV,
                       T004_C004_Id,
                       T004_Numero_Pedido_Compra,
                       T004_Item_Pedido_Compra,
                       T004_Aliquota_IPI,
                       T004_Aliquota_ICMS,
                       T004_Percentual_Reducao_ICMS,
                       T004_Percentual_Reducao_ICMS_ST,
                       T004_ST_VA,
                       T004_Flag_ST,
                       T004_Flag_Situacao_Tributaria,
                       T004_ICMS_Inter_Estadual,
                       T004_ST_ICMS_Interno,
                       T004_Flag_Isento_PIS,
                       T004_Aliquota_PIS,
                       T004_Situacao_Tributaria_PIS,
                       T004_Flag_Isento_COFINS,
                       T004_Aliquota_COFINS,
                       T004_Flag_Preco_Especial,
                       T004_Situacao_Tributaria_COFINS,
                       T004_Situacao_Tributaria_IPI,
                       T004_D059_Id,
                       T004_Valor_Preco_Original,
                       T004_Percentual_Desconto,
                       T004_Liberou_Alcada_Saldo_Futuro,
                       D009_Quantidade_Estoque_Liquido,
                       T004_T075_Id,
                       T004_T206_Id,
                       T004_Cubagem_Unitaria,
                       T004_Total_Cubagem,
                       T004_Flag_Pre_Cadastro,
                       T004_Demonstrativo_Preco,
                       T004_Demonstrativo_Preco_Tabela,
                       T004_Demonstrativo_Preco_Transferencia,
                       T004_Demonstrativo_Preco_Cotacao,
                       T004_Valor_Preco_Compra,
                       T004_Valor_Preco_Tabela,
                       T004_Valor_Preco_Transferencia,
                       T004_Valor_Custo_Transferencia,
                       T004_Flag_Origem_Produto_Transferencia,
                       T004_Valor_Preco_Cotacao,
                       T004_T001_Id,
                       T004_Flag_Destino_Produto,
                       T004_Flag_Origem_Produto,
                       T004_Observacao_Entrega,
                       T004_Observacao,
                       T004_Observacao_Compra,
                       T004_D006_Id_Info,
                       T004_D005_Id_Info,
                       T004_T219_Id,
                       T004_Valor_ICMS_Desonerado,
                       T004_Motivo_Desoneracao_ICMS,
                       T004_Demonstrativo_Custo_Tabela,
                       T004_Demonstrativo_Preco_Venda,
                       T004_Aliquota_IRPJ,
                       T004_Aliquota_CSLL,
                       T004_Flag_Isento_IRPJ,
                       T004_Flag_Isento_CSLL,
                       T004_Flag_Transferencia,
                       T004_C004_Id_Transferencia,
                       T004_UF_ICMS_Substituicao_Devido,
                       T004_Percentual_Base_Operacao_Propria,
                       T004_Percentual_Trib_Federal,
                       T004_Percentual_Trib_Estadual,
                       T004_Percentual_Trib_Municipal,
                       T004_Aliquota_ICMS_Credito,
                       T004_Valor_ICMS_Credito,
                       T004_Preco_Pauta_ST,
                       T004_Modalidade_ICMS_Substituicao,
                       T004_Modalidade_ICMS,
                       T004_Codigo_Local,
                       T004A_T066_Id,
                       D009_Quantidade_OC_3(D009_Id,0) as D009_Quantidade_OC,
                       T004A_Percentual_Reducao_PIS,
                       T004A_Percentual_Reducao_COFINS,
                       T004A_Quantidade_Conversao,
                       T004A_Quantidade_Comercial,
                       T004A_Preco_Unitario_Comercial,
                       T004A_D184_Id,
                       T004A_Quantidade_Comercial_Confirmada,
                       T004A_Percentual_FCP,             
                       T004A_Valor_Base_FCP,                 
                       T004A_Valor_FCP,                          
                       T004A_Valor_Base_FCP_ST,
                       T004A_Valor_FCP_ST,                       
                       T004A_Valor_Base_FCP_Dest,
                       T004A_Valor_FCP_Partilha,
                       T004A_Percentual_Desconto_CFOP 
                  FROM T004 
             LEFT JOIN T006 ON T006_T004_Id=T004_Id 
             LEFT JOIN D009 ON D009_Id=T004_D009_Id
             LEFT JOIN D005 ON D005_Id=T004_D005_Id
             LEFT JOIN T004A ON T004_Id=T004A_T004_Id
                 WHERE T004_T003_Id='{$T003_Id}' 
                   AND T006_Id is null 
                   AND T004_Quantidade_Confirmada>0;
EOT;
            $retornoConsulta = mysql_query($sql);
            if (mysql_error()) {
                mysql_query("ROLLBACK");
                return '8 Erro: ' . mysql_error();
            }

            if (mysql_num_rows($retornoConsulta) <= 0) {
                //mysql_query("ROLLBACK");
                // return 'NÃ£o existem Ã­tens para gerar pedido.';
                // O produto pode ter apenas serviÃ§os. Regra retirada
            }
            // variavel que soma quantos itens do orÃƒÂ§amento foram inseridos no pedido
            $countConfirmados = 0;
            $restricaoProduto = '';
            while ($linha = mysql_fetch_array($retornoConsulta)) {
                /*              // seleciona todos os pedidos gerados a partir do orÃƒÂ§amento atual
                $sql = "SELECT * FROM T005 WHERE T005_T003_Id='$T003_Id';";
                $ret = mysql_query($sql);
                // variavel que verifica se o item do orÃƒÂ§amento atual jÃƒÂ¡ estÃƒÂ¡ em algum pedido ja gerado deste orÃƒÂ§amento
                $incluir=true;
                // para cada pedido jÃƒÂ¡ gerado a partir do orÃƒÂ§amento atual
                while($reg = mysql_fetch_assoc($ret)){
                // seleciona os itens deste pedido
                // e verifica se algum destes itens Ã© igual ao item atual
                $sql1 = "SELECT * FROM T006 WHERE T006_T005_Id='{$reg['T005_Id']}';";
                $ret1 = mysql_query($sql1);
                $item=mysql_fetch_assoc($ret1);
                if (!empty($item['T006_D009_Id'])){
                if ($item['T006_T004_Id']==$linha['T004_Id']){
                $incluir=false; 
                }
                }
                }
                */
                // Atualiza a quantidade de estoque no pedido

                
                //Conferido
                                                    //Conferido
                $verificacaoItemOrcamento = $this->PersonalizacaoTriggerVerificarItemOrcamento($linha['T004_Id'], $linha['T004_Quantidade_Confirmada']);
                if($verificacaoItemOrcamento !== true)
                {
                    mysql_query("ROLLBACK");
                    return $verificacaoItemOrcamento;
                }

                $T006_D009 = mysql_query("SELECT D009_Quantidade_Estoque_Liquido 
                                            FROM D009 
                                           WHERE D009_Id='{$linha['T004_D009_Id']}'");

                $mT006_D009 = mysql_fetch_assoc($T006_D009);
                $linha['D009_Quantidade_Estoque_Liquido'] = $mT006_D009['D009_Quantidade_Estoque_Liquido'];

                $linha1['D009_Quantidade_Estoque_Liquido'] = $mT006_D009['D009_Quantidade_Estoque_Liquido'];

                if ($g['C031']['permitePedidoProdutoPreCadastro'] == 'N') {
                    //Verifica se existe algum produto prÃ© cadastro
                    $sqlRestricao     = "SELECT T004_Codigo_Produto,
                                                D001_Flag_Pre_Cadastro 
                                           FROM T004 
                                      LEFT JOIN D009 ON D009_Id=T004_D009_Id 
                                      LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                      LEFT JOIN D001 ON D001_Id=D049_D001_Id
                                          WHERE T004_T003_Id='$T003_Id' 
                                            AND T004_Quantidade_Confirmada > 0 
                                            AND T004_Id = {$linha['T004_Id']}";

                    $retornoRestricao = mysql_query($sqlRestricao);
                    $rowRestricao     = mysql_fetch_array($retornoRestricao);
                    if ($rowRestricao['D001_Flag_Pre_Cadastro'] == 'S') {
                        $restricaoProduto .= "O produto " . $rowRestricao['T004_Codigo_Produto'] . " Ã© prÃ©-cadastro.<br />";
                        continue;
                    }
                }

                $linha['T004_Valor_Preco_Original'] = ($linha['T004_Valor_Preco_Original'] > 0 ? $linha['T004_Valor_Preco_Original'] : $linha['T004_Valor_Preco_Sem_Desconto_Unitario']);
                $countConfirmados++;
                $sql  = "INSERT INTO T006 (T006_Codigo_Substituto,
                                          T006_Flag_Status,
                                          T006_Item,
                                          T006_T004_Id,
                                          T006_D037_Id,
                                          T006_D005_Id,
                                          T006_D006_Id,
                                          T006_Classificacao_Fiscal,
                                          T006_D009_Id,
                                          T006_Codigo_Produto,
                                          T006_Marca,
                                          T006_Descricao_Produto,
                                          T006_Quantidade,
                                          T006_Valor_Preco_Bruto,
                                          T006_Valor_Custo_Unitario,
                                          T006_Valor_Preco_Unitario,
                                          T006_Valor_Preco_Sem_Desconto_Unitario,
                                          T006_Valor_Preco_Original_Bruto,
                                          T006_Percentual_Desconto_Bruto,
                                          T006_IPV,
                                          T006_T005_Id,
                                          T006_C004_Id,
                                          T006_Numero_Pedido_Compra,
                                          T006_Item_Pedido_Compra,
                                          T006_Aliquota_IPI,
                                          T006_Aliquota_ICMS,
                                          T006_Percentual_Reducao_ICMS,
                                          T006_Percentual_Reducao_ICMS_ST,
                                          T006_ST_VA,
                                          T006_Flag_ST,
                                          T006_Flag_Situacao_Tributaria,
                                          T006_ICMS_Inter_Estadual,
                                          T006_ST_ICMS_Interno,
                                          T006_Flag_Isento_PIS,
                                          T006_Aliquota_PIS,
                                          T006_Situacao_Tributaria_PIS,
                                          T006_Flag_Isento_COFINS,
                                          T006_Aliquota_COFINS,
                                          T006_Flag_Preco_Especial,
                                          T006_Situacao_Tributaria_COFINS,
                                          T006_Situacao_Tributaria_IPI,
                                          T006_D059_Id,
                                          T006_Valor_Preco_Original,
                                          T006_Percentual_Desconto,
                                          T006_Liberou_Alcada_Saldo_Futuro,
                                          T006_Quantidade_Estoque,
                                          T006_T075_Id,
                                          T006_T206_Id,
                                          T006_Cubagem_Unitaria,
                                          T006_Total_Cubagem,
                                          T006_Flag_Pre_Cadastro,
                                          T006_Demonstrativo_Preco,
                                          T006_Demonstrativo_Preco_Tabela,
                                          T006_Demonstrativo_Preco_Transferencia,
                                          T006_Demonstrativo_Preco_Cotacao,
                                          T006_Valor_Preco_Compra,
                                          T006_Valor_Preco_Tabela,
                                          T006_Valor_Preco_Transferencia,
                                          T006_Valor_Custo_Transferencia,
                                          T006_Flag_Origem_Produto_Transferencia,
                                          T006_Valor_Preco_Cotacao,
                                          T006_T001_Id,
                                          T006_Flag_Destino_Produto,
                                          T006_Flag_Origem_Produto,
                                          T006_Observacao_Entrega,
                                          T006_Observacao,
                                          T006_Observacao_Compra,
                                          T006_D006_Id_Info,
                                          T006_D005_Id_Info,
                                          T006_T219_Id,
                                          T006_Valor_ICMS_Desonerado,
                                          T006_Motivo_Desoneracao_ICMS,
                                          T006_Demonstrativo_Custo_Tabela,
                                          T006_Quantidade_OC,
                                          T006_Demonstrativo_Preco_Venda,
                                          T006_Aliquota_IRPJ,
                                          T006_Aliquota_CSLL,
                                          T006_Flag_Isento_IRPJ,
                                          T006_Flag_Isento_CSLL,
                                          T006_Flag_Transferencia,
                                          T006_C004_Id_Transferencia,
                                          T006_UF_ICMS_Substituicao_Devido,
                                          T006_Percentual_Base_Operacao_Propria,
                                          T006_Percentual_Trib_Federal,
                                          T006_Percentual_Trib_Estadual,
                                          T006_Percentual_Trib_Municipal,
                                          T006_Aliquota_ICMS_Credito,
                                          T006_Valor_ICMS_Credito,
                                          T006_Preco_Pauta_ST,
                                          T006_Modalidade_ICMS_Substituicao,
                                          T006_Modalidade_ICMS,
                                          T006_Codigo_Local,
                                          T006_T066_Id
                                          )
                                      VALUES
                                         ('" . mysql_real_escape_string($linha['T004_Codigo_Substituto']) . "',
                                          '',
                                          '" . mysql_real_escape_string($linha['T004_Item']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D037_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D005_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D006_Id']) . "',
                                          '" . mysql_real_escape_string($linha['D005_Classificacao_Fiscal']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D009_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Codigo_Produto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Marca']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Descricao_Produto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Quantidade_Confirmada']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Bruto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Custo_Unitario']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Unitario']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Unitario']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Original_Bruto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Desconto_Bruto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_IPV']) . "',
                                          '" . mysql_real_escape_string($T005_Id) . "',
                                          '" . mysql_real_escape_string($linha['T004_C004_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Numero_Pedido_Compra']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Item_Pedido_Compra']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_IPI']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_ICMS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Reducao_ICMS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Reducao_ICMS_ST']) . "',
                                          '" . mysql_real_escape_string($linha['T004_ST_VA']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_ST']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Situacao_Tributaria']) . "',
                                          '" . mysql_real_escape_string($linha['T004_ICMS_Inter_Estadual']) . "',
                                          '" . mysql_real_escape_string($linha['T004_ST_ICMS_Interno']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Isento_PIS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_PIS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Situacao_Tributaria_PIS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Isento_COFINS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_COFINS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Preco_Especial']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Situacao_Tributaria_COFINS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Situacao_Tributaria_IPI']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D059_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Original']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Desconto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Liberou_Alcada_Saldo_Futuro']) . "',
                                          '" . mysql_real_escape_string($linha['D009_Quantidade_Estoque_Liquido']) . "',
                                          '" . mysql_real_escape_string($linha['T004_T075_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_T206_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Cubagem_Unitaria']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Total_Cubagem']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Pre_Cadastro']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Preco']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Preco_Tabela']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Preco_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Preco_Cotacao']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Compra']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Tabela']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Custo_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Origem_Produto_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_Preco_Cotacao']) . "',
                                          '" . mysql_real_escape_string($linha['T004_T001_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Destino_Produto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Origem_Produto']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Observacao_Entrega']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Observacao']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Observacao_Compra']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D006_Id_Info']) . "',
                                          '" . mysql_real_escape_string($linha['T004_D005_Id_Info']) . "',
                                          '" . mysql_real_escape_string($linha['T004_T219_Id']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_ICMS_Desonerado']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Motivo_Desoneracao_ICMS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Custo_Tabela']) . "',
                                          '" . mysql_real_escape_string($linha['D009_Quantidade_OC']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Demonstrativo_Preco_Venda']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_IRPJ']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_CSLL']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Isento_IRPJ']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Isento_CSLL']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Flag_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_C004_Id_Transferencia']) . "',
                                          '" . mysql_real_escape_string($linha['T004_UF_ICMS_Substituicao_Devido']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Base_Operacao_Propria']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Trib_Federal']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Trib_Estadual']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Percentual_Trib_Municipal']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Aliquota_ICMS_Credito']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Valor_ICMS_Credito']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Preco_Pauta_ST']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Modalidade_ICMS_Substituicao']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Modalidade_ICMS']) . "',
                                          '" . mysql_real_escape_string($linha['T004_Codigo_Local']) . "',
                                          '" . mysql_real_escape_string($linha['T004A_T066_Id']) . "'

                                          );";                                          
                                          log('passando orÃ§amento p/ pedido Modalidade ICMS: ' . $linha['T004_Modalidade_ICMS']);
                $ret  = mysql_query($sql, false);
                $erro = mysql_error();
                if (!empty($erro)) {
                    mysql_query("ROLLBACK");
                    return '9 Erro: ' . $erro;
                }

                $T006_Id = $g['mysqlLastId'];

                // Processa estoque por locaÃ§Ã£o
                require_once('bibliotecas/classes/CAD002.php');
                $CAD002 = new CAD002();

                $CAD002->processaEstoqueLiquidoPorLocacao($linha['T004_D009_Id']);
                $CAD002->reprocessaOCPedidos($linha['T004_D009_Id']);

                //INSERE REGISTRO NA TABELA ESTENDIDA
                 mysql_query("INSERT INTO T006A(T006A_T006_Id,
                                                T006A_Percentual_Reducao_PIS,
                                                T006A_Percentual_Reducao_COFINS,
                                                T006A_Quantidade_Conversao,
                                                T006A_Quantidade_Comercial,
                                                T006A_Preco_Unitario_Comercial,
                                                T006A_D184_Id,
                                                T006A_Data_Hora_Insercao,
                                                T006A_Percentual_FCP,             
                                                T006A_Valor_Base_FCP,                  
                                                T006A_Valor_FCP,                          
                                                T006A_Valor_Base_FCP_ST,
                                                T006A_Valor_FCP_ST,                       
                                                T006A_Valor_Base_FCP_Dest,       
                                                T006A_Valor_FCP_Partilha,
                                                T006A_Percentual_Desconto_CFOP) 
                                        VALUES ('{$T006_Id}',
                                                '{$linha['T004A_Percentual_Reducao_PIS']}',
                                                '{$linha['T004A_Percentual_Reducao_COFINS']}',
                                                '{$linha['T004A_Quantidade_Conversao']}',
                                                '{$linha['T004A_Quantidade_Comercial_Confirmada']}',
                                                '{$linha['T004A_Preco_Unitario_Comercial']}',
                                                '{$linha['T004A_D184_Id']}',
                                                NOW(),
                                                '{$linha['T004A_Percentual_FCP']}',             
                                                '{$linha['T004A_Valor_Base_FCP']}',                  
                                                '{$linha['T004A_Valor_FCP']}',                          
                                                '{$linha['T004A_Valor_Base_FCP_ST']}',
                                                '{$linha['T004A_Valor_FCP_ST']}',                       
                                                '{$linha['T004A_Valor_Base_FCP_Dest']}',       
                                                '{$linha['T004A_Valor_FCP_Partilha']}',
                                                '{$linha['T004A_Percentual_Desconto_CFOP']}')");

                //ATUALIZA UMA REQUISIÃ‡ÃƒO JA CRIADA COM T006_Id
                mysql_query("UPDATE T189 SET T189_T006_Id = '{$T006_Id}', T189_Flag_Status = 'N' WHERE T189_T004_Id = '{$linha['T004_Id']}'");
                // mysql_query("UPDATE T189 SET T189_Flag_Status = 'N' WHERE T189_T006_Id = '{$g['mysqlLastId']}'");

                /*
                JA GRAVA TOTALIZACAO QUANDO Ã‰ CHAMADO O VEN002->atualizarAliquotasPedidos, UPDATE NO T006 E NA TRIGGER Ã‰ CHAMADO O T006 E T005 GRAVAR TOTALIZACAO
                mysql_query("call T006_Gravar_Totalizacao_4($T006_Id)");
                $error = mysql_error();
                if (!empty($error)) {
                    mysql_query("ROLLBACK");
                    return '10 Erro: ' . $error;
                }*/

                //Atualiza a CotaÃ§Ã£o jÃ¡ existente para o orÃ§amento, com o nÃºmero do pedido
                mysql_query("UPDATE T219 SET T219_T006_Id = '{$T006_Id}' WHERE T219_T004_Id = '{$linha['T004_Id']}'");

                //Limpa o campo T004_Flag_Comprar para nÃ£o gerar lentidÃ£o
                mysql_query("UPDATE T004 SET T004_Flag_Comprar='N' WHERE T004_Id = '{$linha['T004_Id']}'");

                //SELECT T006_Id, T066_Id FROM T006 LEFT JOIN T066 ON T066_D009_Id=T006_D009_Id WHERE T006_Id = '294670'

                //Busca D049_Id para executar function copiarEngenhariaProduto
                // Vai ser feito no final da funÃ§Ã£o por conta da transaction que Ã© ativada no copiarEngenhariaProduto
                // E pode acabar dando conflito com a transaction ativada no gerarPedido
/*                 $D049 = mysql_query("SELECT D049_Flag_Tipo 
                                       FROM D049 
                                  LEFT JOIN D009 ON D009_D049_Id=D049_Id 
                                      WHERE D009_Id='{$linha['D009_Id']}'");

                $mD049 = mysql_fetch_array($D049);

                if($mD049['D049_Flag_Tipo'] == 'PD'){
                    require_once('bibliotecas/classes/VEN002.php');
                    $VEN002 = new VEN002();
                    $VEN002->copiarEngenhariaProduto($T006_Id);
                } */

                // verificar se tem algum T145 com T145_T004_Id igual ao T004_Id que acabou de ser inserido na T006
                // ou seja, insere na T147 os serviÃ§os que estÃ£o associados ao Ã­tem que acabou de ser inserido 

                $sql    = "SELECT * FROM T145 WHERE T145_T003_Id='$T003_Id' AND T145_T004_Id='{$linha['T004_Id']}'";
                $result = mysql_query($sql);
                if (mysql_error()) {
                    mysql_query("ROLLBACK");
                    return 'Erro: ' . mysql_error();
                }

                while ($orcamentoSer = mysql_fetch_array($result)) {

                    $sql     = "INSERT INTO T147 (T147_T005_Id, 
                                              T147_T145_Id, 
                                              T147_D110_Id,
                                              T147_D112_Id,
                                              T147_Titulo, 
                                              T147_Descricao, 
                                              T147_Tempo_Estimado, 
                                              T147_Quantidade,
                                              T147_Valor_Unitario,
                                              T147_Valor_Total,
                                              T147_Hora_Inicio,
                                              T147_Hora_Fim,
                                              T147_Status,
                                              T147_T006_Id, 
                                              T147_D116_Id)
                                              VALUES 
                                              ('" . mysql_real_escape_string($T005_Id) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Id']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_D110_Id']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_D112_Id']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Titulo']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Descricao']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Tempo_Estimado']) . "',
                                                                     '1',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Valor_Unitario']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Valor_Unitario']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Hora_Inicio']) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_Hora_Fim']) . "',
                                                                     'P',
                                               '" . mysql_real_escape_string($T006_Id) . "',
                                               '" . mysql_real_escape_string($orcamentoSer['T145_D116_Id']) . "'
                                               );";
                    $retorno = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '4 Erro: ' . mysql_error();
                    }
                    $T147_Id = $g['mysqlLastId'];

                    //selecionar todos da T146 onde T146_T145_Id = T145_Id
                    //mais um looping para cada T146 inserir um registro na T148

                    $sql     = "SELECT * FROM T146 WHERE T146_T145_Id=" . $orcamentoSer['T145_Id'];
                    $result2 = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '5 Erro: ' . mysql_error();
                    }

                    while ($processo = mysql_fetch_assoc($result2)) {
                        $sql     = "INSERT INTO T148 (T148_Id,
                                                  T148_T147_Id,
                                                  T148_Titulo,
                                                  T148_Descricao)
                                                  VALUES
                                                  ('',
                                                   '" . mysql_real_escape_string($T147_Id) . "',
                                                   '" . mysql_real_escape_string($processo['T146_Titulo']) . "',
                                                   '" . mysql_real_escape_string($processo['T146_Descricao']) . "'
                                                   );";
                        $retorno = mysql_query($sql);
                        if (mysql_error()) {
                            mysql_query("ROLLBACK");
                            return '11.3 Erro: ' . mysql_error();
                        }
                    }
                    $sql     = "SELECT * FROM T152 WHERE T152_T145_Id=" . $orcamentoSer['T145_Id'];
                    $result3 = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '5 Erro: ' . mysql_error();
                    }
                    
                    while ($material = mysql_fetch_assoc($result3)) {
                        $sql = "INSERT INTO T149(T149_Id,
                                                T149_D009_Id,
                                                T149_D037_Id,
                                                T149_T147_Id,
                                                T149_Quantidade)
                                                VALUES
                                                ('',
                                                 '" . mysql_real_escape_string($material['T152_D009_Id']) . "',
                                                 '" . mysql_real_escape_string($material['T152_D037_Id']) . "',
                                                 '" . mysql_real_escape_string($T147_Id) . "',
                                                 '" . mysql_real_escape_string($material['T152_Quantidade']) . "'
                                                 );";
                        
                        $retorno = mysql_query($sql);
                        if (mysql_error()) {
                            mysql_query("ROLLBACK");
                            return '7 Erro: ' . mysql_error();
                        }
                    }    
                }
                
                //Copia agregados do produto
                $sql = "SELECT * FROM T202 WHERE T202_T004_Id='{$linha['T004_Id']}'";
                
                $result = mysql_query($sql);
                if (mysql_error()) {
                    mysql_query("ROLLBACK");
                    return 'Erro: ' . mysql_error();
                }
                
                while ($orcamentoSer = mysql_fetch_array($result)) {
                    $sql     = "INSERT INTO T204 (T204_T006_Id, 
                                                      T204_Descricao, 
                                                      T204_Observacao,
                                                      T204_Valor_Unitario)
                                                      VALUES 
                                                      ('" . mysql_real_escape_string($T006_Id) . "',
                                                       '" . mysql_real_escape_string($orcamentoSer['T202_Descricao']) . "',
                                                       '" . mysql_real_escape_string($orcamentoSer['T202_Observacao']) . "',
                                                       '" . mysql_real_escape_string($orcamentoSer['T202_Valor_Unitario']) . "'
                                                       );";
                    $retorno = mysql_query($sql);
                    //return $sql;                 
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '4 Erro: ' . mysql_error();
                    }
                }
            }
            
            
            $sql     = "SELECT * FROM T147 WHERE T147_T005_Id='{$T005_Id}'";
            $result9 = mysql_query($sql);
            while ($linhaVeiculo = mysql_fetch_array($result9)) {
                //Verificando se jÃ¡ existe o veÃ­culo de T147 cadastrado na T171
                $sql    = "SELECT * FROM T171 WHERE T171_D116_Id='{$linhaVeiculo['T147_D116_Id']}' AND T171_T005_Id='{$T005_Id}'";
                $result = mysql_query($sql);
                if (mysql_num_rows($result) == 0) {
                    $sql           = <<<EOT
                    INSERT INTO T171 
                    (T171_D116_Id, T171_T005_Id, T171_T147_Id)
                    VALUES
                    ('{$linhaVeiculo['T147_D116_Id']}','{$T005_Id}','{$linhaVeiculo['T147_Id']}')
EOT;
                    $resultEntrega = mysql_query($sql);
                    if (mysql_error()) {
                        mysql_query("ROLLBACK");
                        return '19 Erro: ' . mysql_error();
                    }
                }
            }
            
            
            mysql_query("UPDATE T138 SET T138_T005_Id='$T005_Id' WHERE T138_T003_Id='$T003_Id'");
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return 'Erro: ' . $error;
            }
            
            /*
            JA GRAVA TOTALIZACAO QUANDO Ã‰ CHAMADO O VEN002->atualizarAliquotasPedidos, UPDATE NO T006 E NA TRIGGER Ã‰ CHAMADO O T006 E T005 GRAVAR TOTALIZACAO
            mysql_query("call T005_Gravar_Totalizacao_4($T005_Id)");
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return '11 Erro: ' . $error;
            }
            */

            /*
            QUANDO GERA O PEDIDO O ORÃ‡AMENTO JA FOI TOTALIZADO
            mysql_query("call T003_Gravar_Totalizacao_4($T003_Id)");
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return '11 Erro: ' . $error;
            }*/

            if (!empty($restricaoProduto)) {
                mysql_query("ROLLBACK");
                return $restricaoProduto . "<br ></EOT>Por favor conclua o cadastro dos itens acima.";
            }
            
            $sqlT089 = mysql_query("SELECT T089_Numero_Parcela,
                                           T089_D027_Id,
                                           T089_Valor_Parcela,
                                           AVG(T089_Prazos_Dias) as PrazoMedio 
                                      FROM T089 
                                     WHERE T089_T003_Id = '{$T003_Id}'");

            $T089    = mysql_fetch_array($sqlT089);
            mysql_query("UPDATE T005 SET T005_Prazo_Medio_Orcamento = '{$T089['PrazoMedio']}' WHERE T005_Id = '{$T005_Id}'");
            
            require_once("bibliotecas/classes/VEN002.php");
            $VEN002 = new VEN002();
                       
            // T185
            require_once('prog/outros/distribuicaoItemPedidoEstoque.php');
            
            // Gera as parcelas
            require_once('bibliotecas/classes/GFormWrap.php');
            if ($mT005['T005_Pgto_Dias_Vcto_Tipo'] == "Intervalo") {
                $mT005['T005_Pgto_Dias_Vcto_Tipo'] = 'I';
            } else {
                $mT005['T005_Pgto_Dias_Vcto_Tipo'] = 'F';
            }
            $dadosTabela = array('tabela' => 'T005',
                                             'tabelaId' =>  $T005_Id,
                                             'campos' => 'T005_Pgto_Primeiro_Vcto_Tipo as primeiroVencimentoTipo,
                                                 T005_Pgto_Primeiro_Vcto_Valor as primeiroVencimentoNum,
                                                 T005_Pgto_Primeiro_Vcto_Valor_Data as primeiroVencimentoData,
                                                 T005_Pgto_Dias_Vcto_Tipo as diasVctoTipo,
                                                 T005_Pgto_Dias_Vcto_Valor as valorIntervaloDiaFixo,
                                                 T005_D027_Id as D027_Id,
                                                 T005_Flag_Frete_Primeira_Parcela as campoUsarFreteParcela,
                                                 T005_Flag_ST_Primeira_Parcela as campoUsarSTParcela,
                                                 T005_Flag_IPI_Primeira_Parcela as campoUsarIPIParcela,
                                                 T005_Pgto_Parcelas as Total_Parcelas,
                                                 T005_Valor_Total_IPI as valorTotalIPI,
                                                 T005_Valor_Total as valorTotal,
                                                 T005_Valor_Frete as valorTotalFrete,
                                                 T005_Valor_ICMS_Substituicao_Retencao as valorTotalST',
                                             'where' => "T005_Id='{$T005_Id}'",
                                             'tabelaPgto' => 'T090',
                                             'camposPgto' => 'T090_Prazos_Dias as prazoDias,T090_D027_Id as D027Pagamento,T090_Numero_Parcela as numeroParcela',
                                             'wherePgto' => "T090_T005_Id='{$T005_Id}'");
            // Gerar Parcelas
            $retorno         = $this->atualizarParcelas($dadosTabela);
            //Atualiza aliquotas
            $retorno         = $VEN002->atualizarAliquotasPedidos($T005_Id);  
            //Verifica liberaÃ§Ã£o de crÃ©dito          
            $creditoLiberado = $VEN002->verificaCreditoLiberado($T005_Id);

            $T005 = mysql_query("SELECT T005_Valor_Total,
                                        T005_Motivo_Pedido_Liberacao
                                   FROM T005
                                  WHERE T005_Id = '{$T005_Id}'");                       
            $mT005 = mysql_fetch_array($T005);
                       
            //ALTERAR TAMBEM OS DADOS DO PAGAMENTO QUANDO GERA O PEDIDO, CASO A PESSOA FAÃ‡A ALGUMA ALTERAÃ‡ÃƒO NO PORTADOR OU NO VALOR DA PARCELA ---LUCAS 31/07/2015
            $sql = mysql_query("SELECT T089_Valor_Parcela, 
                                       D027_Portador, 
                                       T089_D027_Id,
                                       T089_Numero_Parcela,
                                       T089_Data_Vencimento,
                                       T089_Prazos_Dias 
                                  FROM T089 
                             LEFT JOIN D027 ON D027_Id=T089_D027_Id
                                 WHERE T089_T003_Id = '{$T003_Id}'");

            while ($row = mysql_fetch_array($sql)){

                // Pega a parcela do pedido igual ao numero da parcela do orÃ§amento
                $T090 = mysql_query("SELECT T090_D027_Id,
                                            T090_Valor_Parcela,
                                            T090_Data_Vencimento,
                                            T090_Prazos_Dias 
                                       FROM T090 
                                      WHERE T090_T005_Id = '{$T005_Id}' 
                                        AND T090_Numero_Parcela='{$row['T089_Numero_Parcela']}'");

                $mT090 = mysql_fetch_array($T090);

                if($orcamento['T003_Valor_Total'] == $mT005['T005_Valor_Total']){

                    //  E feito o mesmo UPDATE separado abaixo
                    /*if($mT090['T090_D027_Id'] != $row['T089_D027_Id'] || $row['T089_Valor_Parcela'] != $mT090['T090_Valor_Parcela']){
                        mysql_query("UPDATE T090 SET T090_D027_Id = '{$row['T089_D027_Id']}', T090_Valor_Parcela = '{$row['T089_Valor_Parcela']}' WHERE T090_T005_Id = '{$T005_Id}' and T090_Numero_Parcela = '{$row['T089_Numero_Parcela']}'");   
                    } */
                    
                    if($mT090['T090_D027_Id'] != $row['T089_D027_Id']){
                        mysql_query("UPDATE T090 SET T090_D027_Id = '{$row['T089_D027_Id']}' WHERE T090_T005_Id = '{$T005_Id}' and T090_Numero_Parcela = '{$row['T089_Numero_Parcela']}'");
                    }   

                    if(($row['T089_Data_Vencimento'] != $mT090['T090_Data_Vencimento']) && ($g['C031']['dataVencimentoParcelasAoGerarPedido'] == 1)){
                        // dataVencimentoParcelasAoGerarPedido = 1 mantem o vencimento igual do orÃ§amento
                        mysql_query("UPDATE T090 SET T090_Data_Vencimento = '{$row['T089_Data_Vencimento']}' WHERE T090_T005_Id = '{$T005_Id}' and T090_Numero_Parcela = '{$row['T089_Numero_Parcela']}'");
                    }                     
                    
                    if($row['T089_Valor_Parcela'] != $mT090['T090_Valor_Parcela']){
                        mysql_query("UPDATE T090 SET T090_Valor_Parcela = '{$row['T089_Valor_Parcela']}' WHERE T090_T005_Id = '{$T005_Id}' and T090_Numero_Parcela = '{$row['T089_Numero_Parcela']}'");
                    }
                       
                    if($row['T089_Prazos_Dias'] != $mT090['T090_Prazos_Dias']){
                        mysql_query("UPDATE T090 SET T090_Prazos_Dias = '{$row['T089_Prazos_Dias']}' WHERE T090_T005_Id = '{$T005_Id}' and T090_Numero_Parcela = '{$row['T089_Numero_Parcela']}'");
                    }                    
                }
            }
            $error = mysql_error();
            if (!empty($error)) {
                mysql_query("ROLLBACK");
                return 'Erro: ' . $error;
            }
            //FIM

            if ($orcamento['T003_Flag_Acp'] != 4 && $creditoLiberado == false && $g['C031']['verificaCreditoLiberadoGerarPedido'] == 'S') {
                
                mysql_query("UPDATE T005 SET T005_Flag_Status=6, T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), T005_Flag_Separacao_Liberar='S', T005_Flag_Separacao='S',T005_Flag_Primeira_Liberacao_Credito = '0'  WHERE T005_Id='{$T005_Id}'");
                
                //Insere registros no histÃ³rico do pedido (T178)
                $mT005['T005_Motivo_Pedido_Liberacao'] = str_replace("Enviou para anÃ¡lise de crÃ©dito", "Enviou para anÃ¡lise de crÃ©dito (automaticamente ao gerar pedido)", $mT005['T005_Motivo_Pedido_Liberacao']);
                
                $mT005['T005_Motivo_Pedido_Liberacao'] = mysql_real_escape_string($mT005['T005_Motivo_Pedido_Liberacao']);
                mysql_query("insert into T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'{$mT005['T005_Motivo_Pedido_Liberacao']}','')");

                try {
                    require_once('bibliotecas/classes/Webhook.php');
                    Webhook::publishForCompany((int) $mT005['T005_C004_Id'], 'financial_analysis.requested', (int) $T005_Id);
                } catch (\Throwable $exception) {
                    // Webhook is auxiliary and must not cancel order generation.
                }
            } else {
                mysql_query("UPDATE T005 SET T005_Flag_Primeira_Liberacao_Credito = '1'  WHERE T005_Id='{$T005_Id}'");
            }

            // Retorna true, essa funÃ§Ã£o Ã© personalizada de acordo com a aplicaÃ§Ã£o
            // 1Âº utilizacaÃ§Ã£o APP resposta rapida cotaÃ§Ã£o compra - Felipe 21/10/2022
            $this->acoesFinaisGerarPedido($T003_Id);

            // VERIFICANDO SE GEROU CORRETAMENTE O T005 E OS T006
            $T005_T006=mysql_query("SELECT T005_Id,T006_Id from T005 LEFT JOIN T006 ON T006_T005_Id=T005_Id WHERE T005_Id='{$T005_Id}'");
            $mT005_T006=mysql_fetch_array($T005_T006);
            if($mT005_T006['T005_Id'] <=0 OR $mT005_T006['T006_Id'] <=0 ){
                mysql_query("ROLLBACK");
                return "Houve um problema na gravaÃ§Ã£o dos dados. Por favor, refaÃ§a a operaÃ§Ã£o";
            }

            mysql_query("COMMIT");


            //Busca D049_Id para executar function copiarEngenhariaProduto
            // Vai copiar a engenharia no fim porque start uma transaction na funcion copiarEngenhariaProduto
            // Para nÃ£o dar conflito com a transaÃ§Ã£o que jÃ¡ estÃ¡ ativa no gerarPedido, nÃ£o vai ser mais feito no while quando o item Ã© inserido no T006
            $D049 = mysql_query("SELECT T006_Id
                                   FROM T006 
                              LEFT JOIN D009 ON T006_D009_Id=D009_Id 
                              LEFT JOIN D049 ON D009_D049_Id=D049_Id 
                                  WHERE T006_T005_Id = '{$T005_Id}'
                                    AND D049_Flag_Tipo = 'PD'");
            if(mysql_num_rows($D049) > 0){
                require_once('bibliotecas/classes/VEN002.php');
                $VEN002 = new VEN002();
                while($mD049 = mysql_fetch_array($D049)){
                    $retorno = $VEN002->copiarEngenhariaProduto($mD049['T006_Id']);
                    if($retorno !== true){
                        return 'O pedido foi gerado com sucesso, mas houve um problema em copiar a engenharia dos produtos, favor entre no pedido e atualize a engenharia pelo botÃ£o Atualizar->Atualizar Engenharia';
                    }
                }
            }
            
            return true;
        } else {
            return "ERRO: T003_Id nÃ£o existe";
        }
    }
    // Miguel - 17/07/2026
    // Personalizado para forçar liberação de diretor quando a margem for menor que 30%
    public function verificaAlcadaNovo($T003_Id = false, $T005_Id = false, $acao = false)
    {
        global $g;

        // 1) Executa a lógica original (todas as regras de valor, prazo, desconto, portador, etc.)
        $retorno = parent::verificaAlcadaNovo($T003_Id, $T005_Id, $acao);

        // 2) Aplica a regra de margem mínima em cima
        $margemMinima = 30; // Percentual mínimo de margem para gerar sem alçada de diretor

        // Descobre o perfil do usuário
        $C007   = mysql_query("SELECT C007_Flag_Vendedor FROM C007 WHERE C007_Id='{$g['usuarioAtual']}'");
        $mC007  = mysql_fetch_array($C007);
        $perfil = $mC007['C007_Flag_Vendedor'];

        // Diretor gera sem alçada em qualquer margem
        if ($perfil == 'D') {
            return $retorno;
        }

        // Define tabela e Id dependendo se é orçamento (T003) ou pedido (T005)
        if ($T003_Id > 0) {
            $tabela = 'T003';
            $id     = $T003_Id;
        } else if ($T005_Id > 0) {
            $tabela = 'T005';
            $id     = $T005_Id;
        } else {
            return $retorno; // Não há id, não faz nada
        }

        // ===== BYPASS IDEMPOTENTE COM RE-CHECK POR VALOR =====
        // Regra: se o gestor já decidiu, **e** o valor total do pedido/orçamento
        // não mudou desde então, retorna sem tocar. Se o valor mudou (itens editados,
        // preço alterado, desconto novo), dropa o bypass e deixa a regra recalcular
        // a margem — se ainda estiver < mínimo, força nova análise.
        //
        // De onde vem o "valor aprovado":
        //   - T005: VEN013::ven013AprovarPedido (linha 98) grava T005_Liberou_Alcada_Valor_Total
        //     no momento da aprovação. Ao gerar pedido a partir de orçamento, a base
        //     VEN001 (linhas ~2009-2087) *copia* T003_Liberou_Alcada_Valor_Total para
        //     T005_Liberou_Alcada_Valor_Total, então o snapshot vem do orçamento aprovado.
        //   - T003: VEN005 grava T003_Liberou_Alcada_Valor_Total no fluxo desktop. No fluxo
        //     mobile (analiseOrcamentoAcao.php) esse campo NÃO é preenchido — nesse caso
        //     o bypass mantém o comportamento antigo (passa direto).

        if ($tabela == 'T005') {
            $sqlStatus = mysql_query("SELECT T005_Flag_Status,
                                             T005_Liberou_Alcada_C007_Id,
                                             T005_Liberou_Alcada_Data,
                                             T005_Liberou_Alcada_Valor_Total,
                                             T005_Valor_Total
                                        FROM T005
                                       WHERE T005_Id='{$id}'");
            $mStatus   = mysql_fetch_array($sqlStatus);

            // (2) pedido avançou no ciclo (ex: Flag_Status=6 aguardando faturamento).
            //     Mesmo early-return da base VEN001 (linha ~10683). Vale por si só:
            //     depois que o pedido avança, não faz sentido reabrir alçada de margem.
            if ($mStatus['T005_Flag_Status'] > 0
                && !in_array($mStatus['T005_Flag_Status'], ['13', '14', '15'])) {
                return $retorno;
            }

            // (1) gestor já decidiu (aprovou em VEN013:98 ou negou em VEN013:30).
            //     Só passa direto se o valor total não mudou desde a aprovação.
            if ($mStatus['T005_Liberou_Alcada_C007_Id'] > 0
                && $mStatus['T005_Liberou_Alcada_Data'] > 0) {
                $valorAprovado = (float) $mStatus['T005_Liberou_Alcada_Valor_Total'];
                $valorAtual    = (float) $mStatus['T005_Valor_Total'];
                if ($valorAprovado <= 0 || abs($valorAtual - $valorAprovado) < 0.01) {
                    return $retorno;
                }
                // Chegou aqui: valor mudou → cai fora do bypass, regra recalcula margem.
            }
        }

        if ($tabela == 'T003') {
            $sqlStatus = mysql_query("SELECT T003_Liberou_Alcada_C007_Id,
                                             T003_Liberou_Alcada_Data,
                                             T003_Liberou_Alcada_Valor_Total,
                                             T003_Valor_Total
                                        FROM T003
                                       WHERE T003_Id='{$id}'");
            $mStatus   = mysql_fetch_array($sqlStatus);

            if ($mStatus['T003_Liberou_Alcada_C007_Id'] > 0
                && $mStatus['T003_Liberou_Alcada_Data'] > 0) {
                $valorAprovado = (float) $mStatus['T003_Liberou_Alcada_Valor_Total'];
                $valorAtual    = (float) $mStatus['T003_Valor_Total'];
                if ($valorAprovado <= 0 || abs($valorAtual - $valorAprovado) < 0.01) {
                    return $retorno;
                }
                // Chegou aqui: valor mudou desde a aprovação → regra recalcula margem.
            }
        }
        // ==================================================

        // Busca valores atuais para recalcular a margem em tempo real
        $sql = mysql_query("SELECT {$tabela}_Valor_Total_Produtos     AS produtos,
                                   {$tabela}_Valor_Total_Custo        AS custo,
                                   {$tabela}_Motivo_Pedido_Liberacao  AS motivoAtual
                              FROM {$tabela}
                             WHERE {$tabela}_Id = '{$id}'");
        $row = mysql_fetch_array($sql);

        $valorProdutos = (float) $row['produtos'];
        $valorCusto    = (float) $row['custo'];

        // Divisão por zero: sem produtos, não bloqueia
        if ($valorProdutos <= 0) {
            return $retorno;
        }

        // Cálculo em tempo real, mesma fórmula do outro.php (variável $margem)
        $margemAtual = (($valorProdutos - $valorCusto) / $valorProdutos) * 100;

        if ($margemAtual < $margemMinima) {
            $margemFmt       = gCorrigeNumero($margemAtual, 2);
            $margemMinimaFmt = gCorrigeNumero($margemMinima, 2);

            $msgMargem = "<br /><b style=\"color:red\">Margem abaixo do mínimo permitido ({$margemFmt}% < {$margemMinimaFmt}%).</b><br />";

            // Remove qualquer mensagem antiga de margem para não duplicar em reprocessamentos
            $motivoLimpo = preg_replace(
                '#<br />\s*<b style="color:red">Margem abaixo do mínimo permitido \([^)]*\)\.</b>\s*<br />#u',
                '',
                $row['motivoAtual']
            );

            // Anexa o motivo da margem ao já existente (agora sem duplicação)
            $novoMotivo       = $motivoLimpo . $msgMargem;
            $novoMotivoEscape = mysql_real_escape_string($novoMotivo);

            // Força liberação de diretor
            mysqli_query("UPDATE {$tabela}
                             SET {$tabela}_Flag_Alcada = 'D',
                                 {$tabela}_Motivo_Pedido_Liberacao = '{$novoMotivoEscape}',
                                 {$tabela}_Flag_Status_Liberacao = 4
                           WHERE {$tabela}_Id = '{$id}'");
        }

        return $retorno;
    }
}















