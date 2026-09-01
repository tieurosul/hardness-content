<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /ven-ven003-grid-ven003GridPrincipal/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/


include 'prog/ven/ven003/grid_func-js.php';

if (!function_exists('hardness\\ven003SelecionarParanaCompetitivo')) {
    function ven003SelecionarParanaCompetitivo($array)
    {
        $id = (int) $array['todosCampos']['T007_Id'];

        return '<input type="checkbox" class="paranaCompetitivoSelecionar" value="' . $id . '" onclick="event.stopPropagation();" title="Selecionar para Paraná Competitivo" />';
    }
}
        $filtroObrigatorio = ($g['C031']['obrigatorioFiltroTelaTransicao'] == 'S') ? true : false;
        if($g['c029Ids'][242]){
            $habilitaCampo = true;
        }else{
            $habilitaCampo = false;
        }

		$grid = array(
			array('colunaValor' => 'paranaCompetitivoSelecionar', 'colunaTitulo' => 'PC', 'colunaTipo' => 'livre', 'callback' => 'ven003SelecionarParanaCompetitivo', 'colunaAlinhar' => 'center', 'style' => 'width:1%;'),
            array('colunaValor' => 'excluirLinha', 'colunaTipo' => 'livre', 'callback' => 'excluirNFe'),
            array('colunaValor' => 'imprimirMinutaDeDespacho', 'colunaTipo' => 'livre', 'colunaTitulo' => 'Minuta', 'callback' => 'imprimirMinutaDeDespacho', 'style' => 'width:30px; padding: 3px 10px 3px 20px;', 'colunaAlinhar' => 'center',),
            array('colunaValor' => 'impressaoEtiqueta', 'colunaTipo' => 'livre', 'callback' => 'abrirNotificacao', 'callbackParameter' => array('Imprimir Etiquetas', '/sistema/impressao/etiquetas/notafiscal/', 'T007_Id', 'right', false, '', '<span class="ui-icon ui-icon-tag"></span>', 330), 'style' => 'width: 1%;', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Id', 'colunaTitulo' => 'Id', 'colunaAlinhar' => 'center', 'chavePrimaria' => true, 'style' => 'width:1%;font-size:10px', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Flag_Impresso', 'colunaTitulo' => '', 'callback' => 'campoMarcarNFImpressao', 'colunaEditarTodos' => 'S', 'colunaEditarTipo' => 'flag', 'colunaEditarExtra' => 'M|S'),
            array('colunaValor' => 'if(T007_Flag_ACP!="3",LPAD(T007_Numero_Nota_Fiscal,7,"0"),T007_Numero_Nota_Fiscal) as T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'NF-e', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T007_Numero_Nota_Fiscal', 'colunaTitulo' => 'T007_Numero_Nota_Fiscal', 'colunaExibir' => false),
            array('colunaValor' => 'T276_Flag_Cancelada', 'colunaTitulo' => 'T276_Flag_Cancelada', 'colunaExibir' => false),
            array('colunaValor' => 'T276_C004_Id', 'colunaTitulo' => 'T276_C004_Id', 'colunaExibir' => false),
            array('colunaValor' => 'T276_Numero_Protocolo_MDFe', 'colunaTitulo' => 'T276_Numero_Protocolo_MDFe', 'colunaExibir' => false),
            array('colunaValor' => 'T276_Numero_MDFe', 'colunaTitulo' => 'MDF-e', 'colunaAlinhar' => 'left','colunaExibir' => false),
            array('colunaValor' => 'T007_Flag_ACP', 'colunaTitulo' => 'Tipo', 'callback' => 'validaTipo'),
            array('colunaValor' => 'T007_Serie', 'colunaTitulo' => 'Série', 'colunaAlinhar' => 'center', 'style' => 'width:1%'),
            array('colunaValor' => 'T005_Numero_Pedido_Venda', 'colunaTitulo' => 'N Pedido', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Data_Emissao', 'colunaTitulo' => 'Emissão', 'callback' => 'gCorrigeData', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'T007_Flag_Marcado', 'colunaTitulo' => 'MDF-e', 'callback' => 'validaFlagNotaMarcada', 'colunaEditarTodos' => 'S', 'colunaEditarTipo' => 'flag', 'colunaEditarExtra' => 'N|S','colunaExibir' => $habilitaCampo,'style' => '','colunaAlinhar' => 'center',),
            array('colunaValor' => 'T005_Id', 'colunaTitulo' => 'Pedido', 'colunaAlinhar' => 'center', 'chavePrimaria' => true),
            array('colunaValor' => 'T007_D024_Id', 'colunaTitulo' => 'Código', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'D024_Nome_Empresa', 'colunaTitulo' => 'Razao Social'),
            array('colunaValor' => 'D024_Nome_Fantasia', 'colunaTitulo' => 'Nome Fantasia', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'D024_Cnpj', 'colunaTitulo' => 'CNPJ', 'colunaAlinhar' => 'left', 'style' => 'width: 1%'),
            array('colunaValor' => 'D024_Cpf', 'colunaTitulo' => 'CPF', 'colunaAlinhar' => 'left', 'style' => 'width: 1%'),
            array('colunaValor' => 'concat(CONVERT(substr(Pedido.D013_Nome_Contato,1,10) USING utf8),\' \',ifnull(Pedido.D013_DDD_Telefone_1,\'\'),\'-\',ifnull(Pedido.D013_Telefone_1,\'\')) as Contato', 'colunaTitulo' => 'Contato', 'colunaAlinhar' => 'left', 'style' => 'max-width:170px;white-space:normal;min-width:170px;width:170px'),
            array('colunaValor' => 'UFCliente.D018_UF', 'colunaTitulo' => 'UF', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'UFClienteEntrega.D018_UF', 'colunaTitulo' => 'UF Entrega', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'D006_Codigo_CFOP', 'colunaTitulo' => 'CFOP', 'colunaDimensao' => 'S'),
            array('colunaValor' => 'T007_Valor_Total_Produtos', 'colunaTitulo' => 'Produtos', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S'),
            array('colunaValor' => 'T007_Valor_Total', 'colunaTitulo' => 'Total', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'style' => 'background:var(--celula-amarelo-claro)'),
            array('colunaValor' => 'T007_Prazos(T007_Id)', 'colunaTitulo' => 'Prazo', 'style' => 'max-width:300px;white-space:normal;min-width:200px;width:200px;word-wrap:break-word;', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007_Percentual_Margem', 'colunaTitulo' => '% Marg', 'colunaAlinhar' => 'right', 'style' => 'width:1%', 'colunaCasasDecimais' => '2', 'colunaExibirNivel' => '4', 'colunaExibir' => false),
            array('colunaValor' => 'T007_IPV', 'colunaTitulo' => 'IPV', 'colunaCasasDecimais' => '4', 'colunaAlinhar' => 'right', 'style' => 'width:1%', 'colunaExibirNivel' => '4', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Data_Envio_XML', 'colunaTitulo' => 'Mail', 'callback' => 'validaCampoEmail'),
            array('colunaValor' => 'T007_Data_Envio_Email', 'colunaTitulo' => 'Data Envio Email', 'colunaExibir' => false),
            array('colunaValor' => 'Danfe', 'colunaTitulo' => 'Danfe', 'colunaTipo' => 'livre', 'callback' => 'ValidaCampoDanfe'),
            array('colunaValor' => 'NF', 'colunaTitulo' => 'NF', 'colunaTipo' => 'livre', 'callback' => 'validaCampoNF', 'colunaExibir' => false),
            array('colunaValor' => 'ACP', 'colunaTitulo' => 'ACP', 'colunaTipo' => 'livre', 'callback' => 'validaCampoACP', 'colunaExibir' => true),
            array('colunaValor' => 'CCe', 'colunaTitulo' => 'CC-e', 'colunaTipo' => 'livre', 'callback' => 'enviarCCe'),
            array('colunaValor' => 'T007_Flag_Cancelada', 'colunaTitulo' => 'Cancelar', 'callback' => 'validaCampoCancelar', 'style'=>'max-width:350px;min-width:350px;width:350px;white-space:normal;'),
            array('colunaValor' => 'Damdfe', 'colunaTitulo' => 'Damdfe', 'colunaTipo' => 'livre', 'callback' => 'ValidaCampoDamdfe','colunaExibir' => $habilitaCampo),
            array('colunaValor' => 'XML', 'colunaTitulo' => 'XML', 'callback' => 'validaCampoXML', 'colunaTipo' => 'livre'),
            array('colunaValor' => 'D021_Nome_Empresa', 'colunaTitulo' => 'Representante', 'colunaDimensao' => 'S', 'colunaExibir' => false),
            array('colunaValor' => 'T007_C007_Id_Vendedor_Interno', 'colunaTitulo' => 'Vendedor', 'colunaEditar' => true, 'colunaEditarGeraSelect' => array('C007', 'C007_Id', 'C007_Primeiro_Nome', 'where C007_Ativo="S" order by C007_Primeiro_Nome', true), 'callback' => 'mostrarVendedorInternoNF', 'colunaEditarNivel' => 4, 'style' => 'width:1%;background:var(--celula-amarelo-claro)'),
            array('colunaValor' => 'vendedor.C007_Primeiro_Nome', 'colunaTitulo' => 'Vendedor', 'colunaDimensao' => 'S', 'style' => 'background:var(--celula-amarelo-claro)', 'colunaExibir' => false),
            array('colunaValor' => 'T007_C007_Id_Vendedor_Externo', 'colunaTitulo' => 'Vendedor Ext', 'colunaExibir' => false, 'colunaEditar' => true, 'colunaEditarGeraSelect' => array('C007', 'C007_Id', 'C007_Primeiro_Nome', 'where C007_Ativo="S" order by C007_Primeiro_Nome', true), 'callback' => 'mostrarVendedorExterno', 'colunaEditarNivel' => 4, 'style' => 'width:1%;background:var(--celula-amarelo-claro)'),
            array('colunaValor' => 'Externo.C007_Primeiro_Nome', 'colunaTitulo' => 'Vendedor Ext', 'colunaDimensao' => 'S', 'style' => 'background:var(--celula-amarelo-claro)', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Percentual_Comissao', 'colunaTitulo' => '%Comissão', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaExibir' => true, 'colunaEditarOculto' => (isset($g['c029Ids'][201])) ? true : false, 'style' => 'background:var(--celula-amarelo-claro)', 'colunaEditarJSCallback' => 'colunaEditarPercentualComissao'),
            array('colunaValor' => 'T007_Valor_Total_Comissao', 'colunaTitulo' => 'Comissão', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaExibir' => true, 'style' => 'background:var(--celula-amarelo-claro)'),
            array('colunaValor' => 'T007_D021_Id', 'colunaTitulo' => 'Representante', 'colunaEditarGeraSelect' => array('D021', 'D021_Id', 'D021_Nome_Empresa','',true), 'colunaEditarOculto' => true, 'callback' => 'campoMostraValor', 'callbackParameter' => 'D021_Nome_Empresa', 'colunaEditarJSCallback' => "colunaEditarRepresentante", 'style' => 'background:var(--celula-verde)'),
            array('colunaValor' => 'T007_Percentual_Comissao_Representante', 'colunaTitulo' => '%Comissão', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right', 'colunaExibir' => true, 'colunaEditarOculto' => (isset($g['c029Ids'][202])) ? true : false, 'style' => 'background:var(--celula-verde)', 'colunaEditarJSCallback' => 'colunaEditarPercentualComissao'),
            array('colunaValor' => 'T007_Valor_Comissao_Representante', 'colunaTitulo' => 'Comissão R$', 'colunaCasasDecimais' => '2', 'colunaAlinhar' => 'right','colunaExibir' => true, 'style' => 'background:var(--celula-verde)'),
            array('colunaValor' => 'D022_Nome_Empresa', 'colunaTitulo' => 'Transportadora'),
            array('colunaValor' => 'T007_Valor_Total_ICMS', 'colunaTitulo' => 'ICM', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007_Valor_ICMS_Substituicao_Retencao', 'colunaTitulo' => 'ST', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007_Valor_Total_IPI', 'colunaTitulo' => 'IPI', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007_Valor_Total_PIS', 'colunaTitulo' => 'PIS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007_Valor_Total_COFINS', 'colunaTitulo' => 'COFINS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007A_Valor_Total_PIS_Retido', 'colunaTitulo' => 'PIS Ret.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            array('colunaValor' => 'T007A_Valor_Total_COFINS_Retido', 'colunaTitulo' => 'COFINS Ret.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4),
            //array('colunaValor' => 'Retorno Sefaz', 'colunaTipo' => 'livre', 'colunaTitulo' => 'Retorno SEFAZ', 'callback' => 'chamaCampo'),
            array('colunaValor' => 'T007_Numero_Protocolo_Nfe', 'colunaTitulo' => 'Protocolo NFe'),
            array('colunaValor' => 'T007_Numero_Protocolo_Cancelamento_Nfe', 'colunaTitulo' => 'Prot. Cancel', 'callback' => 'validaCampoXMLCancelado'),
            array('colunaValor' => 'T007_Chave_Acesso_Nfe', 'colunaTitulo' => 'Chave Acesso', 'callback' => 'ValidaChaveAcesso'),
            array('colunaValor' => 'D024_Id', 'colunaTitulo' => 'ID', 'colunaExibir' => false),
            array('colunaValor' => 'T007_C004_Id', 'colunaTitulo' => 'ID', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Valor_Desconto', 'colunaTitulo' => 'Desc.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaExibirNivel' => 4, 'colunaExibir' => false),
            array('colunaValor' => 'T007_Versao_Nfe', 'colunaTitulo' => 'Versão', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Flag_Status', 'colunaTitulo' => 'Status', 'colunaExibir' => false),
            array('colunaValor' => 'T005_Nome_Status as T005_Status', 'colunaTitulo' => 'Status Pedido', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Data_Hora_Inutilizacao', 'colunaTitulo' => 'Hora Inutilização', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Flag_Inutilizado', 'colunaTitulo' => 'Inutilização', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Numero_Recibo_Nfe', 'colunaTitulo' => 'Recibo', 'colunaExibir' => false),
            //array('colunaValor' => 'T007_Data_Envio_XML', 'colunaTitulo' => 'XML', 'colunaExibir' => false),
            //array('colunaValor' => '(select ifnull(T127_Id,0) from T127 where T127_Numero_Nota_Fiscal=T007_Numero_Nota_Fiscal and T127_C004_Id=T007_C004_Id limit 1) as T127_Id', 'colunaTitulo' => '', 'colunaExibir' => false),
            //array('colunaValor' => '(select T008_Codigo_Produto from T008 where T008_T007_Id=T007_Id limit 1) as T008_Codigo_Produto', 'colunaTitulo' => '', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Protocolo_Inutilizacao', 'colunaTitulo' => 'Prot.Inutilizacao', 'colunaExibir' => false),
            array('colunaValor' => 'T007_Observacao_Cancelamento', 'colunaTitulo' => 'Motivo Cancelamento', 'colunaExibir' => false),
            array('colunaValor' => 'UFFaturamento.D018_UF', 'colunaTitulo' => 'UF Faturamento', 'colunaAlinhar' => 'center'),
            array('colunaValor' => 'T007_Data_Entrega', 'colunaTitulo' => 'Data Entrega', 'callback' => 'gCorrigeData', 'colunaAlinhar' => center, 'colunaEditar' => true, 'colunaExibir' => false),
            array('colunaValor' => 'T007_Cliente_Endereco', 'colunaTitulo' => 'Rua', 'colunaAlinhar' => 'left'),
            array('colunaValor' => 'T007_Cliente_Endereco_Numero', 'colunaTitulo' => 'Número', 'colunaAlinhar' => 'right'),
            array('colunaValor' => 'T007_Cliente_Bairro', 'colunaTitulo' => 'Bairro', 'colunaAlinhar' => 'left'),
            array('colunaValor' => 'T007_Cliente_CEP', 'colunaTitulo' => 'CEP', 'colunaAlinhar' => 'left'),
            array('colunaValor' => 'T007A_Id', 'colunaTitulo' => '',  'chavePrimaria' => true, 'colunaExibir' => false),
            array('colunaValor' => 'T007A_Flag_Contingencia_Offline', 'colunaExibir' => false),
            /*            array('colunaValor' => '(SELECT GROUP_CONCAT(DISTINCT(C008_Tipo)) as Tipo
            FROM T008 
            LEFT JOIN D009 ON D009_Id=T008_D009_Id 
            LEFT JOIN D049 ON D049_Id=D009_D049_Id 
            LEFT JOIN D001 ON D001_Id=D049_D001_Id 
            LEFT JOIN C008 ON C008_Id=D001_C008_Id 
            WHERE T008_T007_Id=T007_Id) as Tipos', 'colunaTitulo' => 'Tipos'),
            array('colunaValor' => '(SELECT SUM(T006_Valor_Desconto_Unitario_Bruto)
            FROM T006 
            LEFT JOIN T005 ON T006_T005_Id = T005_Id 
            WHERE T005_Id = T007_T005_Id) as desconto', 'colunaTitulo' => 'Desc.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaTipo' => 'banco', 'colunaExibir' => false),
            */
            /* [REFORMA TRIBUTÁRIA] - Gabriel Cegantini 19/09/2025
            ==============================================================*/
            array('colunaValor' => 'T007A_Valor_Total_Reforma', 'colunaTitulo' => 'Total RT', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_CBS', 'colunaTitulo' => 'CBS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_Diferimento_CBS', 'colunaTitulo' => 'Dif. CBS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_IBS_UF', 'colunaTitulo' => 'IBS UF', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_Diferimento_IBS_UF', 'colunaTitulo' => 'Dif. IBS UF', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_IBS_Municipal', 'colunaTitulo' => 'IBS Mun.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_Diferimento_IBS_Municipal', 'colunaTitulo' => 'Dif. IBS Mun.', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_IBS', 'colunaTitulo' => 'IBS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            array('colunaValor' => 'T007A_Valor_Total_Base_CBS_IBS', 'colunaTitulo' => 'Base CBS/IBS', 'colunaAlinhar' => 'right', 'colunaCasasDecimais' => '2', 'colunaMetrica' => 'S', 'colunaExibirNivel' => 4, 'style' => 'background:var(--celula-azul)'),
            /*==============================================================*/
        );

		/**
		* Pode ser definido uma classe para cada linha do grid
		*/
		function gridLinha($array) {
			if($array['todosCampos']['T007_Flag_Cancelada']=='S'){
				return 'linhaVermelho';
			}

            if($array['todosCampos']['T276_Flag_Cancelada']=='S'){
                return 'linhaRed';
            }
		}
		

		/**
		 * Definição da SQL
		 */
		$from = "T007";
		
		$extra = <<<EOT
        -- JOIN
			left join T005 on T005_Id=T007_T005_Id
            left join D013 as Pedido on Pedido.D013_Id=T005_D013_Id
		    left join D024 on D024_Id=T007_D024_Id
		    left join D018 as UFCliente on UFCliente.D018_Id=D024_D018_Id
		    left join D006 on D006_Id=T007_D006_Id
            left join C007 as Vendedor on Vendedor.C007_Id=T007_C007_Id_Vendedor_Interno
            left join C007 as Supervisor on Supervisor.C007_Id=Vendedor.C007_C007_Id
            left join C007 as Supervisor2 on Supervisor2.C007_Id=Vendedor.C007_C007_Id_2
            left join C007 as Externo on Externo.C007_Id=T007_C007_Id_Vendedor_Externo
            left join C007 as Substituto on Substituto.C007_Id=Vendedor.C007_Id_Vendedor_Substituto
		    left join D021 on D021_Id=T007_D021_Id
		    left join D022 on D022_Id=T007_D022_Id
            left join D018 as UFFaturamento on UFFaturamento.D018_Id=D022_D018_Id
            left join D148 on D148_Id=T007_D148_Id_Entrega
            left join D018 as UFClienteEntrega on UFClienteEntrega.D018_Id=D148_D018_Id
            left join T284 on T007_Id=T284_T007_Id
            left join T276 on T276_Id=T284_T276_Id
            left join T007A on T007_Id = T007A_T007_Id
        -- WHERE
		    where T007_C004_Id='{$g['empresaAtual']}'
EOT;

        // and T007_Flag_Emitida_Recebida!='R'

		/*
		echo <<<EOT
			<div class="buttonsBottomTop">
				<button type="button" id="ven003IncluirNotaFiscal">Incluir</button>
                <button type="button" id="ven003IncluirNotaFiscalConsumidor">PDV</button>
                <button type="button" id="ven003ImprimirNFeMarcada">Imprimir Marcadas</button>
                <button type="button" id="ven003BaixarXMLNFEMarcada">Baixar XML Marcadas</button>
EOT;
        if($habilitaCampo){
        echo <<<EOT
                <button type="button" id="ven003GerarMdfe">Gerar MDF-e</button>
EOT;
        }  
        echo <<<EOT
                <!--<button type="button" id="ven003MarcarTodos">Marcar Todos</button>-->
                <!--<button type="button" id="ven003DesmarcarTodos">Desmarcar Todos</button>-->
                <!--<button type="button" id="ven003Inutilizar">Inutilizar Numeração</button>-->
                <!--<button type="button" id="ven003ImportarXML">Importar XML</button>-->
                <!--<button type="button" id="ven003GerarLoteRps">Gerar Lote Rps</button>-->
            </div>
EOT;
        */


		/**
		* Filtro
		*/
		$gridFiltro = array(
            'D024_Nome_Empresa,D024_Nome_Fantasia' => array('titulo' => 'Cliente', 'tipo' => 'texto'),
            'Pedido.D013_Nome_Contato' => array('titulo' => 'Contato', 'tipo' => 'texto'),
            'T007_Numero_Nota_Fiscal' => array('titulo' => 'Número', 'tipo' => 'numero'),
            'T276_Numero_MDFe' => array('titulo' => 'Número MDF-e', 'tipo' => 'texto'),
            'T007_Data_Emissao' => array('titulo' => 'Emissão', 'tipo' => 'data'),
            'UFCliente.D018_UF' => array('titulo' => 'UF', 'tipo' => 'texto'),
            'D006_Codigo_CFOP,D006_Descricao' => array('titulo' => 'CFOP', 'tipo' => 'texto'),
            'D024_Cnpj' => array('titulo' => 'CNPJ', 'tipo' => 'texto'),
            'D006_Flag_Entrada_Saida' => array('titulo' => 'Origem', 'tipo' => 'select', 'select' => array(
                array('title' => 'Todas', 'value' => ''),
                array('title' => 'Entrada', 'value' => 'E'),
                array('title' => 'Saída', 'value' => 'S'),
              ),
              'naoGerarExtra' => ''),
            'vendedor.C007_Primeiro_Nome' => array('titulo' => 'Vendedor', 'tipo' => 'texto'),
            'D021_Nome_Empresa' => array('titulo' => 'Representante', 'tipo' => 'texto'),
            'T007_Valor_Total' => array('titulo' => 'Valor', 'tipo' => 'moeda'),
            'T007_Flag_Cancelada' => array('titulo' => 'Cancelada', 'tipo' => 'select', 'select' => array(
                array('title' => 'Todas', 'value' => ''),
                array('title' => 'Sim', 'value' => 'S'),
                array('title' => 'Não', 'value' => 'N'),
              ),
              'naoGerarExtra' => ''),
            'T007_Flag_Marcado' => array('titulo' => 'Gerar MDFe', 'tipo' => 'select', 'select' => array(
                array('title' => 'Todas', 'value' => ''),
                array('title' => 'Sim', 'value' => 'S'),
                array('title' => 'Não', 'value' => 'N'),
              ),
              'naoGerarExtra' => ''),
            '(SELECT GROUP_CONCAT(DISTINCT(C008_Tipo)) as Tipo
                                                  FROM T008 
                                                  LEFT JOIN D009 ON D009_Id=T008_D009_Id 
                                                  LEFT JOIN D049 ON D049_Id=D009_D049_Id 
                                                  LEFT JOIN D001 ON D001_Id=D049_D001_Id 
                                                  LEFT JOIN C008 ON C008_Id=D001_C008_Id 
                                                  WHERE T008_T007_Id=T007_Id)' => array('titulo' => 'Tipo produto', 'tipo' => 'texto'),
            '(SELECT GROUP_CONCAT(DISTINCT(D082_Marca)) as Marca
                                                  FROM T008 
                                                  LEFT JOIN D009 ON D009_Id=T008_D009_Id 
                                                  LEFT JOIN D049 ON D049_Id=D009_D049_Id 
                                                  LEFT JOIN D082 ON D082_Id=D049_D082_Id 
                                                  LEFT JOIN D001 ON D001_Id=D049_D001_Id 
                                                  WHERE T008_T007_Id=T007_Id)' => array('titulo' => 'Marca produto', 'tipo' => 'texto'),
            'T007_Numero_Protocolo_Nfe' => array('titulo' => 'Protocolo', 'tipo' => 'text', 'exibir' => false),
            'T007_Flag_ACP' => array('titulo' => 'Tipo', 'tipo' => 'select', 'select' => array(
                array('title' => 'Todos', 'value' => ''),
                array('title' => 'NF-e', 'value' => '6'),
                array('title' => 'NFC-e', 'value' => '8'),
                array('title' => 'CF', 'value' => '4'),
                array('title' => 'ACP', 'value' => '3')
              ),
            ),
            'T007_Prazos(T007_Id)' => array('titulo' => 'Prazo', 'tipo' => 'texto'),
            'T007_Percentual_Margem' => array('titulo' => '% Margem', 'tipo' => 'moeda'),
            'T007_IPV' => array('titulo' => 'IPV', 'tipo' => 'moeda'),
            'T005_Ordem_Compra' => array('titulo' => 'OC. Cliente', 'tipo' => 'texto'),
            'D022_Nome_Empresa' => array('titulo' => 'Transportadora', 'tipo' => 'texto'),
            'T007_Data_Entrega' => array('titulo' => 'Data Entrega', 'tipo' => 'data', 'exibir' => false),
        );

        //Gera o filtro e acrescenta ao $extra
        $extra = $g['ui']->gridFiltroExtra(true, $gridFiltro, $extra);
        $extra_ = base64_encode($extra);

		// Monta fórmula total produtos sem impostos como base do cálculo da Margem
		$baseMargem  = "({Valor_Produtos}";
		$baseMargem .= ($g['C031']['somarICMSPrecoVenda']   == 'S') ? "-{Valor_ICMS_}"   : "" ;
		$baseMargem .= ($g['C031']['somarPISPrecoVenda']    == 'S') ? "-{Valor_PIS_}"    : "" ;
		$baseMargem .= ($g['C031']['somarCOFINSPrecoVenda'] == 'S') ? "-{Valor_COFINS_}" : "" ;
		$baseMargem .= ($g['C031']['somarIRPJPrecoVenda']   == 'S') ? "-{Valor_IRPJ_}"   : "" ;
		$baseMargem .= ($g['C031']['somarCSLLPrecoVenda']   == 'S') ? "-{Valor_CSLL_}"   : "" ;
		$baseMargem .= ")";

        /**
         * Totais
         */
        $totais = array(
            // sql
            array('nome' => 'Count', 'titulo' => 'Registros', 'totalizador' => 'count', 'callbackParameter' => 0),
            array('nome' => 'Valor_ICMS_', 'valor' => 'T007_Valor_Total_ICMS', 'titulo' => 'ICMS R$', 'exibir' => false),
            array('nome' => 'Valor_PIS_', 'valor' => 'T007_Valor_Total_PIS', 'titulo' => 'ICMS R$', 'exibir' => false),
            array('nome' => 'Valor_COFINS_', 'valor' => 'T007_Valor_Total_COFINS', 'titulo' => 'ICMS R$', 'exibir' => false),
            array('nome' => 'Valor_IRPJ_', 'valor' => 'T007_Valor_Total_IRPJ', 'titulo' => 'ICMS R$', 'exibir' => false),
            array('nome' => 'Valor_CSLL_', 'valor' => 'T007_Valor_Total_CSLL', 'titulo' => 'ICMS R$', 'exibir' => false),
            array('nome' => 'Valor_Produtos', 'valor' => 'T007_Valor_Total_Produtos', 'titulo' => 'Produtos R$'),
            array('nome' => 'Valor_IPI', 'valor' => 'T007_Valor_Total_IPI', 'titulo' => 'IPI R$'),
            array('nome' => 'Valor_ST', 'valor' => 'T007_Valor_ICMS_Substituicao_Retencao', 'titulo' => 'ST R$'),
            array('nome' => 'Valor_Desconto', 'valor' => 'T007_Valor_Desconto', 'titulo' => 'Desconto R$'),
            array('nome' => 'Valor_Despesas', 'valor' => 'T007_Valor_Despesas_Acessorias', 'titulo' => 'Desp.Assec. R$'),
            array('nome' => 'Valor_Frete', 'valor' => 'T007_Valor_Frete', 'titulo' => 'Frete R$'),
            array('nome' => 'Valor_Total', 'valor' => 'T007_Valor_Total', 'titulo' => 'Total R$'),
            array('nome' => 'Valor_Custo', 'valor' => 'T007_Valor_Total_Custo', 'titulo' => 'Custo R$'),
            array('nome' => 'Percentual_Margem', 'tipo' => 'totais', 'valor' => '(('.$baseMargem.' - {Valor_Custo}) / '.$baseMargem.') * 100', 'titulo' => '% Margem'),
            array('nome' => 'IPV', 'tipo' => 'totais', 'valor' => '('.$baseMargem.'/{Valor_Custo})', '1,' => 4, 'titulo' => 'IPV', 'exibir' => true),
            array('nome' => 'Valor_Comissao', 'valor' => 'T007_Valor_Total_Comissao', 'titulo' => 'Comissão R$'),
            array('nome' => 'Percentual_Comissao', 'tipo' => 'totais', 'valor' => '({Valor_Comissao}/{Valor_Produtos})*100', 'titulo' => '% Comissão'),
        );
        
        /**
         * Geração: Monta o SQL e retorna o dados
         */
        list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra);
		$sqlGridFiltro = base64_encode($geradoSql);
        
        //Botões
        $botoes = array(
            array('titulo' => 'Incluir', 'id' => 'ven003IncluirNotaFiscal', 'janelaUrl' => '/ven/ven003/content/ven003contentIncluirNotaFiscal/', 'janelaTamanho' => '[900,550]'),
            array('titulo' => 'PDV', 'id' => 'ven003IncluirNotaFiscalConsumidor'),
            array('titulo' => 'Imprimir Marcadas', 'id' => 'ven003ImprimirNFeMarcada'),
            array('titulo' => 'Baixar XML Marcadas', 'id' => 'ven003BaixarXMLNFEMarcada'),
            array('titulo' => 'Gerar MDF-e', 'id' => 'ven003GerarMdfe', 'exibirCallback' => 'return $g["c029Ids"][242];'),
			array('titulo' => 'Preencher Observações', 'id' => 'ven003ParanaCompetitivo', 'exibirCallback' => 'return isset($g["c029Ids"][303]);'),
        );
        echo Botoes::processar($botoes);

        // Imprime o filtro do GRID (normalmente executado acima do GRID)
        echo $g['ui']->gridFiltroPrint($gridFiltro);

        // Não executar quando houver o refresh de linha
        if (empty($r_linhaGridId)) {
            echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {		
                $('#ven003GerarMdfe').unbind('click').bind('click', function(data) {
                    $('#{$g['divId']}').showLoading();
                    $.getJSON('/fis/fis012/grid_func-ajax/gerarMDFeCabecalho/?ajax=true&callback=?', function(request) {
                        if (request.code) {
                            $('#{$g['divId']}').hideLoading();
                            var T276_Id=request.T276_Id;
                            abrirJanela(data, '{$g['divIdRoot']}', '{$g['divIdParent']}', unique(), '', 'Editar MDF-e - ID: ' + T276_Id, '/fis/fis012/content/fis012MdfeCadastro/', '&tab=geral&acaoId=' + encodeURIComponent(T276_Id), [1000,550]);
                            divRefresh('{$g['divIdParent']}', true);
                            divRefresh('{$g['divId']}', true);
                        } else {
                            $('#{$g['divId']}').hideLoading();
                            dialogConfirm('Erro', request.data);
                        }                                           
                    });
                });

				$('#ven003ParanaCompetitivo').unbind('click').bind('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
					var ids = $('#{$g['divId']} .paranaCompetitivoSelecionar:checked').map(function() { return this.value; }).get();
					$('#{$g['divId']}').showLoading();
					$.getJSON('/ven/ven003/grid_func-ajax/paranaCompetitivoPrevia/?ajax=true&ids=' + encodeURIComponent(ids.join(',')) + '&extra=' + encodeURIComponent('{$extra_}') + '&callback=?', function(request) {
						$('#{$g['divId']}').hideLoading();
						if (!request.code) { dialogConfirm('Erro', request.data); return; }
						var r = request.data, texto = ids.length ? 'Foram selecionadas ' + request.ids.length + ' notas fiscais.' : 'Foram encontradas ' + request.ids.length + ' notas fiscais nos filtros atuais.';
						texto += '<br><br>Itens aptos: ' + request.linhas.filter(function(l) { return l.situacao === 'apta para preenchimento'; }).length + '.<br>Já existentes: ' + r.ja_existentes + '.<br>Deseja preencher automaticamente as observações fiscais do Paraná Competitivo?';
						dialogConfirm('Preencher Observações', texto, {'Confirmar': function() {
							$(this).dialog('destroy').remove();
							var notas = request.ids || [], tamanhoLote = 25, indice = 0, etapa = 0;
							var totalEtapas = Math.ceil(notas.length / tamanhoLote), progressoId = 'ven003ParanaCompetitivoProgresso';
							var resumo = {notas_analisadas: notas.length, notas_atualizadas: 0, observacoes_incluidas: 0, ja_existentes: 0, ncm_sem_flag: 0, erros: 0, mensagens: []};
							if (!notas.length) { dialogConfirm('Nenhum registro', 'Não há notas fiscais para processar.'); return; }
							dialogAlert('Paraná Competitivo', "<div id='" + progressoId + "'></div>");
							function atualizarProgresso(mensagem) {
								var percentual = Math.round((indice / notas.length) * 100);
								$('#' + progressoId).html('<b>Etapa ' + (etapa || 1) + ' de ' + totalEtapas + '</b><br>' + mensagem + '<br><br>Registros processados: ' + indice + ' de ' + notas.length + ' (' + percentual + '%).<br>Notas atualizadas: ' + resumo.notas_atualizadas + '. Observações incluídas: ' + resumo.observacoes_incluidas + '.');
							}
							function processarProximoLote() {
								if (indice >= notas.length) {
									var pendencias = resumo.erros ? '<br><br><b>Pendências: ' + resumo.erros + '.</b><br>' + resumo.mensagens.join('<br>') : '';
									atualizarProgresso('<b>Processamento concluído.</b>' + pendencias);
									divRefresh('{$g['divId']}', true);
									return;
								}
								var lote = notas.slice(indice, indice + tamanhoLote); etapa++;
								atualizarProgresso('Processando registros ' + (indice + 1) + ' a ' + (indice + lote.length) + '.');
								$.getJSON('/ven/ven003/grid_func-ajax/paranaCompetitivoProcessar/?ajax=true&ids=' + encodeURIComponent(lote.join(',')) + '&callback=?', function(resp) {
									if (!resp.code) { $('#' + progressoId).html('<b>Processamento interrompido na etapa ' + etapa + '.</b><br>' + resp.data); return; }
									var s = resp.data;
									['notas_atualizadas', 'observacoes_incluidas', 'ja_existentes', 'ncm_sem_flag', 'erros'].forEach(function(campo) { resumo[campo] += parseInt(s[campo] || 0, 10); });
									if (s.mensagens && s.mensagens.length) { resumo.mensagens = resumo.mensagens.concat(s.mensagens); }
									indice += lote.length;
									setTimeout(processarProximoLote, 0);
								}).fail(function() {
									$('#' + progressoId).html('<b>Processamento interrompido na etapa ' + etapa + '.</b><br>Não foi possível obter resposta do servidor.');
								});
							}
							processarProximoLote();
						}, 'Cancelar': function() { $(this).dialog('destroy').remove(); }});
					}).fail(function() { $('#{$g['divId']}').hideLoading(); dialogConfirm('Erro', 'Não foi possível iniciar o processamento.'); });

				return false;
				});

                $('#ven003BaixarXMLNFEMarcada').bind('click', function(data) {
					var \$buttons = {
						"Sim": function() {
							$('#{$g['divId']}').showLoading();
							$.getJSON('/ven/ven003/grid_func-ajax/BaixarXMLNFEMarcada/?ajax=true&extra=' + encodeURIComponent('{$extra_}') + '&callback=?', function(request) {
								if (request.code) {
                                    var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
									window.location = request.data;
								    divRefresh('{$g['divId']}', true);
								} else {
									var \$buttons = { "Fechar": function() { $(this).dialog("destroy").remove(); } }
						    		dialogConfirm('Erro', request.data, \$buttons);
								}
								$('#{$g['divId']}').hideLoading();
								divRefresh('{$g['divId']}');
							});
							$(this).dialog("destroy").remove();
						},
						"Não": function() { $(this).dialog("destroy").remove(); }
					};
					dialogConfirm('Baixar', 'Baixar XMLs das NF-e marcadas em formato zip? ', \$buttons);
                });

				$('#ven003DesmarcarTodos').unbind('click').bind('click', function(data) {
					$.getJSON('/ven/ven003/grid_func-ajax/ven003checkDesmarcarTodos/?ajax=true&callback=?', function(request) {
						if (request.code) {
							divRefresh('{$g['divId']}', true);
						}
					});
				});
				$('#ven003MarcarTodos').unbind('click').bind('click', function(data) {
					$.getJSON('/ven/ven003/grid_func-ajax/ven003checkMarcarTodos/?ajax=true&sqlFiltro='+encodeURIComponent('{$sqlGridFiltro}')+'&callback=?', function(request) {
						if (request.code) {
							divRefresh('{$g['divId']}', true);
						} else {
							dialogConfirm('Erro', request.data);
						}											
					});
				});
                $('#xven003IncluirNotaFiscal').unbind('click').bind('click', function(data) {
                    abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Cadastro Nota Fiscal', '/ven/ven003/content/ven003contentIncluirNotaFiscal/','', [900,550]);
                });

                $('#ven003ImprimirNFeMarcada').unbind('click').bind('click', function(data) {
                    nova_janela('/ven/ven003/grid_func-ajax/ven003ImprimirMarcadas/?ajax=true&extra=' + encodeURIComponent('{$extra_}'),0,0,750,500,'yes','no','no','no','yes','no','Impressao Danfe');
                });
                $('#ven003Inutilizar').unbind('click').bind('click', function(data) {
                    abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Inutilizar Numeração', '/ven/ven003/content/ven003ContentInutilizarNumeracao/','', [500,450]);
                });
                $('#ven003ImportarXML').unbind('click').bind('click', function(data) {
                    abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Inutilizar Numeração', '/ven/ven003/content/ven003ContentImportarXML/','', [500,120]);
                });
                $('#ven003GerarLoteRps').unbind('click').bind('click', function(data) {
                    $.getJSON('/ven/ven003/grid_func-ajax/ven003gerarLoteRps/?ajax=true&callback=?', function(request) {
                        if (request.code) {
                            dialogConfirm('Operação Ok', request.data);
                            divRefresh('{$g['divId']}', true);
                        } else {
                            dialogConfirm('Erro', request.data);
                        }
                    });
                });
                
                $('#ven003IncluirNotaFiscalConsumidor').unbind('click').bind('click', function(data) {
                    $('#{$g['divId']}').showLoading();
                    $.getJSON('/ven/ven003/grid_func-ajax/ven003incluirNotaFiscalConsumidor/?ajax=true&callback=?', function(request) {
                        $('#{$g['divId']}').hideLoading();
                        if (request.code) { 
                            abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}', menuId('PDV'), '', 'Nota Fiscal', '/ven/ven003/content/ven003contentIncluirNotaFiscal/','&acaoId=' + encodeURIComponent(request.data), [1000,650]);
                        } else {
                            dialogConfirm('Erro', request.data, false);
                        }
                    });
                });

                function colunaEditarRepresentante(T007_Id, campoEditado, valorAntigo, novoValor) {
                  T007_Id = T007_Id.replace(/T007_Id-/, '');
                  $('#{$g['divId']}').showLoading();
                  $.getJSON('/ven/ven001/grid_func-ajax/alterarRepresentante/?ajax=true&T007_Id=' + encodeURIComponent(T007_Id) + 
                                              '&campoEditado='    + encodeURIComponent(campoEditado) +
                                              '&valorAntigo='     + encodeURIComponent(valorAntigo) +
                                              '&D021_Id='       + encodeURIComponent(novoValor) +  
                                              '&callback=?', function(request) {
                    $('#{$g['divId']}').hideLoading();
                    if (request.code) {
                      divRefresh('{$g['divId']}', true);
                    } else {
                      dialogConfirm('Erro', request.data);
                    }
                  });
                }
                function colunaEditarPercentualComissao(T007_Id, campoEditado, valorAntigo, novoValor) {
                    T007_Id = T007_Id.replace(/T007_Id-/, '');
                    divRefresh('{$g['divId']}', true, false, false, T007_Id, 'T007_Id'); // grid
                }
			} </script></div>
EOT;
        }

		/**
		* Botões (Versão Acima do GRID)
		*/
/*          $IPVTotal = $g['sqlAuto']->pegarSqlCampo("(SUM(T007_Valor_Total_Produtos-
                        IF('{$g['C031']['somarICMSPrecoVenda']}' = 'S', IFNULL(T007_Valor_Total_ICMS,0), 0)-
                        IF('{$g['C031']['somarPISPrecoVenda']}' = 'S', IFNULL(T007_Valor_Total_PIS,0), 0)-
                        IF('{$g['C031']['somarCOFINSPrecoVenda']}' = 'S', IFNULL(T007_Valor_Total_COFINS,0), 0)-
                        IF('{$g['C031']['somarIRPJPrecoVenda']}' = 'S', IFNULL(T007_Valor_Total_IRPJ,0), 0)-
                        IF('{$g['C031']['somarCSLLPrecoVenda']}' = 'S', IFNULL(T007_Valor_Total_CSLL,0), 0)) / SUM(T007_Valor_Total_Custo))", $from, $extra);
                        $IPVTotal = gCorrigeNumero($IPVTotal,4);
  */

		
		echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {

                acaoClickLinha('{$g['divId']}', function(data, array) {
                    abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' ,menuId('PDV'), '', 'Editar Nota Fiscal - ID: ' + array['todosCampos']['T007_Id'], '/ven/ven003/content/ven003contentIncluirNotaFiscal/', '&acaoId=' + encodeURIComponent(array['todosCampos']['T007_Id']) + '&tabela={$from}', [990,605]);
                });
			} </script></div>
EOT;
		
		/**
		 * Geração: Grid
		 */
		$gridTabela = $g['ui']->grid($grid, $geradoDados);
        $g['smarty']->assign('thead', $gridTabela['thead']);
        $g['smarty']->assign('tbody', $gridTabela['tbody']);
        $g['smarty']->assign('tfoot', $gridTabela['tfoot']);
		$g['smarty']->assign('gridId', md5($g['r_']));
		$g['smarty']->assign('gridPersonalizacao', json_encode(array($g['r_'], $g['divIdRoot'], $g['divId']), JSON_FORCE_OBJECT));
        gBotaoAuditoria($from);
		if (isset($r_generateHTML)) { ob_end_clean(); ob_start(); $g['smarty']->display('gridHTML.tpl'); } else { $g['smarty']->display('grid.tpl'); }




