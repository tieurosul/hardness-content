<?php
/**
* triggers
* 
* Execução de código PHP antes/após INSERT/UPDATE/DELETE
* A chave primária sempre estará disponível (exceto em beforeInsert) pela variavél: $chaveValor 
* IMPORTANTE: coloque um '\' na função mysql_query caso use ela: mysqli_query() para utilizar a original do mysql
*/

/*
Exemplos:
$conf['triggers']['RMA001']['insertBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['RMA001']['insertAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['RMA001']['updateBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['RMA001']['updateAfter'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['RMA001']['deleteBefore'] = <<<'EOT'
	// Código PHP
EOT;
$conf['triggers']['RMA001']['deleteAfter'] = <<<'EOT'
	// Código PHP
EOT;
*/

// Felipe Kadanos - 30/04/2026
// Personalizei o insertAfter com o mesmo codigo que esta no app do RMA.
// Ja é a segunda vez que acontece do RMA001_T182_Id ficar vazio. 
// Ai estou tentando sobrescrever para tentar forçar a entrar aq no insertAfter.
$conf['triggers']['RMA001']['insertAfter'] = <<<'EOT'
	global $g;
	$RMA001 = mysql_query("select RMA001_D024_Id, RMA001_C007_Id, RMA001_D013_Id, RMA001_T007_Id from RMA001 where RMA001_Id='{$chaveValor}'");
	$mRMA001 = mysql_fetch_array($RMA001);  

	$T182 = "INSERT INTO T182 (
				T182_D024_Id_Cliente,
				T182_C007_Id,
				T182_D013_Id_Cliente,
				T182_T007_Id
			) VALUES (
				'{$mRMA001['RMA001_D024_Id']}',
				'{$mRMA001['RMA001_C007_Id']}',
				'{$mRMA001['RMA001_D013_Id']}',
				'{$mRMA001['RMA001_T007_Id']}'
			)";	  
	mysql_query($T182);

	// $RMA001_Id = mysqli_insert_id();
	$T182_Id = $g['mysqlLastId'];

	mysql_query("UPDATE RMA001 SET RMA001_T182_Id = '{$T182_Id}' WHERE RMA001_Id = '{$chaveValor}'");

EOT;

/* 
 * Felipe Kadanos - 28/04/2026 - EUROSUL FORNECEDO - 46633
 * Melhoria: quando o status do RMA e alterado, o sistema registra uma atividade
 * automatica na aba Atividades e envia o e-mail de atualizacao usando nfe@hardness.com.br.
*/

/* 
    emails:
    status - emails 
    Comercial - responsável pelo rma (email no cad de user do responsavel)
    Concluido - todos os e-mails envolvidos
    Logistica Eurosul - log@eurosul.com
    Financeiro - aux@eurosul.com
    Qualidade - qualidade@eurosul.com
    Logistica Cliente - responsável pelo rma (email no cad de user do responsavel)

    se a data previsão fechamento + 5 dias sem conclusão, notifica o vendas8@eurosul.com
*/

$conf['triggers']['RMA001']['updateBefore'] .= <<<'EOT'
	// Código PHP
	global $g;
	$sql =  "SELECT RMA001_D162_Id, D162_Status as statusNome FROM RMA001 LEFT JOIN D162 ON D162_Id = RMA001_D162_Id WHERE RMA001_Id = '{$chaveValor}'";
	$row = mysql_fetch_assoc(mysqli_query($sql));
	$g['updtBef-RMA_Status'] = $row['RMA001_D162_Id'];
	$g['updtBef-RMA_StatusNome'] = $row['statusNome'];
EOT;

$conf['triggers']['RMA001']['updateAfter'] .= <<<'EOT'
	// Código PHP
	global $g;

	$sql =  "SELECT RMA001_D162_Id as statusAfter FROM RMA001 WHERE RMA001_Id = '{$chaveValor}'";
	$row = mysql_fetch_assoc(mysqli_query($sql));
	if ($row['statusAfter'] != $g['updtBef-RMA_Status']) {

        $sql = "SELECT 
                    RMA001_Id,
                    RMA001_T182_Id,
                    RMA001_D024_Id,
                    RMA001_D162_Id as statusAfter,
                    RMA001_Data_Abertura,
                    RMA001_Hora_Abertura,
                    RMA001_Data_Previsao_Fechamento,
                    RMA001_Data_Conclusao,
                    RMA001_Data_Conclusao_Cliente,
                    RMA001_Data_Previsao_Reenvio,
                    RMA001_Data_Previsao_Retorno,
                    RMA001_Tipo_Documento,
                    RMA001_T005_Id_Reserva,
                    RMA001_Observacao,
                    D162_Status as statusNomeAfter,
                    D024_Nome_Empresa,
                    C004_Nome_Empresa,
                    T005_Id,
                    T005_Ordem_Compra,
                    T007_Numero_Nota_Fiscal,
                    C007responsavel.C007_Primeiro_Nome as responsavelNome,
                    C007responsavel.C007_Email as responsavelEmail
                FROM RMA001
                    LEFT JOIN D162 ON D162_Id = RMA001_D162_Id
                    LEFT JOIN D024 ON D024_Id = RMA001_D024_Id
                    LEFT JOIN C004 ON C004_Id = RMA001_C004_Id
                    LEFT JOIN T005 ON T005_Id = IF(RMA001_T005_Id_Reserva > 0, RMA001_T005_Id_Reserva, RMA001_T005_Id)
                    LEFT JOIN T007 ON T007_Id = RMA001_T007_Id
                    LEFT JOIN C007 C007responsavel ON C007responsavel.C007_Id = RMA001_Responsavel_C007_Id
                WHERE RMA001_Id = '{$chaveValor}'";
        $row = mysql_fetch_assoc(mysql_query($sql));

        $atv = "SELECT D085_T259_Id FROM D085 WHERE D085_T182_Id = '{$row['RMA001_T182_Id']}'";
        $rowAtv = mysql_fetch_assoc(mysql_query($atv));

		$mensagemAtividade = "Status do RMA alterado de <b>{$g['updtBef-RMA_StatusNome']}</b> para <b>{$row['statusNomeAfter']}</b>.";
		$mensagemAtividade = addslashes($mensagemAtividade);
		$ins = "INSERT INTO D085 (
                    D085_D024_Id,
                    D085_T259_Id,
                    D085_T182_Id,
                    D085_C007_Id,
                    D085_C007_Id_Inclusao,
                    D085_Data,
                    D085_Hora,
                    D085_Mensagem,
                    D085_Flag_Manual
                ) VALUES (
                    '{$row['RMA001_D024_Id']}',
                    '{$rowAtv['D085_T259_Id']}',
                    '{$row['RMA001_T182_Id']}',
                    '{$g['usuarioAtual']}',
                    '{$g['usuarioAtual']}',
                    CURDATE(),
                    CURTIME(),
                    '{$mensagemAtividade}',
                    'S'
                )";
        if (!mysqli_query($ins)) {
            log("Erro ao inserir atividade no RMA001 = {$chaveValor}: " . mysql_error());
        }

		$stsConcluido = ((int) $row['statusAfter'] === 2) ? true : false;
		$destinatarios = array();
		$notificarAtraso = false;
		$responsavelEmail = strtolower(trim((string) $row['responsavelEmail']));

		// Se status for 1, 6 ou 2 o responsavel pelo RMA recebe o email
		if (!empty($responsavelEmail) && filter_var($responsavelEmail, FILTER_VALIDATE_EMAIL)) {
			// 1- COMERCIAL; 6- LOGISTICA CLIENTE;
			if ((int) $row['statusAfter'] === 1 || (int) $row['statusAfter'] === 6 || $stsConcluido) {
				$destinatarios[] = $responsavelEmail;
			}
		}

		// Se status for LOGISTICA EUROSUL recebe email
		if ((int) $row['statusAfter'] === 3 || $stsConcluido) {
			$destinatarios[] = 'log@eurosul.com';
			$destinatarios[] = 'log1@eurosul.com';
			$destinatarios[] = 'log2@eurosul.com';
			$destinatarios[] = 'vendas8@eurosul.com';
			$destinatarios[] = 'qualidade2@eurosul.com';
		}

		// Se status for FINANCEIRO recebe email
		if ((int) $row['statusAfter'] === 4 || $stsConcluido) {
			$destinatarios[] = 'aux@eurosul.com';
			$destinatarios[] = 'aux2@eurosul.com';
			$destinatarios[] = 'adm@eurosul.com';
			$destinatarios[] = 'vendas8@eurosul.com';
			$destinatarios[] = 'qualidade2@eurosul.com';
		}

		// Se status for QUALIDADE recebe email
		if ((int) $row['statusAfter'] === 5 || $stsConcluido) {
			$destinatarios[] = 'qualidade@eurosul.com';
			$destinatarios[] = 'vendas8@eurosul.com';
			$destinatarios[] = 'qualidade2@eurosul.com';
		}

		if ($stsConcluido) {
			$destinatarios[] = 'vendas8@Eurosul.com';
		}

		/* Se a previsão de fechamento venceu há mais de 5 dias e o RMA ainda não foi concluído,
		 * adiciona o e-mail de acompanhamento comercial. */
		if ( !empty($row['RMA001_Data_Previsao_Fechamento']) && empty($row['RMA001_Data_Conclusao']) && (int) $row['statusAfter'] !== 2 ) {
			$dataLimiteAtraso = date('Y-m-d', strtotime($row['RMA001_Data_Previsao_Fechamento'] . ' +5 days'));
			if ($dataLimiteAtraso <= date('Y-m-d')) {
				$notificarAtraso = true;
				$destinatarios[] = 'vendas8@eurosul.com';
			}
		}

		// Remove vazios e evita envio duplicado quando o mesmo e-mail entra por mais de uma regra.
		$destinatarios = array_filter($destinatarios);
		$destinatarios = array_values(array_unique($destinatarios));

		if (!empty($destinatarios)) {
			require_once('bibliotecas/phpmailer/class.phpmailer.php');
			$mail = new \phpmailer;
			
			$assunto = "RMA {$row['RMA001_T182_Id']} - status alterado para " . trim((string) $row['statusNomeAfter']);

			$alertaAtraso = '';
			$cliente = htmlentities((string) $row['D024_Nome_Empresa'], ENT_COMPAT, 'utf-8');
			$empresa = htmlentities((string) $row['C004_Nome_Empresa'], ENT_COMPAT, 'utf-8');
			$responsavel = htmlentities((string) $row['responsavelNome'], ENT_COMPAT, 'utf-8');
			$statusAnteriorHtml = htmlentities(trim((string) $g['updtBef-RMA_StatusNome']), ENT_COMPAT, 'utf-8');
			$statusAtualHtml = htmlentities(trim((string) $row['statusNomeAfter']), ENT_COMPAT, 'utf-8');
			$pedidoReserva = !empty($row['T005_Id']) ? $row['T005_Id'] : '-';
			$notaFiscal = !empty($row['T007_Numero_Nota_Fiscal']) ? $row['T007_Numero_Nota_Fiscal'] : '-';
			$previsaoFechamento = !empty($row['RMA001_Data_Previsao_Fechamento']) ? gCorrigeData($row['RMA001_Data_Previsao_Fechamento']) : '-';
			$observacaoHtml = '';

			if ($notificarAtraso) {
				$alertaAtraso = "
					<div style='margin:0 0 16px 0;padding:12px 14px;background:#fff4e5;border:1px solid #ffd8a8;border-radius:10px;color:#9a3412;font-size:14px;line-height:1.5;'>
						RMA com previsao de fechamento vencida ha mais de 5 dias sem conclusao.
					</div>
				";
			}

			if (!empty(trim((string) $row['RMA001_Observacao']))) {
				$observacaoHtml = "
					<div style='margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb;'>
						<div style='font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;'>Observacao</div>
						<div style='font-size:14px;line-height:1.6;color:#111827;'>" . nl2br(htmlentities(trim((string) $row['RMA001_Observacao']), ENT_COMPAT, 'utf-8')) . "</div>
					</div>
				";
			}

			$mail->From     = 'nfe@hardness.com.br';
			$mail->FromName = $g['C004']['C004_Nome_Abreviado'];
			$mail->Host     = 'smtp.gmail.com';
			$mail->Mailer   = "smtp";
			$mail->SMTPAuth = 'true';
			$mail->Username = 'nfe@hardness.com.br';
			$mail->Password = 'hgrw nvpe xliv cluz';
			$mail->Subject  = '=?UTF-8?B?'.base64_encode($assunto).'?=';
			$mail->Body     = <<<HTML
				<div style='margin:0;padding:24px;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#111827;'>
					<div style='max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe3ea;border-radius:14px;overflow:hidden;'>
						<div style='padding:20px 24px;background:#0f172a;color:#ffffff;'>
							<div style='font-size:12px;letter-spacing:.08em;text-transform:uppercase;opacity:.72;'>Atualizacao de RMA</div>
							<div style='margin-top:6px;font-size:24px;font-weight:bold;'>RMA {$row['RMA001_T182_Id']}</div>
							<div style='margin-top:10px;display:inline-block;padding:6px 10px;border-radius:999px;background:#1e293b;font-size:12px;'>
								{$statusAtualHtml}
							</div>
						</div>
						<div style='padding:24px;'>
							{$alertaAtraso}
							<div style='font-size:15px;line-height:1.6;color:#334155;margin-bottom:18px;'>
								O status do RMA foi atualizado de <strong>{$statusAnteriorHtml}</strong> para <strong>{$statusAtualHtml}</strong>.
							</div>
							<table cellpadding='0' cellspacing='0' border='0' style='width:100%;border-collapse:collapse;'>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Cliente</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$cliente}</td>
								</tr>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Empresa</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$empresa}</td>
								</tr>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Responsavel</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$responsavel}</td>
								</tr>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Previsao fechamento</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$previsaoFechamento}</td>
								</tr>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Pedido reserva</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$pedidoReserva}</td>
								</tr>
								<tr>
									<td style='width:34%;padding:10px 0;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;'>Nota fiscal</td>
									<td style='padding:10px 0;border-top:1px solid #e5e7eb;font-size:14px;color:#111827;'>{$notaFiscal}</td>
								</tr>
							</table>
							{$observacaoHtml}
						</div>
					</div>
				</div>
HTML;
			$mail->AltBody  = "RMA {$row['RMA001_T182_Id']} | Status anterior: {$statusAnteriorHtml} | Novo status: {$statusAtualHtml} | Cliente: {$row['D024_Nome_Empresa']} | Responsavel: {$row['responsavelNome']} | Previsao fechamento: {$previsaoFechamento}";
			$mail->CharSet  = 'utf-8';
			$mail->SetLanguage("br");
			$mail->IsHTML(true);
			$mail->SMTPSecure = "ssl";
			$mail->Port = "465";

			foreach ($destinatarios as $email) {
				$mail->AddAddress($email);
			}

			$retornoEmail = $mail->Send();
			log('Envio de e-mail RMA001: ' . json_encode(array(
				'RMA001_Id' => $row['RMA001_Id'],
				'statusAnterior' => trim((string) $g['updtBef-RMA_StatusNome']),
				'statusAtual' => trim((string) $row['statusNomeAfter']),
				'destinatarios' => array_values($destinatarios),
				'retorno' => $retornoEmail,
				'erro' => $mail->ErrorInfo
			)));
		}
	}
EOT;







