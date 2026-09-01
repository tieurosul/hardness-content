<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /etc-etc016-ajax-etc016ProdutoNovo/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());

$codbarra = isset($r_codbarra) ? $r_codbarra : '';
		$codbarra = mysql_real_escape_string($codbarra);
		$flag = base64_decode($r_flag);
		$cod = unserialize(base64_decode($r_cod));
		$qtd = base64_decode($r_qtd);
		$desc = base64_decode($r_desc);
		$itens = json_decode($r_itens, true);
		if (empty($codbarra)) {
			$resposta['code'] = false;
			$resposta['data'] = "Código de barras não encontrado no sistema!";
		} else {
			// Sql vindo do grid
			$sql = base64_decode($r_sql);
			$resGrid = mysql_query($sql);
			$encontrou = false;
			$rowsGrid = array();
			while ($rowGrid = mysql_fetch_assoc($resGrid)) {
				$rowsGrid[] = $rowGrid;
			}
			// Soma quantidade por cod. barra (GRID) para não limitar por linha
			$arrayGridQtd = array();
			foreach ($rowsGrid as $rowGrid) {
				$codIguais = array(); // evita duplicar quando o mesmo cod. aparece em mais de um campo
				foreach ($cod as $codValue) {
					$codBarras = explode('|', $rowGrid[$codValue]);
					foreach ($codBarras as $codBarrasItem) {
						if (empty($codBarrasItem)) { continue; }
						$codKey = $codBarrasItem;
						if (is_numeric($codKey)) {
							$codKey = ltrim($codKey, '0');
							if ($codKey === '') { $codKey = '0'; }
						}
						if (in_array($codKey, $codIguais)) { continue; }
						if (!isset($arrayGridQtd[$codKey])) {
							$arrayGridQtd[$codKey] = 0;
						}
						$rowQtd = str_replace(',', '.', $rowGrid[$qtd]);
						$arrayGridQtd[$codKey] += floatval($rowQtd);
						$codIguais[] = $codKey;
					}
				}
			}
			// Normaliza itens recebidos do front para evitar inconsistências com zeros à esquerda
			$itensNorm = array();
			if (is_array($itens)) {
				foreach ($itens as $itensKey => $itensValue) {
					$itensKeyNorm = $itensKey;
					if (is_numeric($itensKeyNorm)) {
						$itensKeyNorm = ltrim($itensKeyNorm, '0');
						if ($itensKeyNorm === '') { $itensKeyNorm = '0'; }
					}
					if (!isset($itensNorm[$itensKeyNorm])) {
						$itensNorm[$itensKeyNorm] = 0;
					}
					$itensValue = str_replace(',', '.', $itensValue);
					$itensNorm[$itensKeyNorm] += floatval($itensValue);
				}
			}
			foreach ($rowsGrid as $rowGrid) {
				foreach ($cod as $codValue) {
					$linhaArray = json_encode($rowGrid);
					$codBarras = explode('|', $rowGrid[$codValue]);
					$multiplicadores = explode('|', $rowGrid['D083A_Multiplicador']);
					foreach ($codBarras as $codBarrasItem) {
						
						if (empty($codBarrasItem)) { continue; }
						if ($codBarrasItem == $codbarra) {
							$codbarraNorm = $codbarra;
							if (is_numeric($codbarraNorm)) {
								$codbarraNorm = ltrim($codbarraNorm, '0');
								if ($codbarraNorm === '') { $codbarraNorm = '0'; }
							}
							$quantidade = 1;
							$value      = '';
							$encontrou  = true;
							foreach ($multiplicadores as $multiplicador) {
								$quantidades = explode('X', $multiplicador);
								if ((count($quantidades) === 2) and ($quantidades[0] === $codbarra)) {
									$quantidade = floatval($quantidades[1]);
									$value      = $quantidades[1];
								}
							}

							$limiteTotal = isset($arrayGridQtd[$codbarraNorm]) ? $arrayGridQtd[$codbarraNorm] : 0;
							$qtdAtual = isset($itensNorm[$codbarraNorm]) ? $itensNorm[$codbarraNorm] : 0;
							$qtdNovo = ($value !== '') ? floatval(str_replace(',', '.', $value)) : 1;
							if (($qtdAtual + $qtdNovo) > $limiteTotal) {
								$resposta['code'] = false;
                                $resposta['data'] = "Quantidade excedida para o item {$codbarra}";
							} else {														
							$descricao = $codBarrasItem . ' - ' . $rowGrid[$desc];
							$resposta['data'] = <<<EOT
<tr class="item" style="width: 100%; height: 35px;" verificado="1" cod="{$codBarrasItem}">
	<td style="width: 1%;">
		<span title="Cancelar" class="ui-icon ui-icon-circle-close" style="margin: 0 8px; cursor: pointer;" onclick="$(this).closest('tr').remove(); verCodBarraAjustesUI();"></span>
	</td>
	<td style="text-align: left;">
		<div style="font-size: 1.3em; font-weight: bold;">
			{$descricao}
		</div>
	</td>
	<td style="text-align: right; width: 1%; padding: 6px 11px;">
		<input type="text" style="width: 80px; font-size: 15px; text-align: right;" placeholder="{$quantidade}" value="{$value}" onkeypress="if (event.which == 13) { $(this).blur(); }" onblur="verCodBarraAjustesUI();" />
	</td>
</tr>

EOT;
							}
						}
					}
				}
			}
			if (!$encontrou) {
				$resposta['code'] = false;
				$sql = "SELECT D001_Codigo_Produto, D001_Descricao_Produto FROM D001 WHERE D001_Codigo_Barras = '{$codbarra}'";
				$resD001 = mysql_query($sql);
				if (!mysql_num_rows($resD001)) {
					$sql = "SELECT D001_Codigo_Produto, D001_Descricao_Produto FROM D083 LEFT JOIN D001 ON D001_Id = D083_D001_Id WHERE D083_Codigo_Barras = '{$codbarra}' LIMIT 1";
					$resD001 = mysql_query($sql);
					if (!mysql_num_rows($resD001)) {
						$resposta['data'] = "Código de barras não encontrado no sistema!";
					} else {
						$rowD001 = mysql_fetch_assoc($resD001);
						$resposta['data'] = "Produto não encontrado no pedido: " . $rowD001['D001_Codigo_Produto'] . ' - ' . substr($rowD001['D001_Descricao_Produto'], 0, 40) . " ...";	
					}
				} else {
					$rowD001 = mysql_fetch_assoc($resD001);
					$resposta['data'] = "Produto não encontrado no pedido: " . $rowD001['D001_Codigo_Produto'] . ' - ' . substr($rowD001['D001_Descricao_Produto'], 0, 40) . " ...";	
				}
			}
		}

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
