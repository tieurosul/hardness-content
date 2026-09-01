<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class VEN002 extends VEN002_ {
	// defina os métodos para sobreescrever
	
	public function montarEmailPdf($T005_Id) {
		global $g, $confUsuario;
		
		$layout = 2;
		
		switch ($layout) {
			
			case 2:
				/**
				* SELECT's
				*/
				$C004 = mysql_query("SELECT * FROM C004 WHERE C004_Id = '{$g['empresaAtual']}'");
				$resC004 = mysql_fetch_array($C004);
				$footer = $resC004['C004_Observacao_Rodape_Pedido'];
				
				$T005_Id = mysql_real_escape_string($T005_Id);
				$sql = "SELECT *,T005_Prazos(T005_Id) as Prazos FROM T005 
						   LEFT JOIN D013 ON D013_Id=T005_D013_Id 
						   LEFT JOIN D024 ON D024_Id=T005_D024_Id 
						   LEFT JOIN D020 ON D020_Id=D024_D020_Id
						   LEFT JOIN D018 ON D018_Id=D024_D018_Id 
                           LEFT JOIN T005A ON T005_Id = T005A_T005_Id
						   WHERE T005_Id='{$T005_Id}'";
				$T005 = mysql_query($sql);
				$T005 = mysql_fetch_array($T005);
		
				$sql = <<<EOT
					 SELECT * FROM T006 
					LEFT JOIN D009 ON D009_Id=T006_D009_Id 
					LEFT JOIN D049 ON D049_Id=D009_D049_Id
					LEFT JOIN D001 ON D001_Id=D049_D001_Id
					LEFT JOIN T144 ON T144_D001_Id=D001_Id AND T144_Flag_Tipo != 'F'
					LEFT JOIN D037 ON D037_Id=T006_D037_Id
					LEFT JOIN D005 ON D005_Id=T006_D005_Id
                    LEFT JOIN T006A ON T006A_T006_Id = T006_Id
					WHERE T006_T005_Id = '{$T005_Id}' 
					GROUP BY T006_Id
					ORDER BY T006_Item_Pedido_Compra,T006_Id
EOT;
				$T006_sql = mysql_query($sql);				
				
				$sql = "SELECT D024_E_Mail FROM D024 WHERE D024_Id='{$T005['T005_D024_Id']}'";
				$D024 = mysql_query($sql);
				$D024 = mysql_fetch_array($D024);	
				
				$sql = "SELECT * FROM D013 WHERE D013_Id='{$T005['T005_D013_Id']}'";
				$D013 = mysql_query($sql);
				$D013 = mysql_fetch_array($D013);
				
				// Array de retorno para criação do e-mail
				$array = array();
				$array['Para'] = (!empty($T005['D013_E_Mail'])) ? $T005['D013_E_Mail'] : $D024['D024_E_Mail'];
				$array['Assunto'] = "Pedido Nº[{$T005_Id}]";
				
				/**
				* PDF
				*/
				
				// create new PDF document
				$pdf = new MYTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
				// set document information
				$pdf->SetCreator(PDF_CREATOR);
				$pdf->SetAuthor('Hardness Sistemas');
				// remove default header/footer
				$pdf->setPrintHeader(false);
				//$pdf->setPrintFooter(true);
				$pdf->my_footer = $footer;
				// set font
				$pdf->SetFont('helvetica', '', 7);
				//set auto page breaks
				$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
				//set image scale factor
				$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
				// add a page
				$pdf->AddPage();
				
				$html = '';
				/**
				* PDF - STYLE
				*/
				$html .= <<<EOT
				<style>
					.header .text {
						font-size: 25px;
					}
					.header .text2 {
						font-size: 35px;
						font-weight: bold;
					}
					.titulo {
						background-color: #E1ECF1;
					}
					.titulo_superior {
						font-size: 30px;
					}
					.itens td {
						border: 1px solid black;
					}
					.textIncluso {
						font-size: 18px;
						color: red;
						background-color: #E1ECF1;
					}
					
				</style>
EOT;
				
				/**
				* PDF - HEADER
				*/
				if(!empty($g['c004']['C004_Logo_Marca2'])){
					$pathinfo = pathinfo($g['c004']['C004_Logo_Marca2']);
					$caminhoImagem = "{$confUsuario['urlRaiz']}{$g['pathWeb']}{$g['pathDados']}imagens/logo/{$g['empresaAtual']}.2.{$pathinfo['extension']}";
					$caminhoImagem = "<img src=\"{$caminhoImagem}\">";
				}else{
					$caminhoImagem="";
				}
				// (TODO) passar para o arq. conf
				$caminhoImagem = str_replace('201.22.57.137:8282', 'eurosul', $caminhoImagem);
				$caminhoImagem = str_replace('201.22.57.137:8181', 'sigma', $caminhoImagem);
				if(!empty($T005['D024_Cnpj'])){
					$cpfCnpj = $T005['D024_Cnpj'];
				}else{
					$cpfCnpj = $T005['D024_Cpf'];
				}
				$tel="";
				if(!empty($D013['D013_Telefone_1'])){
					$tel = '('.$D013['D013_DDD_Telefone_1'].') '.$D013['D013_Telefone_1'];
				}else if(!empty($T005['D024_Telefone_1'])){
					$tel = '('.$T005['D024_DDD_Telefone_1'].') '.$T005['D024_Telefone_1'];
				}
				$T005['D024_Nome_Empresa']=substr($T005['D024_Nome_Empresa'],0,60);
				$dataEmissao = gCorrigeData($T005['T005_Data_Emissao']);
				// $html .= <<<EOT
					// <table class="header">
						// <tr>
							// <td width="150" align="center">{$caminhoImagem}</td>
							// <td width="40"></td>
							// <td align="left" class="text" width="330">
								// <b>{$T005['D024_Id']} - {$T005['D024_Nome_Empresa']}</b><br>
								// CNPJ: $cpfCnpj<br>
								// <b>Att: {$D013['D013_Nome_Contato']} - {$tel}</b><br>
								// {$T005['D024_Endereco']} - {$T005['D024_Numero']} <br>
								// {$T005['D024_Bairro']} - {$T005['D024_Cep']} - {$T005['D020_Nome_Cidade']} - {$T005['D018_UF']}
							// </td>
							// <td align="center" width="150" class="text2">
								// <br>
								// PEDIDO<br>Nº {$T005['T005_Id']}<br>{$dataEmissao}
							// </td>
						// </tr>
					// </table>
					// <br />
// EOT;
				$sqlC004 = "SELECT * FROM C004 left join D018 on D018_Id=C004_D018_Id left join D020 on D020_Id=C004_D020_Id WHERE C004_Id='{$g['empresaAtual']}'";
				$resultC004 = mysql_query($sqlC004);
				$linhaC004 = mysql_fetch_array($resultC004);
				
				$sqlC007 = "SELECT * FROM C007 WHERE C007_Id='{$T005['T005_C007_Id_Vendedor_Interno']}'";
				$resultC007 = mysql_query($sqlC007);
				$linhaC007 = mysql_fetch_array($resultC007);
				
				if(($T005['D024_Cnpj'] != "") || (!empty($T005['D024_Cnpj']))){
					$cpfCnpj = "<b>CNPJ:</b> ".$T005['D024_Cnpj'];
				}else{
					$cpfCnpj = "<b>CPF:</b> ".$T005['D024_Cpf'];
				}
				$T005_Data_Emissao = gCorrigeData($T005['T005_Data_Emissao']);
				$html .=<<<EOT
				<table>
					<tr>
						<td align="left">{$caminhoImagem}</td>
					</tr>
				</table>
				<br />
				<table class="header" width="100%">
					<tr>
						<td>
							<table align="left" style="font-size:24px;"  cellpadding="0">
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
						<td>
							<table align="left" style="font-size:24px;"  cellpadding="0">
								<tr>
									<td class="titulo_superior"><b><u>Cliente</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral">{$T005['D024_Id']} - {$T005['D024_Nome_Empresa']}</td>
								</tr>
								<tr>
									<td class="tdGeral">{$cpfCnpj}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Contato: </b>{$D013['D013_Nome_Contato']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Função: </b>{$D013['D013_Tipo_Contato']}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Tel. </b>{$tel}</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Ref:</b> </td>
								</tr>
							</table>
						</td>
						<td width=100px>
							<table align="left" style="font-size:24px;" cellpadding="0">
								<tr>
									<td class="titulo_superior"><b><u>Vendedor</u></b></td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Nº Pedido:</b> $T005_Id</td>
								</tr>
								<tr>
									<td class="tdGeral"><b>Data:</b> {$T005_Data_Emissao}</td>
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
					<table class="itens" cellspacing="0" cellpadding="2">
						<tr class="titulo">
							<td width="3%">It</td>
							<td width="5%">Código</td>
							<td width="33%">Descrição</td>
							<td align="right" width="6%">Quant</td>
							<td align="center" width="3%">Und</td>
							<td align="center" width="4%">ICM %</td>
EOT;
			//Verifica se existe algum produto com ST
			$sqlVerificaST = mysql_query("SELECT * FROM T006 WHERE T006_T005_Id = '{$T005_Id}' AND T006_Flag_ST='S' GROUP BY T006_Id");
			if(mysql_num_rows($sqlVerificaST) > 999999){
				$html .= <<<EOT
							<td align="center" width="8%">Unit <font style="color:red;"><b>sem</b></font> IPI R\$</td>
							<td align="center" width="4%">IPI</td>
							<td align="center" width="8%">Valor<br>ST R$</td>
							<td align="center" width="8%">Total <font style="color:red;"><b>com</b></font> IPI R\$</td>
EOT;
			}else{
				$html .= <<<EOT
							<td align="center" width="8%">Unit <font style="color:red;"><b>sem</b></font><br> IPI e ST R\$</td>
							<td align="center" width="4%">IPI %</td>
							<td align="center" width="6%">Valor<br>ST R$</td>
                            <td align="center" width="6%">Valor<br>FCT ST R$</td>
							<td align="center" width="8%">Total <font style="color:red;"><b>com</b></font> IPI e ST R\$</td>
EOT;
			}
				$html .= <<<EOT
							<td align="center" width="7%">NCM</td>
							<td width="8%">Link</td>
						</tr>
EOT;
				$count = 1;
				while($T006 = mysql_fetch_array($T006_sql)){
					$codigo = !empty($T006['T006_Codigo_Substituto']) ? $T006['T006_Codigo_Substituto'] : $T006['T006_Codigo_Produto'];
					$qtd = gCorrigeNumero($T006['T006_Quantidade']);
					$unitario = gCorrigeNumero($T006['T006_Valor_Preco_Sem_Desconto_Unitario'],4);
					//$total = gCorrigeNumero($T006['T006_Valor_Total_Preco_Sem_Desconto']+$T006['T006_Valor_IPI']+$T004['T006_Valor_ICMS_Substituicao']);
					$total = gCorrigeNumero($T006['T006_Valor_Total_Preco']+$T006['T006_Valor_IPI']+$T006['T006_Valor_ICMS_Substituicao_Retencao']);
					$ipi = gCorrigeNumero($T006['T006_Aliquota_IPI']);
					$icm = number_format($T006['T006_Aliquota_ICMS'],0);
					$valorST = gCorrigeNumero($T006['T006_Valor_ICMS_Substituicao_Retencao']);
                    $valorFCPST = gCorrigeNumero($T006['T006A_Valor_FCP_ST']);
					$link = '';
					if ($T006["T144_Url"]) {
						$T006["T144_Url"] = strpos($T006["T144_Url"], 'http') !== false ? $T006["T144_Url"] : 'http://' . $T006["T144_Url"];
						$link = "<a href=\"{$T006["T144_Url"]}\">ver produto</a>";
					}
					//if (!empty($T006["T144_Url"]))
					//	$corpo.='<a href="'.$T006["T144_Url"].'" target=_blank style="text-decoration:none;font-weight:bold;color:#000">Ver Produto</a>';
					$T006['D001_Especificacoes'] = ($T006['D001_Especificacoes'] != '') ? "<br /><i>".$T006['D001_Especificacoes']."</i>" : '';
					$html .= <<<EOT
					<tr>
						<td align="center">
							{$T006['T006_Item']}
						</td>
						<td align="center">
							{$codigo}
						</td>
						<td align="left">
							<b>{$T006['T006_Descricao_Produto']}<br><i>{$T006['T006_Observacao']}</i>{$T006['D001_Especificacoes']}</b>
						</td>
						<td align="right">
							<b>{$qtd}</b>
						</td>
						<td>
							{$T006['D037_Unidade']}
						</td>
						<td align="center">
							{$icm}
						</td>
						<td align="right">
							<b>{$unitario}</b>
						</td>
						<td align="center">
							{$ipi}
						</td>
						<td align="right">
							{$valorST}
						</td>
                        <td align="right">
							{$valorFCPST}
						</td>
						<td align="right">
							{$total}
						</td>
						<td align="center">
							{$T006['D005_Classificacao_Fiscal']}
						</td>
						<td>
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
				SELECT * FROM T147 
				WHERE T147_T005_Id='{$T005_Id}'
EOT;
				$result = mysql_query($sql);
				
				$valorTotalOrcamento = $T005['T005_Valor_Total'];
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
					while($T147 = mysql_fetch_array($result)){
						$titulo = $T147['T147_Titulo'] . " - " . $T147['T147_Descricao'];
						$preco = gCorrigeNumero($T147['T147_Valor_Unitario']);
						$valorTotal += ($T147['T147_Quantidade'] * $T147['T147_Valor_Unitario']);
						$html .= <<<EOT
						<tr>
							<td align="center">
								{$T147['T147_Id']}
							</td>
							<td align="left">
								{$titulo}
							</td>
							<td align="right">
								{$T147['T147_Quantidade']}
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
				
				switch ($T005['T005_Flag_Frete']) {
					case '0':
						$T005_Frete = "Por conta do emitente";
						break;
					case '1':
						$T005_Frete = "Por conta do destinatário";
						break;
					case '2':
						$T005_Frete = "Terceiros";
						break;
					case '9':
						$T005_Frete = "Sem frete";
						break;
					default:
						$T005_Frete = "Não informado";
						break;
				}
				
				/**
				* PDF - TOTAIS
				*/
				$valorTotalProd = gCorrigeNumero($T005['T005_Valor_Total_Produtos']);
				$ipi = gCorrigeNumero($T005['T005_Valor_Total_IPI']);
				$valorTotal = gCorrigeNumero($T005['T005_Valor_Total']);
				$valorST = gCorrigeNumero($T005['T005_Valor_ICMS_Substituicao_Retencao']);
                $valorFCPST = gCorrigeNumero($T005['T005A_Valor_FCP_ST']);
				$html .= <<<EOT
					<br><br />
					<table width=>
						<tr>
							<td align="left" width="105">Prazo de Entrega: </td><td align="left" width="330"><b>{$T005['T005_Prazo_Entrega']}</b></td>
							<td align="left" width="105">Valor total Produtos: </td><td  align="left" width="100"><b>R$ {$valorTotalProd}</b></td>
						</tr>
						<tr>
							<td align="left" width="105">Frete: </td><td align="left"><b>{$T005_Frete}</b></td>
							<td align="left" width="105">Valor total IPI: </td><td align="left"><b>R$ {$ipi}</b></td>
						</tr>
						<tr>
							<td align="left" width="105">Forma pagamento: </td><td align="left"><b>{$T005['Prazos']}</b></td>
							<td align="left" width="105">Valor total ST: </td><td  align="left"><b>R$ {$valorST}</b></td>
						</tr>
						<tr>
							<td align="left" width="105">Validade proposta: </td><td align="left" ><b>{$T005['T005_Validade_Proposta']}</b></td>
                            <td align="left" width="105">Valor total FCP ST: </td><td  align="left"><b>R$ {$valorFCPST}</b></td>
						</tr>
                        <tr>
							<td align="left" width="105">Validade proposta: </td><td align="left" ><b></b></td>
							<td align="left" width="105">Valor total: </td><td  align="left" style="font-size:30px"><b>R$ {$valorTotal}</b></td>
						</tr>
					</table>
					<br>
EOT;
				if (!empty($T005["T005_Observacao"])) {
					$html .= '<br><span align="left">Observação: <br><b>'.str_replace(chr(10),'<br>',$T005['T005_Observacao']).'</b></span>';
				}
				// Felipe Kadanos - 23/03/2025
				// Personalizado a pedido do Miguel pois eles não vão mais utilizar
				// Assinatura
				// if (!empty($g['c007']['C007_Email_Assinatura']) && trim($g['c007']['C007_Email_Assinatura']) != '<br>') {
				// 	$assinatura = gCorrigeAssinatura($g['c007']['C007_Email_Assinatura']);
				// 	$html .= "<br /><br /><span align=\"left\" style=\"font-size: 25px;\">{$assinatura}</span>";
				// }									
			break;
		}
		

		/**
		* PDF - GERA!
		*/
		set_time_limit(0);
		$pdf->writeHTML($html, true, false, true, false, 'C');
		// reset pointer to the last page
		$pdf->lastPage();
		// get data
		$data = $pdf->getPDFData();
		
		
		// Gravar arquivo no disco
		$anexoDir = $g['pathDados'] . 'tmp/';
		is_dir($anexoDir) or mkdir($anexoDir, 0777, true);
		$anexoNome = "{$anexoDir}pedido-{$T005_Id}.pdf";
		@unlink($anexoNome);
		$h = fopen($anexoNome, 'w');
		fwrite($h, $data);
		fclose($h);
		// Adiciona como anexo
		$array['Anexos'] = array($anexoNome);
		$array['AnexosWeb'] = array('/hardness3/' . $anexoNome);
		// Logo 		
		if(!empty($g['c004']['C004_Logo_Marca'])){
			$pathinfo = pathinfo($g['c004']['C004_Logo_Marca']);
			$caminhoImagem = "{$confUsuario['urlRaiz']}{$g['pathWeb']}{$g['pathDados']}imagens/logo/{$g['empresaAtual']}.{$pathinfo['extension']}";
		}else{
			$caminhoImagem="";
		}
		// (TODO) passar para o arq. conf
		$caminhoImagem = str_replace('201.22.57.137:8282', 'eurosul', $caminhoImagem);
		$caminhoImagem = str_replace('201.22.57.137:8181', 'sigma', $caminhoImagem);
		// Variaveis enviadas para o modulo de e-mails (que serão gravadas na tabela T139)
		$array['Corpo'] = "<img src='{$caminhoImagem}' style='margin-left:10px'><br /><br /><br /><br /><font style='font-size:16px'>Segue pedido de venda número <b>$T005_Id</b> em anexo.</font><br /><br /><br />";
		$array['D024_Id'] = $T005['T005_D024_Id'];
		$array['T139_Valor_Total'] = $T005['T005_Valor_Total'];
		$sql = "SELECT COUNT(*) FROM T006 where T006_T005_Id='{$T005_Id}'";
		$registros = mysql_query($sql);
		$registros = mysql_fetch_array($registros);
		$array['T139_Numero_Registros'] = $registros[0];
		
		return $array;
	
	
	}

    /**
     * verificaCreditoLiberado
     *
     * @param String $T005_Id
     * @return bool
     */    
	 // PERSONALIZACAO PARA SOMAR OS LIMITES E CONTAS A RECEBER PELO RADICAL DO CNPJ
    public function verificaCreditoLiberado($T005_Id) {
        global $g, $pedirSegundaLiberacao;

        log("entrou em verificaCreditoLiberado");

        $msg = "";
		$total = mysql_fetch_assoc(mysqli_query("SELECT T005_Valor_Total FROM T005 WHERE T005_Id='{$T005_Id}'"));
        if ($total['T005_Valor_Total'] >= 150000) {
            $msg = mysql_real_escape_string("- Pedido maior ou igual a <b>R$ 150.000,00</b>.");
        }
        
        if ($g['C031']['usarControleCredito'] == 'S') {
            //Verifica se é pagamento a vista e se o portador está especificado na configuração global
            if(!empty($g['C031']['portadoresLiberadosAVista'])){
                $sqlT090 = mysql_query("SELECT T090_Prazos_Dias, T090_D027_Id FROM T090 WHERE T090_T005_Id = '{$T005_Id}'");
                if(mysql_num_rows($sqlT090) == 1){
                    $rowT090 = mysql_fetch_array($sqlT090);
                    $portadoresValidos = explode(",", str_replace(" ", "", trim($g['C031']['portadoresLiberadosAVista'],",")));
                    if(in_array($rowT090['T090_D027_Id'], $portadoresValidos) && $rowT090['T090_Prazos_Dias'] == '0'){
                        $msgLiberado = mysql_real_escape_string("<b>Motivo não caiu na liberação de crédito:</b> <br />- Liberado por pagamento a vista. Prazo pedido: ".$rowT090['T090_Prazos_Dias']." dias, portadores liberados a vista: ".$g['C031']['portadoresLiberadosAVista']);
                        log($msgLiberado);
                        mysql_query("UPDATE T005 SET T005_Motivo_Pedido_Liberacao='{$msgLiberado}' WHERE T005_Id='{$T005_Id}'");
                        mysql_query("insert into T178 (T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) values ('{$T005_Id}','{$g['usuarioAtual']}',CURRENT_TIMESTAMP(),'$msgLiberado','')");
                        return true;
                    }
                }
            }

            if(!empty($g['C031']['portadoresLiberados'])){
                $portadoresValidos = explode(",", str_replace(" ", "", trim($g['C031']['portadoresLiberados'],",")));
                $sqlT090 = mysql_query("SELECT T090_D027_Id FROM T090 WHERE T090_T005_Id = '{$T005_Id}' GROUP BY T090_D027_Id");
                $liberado = true;
                while($rowT090 = mysql_fetch_array($sqlT090)){
                    if(!in_array($rowT090['T090_D027_Id'], $portadoresValidos) && $liberado){
                        $liberado = false;
                        $msg .= ($msg != '') ? '<br>' : '';
                        $msg .= mysql_real_escape_string("- Obrigatório análise de crédito para o portador {$rowT090['T090_D027_Id']}");
                    }
                }
                if($liberado){
                    $msgLiberado = mysql_real_escape_string("<b>Motivo não caiu na liberação de crédito:</b> <br />- Portador não necessita análise de crédito, portadores liberados: ".$g['C031']['portadoresLiberados']);
                    mysql_query("UPDATE T005 SET T005_Motivo_Pedido_Liberacao='{$msgLiberado}' WHERE T005_Id='{$T005_Id}'");
                    mysql_query("insert into T178 (T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) values ('{$T005_Id}','{$g['usuarioAtual']}',CURRENT_TIMESTAMP(),'$msgLiberado','')");
                    return true;
                }
            }

            $T005  = mysql_query("SELECT * FROM T005 
                                      LEFT JOIN D024 ON D024_Id=T005_D024_Id 
                                      LEFT JOIN D006 ON D006_Id=T005_D006_Id 
                                          WHERE T005_Id='{$T005_Id}'");
            $mT005 = mysql_fetch_array($T005);
           
            $duplicatasVencidas = 0;
            $sqlT002            = mysql_query("SELECT * FROM T002 
                                                   LEFT JOIN T117 ON T117_Id = T002_T117_Id 
                                                       WHERE T002_D024_Id = '{$mT005['T005_D024_Id']}' 
                                                         AND T002_Data_Recebimento = '0000-00-00' 
                                                         AND (T002_T002_Id_Agrupado<=0 OR T002_T002_Id_Agrupado is null)");
            
            log('D024='.$mT005['T005_D024_Id']);
            
            while ($T002 = mysql_fetch_array($sqlT002)) {
                if ($T002['T117_Id'] > 0 && $T002['T117_Data_Vencimento'] < date("Y-m-d")) {
                    $duplicatasVencidas++;
                } else if ($T002['T002_Data_Vencimento'] < date("Y-m-d")) {
                    $duplicatasVencidas++;
                }
            }

            $D024_Id = $mT005['D024_Id'];
            $filtroSaldo = "D024_Id = '{$D024_Id}'";

            //calcula a soma dos limites das empresas vinculadas a um Cnpj Matriz
            if($g['C031']['somarLimiteCnpjMatriz'] == 'S'){
				// Feita aqui a alteração para pegar montar o filtro da busca pelo radical do CNPJ
                $filtroSaldo = "SUBSTR(D024_Cnpj,1,10) = SUBSTR('{$mT005['D024_Cnpj']}',1,10)";
                //$msg .= $filtroSaldo;

                $dadosLimite = mysql_query("SELECT SUM(D024_Valor_Limite) AS D024_Valor_Limite, 
                                        SUM(D024_Valor_Credito_Antecipado) AS D024_Valor_Credito_Antecipado, 
                                        SUM(D024_Valor_Credito_Troca) AS D024_Valor_Credito_Troca 
                                        FROM D024 
                                        WHERE $filtroSaldo");
                $dadosLimite = mysql_fetch_array($dadosLimite);
                $mT005['D024_Valor_Limite'] = $dadosLimite['D024_Valor_Limite'];
            }
        
            $valorLimite     = $mT005['D024_Valor_Limite'];
            
            //Calcula saldo do cliente
            $sqlSaldo    = "SELECT ifnull(sum(if(T002_Data_Vencimento<curdate(),T002_Valor_Total,0)),0) as Vencido, 
                                ifnull(sum(if(T002_Data_Vencimento>=curdate(),T002_Valor_Total,0)),0) as Vencer
                            FROM T002
                            LEFT JOIN D024 ON D024_Id=T002_D024_Id
                            WHERE $filtroSaldo
                            AND T002_Data_Recebimento='0000-00-00' 
                            AND (T002_T002_Id_Agrupado<=0 OR T002_T002_Id_Agrupado is null)";
            $resultSaldo = mysql_query($sqlSaldo);
            $linhaSaldo  = mysql_fetch_array($resultSaldo);
            
            //Verifica os pedidos em aberto
            $sqlPedidos   = mysql_query("SELECT ifnull(sum(T005_Valor_Total),0) as Pedidos
                                       FROM T005 
                                  LEFT JOIN T007 ON T007_T005_Id=T005_Id 
                                  LEFT JOIN D024 ON D024_Id=T005_D024_Id
                                      WHERE $filtroSaldo
                                        AND T005_Flag_Status != 8
                                        AND (T005_T005_Id_Agrupado<=0 or T005_T005_Id_Agrupado is null) 
                                        AND T007_Id IS NULL");
            $linhaPedidos = mysql_fetch_array($sqlPedidos); 

            // CALCULA SALDO LIMITE
            $saldoAtual = ($valorLimite - ($linhaSaldo['Vencido'] + $linhaSaldo['Vencer'] + $linhaPedidos['Pedidos']));

            $msgLiberado = "<b>Motivo não caiu na análise de crédito:</b> <br />";
            if ($duplicatasVencidas > 0) {
                $msg .= ($msg != '') ? '<br>' : '';
                $msg .= "- Cliente possui duplicatas atrasadas.";
            } else {
                $msgLiberado .= "- Cliente não possui duplicatas atrasadas.";
            }

            if ($mT005['D024_Flag_Liberado'] == "N") {
                $msg .= ($msg != '') ? '<br>' : '';
                $msg .= "- Cliente possui o campo 'Crédito Liberado' definido como NÃO.";
            } else {
                $msgLiberado .= ($msgLiberado != '') ? '<br>' : '';
                $msgLiberado .= "- Cliente possui o campo 'Crédito Liberado' definido como SIM.";
            }
    
            //$msg .= "$saldoAtual = ($valorLimite - ({$linhaSaldo['Vencido']} + {$linhaSaldo['Vencer']} + {$linhaPedidos['Pedidos']}));";

            if ($saldoAtual < 0) {
                $msg .= ($msg != '') ? '<br>' : '';
                $msg .= "- Cliente possui limite de crédito insuficiente. Valor pedido R$ ".number_format($mT005['T005_Valor_Total'],2,',','.')." Saldo R$ ".number_format($saldoAtual,2,',','.').".";
            } else {
                $msgLiberado .= ($msgLiberado != '') ? '<br>' : '';
                $msgLiberado .= "- Cliente possui limite de crédito suficiente. Valor pedido R$ ".number_format($mT005['T005_Valor_Total'],2,',','.')." Saldo R$ ".number_format($saldoAtual,2,',','.').".";
            }

            if($mT005['D024_Flag_Pre_Cadastro'] == 'S'){
                $msg .= ($msg != '') ? '<br>' : '';
                $msg .= "- O cliente está em pré cadastro.";
            } else {
                $msgLiberado .= ($msgLiberado != '') ? '<br>' : '';
                $msgLiberado .= "- O cliente não está em pré cadastro";   
            }
   
            if(!empty($g['C031']['portadoresNaoLiberado'])){
                $portadoresValidos = explode(",", str_replace(" ", "", trim($g['C031']['portadoresNaoLiberado'],",")));
                $sqlT090 = mysql_query("SELECT T090_D027_Id, D027_Portador FROM T090 LEFT JOIN D027 ON D027_ID=T090_D027_Id WHERE T090_T005_Id = '{$T005_Id}' GROUP BY T090_D027_Id");
                $liberado = true;
                while($rowT090 = mysql_fetch_array($sqlT090)){
                    if(in_array($rowT090['T090_D027_Id'], $portadoresValidos)){
                       $liberado = false;
                       break;
                    }
                }
                if(!$liberado){
                    $msg .= ($msg != '') ? '<br>' : '';
                    $msg .= mysql_real_escape_string("- Obrigatório analise crédito para {$rowT090['D027_Portador']}");
                }
            }

            if ($mT005['D006_Flag_Venda_Compra_Outros']=='V'){
                //Verifica número de dias desde a última venda
                $sqlUltVenda=mysql_query("SELECT datediff(curdate(),T007_Data_Emissao) as ultVendaDias,
                                                 T007_Id
                                            FROM T007
                                       LEFT JOIN D006 ON D006_Id = T007_D006_Id    
                                           WHERE T007_D024_Id='{$mT005['D024_Id']}'
                                             AND T007_Flag_Cancelada!='S'
                                             AND D006_Flag_Venda_Compra_Outros='V'
                                             AND D006_Flag_Devolucao!='S'
                                           order by T007_Id desc limit 1");
                log("SELECT datediff(curdate(),T007_Data_Emissao) as ultVendaDias,
                                                 T007_Id
                                            FROM T007
                                       LEFT JOIN D006 ON D006_Id = T007_D006_Id    
                                           WHERE T007_D024_Id='{$mT005['D024_Id']}'
                                             AND T007_Flag_Cancelada!='S'
                                             AND D006_Flag_Venda_Compra_Outros='V'
                                             AND D006_Flag_Devolucao!='S'
                                           order by T007_Id desc limit 1");
                $mUltVenda  = mysql_fetch_array($sqlUltVenda);
                if ($mUltVenda['T007_Id']<=0) {
                    $msg .= ($msg != '') ? '<br>' : '';
                    $msg .= mysql_real_escape_string("- Primeira venda para este cliente");
                } else if ($g['C031']['diasUltimaVendaPedirAnaliseCredito']>0 and $mUltVenda['ultVendaDias']>=$g['C031']['diasUltimaVendaPedirAnaliseCredito']) {
                    $msg .= ($msg != '') ? '<br>' : '';
                    $msg .= mysql_real_escape_string("- Última venda superior a {$g['C031']['diasUltimaVendaPedirAnaliseCredito']} dias. ({$mUltVenda['ultVendaDias']} dias)");
                }
            }

            if($msg != ''){
                $msg = mysql_real_escape_string($msg);
                //$msgCab = "<b>Enviou para análise de crédito.</b><br /><br /> Motivo(s):<br />";
                //$msg = $msgCab . $msg;

                log($msg);
                mysql_query("UPDATE T005 SET T005_Motivo_Pedido_Liberacao='{$msg}' WHERE T005_Id='{$T005_Id}'", false);
                return false;
            } else {
                $msgLiberado = mysql_real_escape_string($msgLiberado);
                log($msgLiberado);
                mysql_query("UPDATE T005 SET T005_Motivo_Pedido_Liberacao='{$msgLiberado}' WHERE T005_Id='{$T005_Id}'", false);
                mysql_query("insert into T178 (T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) values ('{$T005_Id}','{$g['usuarioAtual']}',CURRENT_TIMESTAMP(),'$msgLiberado','')");
                return true;
            }
        } else {
            $msgLiberado = mysql_real_escape_string("- A análise de crédito está desativada na configuração");
            log($msgLiberado);
            mysql_query("UPDATE T005 SET T005_Motivo_Pedido_Liberacao='{$msgLiberado}' WHERE T005_Id='{$T005_Id}'");
            mysql_query("insert into T178 (T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) values ('{$T005_Id}','{$g['usuarioAtual']}',CURRENT_TIMESTAMP(),'$msgLiberado','')");
            return true;
        }
    }
	
	// Felipe Kadanos - 09/09/2025
	// Personalizado para validar se foi adicionado um documento antes de faturar. Solicitado pelo Miguel
	public function faturarPedido($T005_Id)
    {
        global $g, $Perfil_Usuario, $mensagem, $pedirSegundaLiberacao;
        unset($pedirSegundaLiberacao);
        $T005  = mysql_query("SELECT T005_Flag_Status,
                                     T005_D148_Id_Entrega,
                                     D024_D030_Id,
                                     T005_D148_Id_Cobranca,
                                     T005_Flag_Frete,
                                     T005_D024_Id,
                                     T005_Valor_Total_Produtos,
                                     T005_Liberou_Credito_Valor_Total,
                                     T005_Liberou_Credito_Desconto,
                                     T005_Liberou_Credito_Prazo_Medio,
                                     T005A_Liberou_Credito_Portador,
                                     T005_D027_Id,
                                     T005_Liberou_D024_Id,
                                     T005_Hora_Emissao,
                                     T005_Data_Emissao,
                                     T005_Flag_ACP, 
                                     Cobranca.D148_D030_Id as paisCobranca, 
                                     Entrega.D148_D030_Id as paisEntrega,
                                     T224_Flag_Tipo
                                FROM T005
                           LEFT JOIN T005A ON T005A_T005_Id = T005_Id
                           LEFT JOIN D024 ON D024_Id=T005_D024_Id 
                           LEFT JOIN D148 AS Entrega  ON Entrega.D148_Id = T005_D148_Id_Entrega 
                           LEFT JOIN D148 AS Cobranca ON Cobranca.D148_Id = T005_D148_Id_Cobranca 
                           LEFT JOIN T224 ON T224_Id = T005_T224_Id
                               WHERE T005_Id='{$T005_Id}'");
        $mT005 = mysql_fetch_array($T005);

        if($mT005['T005_Flag_Status'] > 0 AND $mT005['T005_Flag_Status']!=14 AND $mT005['T005_Flag_Status']!=15 AND $mT005['T005_Flag_Status']!=9 AND $mT005['T005_Flag_Status']!=26 AND $mT005['T005_Flag_Status']!=22){
            return "Este pedido já está faturado.";
        }

		// Validação para ver se tem algum doc
        $T167 = mysql_query("SELECT T167_Id FROM T167 WHERE T167_T005_Id = '{$T005_Id}'");
        if (mysql_num_rows($T167) <= 0) {
            return "Sem documentos anexados. Por favor verifique a aba documentos.";
        }

        if($mT005['T005_Flag_Status'] == 22 ){
            // Trava para o modelo expedicao 2, os pedidos podem ser divido na tela de faturamento comercial/balcao
            // E depois faturados, precisa verificar se a quantidade separação está batendo para lançamento no historico ao gerar nota
            $res = mysql_query("SELECT T006_Id,
                                       T238_Id,
                                       T006_Codigo_Produto,
                                       T006_Descricao_Produto,
                                       T006_Quantidade,
                                       SUM(T238_Quantidade_Separacao) AS SOMAT238
                                  FROM T006 
                             LEFT JOIN T238 ON T238_T006_Id = T006_Id 
                             LEFT JOIN D006 ON D006_Id=T006_D006_Id
                                 WHERE T006_T005_Id = '{$T005_Id}' 
                                   AND D006_Flag_Estoque != 'N'
                              GROUP BY T006_Id 
                                HAVING T238_Id IS NULL OR T006_Quantidade != SOMAT238");

            if (mysql_num_rows($res) > 0) {
                $produtos= '';
                while($row = mysql_fetch_array($res)){
                    $produtos .= $row['T006_Codigo_Produto'] .' - '. $row['T006_Descricao_Produto'] .' - Separado: '. gCorrigeNumero($row['SOMAT238']).' / Quantidade: '.gCorrigeNumero($row['T006_Quantidade'])."\n";
                }
                
                return "Os produtos abaixo não estão separados corretamente\n\n".$produtos;
            }
        }

        //Verifica como as CFOP's estão configuradas para gerar contas
        $cSql_T006_CFOP = mysql_query("SELECT D006_Flag_Gera_Contas,
                                                D006_Codigo_CFOP
                                            FROM T006
                                    LEFT JOIN D006 ON D006_Id=T006_D006_Id
                                        WHERE T006_T005_Id='{$T005_Id}'
                                        GROUP BY D006_Flag_Gera_Contas");
        if (mysql_num_rows($cSql_T006_CFOP) > 1) {
            $CFOP_Utilizados = '';
            while ($mSql_T006_CFOP = mysql_fetch_array($cSql_T006_CFOP)) {
                $CFOP_Utilizados .= $mSql_T006_CFOP['D006_Codigo_CFOP']."; ";
            }
            return "Foi selecionada mais de uma CFOP com o parametro gerar contas DIFERENTE.\nO gerar contas deve ser igual para todas as CFOP's.\n\nCFOP's utilizadas: " . $CFOP_Utilizados;
        } else if (mysql_num_rows($cSql_T006_CFOP) == 0) {
            $cSql_T006_CFOP    = mysql_query("SELECT D006_Flag_Gera_Contas,
                                                        D006_Codigo_CFOP
                                                FROM T005
                                            LEFT JOIN D006 ON D006_Id=T005_D006_Id
                                                WHERE T005_Id='{$T005_Id}'
                                            GROUP BY D006_Flag_Gera_Contas");
            $mT006_CFOP        = mysql_fetch_array($cSql_T006_CFOP);
            $cFlag_Gera_Contas = $mT006_CFOP['D006_Flag_Gera_Contas'];
        } else {
            $mT006_CFOP        = mysql_fetch_array($cSql_T006_CFOP);
            $cFlag_Gera_Contas = $mT006_CFOP['D006_Flag_Gera_Contas'];
        }
        
        if (($cFlag_Gera_Contas != 'N') AND ($cFlag_Gera_Contas != 'P') AND ($cFlag_Gera_Contas != 'R')) {
            return "O campo 'Gera contas' do cadastro da CFOP está com um valor inválido.\n\nAcesse: Fiscal -> CFOP -> Selecione a CFOP -> aba Financeiro -> Campo 'Gera Contas'.\n\nDeve estar preenchido com: 'NÃO ALTERA CONTAS', 'RECEBER' ou 'PAGAR'.\n\nPor favor, verifique.";
        }

        $retornoValidacao = $this->excecaoValidaPedido($T005_Id);
        if ($retornoValidacao !== true)
        {
            return $retornoValidacao;
        }
        
        /*$retornoValidacao = $this->validador(null, $T005_Id, null);
        if($retornoValidacao !== true){
            return $retornoValidacao;
        }*/

        $erros = '';
        // Verifica se o endereço de entrega e cobrança está preenchido com o país.
        if(($mT005['T005_D148_Id_Entrega'] > 0) AND ($mT005['paisEntrega'] <= 0)){
            $erros .= "País do endereço de entrega, no cadastro de endereço do cliente, não informado. \n";
        }
        if($mT005['D024_D030_Id'] <= 0){
            $erros .= "País do endereço padrão, no cadastro do cliente, não informado. \n\n";
        }
        if(($mT005['T005_D148_Id_Cobranca'] > 0) AND ($mT005['paisCobranca'] <= 0)){
            $erros .= "País do endereço de cobrança, no cadastro de endereço, do cliente não informado. \n";
        }

        if(!empty($erros)){
            return $erros;
        }

        // Irá verificar se deve validar dados do pedido de transferência, hoje não valida pagamento e transportadora. / T224_Flag_Tipo = 2 OC de transferencia
        $validaPedidoTransferencia = ($mT005['T224_Flag_Tipo'] == '2' && $g['C031']['validaDadosAoFaturarPedidoTransferencia'] == 'N') ? 'N' : 'S';
        $permitirVenda = $g['C031']['permitirFaturarSemEstoque'];

        $sqlEstoque  = mysql_query("SELECT T006_Id
                                      FROM T006 
                                 LEFT JOIN T005 ON T005_Id=T006_T005_Id
                                 LEFT JOIN T055 ON T055_D009_Id=T006_D009_Id AND T055_T075_Id=T006_T075_Id
                                 LEFT JOIN D006 ON D006_Id=T006_D006_Id
                                     WHERE T006_T005_Id='{$T005_Id}' 
                                       AND (T006_Quantidade>IF(IFNULL(T006_T075_Id,0)>0,IFNULL(T055_Quantidade,0),IF(IFNULL(T006_T206_Id,0)>0,D009_Quantidade_DF(T006_D009_Id,T006_T206_Id, T006_Id),IFNULL(T006_Quantidade_Estoque,0))))
                                       AND '{$permitirVenda}'='N' 
                                       AND D006_Flag_Estoque = 'D'
                                       AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id");
        
        $rowsEstoque = mysql_num_rows($sqlEstoque);
        if ($rowsEstoque > 0) {
            $msg = ($rowsEstoque > 1 ? "Há itens sem estoque suficiente, favor verificar" : "Existe item sem estoque suficiente, favor verificar");
            return $msg;
        }

        $estoqueForaErro = 0;
        $sqlEstoqueFora = mysql_query("SELECT T006_Id, T006_D009_Id, D009_D049_Id, T006_Quantidade, T006_C004_Id_Estoque 
                                        FROM T006 
                                    LEFT JOIN D009 ON D009_Id=T006_D009_Id 
                                    LEFT JOIN T005 ON T005_Id=T006_T005_Id
                                    LEFT JOIN D006 ON D006_Id=T006_D006_Id  
                                       WHERE T006_T005_Id='{$T005_Id}' 
                                        AND '{$permitirVenda}'='N' 
                                        AND D006_Flag_Estoque = 'D'
                                        AND (T006_T206_Id IS NULL OR T006_T206_Id = '') 
                                        AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) != T005_C004_Id");
        while($rowEstoqueFora = mysql_fetch_array($sqlEstoqueFora)){
            $sqlD009 = mysql_query("SELECT D009_Id FROM D009 WHERE D009_D049_Id = '{$rowEstoqueFora['D009_D049_Id']}' AND D009_C004_Id = '{$rowEstoqueFora['T006_C004_Id_Estoque']}' AND D009_Quantidade_Estoque_Liquido < {$rowEstoqueFora['T006_Quantidade']}");
            if(mysql_num_rows($sqlD009) > 0){
                $estoqueForaErro++;
            }
        }

        if ($estoqueForaErro > 0) {
            $msg = ($estoqueForaErro > 1 ? "Há itens sem estoque suficiente, favor verificar" : "Existe item sem estoque suficiente, favor verificar");
            return $msg;
        }

        $D024=mysql_query("SELECT D024_Flag_Permite_ACP,T005_Flag_ACP,D024_Flag_Transporta_Diferentes_UF,(T005_Valor_Total_IPI+T005_Valor_Total_ICMS+T005_Valor_ICMS_Substituicao_Retencao) as tributos FROM T005 LEFT JOIN D024 ON D024_Id=T005_D024_Id WHERE T005_Id='{$T005_Id}'");
        $mD024=mysql_fetch_array($D024);
        if($mD024['D024_Flag_Permite_ACP']=='N' && $mD024['T005_Flag_ACP']=='3'){
            return "Não é permitido emitir ACP para este cliente.";
        }

        if($mD024['tributos']>0 && $mD024['T005_Flag_ACP']=='3'){
            return "Não é permitido TRIBUTOS para uma ACP, favor atualizar aliquotas.";
        }

        if($g['C031']['venderEstoqueMatriz'] == 'N'){
            // VERIFICA SE EXISTEM ITENS SEM PRECO
            $D009EmpresaErrada = array();
            $cSql_T006 = mysql_query("SELECT T005_C004_Id, 
                                             D009_C004_Id,
                                             T006_Codigo_Produto, 
                                             T006_Descricao_Produto,
                                             C004_Nome_Abreviado 
                                        FROM T006 
                                   LEFT JOIN T005 ON T005_Id = T006_T005_Id
                                   LEFT JOIN D009 ON D009_Id = T006_D009_Id
                                   LEFT JOIN C004 ON C004_Id = D009_C004_Id
                                       WHERE T006_T005_Id='{$T005_Id}'");
            while ($mT006 = mysql_fetch_array($cSql_T006)) {
                if($mT006['T005_C004_Id'] != $mT006['D009_C004_Id']){
                    $D009EmpresaErrada[] = "{$mT006['T006_Codigo_Produto']} - {$mT006['T006_Descricao_Produto']} : Vinculado a empresa {$mT006['C004_Nome_Abreviado']}";
                }
            }
            
            if (!empty($D009EmpresaErrada)) {
                $mD009EmpresaErrada = implode("\n", $D009EmpresaErrada);
                return "Os itens abaixo estão vinculados a outra empresa, favor verificar:\n\n" . $mD009EmpresaErrada . "\n\n";
            }
        }
        
        $sqlPreCadastroProduto  = mysql_query("SELECT T006_Id 
                                              FROM T006
                                         LEFT JOIN D009 ON D009_Id=T006_D009_Id
                                         LEFT JOIN D049 ON D049_Id=D009_D049_Id
                                         LEFT JOIN D001 ON D001_Id=D049_D001_Id
                                             WHERE T006_T005_Id='{$T005_Id}' 
                                               AND D001_Flag_Pre_Cadastro='S';
                                     ");
        $rowsPreCadastroProduto = mysql_num_rows($sqlPreCadastroProduto);
        if ($rowsPreCadastroProduto > 0) {
            return "Existem itens em pré-cadastro, não foi possível faturar o pedido. Por favor, verifique.";
        }
        
        $T006Pre = mysql_query("SELECT T006_Id FROM T006 WHERE T006_T005_Id='{$T005_Id}' AND T006_Flag_Pre_Cadastro='S'");
        if (mysql_num_rows($T006Pre) > 0) {
            return "Existem itens em pré-cadastro. Por favor conclua o cadastro dos itens.<br />Caso já tenha sido incluído, pressione o botão 'Atualizar Alíquotas', logo em seguida 'Atualizar CFOP' e por fim 'Aplicar regras CFOP'.";
        }
        
        //PARA SETAR A EXPEDIÇÃO PADRÃO DO PEDIDO, EXISTE A VARIÁVEL GLOBAL ABAIXO QUE É CONFIGURADA DENTRO DE _GLOBAL
        $statusPedido = $g['C031']['statusInicialPedido'];
        $sql          = "SELECT T005_D024_Id,T005_Data_Entrega,T005_D022_Id,T005_Flag_Status,T005_Ordem_Compra, D006_Flag_Gera_Contas, T005_Valor_Total_Produtos, T005_Valor_Total, T005_Flag_Frete FROM T005 LEFT JOIN D006 ON D006_Id=T005_D006_Id WHERE T005_Id='{$T005_Id}'";
        $resultado    = mysql_query($sql);
        $row          = mysql_fetch_array($resultado);
        
        $sql2       = "SELECT D024_Flag_Liberado,D024_Flag_Restricao FROM D024 WHERE D024_Id='{$row['T005_D024_Id']}'";
        $resultado2 = mysql_query($sql2);
        $row2       = mysql_fetch_array($resultado2);
        
        $restricaoProduto = mysql_query("SELECT T006_Id FROM T006 WHERE T006_T005_Id='{$T005_Id}' AND (T006_D009_Id='' OR T006_D009_Id IS NULL OR T006_D009_Id=0)");
        $numRestricao     = mysql_num_rows($restricaoProduto);
        if ($numRestricao > 1) {
            return "Há itens sem cadastro. Verifique!";
        } elseif ($numRestricao > 0) {
            return "Existe item sem cadastro. Verifique!";
        }
        
        //return "SQL1 --> " . $sql . "<br /><br /><br /> SQL2 -->" . $sql2 . "qualquer coisa";
        
        /** 
         * Verifica se já foi faturado o pedido
         */
        if ($row['T005_Flag_Status'] == $statusPedido) {
            return "Este pedido já foi faturado!";
        }
        
        /**
         * Verifica se é obrigatório forma de pagamento
         */
        $faturarSemPreco = $g['C031']['permissaoFaturarSemPreco'];
        if ($row['D006_Flag_Gera_Contas'] == "R" && $faturarSemPreco == 'N' && $validaPedidoTransferencia == 'S') {
            $T090 = mysql_query("SELECT SUM(T090_Valor_Parcela) as TotalParcelas FROM T090 WHERE T090_T005_Id='{$T005_Id}'");
            if (mysql_num_rows($T090) <= 0) {
                return "É necessário preencher a forma de pagamento";
            } else {
                $resT090 = mysql_fetch_array($T090);
                if ($row['T005_Valor_Total'] != $resT090['TotalParcelas']) {
                    return "O valor total da forma de pagamento difere do valor total do pedido";
                }
            }
        }

        // Verifica se o banco vinculado aos portadores da forma de pagamento está ativo
        $T090 = mysql_query("SELECT IFNULL(D007_Flag_Ativo,0) as D007_Flag_Ativo ,
                                    IFNULL(D027_D007_Id,0) as D027_D007_Id,
                                    D027_Portador,
                                    T090_D027_Id 
                               FROM T090
                          LEFT JOIN D027 ON D027_Id=T090_D027_Id
                          LEFT JOIN D007 ON D007_Id=D027_D007_Id
                              WHERE T090_T005_Id='{$T005_Id}'");

        while($mT090=mysql_fetch_array($T090)){
            if($mT090['D027_D007_Id'] > 0 && $mT090['D007_Flag_Ativo'] != 'S'){
                return "O portador \"".$mT090['D027_Portador']."\" está com a conta bancária inativa. Por favor verifique com o setor financeiro";
            }
            if($mT090['T090_D027_Id'] == 0 || empty($mT090['T090_D027_Id'])){
                return "Existem parcelas sem portador definido"; 
            }
        }

        // VERIFICA SE EXISTEM ITENS SEM PRECO
        $cSql_T006 = mysql_query("SELECT T006_Id, T006_Codigo_Produto, T006_Descricao_Produto FROM T006 WHERE T006_T005_Id='{$T005_Id}' AND T006_Valor_Preco_Unitario=0");
        while ($mT006 = mysql_fetch_array($cSql_T006)) {
            $mSem_Preco[] = "{$mT006['T006_Codigo_Produto']} - {$mT006['T006_Descricao_Produto']}";
        }
        
        if (mysql_num_rows($cSql_T006) > 0) {
            $Sem_Preco = implode("<br>", $mSem_Preco);
            return "OS ITENS ABAIXO ESTAO SEM PREÇO:" . $Sem_Preco;
        }

        /**
         *  Verifica se foi preenchido a transportadora
         */
        $usarTransportadora = $g['C031']['usarTransportadoraPedido'];
        if ((empty($row['T005_D022_Id']) || $row['T005_D022_Id'] == 0 || $row['T005_D022_Id'] == null) AND $usarTransportadora == 'S' AND $row['T005_Flag_Frete'] != 9 && $validaPedidoTransferencia == 'S') {
            return "Por favor preencha o campo \"TRANSPORTADORA\" no cabeçalho do pedido!";
        }
        /**
         *  Verifica se foi preenchido a data de entrega
         */
        if ($row['T005_Data_Entrega'] == '0000-00-00' AND $g['C031']['obrigatorioDataEntregaPedido'] == 'S') {
            return "Por favor preencha o campo \"DATA ENTREGA\" no cabeçalho do pedido!";
        }

        //Verifica se a empresa usa o campo Ordem de Comprar como obrigatório
        $usarOrdemCompra = $g['C031']['usarOrdemCompra'];
        if ($usarOrdemCompra == 'S') {
            if (empty($row['T005_Ordem_Compra'])) {
                return "Por favor preencha o campo \"ORDEM DE COMPRA\" no cabeçalho do pedido!";
            }
        }

        if($row['T005_Flag_Status'] == '22' && $g['C031']['modeloExpedicao'] == '2') {
            // Se utiliza modelo expedição 2, com esse status o pedido já passou pela expedição
            // E agora vai para a emissão de nota

            $separacao = mysql_query("SELECT SUM(T006_Quantidade) as totalQuantidade,
                                             SUM(T006_Quantidade_Separacao) as totalQuantidadeSeparacao
                                        FROM T006
                                       WHERE T006_T005_Id = '{$T005_Id}'");
            $mSeparacao = mysql_fetch_array($separacao);
            if($mSeparacao['totalQuantidade'] != $mSeparacao['totalQuantidadeSeparacao']){
                return 'Existem produtos que possuem quantidade separada menor que a quantidade do pedido';
            }

            $statusPedido = "3";
        }
        
        if ($statusPedido == "1") {
            $fraseStatus = "Enviou para impressão";
        }
        if ($statusPedido == "10") {
            $fraseStatus = "Enviou para conferência";
        }
        if ($statusPedido == "21") {
            $fraseStatus = "Enviou para peças";
        }
        if ($statusPedido == "3") {
            $fraseStatus = "Enviou para emissão de NF";
        }

        if($mT005['T005_Flag_Frete'] != 9 AND $g['C031']['permiteTransportarDiferentesUF'] == 'N' AND $mD024['D024_Flag_Transporta_Diferentes_UF'] == 'N'){
            $permiteTransporte = mysql_query("SELECT UFFaturamento.D018_UF as faturamento,  
                                                     UFEntrega.D018_UF as entrega,
                                                     D022_Id
                                                FROM T005
                                                LEFT JOIN D022 ON D022_Id=T005_D022_Id
                                                LEFT JOIN D018 AS UFFaturamento ON UFFaturamento.D018_Id=D022_D018_Id
                                                LEFT JOIN D148 ON D148_Id=T005_D148_Id_Entrega
                                                LEFT JOIN D018 AS UFEntrega ON UFEntrega.D018_Id=D148_D018_Id
                                                WHERE T005_Id='$T005_Id'");
            $mPermiteTransporte = mysql_fetch_array($permiteTransporte);
            if($mPermiteTransporte['faturamento'] != $mPermiteTransporte['entrega']){
                return "UF ".$mPermiteTransporte['faturamento']." da transportadora é diferente da UF ".$mPermiteTransporte['entrega']." do cliente, favor verificar.";
            }            
        }

        /*
        * Verifica se há produtos da Matriz para enviar pedido matriz
        */
        $produtosMatriz = false;
        $sqlD009 = mysql_query("SELECT * FROM T006 LEFT JOIN D009 ON D009_Id = T006_D009_Id WHERE D009_C004_Id != '{$g['empresaAtual']}' AND T006_T005_Id = '{$T005_Id}'");
        if(mysql_num_rows($sqlD009) > 0 && $g['C031']['venderEstoqueMatriz'] == 'S'){
            $produtosMatriz = true;
        }

        // Verificação dos produtos no pedido
        $retornoProdutos = '';
        $T006 = mysql_query("SELECT T006_Item, 
                                    T006_Numero_Pedido_Compra
                               FROM T006 
                              WHERE T006_T005_Id='{$T005_Id}'");

        while($mT006 = mysql_fetch_array($T006)){
            if(strlen(trim($mT006['T006_Numero_Pedido_Compra'])) > 15 ){
                $retornoProdutos .= "Num. OC do item " . $mT006['T006_Item'] . " deve possuir menos de 15 caracteres.\n\n";
            }
        }

        if(!empty($retornoProdutos)){
            return $retornoProdutos;
        }
        /**
         * Fatura o Pedido
         */
        $duplicarPedido = $g['conf']->ini('faturamentoMultiEmpresa');
        
        $pedirSegundaLiberacao = false;
        //////////////////////////////////////////////////////////////////
        
        $T090    = mysql_query("SELECT AVG(T090_Prazos_Dias) AS PrazoMedio FROM T090 WHERE T090_T005_Id='{$T005_Id}'");
        $mPrazos = mysql_fetch_array($T090);
        
        $Total_Desconto              = $this->calculaPercentualDescontoPedido($T005_Id);
        $Total_Desconto              = number_format($Total_Desconto, 2);
        $prazoMedio                  = number_format($mPrazos['PrazoMedio'], 2);
        $portador                    = $mT005['T005_D027_Id'];
        $Liberou_Credito_Portador    = $mT005['T005A_Liberou_Credito_Portador'];
        $Valor_Total_Venda           = $mT005['T005_Valor_Total_Produtos'];
        $Liberou_Credito_Valor_Total = $mT005['T005_Liberou_Credito_Valor_Total'];
        $Liberou_Credito_Desconto    = $mT005['T005_Liberou_Credito_Desconto'];
        $Liberou_Credito_Prazo_Medio = $mT005['T005_Liberou_Credito_Prazo_Medio'];
        $T005_Liberou_D024_Id        = $mT005['T005_Liberou_D024_Id'];
        // Valor total da venda liberado é diferente do valor total da venda atual?
        ///////////////////////////////////////////////////////////////////////////
        
        /*
        die("T005_Flag_Primeira_Liberacao_Credito: ".$row['T005_Flag_Primeira_Liberacao_Credito'] . 
        "prazoMedio: ".$prazoMedio. " Liberou_Credito_Prazo_Medio: ".$Liberou_Credito_Prazo_Medio . 
        "Total_Desconto: ".$Total_Desconto. " Liberou_Credito_Desconto: ".$Liberou_Credito_Desconto . 
        "Valor_Total_Venda: ".$Valor_Total_Venda. " Liberou_Credito_Valor_Total: ".$Liberou_Credito_Valor_Total 
        );
        */

        if ($row['T005_Flag_Primeira_Liberacao_Credito'] == '1' || number_format($prazoMedio, 2) != number_format($Liberou_Credito_Prazo_Medio, 2) || number_format($Total_Desconto, 2) != number_format($Liberou_Credito_Desconto, 2) || number_format($Valor_Total_Venda, 2) != number_format($Liberou_Credito_Valor_Total, 2) || $mT005['T005_D024_Id'] != $T005_Liberou_D024_Id
        || $portador != $Liberou_Credito_Portador) {
            $pedirSegundaLiberacao = true;
        }

        if(!empty($g['C031']['prazoSolicitarAnaliseFaturar'])){
            $mT005['T005_Hora_Emissao'] = (empty($mT005['T005_Hora_Emissao'])) ? '00:00:00' : $mT005['T005_Hora_Emissao'];
            $dataHoraAtual = time();
            $dataEmissao = explode("-", $mT005['T005_Data_Emissao']);
            $horaEmissao = explode(":", $mT005['T005_Hora_Emissao']);
            $time2 = mktime($horaEmissao[0],$horaEmissao[1],$horaEmissao[2],$dataEmissao[1],$dataEmissao[2],$dataEmissao[0]);
            $diasDesdeEmissao = round(($dataHoraAtual - $time2) / 86400);

            if($diasDesdeEmissao > $g['C031']['prazoSolicitarAnaliseFaturar']){
                $pedirSegundaLiberacao = true;
            }
        }
        //if($row['T005_Flag_Primeira_Liberacao_Credito'] == '1' || number_format($T090['PrazoMedio'],0) != $row['T005_Prazo_Medio_Orcamento']){
        //  $pedirSegundaLiberacao = true;
        //}
        mysql_query("insert into T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'Faturou Pedido','')");
        $creditoLiberado = $this->verificaCreditoLiberado($T005_Id);

        //Verifica se os produtos serão transportados por outra empresa
        $sqlPedidos = mysql_query("SELECT T006_Id FROM T006 LEFT JOIN T005 ON T005_Id=T006_T005_Id WHERE IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id AND T005_Id = '{$T005_Id}' GROUP BY T006_C004_Id_Estoque");
        if(mysql_num_rows($sqlPedidos) <= 0){
            $statusPedido = '3';
        }

        if($g['C031']['modeloExpedicao'] == '2'){
            $pedirSegundaLiberacao = true;
        }

        if ($mT005['T005_Flag_ACP'] != 4 && $creditoLiberado == false && $pedirSegundaLiberacao == true) {
            if($mT005['T005_Flag_Status'] == '22'){
                mysql_query("UPDATE T005 SET T005_Flag_Marcado='N', T005_Flag_Status=6, T005_Flag_Separacao_Liberar='N', T005_Flag_Separacao='N', T005_Flag_Almoxarifado='S', T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), T005_Flag_Notificacao_Financeiro = '' WHERE T005_Id='{$T005_Id}'");
            }else{
                mysql_query("UPDATE T005 SET T005_Flag_Marcado='N', T005_Flag_Status=6, T005_Flag_Separacao_Liberar='S', T005_Flag_Separacao='S', T005_Flag_Almoxarifado='N', T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), T005_Flag_Notificacao_Financeiro = '' WHERE T005_Id='{$T005_Id}'");
            }
            $this->triggerAposAlterarStatus($T005_Id);

            //Insere registros no histórico do pedido (T178)
            $T005Msg=mysql_query("SELECT T005_Motivo_Pedido_Liberacao from T005 WHERE T005_Id='{$T005_Id}'");
            $mT005Msg = mysql_fetch_array($T005Msg);
            $mT005Msg['T005_Motivo_Pedido_Liberacao'] = mysql_real_escape_string($mT005Msg['T005_Motivo_Pedido_Liberacao']);
            mysql_query("INSERT INTO T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'{$mT005Msg['T005_Motivo_Pedido_Liberacao']}','')");
            
            try {
                require_once('bibliotecas/classes/Webhook.php');
                Webhook::publishForCompany((int) $mT005['T005_C004_Id'], 'financial_analysis.requested', (int) $T005_Id);
            } catch (\Throwable $exception) {
                // Webhook is auxiliary and must not cancel invoicing.
            }
            
            $mensagem       = "ATENÇÂO!!!<br />O cliente possui restrições! Pedido foi enviado para análise de crédito.";
            $duplicarPedido = false;
            
        } else {
            if ($mT005['T005_Flag_Status'] != 22 && !($duplicarPedido && $produtosMatriz)) {
                require_once('bibliotecas/classes/CAD002.php');
                $CAD002 = new CAD002();
                $sqlT006 = mysql_query("SELECT T006_Id, T006_D009_Id, T006_Quantidade FROM T006 LEFT JOIN D006 ON D006_Id = T006_D006_Id WHERE T006_T005_Id = '{$T005_Id}' AND D006_Flag_Estoque != 'N'");

                mysql_query("START TRANSACTION");

                while ($mT006 = mysql_fetch_assoc($sqlT006)) {
                    $retorno = $CAD002->preencherLocacoes(
                        $mT006['T006_D009_Id'],
                        $T005_Id,
                        $mT006['T006_Id'],
                        $mT006['T006_Quantidade'],
                        0
                    );

                    if ($retorno !== true) {
                        mysql_query("ROLLBACK");
                        return $retorno;
                    }
                }

                mysql_query("COMMIT");
            }
            
            mysql_query("UPDATE T005 
                SET T005_Flag_Marcado='N', 
                    T005_Flag_Status='{$statusPedido}', 
                    T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), 
                    T005_Flag_Separacao='S', 
                    T005_Flag_Separacao_Liberar='N',
                    T005_Flag_Notificacao_Financeiro = '',
                    T005_Data_Hora_Almoxarifado = NOW()  
                WHERE T005_Id='{$T005_Id}'
            ");
            $this->triggerAposAlterarStatus($T005_Id);

            //Insere registros no histórico do pedido (T178)
            mysql_query("INSERT INTO T178(T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ({$T005_Id},{$g['usuarioAtual']},CURRENT_TIMESTAMP(),'{$fraseStatus}','')");
            if ($statusPedido == 3)
                $mensagem = "Pedido enviado para emissão de NF";
            else
                $mensagem = "Pedido enviado para expedição";

            $retorno = $this->gerarPedidoTransporte($T005_Id);
            if($retorno !== true){
                return $retorno;
            }
        }        

        // T185
        include('prog/outros/distribuicaoItemPedidoEstoque.php');
        
        if ($duplicarPedido && $produtosMatriz){
            $duplica = $this->enviarPedidoMatriz($T005_Id);
            if ($duplica !== true) {
                $mensagem = $duplica;
            }

            //Insere os registros da separação do pedido (A SEPARAÇÃO REAL OCORRE APENAS NA MATRIZ)
            $sqlT006 = mysql_query("SELECT T006_Id, T006_T005_Id, T006_Quantidade, T066_Id, T066_D004_Id FROM T006 LEFT JOIN D009 ON D009_Id=T006_D009_Id LEFT JOIN T066 ON T066_D009_Id=D009_Id WHERE T006_T005_Id = '{$T005_Id}' GROUP BY T006_Id");
            while($T006 = mysql_fetch_array($sqlT006)){
                mysql_query("INSERT INTO T238 (T238_T006_Id, T238_T066_Id, T238_Quantidade_Separacao, T238_T005_Id, T238_D004_Id) VALUES ('{$T006['T006_Id']}', '{$T006['T066_Id']}', '{$T006['T006_Quantidade']}', '{$T006['T006_T005_Id']}', '{$T006['T066_D004_Id']}')");
                log("INSERT INTO T238 (T238_T006_Id, T238_T066_Id, T238_Quantidade_Separacao, T238_T005_Id, T238_D004_Id) VALUES ('{$T006['T006_Id']}', '{$T006['T066_Id']}', '{$T006['T006_Quantidade']}', '{$T006['T006_T005_Id']}', '{$T006['T066_D004_Id']}')");
            }
        }

        // Atualiza notificação de análise de crédito
        //require_once('bibliotecas/classes/NOTIFICACOES.php');
        //$NOTIFICACOES= new NOTIFICACOES();
        //$NOTIFICACOES->liberarCredito();
        //$NOTIFICACOES->pedidoRetornoAlmoxarifado();
        //$NOTIFICACOES->pedidoCreditoLiberado();
        //$NOTIFICACOES->pedidoCreditoNaoLiberado();

        return true;
    }

    public function triggerAposAlterarStatus($T005_Id) {
        global $g;

        $updt = "UPDATE T005A SET T005A_Data_Hora_Etapa_Expedicao = CURRENT_TIMESTAMP(), T005A_Dt_Hr_Etapa_Impressao = CURRENT_TIMESTAMP() WHERE T005A_T005_Id = '{$T005_Id}'";
        log("Atualiza Data Hora Etapas Expedição {$updt}");
        mysqli_query($updt);
        
        return true;
    }

    public function ven002CancelarPedido($form)
    {
        global $g;
        $T005_Id                        = $form->campoValorEnviado('T005_Id');
        $T005_D047_Id                   = $form->campoValorEnviado('T005_D047_Id');
        $T005_Observacao_Pedido_Perdido = $form->campoValorEnviado('T005_Observacao_Pedido_Perdido');
        
        /**
         * Verificações iniciais
         */
        mysql_query("START TRANSACTION");
        mysql_query("SET AUTOCOMMIT=0");

        $cSql_T007 = mysql_query("SELECT T007_Id
                                    FROM T007 
                                   WHERE T007_T005_Id='{$T005_Id}' 
                                     AND T007_Flag_Cancelada!='S' 
                                     AND T007_Flag_Inutilizado!='S' 
                                     AND T007_Protocolo_Inutilizacao=''");
        if (mysql_num_rows($cSql_T007) > 0) {
            return "Este pedido já foi faturado, não pode ser cancelado.";
        }
        
        $cSql_T005 = mysql_query("SELECT T005_Id,
                                         T005_Flag_Status,
                                         T005_Flag_Almoxarifado
                                    FROM T005 
                                   WHERE T005_Id='{$T005_Id}'");
        $mT005     = mysql_fetch_array($cSql_T005);    

        // Verifica se existem produtos separados no estoque
        //$cSql_T006Estoque = mysql_query("SELECT T006_Id FROM T006 WHERE T006_T005_Id = '{$T005_Id}' AND (T006_Quantidade_Separacao > 0 );");
        $cSql_T006Estoque = mysql_query("SELECT T238_Id 
                                           FROM T006
                                      LEFT JOIN T238 ON T238_T006_Id=T006_Id
                                          WHERE T006_T005_Id='{$T005_Id}' 
                                            AND T238_Quantidade_Separacao >0");
        if (mysql_num_rows($cSql_T006Estoque) > 0) {
            $sql = <<<SQL
                SELECT T178_Id FROM T178 WHERE T178_Descricao = "Enviou para separação";
            SQL;

            $hist = mysql_query($sql);
            if (mysql_num_rows($hist)) {
                mysql_query("ROLLBACK");
                return "Não pode ser cancelado porque possui produtos já separados no estoque.<br></SQL>Por favor, verifique com o setor de expedição.";
            }
        }
        
        // Busca os dados do usuario
/*         $resultado = mysql_query("SELECT * 
                                    FROM C007 
                                   WHERE C007_Id='{$g['usuarioAtual']}'");
        $mUsuario  = mysql_fetch_array($resultado);
        
        $Tem_Acesso = 'N'; */
        
        if ($mT005['T005_Flag_Status'] == 8 && $g['C031']['permiteRecuperarPedidoCancelado']=='N') {
            
            return "Este pedido já está cancelado e não é permitido recuperar pedidos cancelados. Verifique a configuração de sua empresa no menu Configuração->Opções->Vendas->Pedido->Campo 'Permite recuperar pedido cancelado?'";
            
        } else if (($mT005['T005_Flag_Almoxarifado'] == 'S') AND ($mT005['T005_Flag_Status'] != '9')) {
            
            return "Este pedido esta no ALMOXARIFADO, não pode ser cancelado.";
            
        }
        
/*        $T006 = mysql_query("SELECT T006_T005_Id,
                                      T006_Flag_Status,
                                      T006_Codigo_Produto,
                                      T006_Quantidade_Separacao,
                                      T012_T011_Id,
                                      T014_Id,
                                      T013_Numero_Nota_Fiscal,
                                      T006_D009_Id
                                 FROM T006 
                            LEFT JOIN T012 ON T012_T006_Id=T006_Id
                            LEFT JOIN T014 ON T014_T012_Id=T012_Id
                            LEFT JOIN T013 ON T013_Id=T014_T013_Id     
                            WHERE T006_T005_Id='{$T005_Id}'
                             group by T006_Id");
        
        
        while ($mT006 = mysql_fetch_array($T006)) {
            if ($mT006['T012_T011_Id'] > 0) {
                if ($mT006['T014_Id'] > 0) {
                    return <<<EOT
                        {$mT006['T006_Codigo_Produto']} possui OC recebida: {$mT005['T012_T011_Id']} NF {$mT005['T013_Numero_Nota_Fiscal']}
EOT;
                } else {
                    return <<<EOT
                        {$mT006['T006_Codigo_Produto']} possui OC pendente: {$mT006['T012_T011_Id']};
EOT;
                }
            }
        }
        ////

*/
        
        $sql = mysql_query("SELECT T219_Id 
                              FROM T006 
                         LEFT JOIN T004 ON T004_Id = T006_T004_Id 
                         LEFT JOIN T219 ON T219_Id=T006_T219_Id OR T219_Id=T004_T219_Id 
                         LEFT JOIN T225 ON T225_T219_Id=T219_Id 
                             WHERE T006_T005_Id='{$T005_Id}' 
                               AND T225_Id > 0");

        if(mysql_num_rows($sql) > 0){
            return 'Este pedido gerou ordem de compra e não pode ser cancelado! Favor verificar.';  
        }

        //-- ADICIONA BARRA INVERTIDA PARA NAO DAR ERRO NAS ASPAS:
        $T005_D047_Id                   = AddSlashes($T005_D047_Id);
        $T005_Observacao_Pedido_Perdido = AddSlashes($T005_Observacao_Pedido_Perdido);
        
        //-- ELIMINA OS ESPAÇOS DOS CAMPOS:
        $T005_D047_Id                   = Trim($T005_D047_Id);
        $T005_Observacao_Pedido_Perdido = Trim($T005_Observacao_Pedido_Perdido);
        
        //-- CONVERTE EM MINUSCULO E MAIUSCULO:
        $T005_D047_Id                   = StrToMaiusculo($T005_D047_Id);
        $T005_Observacao_Pedido_Perdido = StrToMaiusculo($T005_Observacao_Pedido_Perdido);
        
        //-- VALIDAÇÕES DOS CAMPOS:
        if (strlen(trim($T005_D047_Id)) == 0) {
            return "Necessario o preenchimento do campo: Motivo";
        }
        if (strlen(trim($T005_Observacao_Pedido_Perdido)) == 0) {
            
            return "Necessario o preenchimento do campo: Observação";
        }
        
        $cSql_T005 = mysql_query("SELECT T005_Flag_Status 
                                    FROM T005
                                   WHERE T005_Flag_Status='8' 
                                     AND T005_Id='{$T005_Id}'");

        if (mysql_num_rows($cSql_T005) > 0) {
            $T005_Flag_Status        = '';
            $T005_Flag_Travar_Pedido = '0';
            $T005_Flag_Situacao      = ' ';
            $T005_Flag_Almoxarifado  = ' ';
            $historicoPedido         = 'Pedido recuperado';
        } else {
            $T005_Flag_Status        = '8';
            $T005_Flag_Travar_Pedido = '1';
            $T005_Flag_Situacao      = ' ';
            $T005_Flag_Almoxarifado  = ' ';
            $historicoPedido         = 'Pedido cancelado';
        }
        
        $T005_Observacao_Pedido_Perdido = date('d/m/Y H:i:s') . ' - ' . $T005_Observacao_Pedido_Perdido;

        $cClausula = " UPDATE T005 SET ";
        $cClausula .= " T005_Flag_Marcado='N', ";
        $cClausula .= " T005_D047_Id='{$T005_D047_Id}', ";
        $cClausula .= " T005_Observacao_Pedido_Perdido='{$T005_Observacao_Pedido_Perdido}', ";
        $cClausula .= " T005_Flag_Status='{$T005_Flag_Status}', ";
        $cClausula .= " T005_Nome_Status = T005_Status_Pedido (T005_Flag_Status, 1), ";
        $cClausula .= " T005_Flag_Travar_Pedido='{$T005_Flag_Travar_Pedido}', ";
        $cClausula .= " T005_Flag_Situacao='{$T005_Flag_Situacao}', ";
        $cClausula .= " T005_Flag_Almoxarifado='{$T005_Flag_Almoxarifado}',  ";
        $cClausula .= " T005_Flag_Status_Servicos=3  ";
        $cClausula .= " WHERE T005_Id='{$T005_Id}' ";
        //return $cClausula;
        $cSql  = mysql_query($cClausula);
        $cErro = mysql_error();
        if (empty($cErro)) {
            //Cria histórico do pedido
            $cClausula = "INSERT INTO T178 (T178_T005_Id,T178_C007_Id,T178_Data,T178_Descricao,T178_Observacao) VALUES ('{$T005_Id}','{$g['usuarioAtual']}',CURRENT_TIMESTAMP(),'{$historicoPedido}','{$T005_Observacao_Pedido_Perdido}')";
            mysql_query($cClausula);
            /*$cErro=mysql_error();
            if (empty($cErro)){
            mysql_query("ROLLBACK");
            return "0. Erro ao cancelar o pedido. Query: ".$cClausula;
            }*/
        } else {
            mysql_query("ROLLBACK");
            return "1. Erro ao cancelar o pedido. Query: " . $cClausula;
        }
        
        $cSql_T005 = mysql_query("SELECT T005_Flag_Status 
                                    FROM T005 
                                   WHERE T005_Flag_Status='8' 
                                     AND T005_Id='{$T005_Id}'");

        if (mysql_num_rows($cSql_T005) > 0) {
            mysql_query("UPDATE T006 SET T006_Flag_Status='3', 
                                         T006_T006_Id_Dividir = '0' 
                                   WHERE T006_T005_Id='{$T005_Id}'");
            if (mysql_error()) {
                
                mysql_query("ROLLBACK");
                return "2. Erro ao cancelar o pedido";
            }
            
            mysql_query("UPDATE T185 
                      LEFT JOIN T006 ON T006_Id=T185_T006_Id 
                            SET T185_Flag_Status = '2' 
                          WHERE T006_T005_Id='{$T005_Id}';");
            
            if (mysql_error()) {
                
                mysql_query("ROLLBACK");
                return "3. Erro ao cancelar o pedido";
            }
        } else {
            
            mysql_query("UPDATE T006 SET T006_Flag_Status='' WHERE T006_T005_Id='{$T005_Id}'");
            if (mysql_error()) {
                
                mysql_query("ROLLBACK");
                return "4. Erro ao cancelar o pedido";
            }
            mysql_query("UPDATE T185 
                      LEFT JOIN T006 ON T006_Id=T185_T006_Id 
                            SET T185_Flag_Status = '0' 
                          WHERE T006_T005_Id='{$T005_Id}';");
            if (mysql_error()) {
                
                mysql_query("ROLLBACK");
                return "5. Erro ao cancelar o pedido";
            }
        }
        
        mysql_query("UPDATE D009
                  LEFT JOIN T006 ON T006_D009_Id=D009_Id
                        SET D009_Quantidade_Estoque_Real='0'
                      WHERE T006_T005_Id='{$T005_Id}'");
        if (mysql_error()) {
            
            mysql_query("ROLLBACK");
            return "6. Erro ao cancelar o pedido";
        }

        //-- SE ACONTECER DO PEDIDO SER CANCELADO  E ESTAR NO ALMOX, O MESMO É RETIRADO DO ALMOX
        mysql_query("UPDATE T005 SET T005_Flag_Almoxarifado='N' WHERE T005_Id='{$T005_Id}'");
        if (mysql_error()) {
            
            mysql_query("ROLLBACK");
            return "7. Erro ao cancelar o pedido";
        }
        
        //-- ELIMINA O RELACIONAMENTO DO PEDIDO CANCELADO COM O ITEM DO ORCAMENTO
        mysql_query("UPDATE T006 SET T006_T004_Id=0 WHERE T006_T005_Id='{$T005_Id}'");
        if (mysql_error()) {
            
            mysql_query("ROLLBACK");
            return "8. Erro ao cancelar o pedido";
        }
        
        //-- RECALCULA TOTAL DO PEDIDO CANCELADO
        mysql_query("call T005_Gravar_Totalizacao_4('{$T005_Id}')");
        if (mysql_error()) {
            
            mysql_query("ROLLBACK");
            return "9. Erro ao cancelar o pedido";
        }
        
        //Atualiza estoque dos produtos do pedido
        require_once('bibliotecas/classes/CAD002.php');
        $CAD002 = new CAD002();
        
        $atualizarT006 = mysql_query("SELECT T006_D009_Id
                                        FROM T006 
                                       WHERE T006_T005_Id='{$T005_Id}'");
        
        while ($mAtualizarT006 = mysql_fetch_array($atualizarT006)) {
            //D009 do produto eh diferente do que estou retornando
            $atualizarEstoque = $CAD002->D001_reprocessa_historico(false, $mAtualizarT006['T006_D009_Id'], 50, true);
            if ($atualizarEstoque !== true) {
                mysql_query("ROLLBACK");
                return "Erro ao atualizar estoque dos produtos. " . $atualizarEstoque;
            }
        }
        
        if (!empty($cErro)) {
            mysql_query("ROLLBACK");
            return <<<EOT
            Ocorreu um erro ao tentar alterar o registro<br ></EOT><br />Erro: {$cErro}<br /><br />Clausula: {$cClausula};
EOT;
        } else {
            mysql_query("COMMIT");

            require_once('bibliotecas/webservices/cyberlog/integracaoWMS.php');
            $WMS = new WMS();

            if($WMS->isEnabled() && $g['C031']['cyberlogLockOrder'] == 'S') {
                $WMS->cancelarPedido($T005_Id);
                
                mysql_query("UPDATE T005A SET T005A_Data_Envio_WMS = '0000-00-00 00:00:00' WHERE T005A_T005_Id = '{$T005_Id}'");
            }

            return true;
        }
    }
}

























