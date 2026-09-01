<?php
namespace hardness;
/**
* Menu do sistema
*/
$menu = array();
global $g;
$menu['Cadastros'] = array();
$menu['Cadastros']['Cliente/Fornecedor'][1] 							= array('(F2)', "abrirJanela(event, divIdRootAberto(), '', menuId('cad001'), 'Clientes e Fornecedores', '.: Clientes e Fornecedores :.', '/cad/cad001/content/cad001ContentPrincipal/', '', 'auto', false, 'center');");
$menu['Cadastros']['Transportadoras'][2]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad004'), 'Transportadoras', '.: Transportadoras :.', '/cad/cad004/content/grid1/', '', 'auto', false, 'center');";
$menu['Cadastros']['Representantes'][3]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad003'), 'Representantes', '.: Representantes :.', '/cad/cad003/content/cad003ContentRepresentantes/', '', 'auto', false, 'center');";
$menu['Cadastros']['Agenda de Contatos'][4] 							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad013'), 'Agenda de Contatos', '.: Agenda de Contatos :.', '/cad/cad013/content/cad013ContentAgendaContatos/', '', 'auto', false, 'center');";
$menu['Cadastros']['Produtos'][5]										= array('(F3)', "abrirJanela(event, divIdRootAberto(), '', menuId('cad002'), 'Produtos', '.:Produtos:.', '/cad/cad002/content/grid1/', '', 'forcarAuto', false, 'center');");
$menu['Cadastros']['Bancos/Caixas'][8]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad014'), 'Bancos/Caixa', '.:Bancos/Caixa:.', '/cad/cad014/content/cad014ContentPrincipal/', '', 'auto', false, 'center');";
$menu['Cadastros']['Centro de Custos'][9]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad015'), 'Centro de custos', '.: Centro de Custos :.', '/cad/cad015/content/cad015ContentCentroDeCustos/', '', 'auto', false, 'center');";
$menu['Cadastros']['Funcionários'][10]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad010'), 'Funcionários', '.: Cadastro de funcionários:.', '/cad/cad010/content/cad010content01/', '', 'auto', false, 'center');";
$menu['Cadastros']['Plano de Contas'][12]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad034'), 'Plano de Contas', '.: Plano de Contas :.', '/cad/cad034/content/cad034ContentPlanoDeContas/', '', 'auto', false, 'center');";
$menu['Cadastros']['Cliente - Grupos'][240] 							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad063'), 'Cliente - Grupos', '.: Cliente - Grupos :.', '/cad/cad063/content/cad063ContentCadastroClienteGrupo/', '', 'auto', false, 'center');";
$menu['Vendas']['C.R.M.'][104]                  						= "abrirJanela(event, 'crm001', '', menuId('crm001'), 'C.R.M', '.: C.R.M :.', '/crm/crm001/content/crm001ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Vendas']['Emails'][13]											= "abrirJanela(event, 'etc006', '', menuId('etc006'), 'Emails', 'Emails Recebidos/Enviados', '/etc/etc006/content/etc006ContentLeitura/', '', 'full', false, 'topleft', false);";
$menu['Vendas']['Notas Fiscais'][16]									= "abrirJanela(event, 'ven003', '', menuId('ven003'), 'Notas Fiscais', '.:Notas Fiscais:.', '/ven/ven003/content/ven003ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Vendas']['Emitir Notas Fiscais (Loja)'][17] 						= "abrirJanela(event, 'ven012', '', menuId('ven012'), 'Gerar Notas Fiscais', '.:Gerar Notas Fiscais:.', '/ven/ven012/content/ven012ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Vendas']['Análise de Orçamento'][18]								= "abrirJanela(event, divIdRootAberto(), '', menuId('ven005'), 'Análise de Orçamento', '.:Análise de Orçamento:.', '/ven/ven005/content/ven005ContentPrincipal/', '', [950,550], false, false, 'center');";
$menu['Vendas']['Análise de Pedido'][99]								= "abrirJanela(event, divIdRootAberto(), '', menuId('ven013'), 'Análise de Pedido', '.:Análise de Pedido:.', '/ven/ven013/content/ven013ContentPrincipal/', '', [950,550], false, false , 'center');";
$menu['Vendas']['Nota de Devolução'][179]								= "abrirJanela(event, 'ven003Devolucao', '', menuId('ven003Devolucao'), 'Nota de Devolução', '.: Nota de Devolução :.', '/ven/ven003/content/ven003ContentDevolucao/', '', 'full', false, 'topleft', false);";
$menu['Vendas']['PDV'][252]								                = array('(F8)', "abrirJanela(event, divIdRootAberto(), '', menuId('ven015'), 'PDV', '.: PDV :.', '/ven/ven015/content/ven015contentNotaFiscalPDV/', '', '[1000,600]', false, 'center');");
$menu['Estoque']['S.R.M.'][181]                  						= "abrirJanela(event, 'srm001', '', menuId('srm001'), 'S.R.M', '.: S.R.M :.', '/srm/srm001/content/srm001ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['P.C.P.'][203]											= "abrirJanela(event, 'est017', '', menuId('est017'), 'P.C.P', '.: P.C.P :.', '/est/est017/content/est017ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['Estoque'][110]										= "abrirJanela(event, 'est013Estoque', '', menuId('est013Estoque'), 'Estoque', '.: Estoque :.', '/est/est003/content/est003Content/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['Expedição Mercadorias'][20]							= "abrirJanela(event, 'est002', '', menuId('est002'), 'Expedição de Mercadorias 1', '.: Expedição de Mercadorias 1 :.', '/est/est002/content/est002ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['Notas Fiscais Recebidas'][21]							= "abrirJanela(event, 'est004', '', menuId('est004'), 'Notas Fiscais Recebidas', '.: Notas Fiscais Recebidas :.', '/est/est004/content/est004ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['Conhecimentos'][22] 									= "abrirJanela(event, 'est005', '', menuId('est005'), 'Conhecimentos', '.: Conhecimentos :.', '/est/est005/content/est005ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['RMA'][216]								 				= "abrirJanela(event, 'est011', '', menuId('est011'), 'Produtos em Garantia/Conserto', '.: Produtos em Garantia/Conserto :.', '/est/est011/content/est011ContentGarantiaConserto/', '', 'full', false, 'topleft', false);";
$menu['Estoque']['Análise de Ordem de compra'][173]						= "abrirJanela(event, divIdRootAberto(), '', menuId('est016'), 'Análise de Ordem de Compra', '.:Análise de Ordem de Compra:.', '/est/est016/content/est016ContentPrincipalAnalise/', '', [950,550], false, false , 'center');";
$sql = <<<EOT
  SELECT T006_Id, 
         D001_Codigo_Barras, 
         T006_Quantidade, 
         T006_Descricao_Produto, 
         T006_Flag_Conf_Cega, 
         group_concat(
           distinct D083_Codigo_Barras separator "|"
         ) AS D083_Codigo_Barras, 
         group_concat(
           distinct CONCAT(D083_Codigo_Barras, "X", D083A_Multiplicador) separator "|"
         ) AS D083A_Multiplicador
  FROM T006 
        left join D009 on D009_Id=T006_D009_Id
        left join D049 on D049_Id=D009_D049_Id
        left join D082 on D082_Id=D049_D082_Id
        left join D001 on D049_D001_Id=D001_Id
        left join T005 on T006_T005_Id=T005_Id
        left join D006 on D006_Id=T006_D006_Id
        left join D037 on D037_Id=D001_D037_Id
        left join D083 on D083_D001_Id=D001_Id
    LEFT JOIN D083A ON D083A_D083_ID = D083_ID 
  WHERE T006_T005_ID = '[[CODVAR]]'
    AND (T006_T006_ID IS NULL OR T006_T006_ID <= 0) 
    AND T006_QUANTIDADE > 0 
    AND IF(IFNULL(T006_C004_Id_Estoque,0) > 0, T006_C004_Id_Estoque, T005_C004_Id) = T005_C004_Id
  GROUP BY T006_Id
  ORDER BY T006_Flag_Tipo_Produto, T006_D009_Id, T006_Id
EOT;
$p1 = base64_encode($sql);
$p2 = base64_encode("T006_Flag_Conf_Cega");
$p3 = base64_encode(serialize(array('D001_Codigo_Barras', 'D083_Codigo_Barras')));
$p4 = base64_encode("T006_Quantidade");
$p5 = base64_encode("T006_Descricao_Produto");
$p6 = base64_encode("/est/est002/grid_func-ajax/conferenciaCegaFim/");
$menu['Estoque']['Conferência Cega'][20]								= "abrirJanela(event, divIdRootAberto(), '', menuId('confCega'), '', 'Conferência Cega', '/etc/etc016/content/etc016VerificarPorCodbarra/', '&tab=geral&sql=&sql_codvar=' + encodeURIComponent('{$p1}') + '&flag=' + encodeURIComponent('{$p2}') + '&cod=' + encodeURIComponent('{$p3}') + '&qtd=' + encodeURIComponent('{$p4}') + '&desc=' + encodeURIComponent('{$p5}') + '&acaoPosConcluirUrl=' + encodeURIComponent('{$p6}'), [900,550]);";
$menu['Financeiro']['Contas a Receber'][35]								= "abrirJanela(event, 'fin001', '', menuId('fin001'), 'Contas a Receber', '.: Contas a Receber :.', '/fin/fin001/content/fin001content01/', '', 'full', false, 'topleft', false);";
$menu['Financeiro']['Contas a Pagar'][36]								= "abrirJanela(event, 'fin002', '', menuId('fin002'), 'Contas a Pagar', '.: Contas a Pagar :.', '/fin/fin002/content/fin002content01/', '', 'full', false, 'topleft', false);";
$menu['Financeiro']['Caixa e Bancos'][37]								= "abrirJanela(event, 'fin003', '', menuId('fin003'), 'Caixa e Bancos', '.: Caixa e Bancos :.', '/fin/fin003/content/fin003ContentGeral/', '', 'full', false, 'topleft', false);";
$menu['Financeiro']['Análise de Crédito'][39]							= "abrirJanela(event, divIdRootAberto(), '', menuId('fin007'), 'Análise de Crédito', '.:Análise de Crédito:.', '/fin/fin007/content/fin007content01/', '', [1010,550], false, false, 'center');";
$menu['Financeiro']['Análise de Crédito (Nota)'][39] 					= "abrirJanela(event, divIdRootAberto(), '', menuId('fin007Nota'), 'Análise de Crédito (Nota)', '.: Análise de Crédito (Nota) :.', '/fin/fin007/content/fin007content02/', '', 'auto');";
$menu['Financeiro']['Projeção Orçamentária'][150] 						= "abrirJanela(event, 'fin009', '', menuId('fin009'), 'Projeção Orçamentária', '.:Projeção Orçamentária:.', '/fin/fin009/content/fin009content01ProjecaoOrcamentaria/', '', 'full', false, 'topleft', false);";
$menu['Financeiro']['Cobranças'][157] 									= "abrirJanela(event, 'fin001_', '', menuId('fin001_'), 'Cobranças', '.: Cobranças :.', '/fin/fin001/content/fin001ContentCobrancas/', '', 'full', false, 'topleft', false);";
$menu['Financeiro']['Serasa - Relato'][178] 							= "abrirJanela(event, 'fin001Serasa', '', menuId('fin001Serasa'), 'Serasa - Relato', '.: Serasa - Relato :.', '/fin/fin001/content/fin001ContentSerasaRelato/', '', [500, 200], false, false, 'center');";
$menu['Fiscal']['CFOP'][41]												= "abrirJanela(event, divIdRootAberto(), '', menuId('fis001'), 'CFOP', '.:CFOP:.', '/fis/fis001/content/fis001content01/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['NCM'][42]												= "abrirJanela(event, divIdRootAberto(), '', menuId('fis003'), 'NCM', '.: NCM :.', '/fis/fis003/content/fis003ContentNcm/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['ICMS'][43]												= "abrirJanela(event, divIdRootAberto(), '', menuId('fis002'), 'ICMS', '.: ICMS :.', '/fis/fis002/content/fis002ContentICMS/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['NCM por Grupo'][44]									= "abrirJanela(event, divIdRootAberto(), '', menuId('fis004'), 'NCM por Grupo', '.: NCM por Grupo :.', '/fis/fis004/content/fis004ContentNcmPorGrupo/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['Simples Nacional'][89]									= "abrirJanela(event, divIdRootAberto(), '', menuId('fis010'), 'Simples Nacional', '.: Simples Nacional :.', '/fis/fis010/content/fis010ContentPrincipal/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['Cidades'][45]											= "abrirJanela(event, divIdRootAberto(), '', menuId('fis005'), 'Cidades', '.: Cidades :.', '/fis/fis005/content/fis005ContentPrincipal/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['Países'][85]											= "abrirJanela(event, divIdRootAberto(), '', menuId('fis009'), 'Países', '.: Países :.', '/fis/fis009/content/fis009ContentPrincipal/', '', [1000,600], false, false, 'center');";
$menu['Fiscal']['Exportar Sintegra'][46]								= "abrirJanela(event, divIdRootAberto(), '', menuId('fis006'), 'Exportar Sintegra', '.: Exportar Sintegra :.', '/fis/fis006/content/fis006ContentExportarSintegra/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportar XML NF-e'][47]								= "abrirJanela(event, divIdRootAberto(), '', menuId('fis007'), 'Exportar XML NF-e', '.: Exportar XML NF-e :.', '/fis/fis007/content/fis007ContentExportarXMLNFe/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportar TXT NF-e'][48]								= "abrirJanela(event, divIdRootAberto(), '', menuId('fis008'), 'Exportar TXT NF-e', '.: Exportar TXT NF-e :.', '/fis/fis008/content/fis008ContentExportarTXTNFe/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportar XML NF-e (Entrada)'][167]						= "abrirJanela(event, divIdRootAberto(), '', menuId('fis007_'), 'Exportar XML NF-e (Entrada)', '.: Exportar XML NF-e (Entrada) :.', '/fis/fis007/content/fis007ContentExportarXMLNFeEntrada/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportar XML CT-e (Recebido)'][48]						= "abrirJanela(event, divIdRootAberto(), '', menuId('est018'), 'Exportar XML conhecimentos', '.: Exportar XML Conhecimentos :.', '/est/est018/content/est018ContentExportarXmlConhecimentos/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportar XML CT-e (Emitido)'][297]	= "abrirJanela(event, divIdRootAberto(), '', menuId('est018_'), 'Exportar XML conhecimentos Emitidos', '.: Exportar XML Conhecimentos Emitidos:.', '/est/est018/content/est018ContentExportarXmlConhecimentosEmitidos/', '', [400, 300], false, false, 'center');";
$menu['Fiscal']['Exportação EBS'][193]   								= "abrirJanela(event, divIdRootAberto(), '', menuId('ExpDadosPDV'), 'Exportação EBS', '.: Exportação EBS :.', '/outros/layoutExportacao/content/uiUsuario/', '', [400,200], false, false, 'center');";
$menu['Fiscal']['SPED Contribuições'][160]								= "abrirJanela(event, divIdRootAberto(), '', menuId('fis011'), 'SPED Contribuições', '.: SPED Contribuições :.', '/fis/fis011/content/fis011ContentPrincipal/', '', [550,300], false, false, 'center');";
$menu['Fiscal']['SPED Fiscal'][174]										= "abrirJanela(event, divIdRootAberto(), '', menuId('fis011_'), 'SPED Fiscal', '.: SPED Fiscal :.', '/fis/fis011/content/fis011ContentSpedFiscal/', '', [600,350], false, false, 'center');";
$menu['Fiscal']['Outros'][254]											= "abrirJanela(event, divIdRootAberto(), '', menuId('fis013'), 'Outros', '.: Outros :.', '/fis/fis013/content/fis013ContentPrincipal/', '', [1000,600], false, false, 'center');";
$menu['Comex']['Importação'][49]			   		                = "abrirJanela(event, 'imp003', '', menuId('imp003'), 'Declaração Importação', '.: Declaração de Importação :.', '/imp/imp003/content/imp003ContentDi/', '', 'full', false, 'topleft', false);";
$menu['Comex']['Análise PO'][255]									= "abrirJanela(event, divIdRootAberto(), '', menuId('imp003_'), 'Análise PO', '.: Análise PO :.', '/imp/imp005/content/imp005ContentPrincipalAnalise/', '', [1050,550], false, false, 'center');";
$menu['Contábil']['Contábil'][256]			   		                	= "abrirJanela(event, 'cont001', '', menuId('cont001'), 'Contábil', '.: Contábil :.', '/cont/cont001/content/cont001ContentPrincipal/', '', 'full', false, 'topleft', false);";
$menu['Gerencial']['Relatórios'][227]						 			= "abrirJanela(event, 'relatorios', '', menuId('relatorios'), 'Relatórios', '.: Relatórios :.', '/rel/menu/menu/content/', '', 'full', false, 'topleft', false);";
$menu['Gerencial']['BI Dashboards'][152]								= "abrirJanela(event, 'B.I.', '', menuId('BIDash'), 'B.I.', '.: B.I. - Business Intelligence :.', '/etc/etc011/content/etc011ContentCockpit/', '', 'full', false, 'topleft', false);";
$menu['Gerencial']['API'][228] 											= "abrirJanela(event, divIdRootAberto(), '', menuId('bi'), 'API Exportação', '.: API Exportação :.', '/api/api001/content/API001ContentAPI/', '', [1130, 700], false, false, 'center');";
$menu['Outros']['Log Erros'][273]											= "abrirJanela(event, divIdRootAberto(), '', menuId('LogErros'), 'Log Erros', '.: Log Erros :.', '/sistema/logSistema/outro/contentErros/', '', [1024,600], false, 'full');";
if ($g['nivelAlcada']==4){
	$menu['Outros']['Importação CSV']									= "abrirJanela(event, 'importacaoCSV', '', menuId('importacaoCSV'), 'Importação CSV', '.: Importação CSV :.', '/sistema/importacaoCSV/content/sistemaPImpCSVPrincipal/', '', 'full', false, 'topleft', false);"; // [142] 
}
$menu['Outros']['Auditoria'][272]  											= "abrirJanela(event, divIdRootAberto(), '', menuId('auditoriaGeral'), 'Auditoria', '.: Auditoria :.', '/etc/etc007/content/etc007ContentAuditoriaGeral/', '', [1024,600], false, 'full');";
$menu['Outros']['Exportar Dados PDV'][184] 								= "abrirJanela(event, divIdRootAberto(), '', menuId('DadosPDV'), 'Exportar Dados PDV', '.: Exportar Dados PDV :.', '/outros/integracaoPDV/integracaoSG/contentExportar/', '', [200,300], false, 'full');";
$menu['Outros']['Contratos Clientes'][196] 								= "abrirJanela(event, divIdRootAberto(), '', menuId('contCli'), 'Contratos Clientes', '.: Contratos Clientes :.', '/outros/contratosClientes/contratosClientes/content/', '', false, false, 'center');";
$menu['Outros']['Email - Whitelist'][205] 								= "abrirJanela(event, divIdRootAberto(), '', menuId('mailWhitelist'), 'Email - Whitelist', '.: Email - Whitelist :.', '/etc/etc006/content/etc006ContentWhitelist/', '', [1000,600], false, false, 'center');";
$menu['Configuração']['Usuários/Perfis'][84]							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad030'), 'Usuários', '.: Usuários :.', '/cad/cad030/content/cad030ContentPrincipal/', '', [950,550], false, false, 'center');";
$menu['Configuração']['Usuário'][86]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad030b'), 'Usuário', '.: Usuário:.', '/cad/cad030/content/cad030ContentCadastroUsuarioAtual/', '', [650,550], false, false, 'center');";
$menu['Configuração']['Empresas'][57]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad031'), 'Empresas', '.: Empresas :.', '/cad/cad031/content/cad031ContentEmpresas/', '', [950,550], false, false, 'center');";
$menu['Configuração']['Produtos'][63]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad023'), 'Produtos', '.: Produtos :.', '/cad/cad023/content/cad023ContentConfiguracaoProdutos/', '', [990,550], false, false, 'center');";
if(isset($g['c029Ids'][71]) || isset($g['c029Ids'][92]) || isset($g['c029Ids'][194]) || isset($g['c029Ids'][197]) || isset($g['c029Ids'][204])){
	$menu['Configuração']['Comercial']									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad046'), 'Comercial', '.: Comercial :.', '/crm/crm001/content/crm001ContentConfiguracoesComerciais/', '', [950,550], false, false, 'center');";
}
if(isset($g['c029Ids'][60]) || isset($g['c029Ids'][62]) || isset($g['c029Ids'][168])){
	$menu['Configuração']['Financeiro']									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad022'), 'Financeiro', '.: Financeiro :.', '/fin/fin001/content/fin001ContentConfiguracoesFinanceiro/', '', [950,550], false, false, 'center');";	
}
$menu['Configuração']['P.C.P.'][120]									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad053'), 'P.C.P.', '.: P.C.P. :.', '/cad/cad053/content/cad053ContentEngenharia/', '', [950,550], false, false, 'center');";
$menu['Configuração']['Comex'][58] 										= "abrirJanela(event, divIdRootAberto(), '', menuId('cad017'), 'Comex', '.: Comex :.', '/cad/cad017/content/cad017ContentConfiguracaoDI/', '', [950,550], false, false, 'center');";
$menu['Configuração']['Nota Fiscal'][266]							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad068'), 'Nota Fiscal', '.: Nota Fiscal :.', '/cad/cad068/content/cad068ContentNotaFiscal/', '', [1000,550], false, false, 'center');";
$menu['Configuração']['MDF-e'][242] 									= "abrirJanela(event, divIdRootAberto(), '', menuId('fis012'), 'MDF-e', '.: MDF-e :.', '/fis/fis012/content/fis012ContentConfiguracaoMDFe/', '', [100,100], false, false, 'center');";
$menu['Configuração']['Periféricos'][246] 								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad067'), 'Periféricos', '.: Periféricos :.', '/cad/cad067/content/cad067ContentPerifericos/', '', [950,550], false, false, 'center');";
$menu['Configuração']['BI'][247]										= "abrirJanela(event, divIdRootAberto(), '', menuId('bi002'), 'BI', '.: BI :.', '/bi/bi002/content/bi002ContentConfBI/', '', [950,550], false, false, 'center');";
if(isset($g['c029Ids'][223]) || isset($g['c029Ids'][224])){
	$menu['Configuração']['Suporte Técnico']							= "abrirJanela(event, divIdRootAberto(), '', menuId('RegSupConf'), 'Configurações Suporte', '.: Configurações Suporte :.', '/outros/registroSuporteCliente/registroSuporteCliente/contentConfiguracaoSuporte/', '', [950,550], false, false, 'center');";
}
if($confUsuario['dbDatabase'] == $sys['dbDatabase']){
	$menu['Configuração']['Banco Dados Clientes']						= "abrirJanela(event, divIdRootAberto(), '', menuId('etc008'), 'Banco Dados Clientes', '.: Banco Dados Clientes :.', '/etc/etc008/content/etc008ContentBancoDadosClientes/', '', [970,550], false, false, 'center');";
	$menu['Configuração']['Contadores Clientes']						= "abrirJanela(event, divIdRootAberto(), '', menuId('etc009'), 'Contadores Clientes', '.: Contadores Clientes :.', '/etc/etc009/content/etc009ContentContadoresClientes/', '', [950,550], false, false, 'center');";
	$menu['Configuração']['Usuarios Clientes']							= "abrirJanela(event, divIdRootAberto(), '', menuId('etc010'), 'Usuarios Clientes', '.: Usuários Clientes :.', '/etc/etc010/content/etc010ContentUsuariosClientes/', '', [970,550], false, false, 'center');";
}
$menu['Configuração']['Opções'] 										= "abrirJanela(event, divIdRootAberto(), '', menuId('cad058'), 'Opções', 'Opções', '/cad/cad058/content/cad058ContentConfGlobal/', '', [1000,680], false, 'center');";
//$menu['Configuração']['Sobre']											= "abrirJanela(event, divIdRootAberto(), '', menuId('sobre'), 'Sobre o Sistema', 'Sobre o Sistema', '/sistema/outros/sobre/content/', '', [530,370], false, 'center');";
//$menu['Relatórios']['Vendas Mensais'][241]		                        = "abrirJanela(event, divIdRootAberto(), '', menuId('rel001'), 'Vendas Mensais', '.: Vendas Mensais :.', '/rel/relatorios/vendas_mensais/contentVendasMensais/', '', [400, 300], false, false, 'center');";

if (isset($g['usuarioAtual']) && $g['C007']['C007_Usuario_Suporte'] == 'S') {
		$menu['Gerencial']['BI Hardness'][152]								= "abrirJanela(event, 'B.I.', '', menuId('BI'), 'B.I.', '.: B.I. - Business Intelligence :.', '/etc/etc011/content/etc011ContentPivot/', '', 'full', false, 'topleft', false);";
		$menu['Gerencial']['BI Link'][152] 								  	= "abrirJanela(event, divIdRootAberto(), '', menuId('bi002'), 'B.I.', '.: BI Link :.', '/bi/bi002/content/bi002ContentRelatorios/', '', [900,500], false, 'full');";
		$menu['Outros']['Atualização BD (Completo)']									= "abrirJanela(event, divIdRootAberto(), '', menuId('atualizarBase'), 'Atualizar', '.: Atualizar Base :.', '/sistema/scripts/atualizacaoBase/', '', false, false, 'center');"; //[53]
		$menu['Outros']['Atualização BD (Rotinas)']							= "abrirJanela(event, divIdRootAberto(), '', menuId('atualizarBaseRotinas'), 'Atualizar Rotinas', '.: Atualizar Base Rotinas :.', '/sistema/scripts/atualizacaoBase-rotinas/', '', [500,200], false, 'center');";
		$menu['Outros']['MySQL']											= "window.open('/hardness/config/cad_executa_sql.php?Oauth=true', '', 'width=750, height=550, titlebar=no,status=no,menubar=no');"; // [118]
	$menu['Outros']['Teclas de Atalho']									= "abrirJanela(event, divIdRootAberto(), '', menuId('cad055'), 'Teclas de Atalho', '.: Teclas de Atalho :.', '/cad/cad055/content/principal/', '', false, false, 'center');"; // [161]
	$menu['Outros']['Log Geral']										= "abrirJanela(event, divIdRootAberto(), '', menuId('LogGeral'), 'Log Geral', '.: Log Geral :.', '/sistema/logSistema/outro/contentGeral/', '', [1024,600], false, 'full');";
	$menu['Outros']['Sistema']											= "abrirJanela(event, divIdRootAberto(), '', menuId('Sistema'), 'Sistema', '.: Sistema :.', '/outros/dadosSistema/content/dadosSistema/', '', [1024,600], false, 'full');";
	$menu['Outros']['Copiar Tabelas'] 									= "abrirJanela(event, divIdRootAberto(), '', menuId('copiarTabelas'), 'Copiar Tabelas', '.: Copiar Tabelas :.', '/sistema/funcoes/copiarTabelas/content/', '', false, false, 'center');";
	$menu['Configuração']['Personalizações']          					= "abrirJanela(event, divIdRootAberto(), '', menuId('Personalizacao'), '', '', '/sistema/personalizacaoConf/content/sistemaPConfPrincipal/', '', 'full', false, 'topleft', false);";
    $menu['Configuração']['IDE']                  					    = "abrirJanela(event, 'IDE', '', menuId('grids'), 'IDE', '.: IDE :.', '/sistema/personalizacaoLowCode/content/ide/', '', 'full', false, 'topleft', false);";
//    $menu['Configuração']['Crontab']                  				    = "abrirJanela(event, 'Crontab', '', menuId('crontab'), 'Crontab', '.: Crontab :.', '/etc/etc018/content/etc018ContentCrontab/', '', 'full', false, 'topleft', false);";
	$menu['Configuração']['Exportar Personalizações'] 					= "abrirJanela(event, divIdRootAberto(), '', menuId('impExpPers'), 'Exportar/Importar Personalizações', '.: Exportar/Importar Personalizações :.', '/sistema/funcoes/expImpPersonalizacao/content/', '', false, false, 'center');";       
	$menu['Outros']['Log erros PHP']									= "abrirJanela(event, divIdRootAberto(), '', menuId('processosBD'), 'Log erros PHP', 'Log erros PHP', '/outros/apacheErros/apacheErros/content/', '', [1024,600], false, 'full');"; // [142] 
	$menu['Outros']['Correção NFC-e']									= "abrirJanela(event, divIdRootAberto(), '', menuId('ven003CorrecaoNFCe'), 'Correção NFC-e', 'Correção NFC-e', '/ven/ven003/content/ven003ContentCorrecaoNFCe/', '', [500,250], false, 'full');"; 
  $menu['Outros']['Processos Banco Dados']							    = "abrirJanela(event, divIdRootAberto(), '', menuId('processosBD'), 'Processos Banco Dados', 'Processos Banco Dados', '/outros/processosBancoDados/processosBancoDados/content/', '', [1024,600], false, 'full');"; // [142]
  $menu['Configuração']['Fast Crud']  		                            = "var win = window.open('/hardness3/outros/builder/index.php', '_blank'); win.focus();";
}

if (gVerificaRoot()) {
    $menu['Configuração']['Loja Apps']                                  = "abrirJanela(event, 'Loja Apps', '', menuId('lojaApps'), 'Loja Apps', '.: Loja Apps :.', '/sistema/personalizacaoLowCode/content/appsListar/', '', 'full', false, 'topleft', false);";
}

// Criados pelo Fast Crud
//$projetos = array_filter(scandir("{$g['pathDados']}/builder/"), function ($item) { return $item != '.' && $item != '..' && !is_dir($item); });
//foreach ($projetos as $projeto) {
//    $projeto = json_decode(file_get_contents("{$g['pathDados']}/builder/{$projeto}"), true);
//    if (!empty($projeto['grids'])) {
//        $menu[$projeto['nome']] = array();
//        foreach ($projeto['grids'] as $grid) {
//            $projetoId = urlencode($projeto['id']);
//            $gridId = urlencode($grid['id']);
//            $menu[$projeto['nome'].'*'][$grid['nome']] = "abrirJanela(event, divIdRootAberto(), '', menuId('builder-{$projeto['id']}-{$grid['id']}'), 'Grid: {$grid['nome']}', '{$grid['nome']}', '/sistema/builder/content/builder/?builderProjeto={$projetoId}&builderGrid={$gridId}', '', 'auto', false, 'center');";
//        }
//    }
//}
//$menu['Cadastros']['  > Tabela Fornecedor'] 				= "abrirJanela(event, divIdRootAberto(), '', menuId('cad006'), 'Tabela Fornecedor', '.:Tabela Fornecedor:.', '/cad/cad006/grid/lista/', '', 'auto', false, 'center');";
//$menu['Cadastros']['  > Catálogos'][6]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad009'), 'Catálogos de Produtos', '.:Catálogos de Produtos:.', '/cad/cad009/content/cad009content01/', '', 'auto', false, 'center');";
//$menu['Cadastros']['  > Certificados'][7]					= "abrirJanela(event, divIdRootAberto(), '', menuId('cad007'), 'Certificados', '.:Certificados:.', '/cad/cad007/content/cad007content01/', '', 'auto', false, 'center');";
//$menu['Cadastros']['Supervisores/Vendedores'][11]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad016'), 'Supervisores/Vendedores', '.: Supervisores/Vendedores :.', '/cad/cad016/content/cad016ContentVendedores/', '', 'auto', false, 'center');";
//$menu['Cadastros']['Rotas'][158]										= "abrirJanela(event, divIdRootAberto(), '', menuId('cad054'), 'Rotas', '.: Rotas :.', '/cad/cad054/content/cad054ContentRotas/', '', 'auto', false, 'center');";
//$menu['Cadastros']['Estoque'] 										= "abrirJanela(event, divIdRootAberto(), '', menuId('cad020'), 'Cadastro de Estoque', '.: Cadastro de Estoque :.', '/cad/cad020/content/cad020ContentPrincipal/', '', 'auto', false, 'center');";
//$menu['Vendas']['Orçamentos'][14]										= "abrirJanela(event, 'ven001', '', menuId('ven001'), 'Orçamentos', '.:Orçamentos:.', '/ven/ven001/content/ven001ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Pedidos'][15]										= "abrirJanela(event, 'ven002', '', menuId('ven002'), 'Pedidos', '.:Pedidos:.', '/ven/ven002/content/ven002content01/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Curva ABC Vendedores'] 								= "abrirJanela(event, 'ven006', '', menuId('ven006'), 'Curva ABC Vendedores', '.:Curva ABC Vendedores:.', '/ven/ven006/grid/lista/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Comodato'] 											= "abrirJanela(event, 'ven007', '', menuId('ven007'), 'Comodato', '.:Comodato:.', '/ven/ven007/grid/lista/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Ordem de Serviço']									= "abrirJanela(event, 'ven008', '', menuId('ven008'), 'Ordem de Serviço', '.:Ordem de Serviço:.', '/ven/ven008/grid/lista/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['CRM'] 												= "abrirJanela(event, 'ven004', '', menuId('ven004'), 'CRM', '.:CRM:.', '/ven/ven004/content/grid1/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Pré Pedido'][141]                					= "abrirJanela(event, 'crm005', '', menuId('crm005'), 'Pré Pedido', '.: Pré Pedido :.', '/crm/crm005/content/crm005PrePedido/', '', 'full', false, 'topleft', false);";
//$menu['Vendas']['Catálogo Representantes'][151] 						= "abrirJanela(event, 'ven014', '', menuId('ven014'), 'Catálogo Representantes', '.: Catálogo Representantes :.', '/ven/ven014/content/ven014ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Divergências de Estoque'][19]						= "abrirJanela(event, 'est001', '', menuId('est001'), 'Divergências de Estoque', '.:Divergências de Estoque:.', '/est/est001/content/est001ContentDivergenciasEstoque/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Expedição Mercadorias 2'][69] 						= "abrirJanela(event, 'est003', '', menuId('est003'), 'Expedição de Mercadorias 2', '.: Expedição de Mercadorias 2 :.', '/est/est003/content/est003ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Montagens/Desmontagens'][24]        				= "abrirJanela(event, 'est007', '', menuId('est007'), 'Montagens/Desmontagens', '.: Montagens/Desmontagens :.', '/est/est007/content/est007ContentMontagens/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Requisitar Compra'][145]							= "abrirJanela(event, 'est015', '', menuId('est015'), 'Requisições de Compra', '.: Requisições de Compra :.', '/est/est014/content/est014ContentRequisicoesdeCompraGeral/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Requisições de Compra'][106]						= "abrirJanela(event, 'est014', '', menuId('est014'), 'Requisições de Compra', '.: Requisições de Compra :.', '/est/est014/content/est014ContentRequisicoesdeCompra/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Compras Estoque/Emergência'][94] 					= "abrirJanela(event, 'est009', '', menuId('est009'), 'Compras de Estoque', '.: Compras de Estoque :.', '/est/est009/content/est009ContentComprasDeEstoque/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Compras de Emergência'][95] 						= "abrirJanela(event, 'est010', '', menuId('est010'), 'Compras de Emergência', '.: Compras de Emergência :.', '/est/est010/content/est010ContentComprasDeEmergencia/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Cotação de Compra'][88]  							= "abrirJanela(event, 'est008', '', menuId('est008'), 'Cotação de Compra', '.: Cotação de Compra :.', '/est/est008/content/est008ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Ordem de Compra'][23] 								= "abrirJanela(event, 'est006', '', menuId('est006'), 'Ordem de Compra', '.: Ordem de Compra :.', '/est/est006/content/est006ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Análise de Cotação'][100] 							= "abrirJanela(event, 'est012', '', menuId('est012'), 'Análise de Cotação', '.: Análise de Cotação :.', '/est/est012/content/est012ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Relatórios'][101] 									= "abrirJanela(event, 'est013', '', menuId('est013'), 'Relatórios', '.: Relatórios :.', '/est/est013/content/est013ContentRelatorios/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Cotação de compra Novo'][170] 						= "abrirJanela(event, 'est015CotNovo', '', menuId('est015CotNovo'), 'Cotação de compra', '.: Cotação de compra :.', '/est/est015/content/est015ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Ordem de compra Novo'][171] 						= "abrirJanela(event, 'est016OrdNovo', '', menuId('est016OrdNovo'), 'Ordem de compra', '.: Ordem de compra :.', '/est/est016/content/est016content01/', '', 'full', false, 'topleft', false);";
//$menu['Estoque']['Análise de Cotação de Compra'][172]					= "abrirJanela(event, divIdRootAberto(), '', menuId('est015'), 'Análise de Cotação de Compra', '.:Análise de Cotação de Compra:.', '/est/est015/content/est015ContentPrincipalAnalise/', '', [950,550], false, false, 'center');";
//$menu['Estoque']['Exportar XML produtos'][48]							= "abrirJanela(event, 'cad002', '', menuId('cad002'), 'Produtos', '.:Produtos:.', '/cad/cad002/content/cad002ContentExportarXmlPrudotos/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Cadastro'][25]										= "abrirJanela(event, 'cadastro-servicos', '', menuId('cadastro-servicos'), 'Cadastro Serviços', '.: Cadastro Serviços :.', '/srv/srv001/content/srv001content001/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Inspeção de Trocas'][96]							= "abrirJanela(event, 'inspecao-trocas', '', menuId('inspecao-trocas'), 'Inspeção de Trocas', '.: Inspeção de Trocas :.', '/srv/srv004/content/srv004contentInspecao/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Materiais'][26]									= "abrirJanela(event, 'cad012', '', menuId('cad012'), 'Materiais', '.: Cadastro de Materiais:.', '/cad/cad012/content/cad012content01/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Gerenciamento'][27]								= "abrirJanela(event, 'gerenciamento-servicos', '', menuId('gerenciamento-servicos'), 'Gerenciamento de Serviços', '.: Gerenciamento de Serviços :.', '/srv/srv002/content/srv002content01/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Operação'][28]										= "abrirJanela(event, 'srv003', '', menuId('srv003'), 'Operação', '.: Operação :.', '/srv/srv003/content/srv003ContentOperacao/', '', 'full', false, 'topleft', false);";
//$menu['Serviços']['Operação'][28]										= "window.location = '/srv/srv003/content/srv003ContentLogin/?oneWindow=true'; return false;";
//$menu['Locação']['Contratos'][29]										= "abrirJanela(event, 'ven010', '', menuId('ven010'), 'Contratos de Aluguel', '.:Contratos de Aluguel de Equipamentos:.', '/ven/ven010/content/ven010content01/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Bens'][30]											= "abrirJanela(event, 'cad008', '', menuId('cad008'), 'Bens', '.:Bens:.', '/cad/cad008/content/cad008content01/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Logística'][31]										= "abrirJanela(event, 'ven009', '', menuId('ven009'), 'Logística de Aluguel', '.: Logística de Aluguel de Equipamentos :.', '/ven/ven009/content/ven009content01/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Manutenção'][32]									= "abrirJanela(event, 'ven011', '', menuId('ven011'), 'Manutenção de bens', '.:Manuitenção de bens:.', '/ven/ven011/content/ven011content01/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Recibos de Locação'][33]							= "abrirJanela(event, 'cad035', '', menuId('cad035'), 'Recibos de Locações', '.: Recibos de Locações :.', '/cad/cad035/content/cad035ContentReciboDeLocacoes/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Certificados de Conformidade'][34]					= "abrirJanela(event, 'cad040', '', menuId('cad040'), 'Certificado de Conformidade', '.: Certificado de Conformidade :.', '/cad/cad040/content/cad040ContentCertificadoConformidade/', '', 'full', false, 'topleft', false);";
//$menu['Locação']['Certificados de Inspeção'][56]						= "abrirJanela(event, 'cad039', '', menuId('cad039'), 'Certificados de Inspeção', '.: Certificados de Inspeção :.', '/cad/cad039/content/cad039ContentCertificadosDeInspecao/', '', 'full', false, 'topleft', false);";
//$menu['Gerencial']['BI - Relatórios'][152] 							= "abrirJanela(event, divIdRootAberto(), '', menuId('BIVisao'), 'B.I.', '.: B.I. - Business Intelligence :.', '/etc/etc011/content/etc011ContentNovaVisao/', '', 'auto', false, 'auto');";
//$menu['Financeiro']['Boleto Bancário'][38]							= "abrirJanela(event, 'fin005', '', menuId('fin005'), 'Boletos Bancários', '.: Boletos Bancários :.', '/fin/fin005/content/fin005ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Financeiro']['Resumo Diário']									= "abrirJanela(event, 'etc012', '', menuId('etc012'), 'Resumo Diário', '.: Resumo Diário :.', '/etc/etc012/content/etc012ContentDashboard/', '', 'full', false, 'topleft', false);";
//$menu['Financeiro']['Referências Comerciais'][156]					= "abrirJanela(event, divIdRootAberto(), '', menuId('fin009'), 'Referências Comerciais', '.: Referências Comerciais :.', '/fin/fin009/content/fin009ContentReferenciasComerciais/', '', [350, 100], false, 'center');";
//$menu['Financeiro']['Comissões'][163]									= "abrirJanela(event, divIdRootAberto(), '', menuId(divIdRootAberto() + 'fin008'), 'Comissões', '.:Comissões:.', '/fin/fin008/content/fin008contentComissoes/', '', [350,250], false, 'center');";
//$menu['Financeiro']['Comissões'] 										= "abrirJanela(event, 'fin006', '', menuId('fin006'), 'Comissões', '.:Comissões:.', '/fin/fin006/content/fin006content01/', '', 'full', false, 'topleft', false);";
//$menu['Fiscal']['MDF-e'][242]        								    = "abrirJanela(event, 'fis012', '', menuId('fis012'), 'MDF-e', '.: MDF-e :.', '/fis/fis012/content/fis012ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Importação']['Notas Despesas'] 								= "abrirJanela(event, 'imp002', '', menuId('imp002'), 'Notas Despesas', '.: Notas Despesas :.', '/imp/imp002/grid/lista/', '', 'full', false, 'topleft', false);";
//$menu['Gerencial']['Mapa de Vendas'][164]								= "abrirJanela(event, divIdRootAberto(), '', menuId(divIdRootAberto() + 'fin008'), 'Mapa de Vendas', '.:Mapa de Vendas:.', '/fin/fin008/content/fin008contentMapaVendas/', '', [350,250], false, 'center');";
//$menu['Gerencial']['Inventário estoque'][153] 						= "abrirJanela(event, divIdRootAberto(), '', menuId('est013'), 'Inventario', '.: Inventario :.', '/est/est013/content/est013ContentInventario/', '', [480, 280], false, 'center');";
//$menu['Gerencial']['Resumo vendedor'][177]							= "abrirJanela(event, divIdRootAberto(), '', menuId('rel001'), 'Vendas Mensais', '.: Vendas Mensais :.', '/rel/relatorios/vendas_mensais/contentVendasMensais/', '', [400, 300], false, false, 'center');";
//$menu['Gerencial']['Fluxo de Caixa'][149]								= "abrirJanela(event, divIdRootAberto(), '', menuId('fin008'), 'Fluxo de Caixa', '.: Fluxo de Caixa :.', '/fin/fin008/content/fin008content01FluxoCaixa/', '', [350,250], false, 'center');";
//$menu['Gerencial']['DRE'][198]		            					= "abrirJanela(event, divIdRootAberto(), '', menuId(divIdRootAberto() + 'fin008'), 'DRE', '.: DRE :.', '/fin/fin008/content/fin008contentDRE/', '', [450,400], false, 'center');";
//$menu['Gerencial']['Balancete'][162]									= "abrirJanela(event, divIdRootAberto(), '', menuId(divIdRootAberto() + 'fin008'), 'Balancete', '.: Balancete :.', '/fin/fin008/content/fin008contentBalancete/', '', [350,250], false, 'center');";
//$menu['Gerencial']['Análitico de Vendas (Período)'][195] 				= "abrirJanela(event, divIdRootAberto(), '', menuId(divIdRootAberto() + 'fin008'), 'Análitico de Vendas', '.: Análitico de Vendas :.', '/rel/relatorios/analitico_vendas/contentVendasPeriodo/', '', [350,210], false, 'center');";
//$menu['Outros']['Emails'][50]											= "abrirJanela(event, 'etc006', '', menuId('etc006'), 'Emails', 'Emails Recebidos/Enviados', '/etc/etc006/content/etc006ContentLeitura/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Coletor'][113]										= "window.open('../hardness3/mobile/honeywell/index.php', '', 'width=400, height=550, titlebar=no,status=no,scrollbars=no,menubar=no');";
//$menu['Outros']['Cadastro de Digitais'][97]							= "abrirJanela(event, 'cad010', '', menuId('cad010'), 'Digitais', '.: Cadastro de Digitais :.', '/cad/cad010/content/cad010ContentCadastroDigital/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Pega Relacionamento'][51]							= "abrirJanela(event, 'tiago2', '', menuId('tiago2'), 'PegaRelacionamento', 'PegaRelacionamento', '/testes/tiago/pegaRelacionamento/pega/');";
//$menu['Outros']['Gerenciador de Links'][52]							= "abrirJanela(event, 'etc001', '', menuId('etc001'), 'Links', '.: Gerenciador de Links :.', '/etc/etc001/content/etc001_content_urls/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Controle Interno'][93]  								= "abrirJanela(event, 'etc013', '', menuId('etc013'), 'Controle Interno', '.: Controle Interno :.', '/etc/etc013/content/etc013ContentControleInterno/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Suporte técnico'] 									= "abrirJanela(event, divIdRootAberto(), '', menuId('RegSup'), 'Registro de Suporte', '.: Registro de Suporte :.', '/outros/registroSuporte/registroSuporte/content/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Dados XML NF-e/NFC-e'][16]								= "abrirJanela(event, divIdRootAberto(), '', menuId('processarXMLNF'), 'Dados XML NF-e/NFC-e', '.: Dados XML NF-e/NFC-e :.', '/outros/dadosXML/notaFiscal/content/', '', [300,120], false, false, 'center');";
//$menu['Outros']['Sair'] 												= "window.location = '/logout';";
//$menu['Outros']['Teste CRUD'] 										= "abrirJanela(event, 'personalizacaoConf', '', menuId('personalizacaoConf'), 'Configurações', '.: Configurações :.', '/cad/cad054/content/cad054ContentPrincipal/', '', 'full', false, 'topleft', false);";
//$menu['Outros']['Atualização BD (Antigo)']							= "window.open('../../hardness/config/alteracoes.php?Confirmado=S&Senha=LOCALHOST', '', 'width=500, height=300, titlebar=no,status=no,scrollbars=no,menubar=no');"; // [54]
//$menu['Outros']['Script B.I.']										= "abrirJanela(event, 'atualizarSSD', '', menuId('atualizarSSD'), 'Atualizar', '.: Atualizar SSD :.', '/sistema/scripts/atualizacaoSSD/', '', false, false, 'center');"; // [55]
//$menu['Outros']['Atualização R001']									= "abrirJanela(event, 'tabelasR', '', menuId('tabelasR'), 'Atualizar', '.: Atualizar R001 :.', '/sistema/scripts/tabelasR/', '', false, false, 'center');";       // [112]
//$menu['Outros']['Svn Personalizações'] 							= "abrirJanela(event, divIdRootAberto(), '', menuId('svnPers'), 'Svn Personalizações', '.: Svn Personalizações :.', '/sistema/funcoes/svnPersonalizacoes/content/', '', 'auto', false, 'center');";
//$menu['Configuração']['Agregados - Tipos'][165]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad023b'), 'Agregados - Tipos', '.: Agregados - Tipos :.', '/cad/cad023/content/cad023ContentTiposAgregadosGrid/', '', [700,500], false, false, 'center');";
//$menu['Configuração']['Serviços - Tipos'][166]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad023c'), 'Serviços - Tipos', '.: Serviços - Tipos :.', '/cad/cad023/content/cad023ContentTiposServicosGrid/', '', [700,500], false, false, 'center');";
//$menu['Configuração']['Serviços - Competências'][72] 					= "abrirJanela(event, divIdRootAberto(), '', menuId('cad011'), 'Competências', '.: Cadastro de competências:.', '/cad/cad011/content/cad011content01/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Bens - Checklist (conformidade)'][73]			= "abrirJanela(event, divIdRootAberto(), '', menuId('cad036'), 'Checklist de Bens', '.: Checklist de Bens :.', '/cad/cad036/content/cad036ContentChecklistDeBens/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Bens - Especificações (inspeção)'][74]			= "abrirJanela(event, divIdRootAberto(), '', menuId('cad037'), 'Especificações', '.: Especificações :.', '/cad/cad037/content/cad037ContentEspecificacoes/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Bens - Tipos'][75]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad042'), 'Tipos', '.: Tipos :.', '/cad/cad042/content/cad042ContentBensTipos/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Bens - Especificações'][76]					= "abrirJanela(event, divIdRootAberto(), '', menuId('cad043'), 'Especificações', '.: Especificações :.', '/cad/cad043/content/cad043ContentBensEspecificacoes/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Cotação Moeda'][77]							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad033'), 'Cotação Moeda', '.: Cotação Moeda :.', '/cad/cad033/content/cad033ContentPrincipal/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Bens - Impressoras de CF'][159]				= "abrirJanela(event, divIdRootAberto(), '', menuId('cad055'), 'Impressoras de CF', '.: Impressoras de CF :.', '/cad/cad055/content/cad055ContentImpressoras/', '', [950,550], false, false, 'center');";
//$menu['Configuração'][$g['conf']->altText('Veículos - Tipos')][78]	= "abrirJanela(event, divIdRootAberto(), '', menuId('cad041'), '{$g['conf']->altText('Veículos - Tipos')}', '.: {$g['conf']->altText('Veículos - Tipos')} :.', '/cad/cad041/content/cad041ContentTiposVeiculos/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Veículos - Especificações'][79]				= "abrirJanela(event, divIdRootAberto(), '', menuId('cad045'), 'Especificações Veículos', '.: Especificações de Veículos :.', '/cad/cad045/content/cad045ContentEspecificacoesVeiculos/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Veículos - Modelos'][90]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad048'), 'Modelos Veículos', '.: Modelos de Veículos :.', '/cad/cad048/content/cad048ContentPrincipal/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Veículos - Cores'][91]							= "abrirJanela(event, divIdRootAberto(), '', menuId('cad049'), 'Cores Veículos', '.: Cores de Veículos :.', '/cad/cad049/content/cad049ContentPrincipal/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Textos de Documento'][80]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad044'), 'Textos de Documento', '.: Textos de Documento:.', '/cad/cad044/content/cad044ContentTextosDeDocumentos/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Etiquetas - Modelos'][119]						= "abrirJanela(event, divIdRootAberto(), '', menuId('cad051'), 'Etiquetas - Modelos', '.: Etiquetas - Modelos :.', '/cad/cad051/content/cad051ContentPrincipal/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Engenharia'][120]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad053'), 'Engenharia', '.: Engenharia :.', '/cad/cad053/content/cad053ContentEngenharia/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Impressoras'][176]								= "abrirJanela(event, divIdRootAberto(), '', menuId('cad057'), 'Impressoras', '.: Impressoras :.', '/cad/cad057/content/cad057ContentImpressoras/', '', [950,550], false, false, 'center');";
//$menu['Configuração']['Atualização Contas-Títulos'][185] 				= "abrirJanela(event, divIdRootAberto(), '', menuId('atualizarTitulos'), 'Atualizar', '.: Atualizar Títulos :.', '/sistema/scripts/atualizacaoTitulos/', '', [300,150], false, 'center');";
//$menu['Outros']['Atualização BD Auditoria']							= "abrirJanela(event, divIdRootAberto(), '', menuId('atualizarBaseAuditoria'), 'Atualizar', '.: Atualizar Base Auditoria :.', '/sistema/scripts/atualizacaoBaseAuditoria/', '', false, false, 'center');"; //[53]
//$menu['Outros']['SQL'][227]											= "abrirJanela(event, 'SQL', '', menuId('SQL'), 'SQL', '.: SQL :.', '/rel/out/outros/formSql/?ajax=true', '', [450,300], false, false, 'center');";
//$menu['Outros']['Layout Exportações']	 							= "abrirJanela(event, divIdRootAberto(), '', menuId('DadosPDV'), 'Exportar Dados PDV', '.: Layouts de Exportação :.', '/outros/layoutExportacao/content/layout/', '', [1000,600], false, false, 'center');";
//$menu['Outros']['Script atualizar Contatos']						= "abrirJanela(event, divIdRootAberto(), '', menuId('LogGeral'), 'Atualizar contatos', '.: Atualizar Contatos :.', '/outros/atualizarContatosE001/scriptConexaoBases/scriptConexaoBases/', '', [1024,600], false, 'full');";
//$menu['Outros']['B2B']  											= "abrirJanela(event, divIdRootAberto(), '', menuId('b2b001'), 'B.2.B', '.: B.2.B :.', '/b2b/b2b001/content/contentB2BPrincipal/', '', [1024,600], false, 'full');";



