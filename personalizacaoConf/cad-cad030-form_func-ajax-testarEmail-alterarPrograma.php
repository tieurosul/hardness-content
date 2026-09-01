<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad030-form_func-ajax-testarEmail/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

$resposta = array('code' => true, 'data' => array());

        $C007_Id = isset($r_C007_Id) ? $r_C007_Id : false;
		$emailDestino = isset($_REQUEST['emailDestino']) ? trim($_REQUEST['emailDestino']) : '';

		if (empty($C007_Id)) {
			$resposta['code'] = false;
			$resposta['data'] = 'Registro do usuario nao informado.';
			echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
		}

		if ($g['C031']['recebimentoEmail'] == 'N') {
			$retorno = $CAD030->testarEmail($C007_Id, $emailDestino);
			if (is_array($retorno) && !empty($retorno['code'])) {
				$resposta['code'] = true;
				$resposta['data'] = $retorno['data'];
			} else {
				$resposta['code'] = false;
				$resposta['data'] = is_array($retorno) && isset($retorno['data']) ? $retorno['data'] : 'Nao foi possivel enviar o e-mail de teste.';
			}
		} else {
			$C007_Id = mysql_real_escape_string($C007_Id);
			$sql = "SELECT C007_Email_POP_Servidor, C007_Email_SMTP_Usuario, C007_Email_SMTP_Senha, C007_Email_POP_Porta FROM C007 WHERE C007_Id = '{$C007_Id}';";
			$resultado = mysql_query($sql);
			$C007 = mysql_fetch_assoc($resultado);

			require_once('sistema/comunicacaoEmail.php');
			$mail = new comunicacaoEmail($C007['C007_Email_POP_Servidor'], $C007['C007_Email_SMTP_Usuario'], $C007['C007_Email_SMTP_Senha'], 'E', false, $C007['C007_Email_POP_Porta']);
			$pastas = $mail->listarPastas();

			if ($pastas === false) {
				$servidorImap = trim((string) $C007['C007_Email_POP_Servidor']);
				$portaImap = trim((string) $C007['C007_Email_POP_Porta']);
				$erroImap = '';
				if (function_exists('imap_last_error')) {
					$erroImap = trim((string) @imap_last_error());
				}

				$mensagem = "Erro ao tentar conectar no IMAP.<br />Por favor, verifique se o Servidor IMAP/Login/Senha estao corretos.";
				if (!empty($servidorImap) || !empty($portaImap)) {
					$mensagem .= "<br /><br />Servidor IMAP: " . htmlspecialchars($servidorImap, ENT_COMPAT, 'UTF-8');
					if (!empty($portaImap)) {
						$mensagem .= " Porta: " . htmlspecialchars($portaImap, ENT_COMPAT, 'UTF-8');
					}
				}
				if (!empty($erroImap)) {
					$mensagem .= "<br />Retorno IMAP: " . htmlspecialchars($erroImap, ENT_COMPAT, 'UTF-8');
				}
				if (!empty($servidorImap) && stripos($servidorImap, 'smtp.') === 0) {
					$mensagem .= "<br /><br />Atencao: o campo Servidor IMAP parece apontar para um host SMTP. Preencha o servidor de entrada/IMAP da conta.";
				}

				$resposta['code'] = false;
				$resposta['data'] = $mensagem;
			} else {
				$select = array();
				foreach ($pastas as $pasta) {
					$mailbox = explode('}', $pasta);
					$select[] = array('title' => $mailbox[1], 'value' => $mailbox[1]);
				}
				$select = mysql_real_escape_string(serialize($select));
				$sql = "UPDATE C007 SET C007_Email_Pastas = '{$select}' WHERE C007_Id = '{$C007_Id}';";
				mysql_query($sql);
				$resposta['code'] = true;
				$resposta['data'] = 'Pastas de e-mail localizadas com sucesso.';
			}
		}

echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
