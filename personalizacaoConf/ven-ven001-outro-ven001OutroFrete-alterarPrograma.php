<?php
namespace hardness;
/**
 * Este codigo PHP sera executado ao acessar: /ven/ven001/outro/ven001OutroFrete/
 */

global $g;

$T003_Id = mysql_real_escape_string($r_acaoId);

$sql = "SELECT T003_Peso_Total,
               SUM(T004_Quantidade) AS quantidadeTotal,
               SUM(T004_Cubagem_Unitaria * T004_Quantidade) AS cubagemTotal
          FROM T003
     LEFT JOIN T004 ON T004_T003_Id = T003_Id
         WHERE T003_Id = '{$T003_Id}'
      GROUP BY T003_Id";

$result = mysql_query($sql);
$mT003 = mysql_fetch_assoc($result);

if (!$mT003) {
    echo '<div style="padding:10px;">Orcamento nao encontrado.</div>';
    return;
}

$quantidadeTotal = gCorrigeNumero($mT003['quantidadeTotal']);
$cubagemTotal = gCorrigeNumero($mT003['cubagemTotal']);
$pesoLiquido = gCorrigeNumero($mT003['T003_Peso_Total']);

$p007Selecionada = mysql_query("SELECT P007_Id,
                                        P007_Valor_Frete,
                                        P007_Prazo_Entrega,
                                        P007_Transportadora,
                                        P007_Quem_Paga
                                   FROM P007
                                  WHERE P007_T003_Id = '{$T003_Id}'
                                    AND P007_Flag_Selecionada = 'S'
                               ORDER BY P007_Id DESC
                                  LIMIT 1");
$mP007Selecionada = mysql_fetch_assoc($p007Selecionada);

$resultadoDisplay = 'none';
$freteValor = '-';
$fretePrazo = '-';
$freteTransportadora = '-';
$freteQuemPaga = '-';

if ($mP007Selecionada) {
    $resultadoDisplay = 'block';
    $freteValor = gCorrigeNumero($mP007Selecionada['P007_Valor_Frete']);
    $fretePrazo = $mP007Selecionada['P007_Prazo_Entrega'];
    $freteTransportadora = $mP007Selecionada['P007_Transportadora'];
    $freteQuemPaga = $mP007Selecionada['P007_Quem_Paga'];
}

echo <<<EOT
<div class="contentCabecalhoLayout" style="padding:10px;">
    <table class="tabela" width="100%">
        <tr class="linhaDesc">
            <td align="center">Quantidade total</td>
            <td align="center">Cubagem total (m&sup3;)</td>
            <td align="center">Peso liquido (kg)</td>
        </tr>
        <tr style="font-size:1.1em; font-weight:bold;">
            <td style="padding:8px;" align="center" id="ven001FreteQuantidade">{$quantidadeTotal}</td>
            <td style="padding:8px;" align="center" id="ven001FreteCubagem">{$cubagemTotal}</td>
            <td style="padding:8px;" align="center" id="ven001FretePesoLiquido">{$pesoLiquido}</td>
        </tr>
    </table>

    <div style="margin-top:15px;">
        <button type="button" id="ven001CalcularFreteBtn" style="float:left; margin:5px 10px 0 0;">Calcular Frete</button>
    </div>

    <div id="ven001FreteResultado" style="display:{$resultadoDisplay}; clear:both; margin-top:15px;">
        <table class="tabela" width="100%">
            <tr class="linhaDesc">
                <td align="center">Valor do frete (R$)</td>
                <td align="center">Prazo de entrega (dias)</td>
                <td align="center">Transportadora</td>
                <td align="center">Quem paga o frete</td>
            </tr>
            <tr style="font-size:1.1em; font-weight:bold;">
                <td style="padding:8px;" align="center" id="ven001FreteValor">{$freteValor}</td>
                <td style="padding:8px;" align="center" id="ven001FretePrazo">{$fretePrazo}</td>
                <td style="padding:8px;" align="center" id="ven001FreteTransportadora">{$freteTransportadora}</td>
                <td style="padding:8px;" align="center" id="ven001FreteQuemPaga">{$freteQuemPaga}</td>
            </tr>
        </table>
    </div>
</div>

<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
    var ven001FreteOpcoes = [];
    var ven001FreteT003Id = '{$T003_Id}';

    function ven001AtualizarResultadoFrete(data) {
        $('#ven001FreteValor').html(data.valorFrete);
        $('#ven001FretePrazo').html(data.prazoEntrega);
        $('#ven001FreteTransportadora').html(data.transportadora);
        $('#ven001FreteQuemPaga').html(data.quemPaga);
        $('#ven001FreteResultado').show();
    }

    function ven001MontarDialogFrete(opcoes) {
        var html = '<table class="tabela" width="100%" style="margin-top:10px;">';
        html += '<tr class="linhaDesc">';
        html += '<td></td><td align="center">Valor (R$)</td><td align="center">Prazo (dias)</td><td align="center">Transportadora</td><td align="center">Frete</td>';
        html += '</tr>';

        for (var i = 0; i < opcoes.length; i++) {
            var op = opcoes[i];
            var radioValue = (typeof op.p007Id !== 'undefined' && op.p007Id !== null && op.p007Id !== '') ? op.p007Id : i;
            html += '<tr style="cursor:pointer;" onclick="$(\'input[name=ven001FreteOpcao][value=' + radioValue + ']\').prop(\'checked\', true);">';
            html += '<td align="center"><input type="radio" name="ven001FreteOpcao" value="' + radioValue + '"' + (i === 0 ? ' checked' : '') + '></td>';
            html += '<td align="center">' + op.valorFrete + '</td>';
            html += '<td align="center">' + op.prazoEntrega + '</td>';
            html += '<td align="center">' + op.transportadora + '</td>';
            html += '<td align="center">' + op.quemPaga + '</td>';
            html += '</tr>';
        }
        html += '</table>';

        var \$buttons = {
            "Confirmar": function() {
                var radioVal = $('input[name=ven001FreteOpcao]:checked').val();
                if (typeof radioVal === 'undefined') {
                    dialogConfirm('Selecionar frete', 'Selecione uma opcao de frete.');
                    return;
                }
                var op = null;
                for (var j = 0; j < ven001FreteOpcoes.length; j++) {
                    if (String(ven001FreteOpcoes[j].p007Id) === String(radioVal)) {
                        op = ven001FreteOpcoes[j];
                        break;
                    }
                }
                if (!op && !isNaN(radioVal) && ven001FreteOpcoes[radioVal]) {
                    op = ven001FreteOpcoes[radioVal];
                }
                if (!op) {
                    dialogConfirm('Selecionar frete', 'Opcao de frete invalida. Calcule o frete novamente.');
                    return;
                }
                var p007IdEnvio = (typeof op.p007Id !== 'undefined' && op.p007Id !== null && op.p007Id !== '') ? op.p007Id : radioVal;
                var dialogRef = $(this);
                dialogRef.dialog("destroy").remove();
                $('#{$g['divId']}').showLoading();
                $.getJSON('/ven/ven001/grid_func-ajax/ven001AplicarFrete/?ajax=true'
                    + '&T003_Id=' + encodeURIComponent(ven001FreteT003Id)
                    + '&p007Id=' + encodeURIComponent(p007IdEnvio)
                    + '&opcaoId=' + encodeURIComponent(op.id || '')
                    + '&valorFreteNum=' + encodeURIComponent(op.valorFreteNum)
                    + '&prazoEntrega=' + encodeURIComponent(op.prazoEntrega)
                    + '&transportadora=' + encodeURIComponent(op.transportadora)
                    + '&quemPaga=' + encodeURIComponent(op.quemPaga)
                    + '&callback=?', function(request) {
                    $('#{$g['divId']}').hideLoading();
                    if (request.code) {
                        ven001AtualizarResultadoFrete(request.data);
                        var divIdParent = $('#{$g['divId']}').data('divIdParent');
                        if (divIdParent) {
                            divRefresh(divIdParent, true);
                        }
                    } else {
                        dialogConfirm('Aplicar frete', request.data);
                    }
                });
            },
            "Cancelar": function() {
                $(this).dialog("destroy").remove();
            }
        };
        dialogConfirm('Selecionar frete', html, \$buttons);
    }

    $('#ven001CalcularFreteBtn').unbind('click').bind('click', function() {
        $('#{$g['divId']}').showLoading();
        $.getJSON('/ven/ven001/grid_func-ajax/ven001CalcularFrete/?ajax=true&T003_Id=' + encodeURIComponent(ven001FreteT003Id) + '&callback=?', function(request) {
            $('#{$g['divId']}').hideLoading();
            if (request.code && request.data.opcoes && request.data.opcoes.length > 0) {
                if (request.data.T003_Id) {
                    ven001FreteT003Id = request.data.T003_Id;
                }
                ven001FreteOpcoes = request.data.opcoes;
                ven001MontarDialogFrete(request.data.opcoes);
            } else if (request.code) {
                dialogConfirm('Calcular frete', 'Nenhuma opcao de frete retornada.');
            } else {
                dialogConfirm('Calcular frete', request.data);
            }
        });
    });
} </script></div>
EOT;
