<?php
namespace hardness;

/**
 * Tela: Cotação de frete — lista opções retornadas pela API externa.
 * Rota: /ven/ven001/content/ven001ContentCotarFrete/
 */
require_once('bibliotecas/classes/VEN001.php');

$T003_Id = isset($r_acaoId) ? (int) $r_acaoId : (isset($r_T003_Id) ? (int) $r_T003_Id : 0);
$VEN001 = new VEN001();
$resultado = $VEN001->cotarFreteApi($T003_Id);

echo '<div style="padding:12px;">';

if (empty($resultado['ok'])) {
    echo gGeraAlertMsg('Erro', $resultado['erro']);
    echo '<div style="margin-top:12px;text-align:right;">';
    echo '<button type="button" onclick="fecharJanela(\'' . $g['divId'] . '\'); return false;" class="btnFechar">Fechar</button>';
    echo '</div></div>';
    return;
}

$opcoes = $resultado['opcoes'];
$opcoesJson = json_encode($opcoes);
$opcoesJsonEsc = htmlspecialchars($opcoesJson, ENT_QUOTES, 'UTF-8');

echo '<p><strong>Orçamento:</strong> ' . (int) $T003_Id . '</p>';
echo '<p>Selecione uma opção de frete:</p>';
echo '<form id="ven001FormCotarFrete" style="margin-top:8px;">';
echo '<input type="hidden" id="ven001CotarFreteT003Id" value="' . (int) $T003_Id . '" />';
echo '<input type="hidden" id="ven001CotarFreteOpcoesJson" value="' . $opcoesJsonEsc . '" />';
echo '<table class="gridTable" style="width:100%; margin-bottom:12px;"><thead><tr>';
echo '<th></th><th>Transportadora</th><th>Serviço</th><th>Prazo</th><th style="text-align:right;">Valor</th>';
echo '</tr></thead><tbody>';

foreach ($opcoes as $idx => $opcao) {
    $valorFmt = gCorrigeNumero($opcao['valor']);
    $prazo = $opcao['prazo'];
    if ($prazo !== '' && $prazo !== null && !preg_match('/dia/i', (string) $prazo)) {
        $prazo = $prazo . ' dia(s)';
    }
    echo '<tr>';
    echo '<td><input type="radio" name="ven001OpcaoFrete" value="' . (int) $idx . '"' . ($idx === 0 ? ' checked="checked"' : '') . ' /></td>';
    echo '<td>' . htmlspecialchars($opcao['transportadora'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($opcao['servico'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars((string) $prazo, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:right;">R$ ' . htmlspecialchars($valorFmt, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';
echo '<div style="text-align:right;">';
echo '<button type="button" id="ven001CotarFreteAplicarBtn">Aplicar frete</button> ';
echo '<button type="button" onclick="fecharJanela(\'' . $g['divId'] . '\'); return false;" class="btnFechar">Fechar</button>';
echo '</div>';
echo '</form>';
echo '</div>';

$divIdParent = addslashes($g['divIdParent']);
$divId = addslashes($g['divId']);

echo <<<EOT
<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
    $('#{$g['divId']} #ven001CotarFreteAplicarBtn').unbind('click').bind('click', function() {
        var idx = $('#{$g['divId']} input[name=ven001OpcaoFrete]:checked').val();
        if (idx === undefined || idx === null) {
            alert('Selecione uma opção de frete.');
            return false;
        }
        var opcoes = JSON.parse($('#{$g['divId']} #ven001CotarFreteOpcoesJson').val());
        var opcao = opcoes[idx];
        var t003Id = $('#{$g['divId']} #ven001CotarFreteT003Id').val();
        $('#{$g['divId']}').showLoading();
        $.getJSON('/ven/ven001/form_func-ajax/aplicarOpcaoFrete/?ajax=true&callback=?', {
            T003_Id: t003Id,
            opcao: JSON.stringify(opcao)
        }, function(request) {
            $('#{$g['divId']}').hideLoading();
            if (request.code) {
                var msg = request.data && request.data.mensagem ? request.data.mensagem : 'Frete aplicado com sucesso.';
                if (request.data && request.data.aviso) {
                    msg += '\\n\\n' + request.data.aviso;
                }
                alert(msg);
                fecharJanela('{$g['divId']}');
                if ('{$divIdParent}') {
                    divRefresh('{$divIdParent}', true);
                }
            } else {
                var buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } };
                dialogConfirm("Erro", request.data, buttons);
            }
        });
        return false;
    });
} </script></div>
EOT;
