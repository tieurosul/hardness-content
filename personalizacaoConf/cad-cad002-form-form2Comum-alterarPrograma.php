<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /cad-cad002-form-form2Comum/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

// Adicionando pequena pausa de 100ms pois um usuario relatou que o botão Habilitar Edição não estava aparecendo
usleep(100000);
log(time());
		global $g;
		gHabilitaNavegacao($g['divId'], 'CAD002', 'form2Comum');
		/**
		* PARTE 1: Variavéis envidas para o formulário
		*/
		// Variável indica qual tab do form será exibida
		$tab = (isset($_REQUEST['tab'])) ? $_REQUEST['tab'] : false;
		// Utilizada em UPDATE, é o ID do registro que está sendo editado
		$acaoId = (isset($_REQUEST['acaoId'])) ? $_REQUEST['acaoId'] : false;
		
		/**
		* PARTE 2: Definição do formulário
		*/
		// Este mesmo form será exibido em multiplas tabs ou não?
		$formMultiTab = false;
		if ($formMultiTab) {
			$formId = (isset($_REQUEST['formId'])) ? $_REQUEST['formId'] : false;
		} else {
			$formId = uniqid();
		}
		// A qual tabela este form está relacionada, e a chave primária da tabela
		$form_tabela = 'D001';
		$form_campo_pk = 'D001_Id';
		// ID do botão que irá executar o envio desse formulário (o botão provavelmente foi criado dentro do content.php, use o mesmo ID de lá)
		$enviar_btnId = 'content_form2_btnEditarProduto_'.$g['divId'];
		// Form que será executado ao clicar no botão
		$enviar_acaoHref = '/cad/cad002/form/form2Comum/';
		// Form Editor
		$form_editor = array(
			'geral' => '{"D001_Codigo_Produto":{"top":6.984375,"left":62.234375,"width":392,"hidden":0},"D049_D082_Id":{"top":6.984375,"left":612.171875,"width":255,"hidden":0},"D001_D015_Id":{"top":6.984375,"left":1066.171875,"width":182,"hidden":1},"D001_Codigo_Barras":{"top":30,"left":117.3125,"width":136,"hidden":0},"D001_Codigo_Barras_Tributavel":{"top":30,"left":322.71875,"width":136,"hidden":0},"D049_D024_Id":{"top":30,"left":594.921875,"width":233,"hidden":0},"D009_Aliquota_ICMS_Tabela":{"top":30,"left":1050.890625,"width":75,"hidden":1},"D001_Descricao_Produto":{"top":52.984375,"left":82.21875,"width":392,"hidden":0},"D049_Flag_Tipo":{"top":52.984375,"left":621.640625,"width":256,"hidden":0},"D049_Modelo":{"top":52.984375,"left":1077.78125,"width":174,"hidden":1},"D001_Descricao_Ingles":{"top":75.984375,"left":57.53125,"width":392,"hidden":0},"D001_D037_Id":{"top":75.984375,"left":597.34375,"width":255,"hidden":0},"D009_Flag_Promocao":{"top":75.984375,"left":1047.828125,"width":13,"hidden":1},"D049_IPV":{"top":75.984375,"left":1166.953125,"width":78,"hidden":1},"D001A_Preco_Nuvemshop":{"top":99,"left":0.59375,"width":71,"hidden":1},"D001_D060_Id":{"top":98.984375,"left":105.265625,"width":399,"hidden":0},"D001_Flag_Tipo_Item":{"top":99,"left":595.703125,"width":254,"hidden":0},"D049_IPV2":{"top":98.984375,"left":1086.953125,"width":78,"hidden":1},"D049_Flag_Ativo":{"top":98.984375,"left":1281.40625,"width":80,"hidden":1},"D001A_Tipo_Zincagem":{"top":122,"left":2.671875,"width":83,"hidden":1},"D001_C008_Id":{"top":121.984375,"left":102.125,"width":399,"hidden":0},"D009_Quantidade_Estoque":{"top":122,"left":610.75,"width":72,"hidden":0},"Pedido":{"top":122,"left":786.84375,"width":72,"hidden":0},"RMA002_Soma_Estoque_Resevado_Produto":{"top":122,"left":802.9375,"width":72,"hidden":0},"D049_Flag_Substituicao_Tributaria":{"top":121.984375,"left":988.828125,"width":80,"hidden":1},"D049_Observacao_Compra":{"top":121.984375,"left":1236.375,"height":15,"width":88,"hidden":1},"D001_D003_Id":{"top":145,"left":108.859375,"width":399,"hidden":0},"D009_Quantidade_Estoque_Liquido":{"top":145,"left":614.515625,"width":72,"hidden":0},"Separado":{"top":145,"left":780.09375,"width":72,"hidden":0},"D009_Data_Alteracao_Custo":{"top":145,"left":974.453125,"width":72,"hidden":1},"D049_Codigo_Produto_Fornecedor":{"top":145,"left":1150.046875,"width":174,"hidden":1},"D001_D002_Id":{"top":167.984375,"left":87.046875,"width":399,"hidden":0},"D009_Quantidade_Estoque_Loja":{"top":168,"left":629.765625,"width":72,"hidden":0},"D009_Quantidade_OC":{"top":168,"left":811.859375,"width":72,"hidden":0},"D009_Percentual_Desconto_Tabela":{"top":167.984375,"left":1031.921875,"width":75,"hidden":1},"D001_D005_Id":{"top":190.984375,"left":106.515625,"width":399,"hidden":0},"D009_Quantidade_Estoque_Fora":{"top":191,"left":628.84375,"width":72,"hidden":0},"D009_Quantidade_Estoque_Similar":{"top":191,"left":781.265625,"width":72,"hidden":0},"D049_Percentual_Acrescimo_Tabela":{"top":190.984375,"left":1016.65625,"width":72,"hidden":1},"D049_Origem_Mercadoria":{"top":213.984375,"left":40.6875,"width":400,"hidden":0},"D009_Origem_Mercadoria":{"top":213.984375,"left":1024.21875,"width":97,"hidden":1},"D001_Especificacoes":{"top":236.984375,"left":64.828125,"height":39,"width":392,"hidden":0},"D009_Valor_Custo_Tabela":{"top":237,"left":588.9375,"width":72,"hidden":0},"D009_Data_Atualizacao_Custo_Fornecedor":{"top":237,"left":752.9375,"width":72,"hidden":0},"D049_Flag_Nacional_Importado":{"top":236.953125,"left":1018.484375,"width":120,"hidden":1},"D009_Valor_Custo_Ultima_Compra":{"top":260,"left":548.46875,"width":72,"hidden":0},"D009_Data_Ultima_Entrada":{"top":260,"left":753.390625,"width":72,"hidden":0},"D001_Preco_4":{"top":260,"left":1077.265625,"width":86,"hidden":1},"infoCustoVendaProduto":{"top":262.984375,"left":948.984375,"width":null,"hidden":1},"D001_Observacao_Compra":{"top":282.984375,"left":46.671875,"height":39,"width":392,"hidden":0},"D009_Valor_Custo_Medio_Unitario":{"top":283,"left":588.5625,"width":72,"hidden":0},"D009_Data_Atualizacao_Preco_Tabela":{"top":283,"left":755.921875,"width":72,"hidden":0},"D009_ICF_3":{"top":282.9375,"left":1086.96875,"width":75,"hidden":1},"D009_Valor_Preco_Tabela":{"top":306,"left":582.765625,"width":72,"hidden":0},"D009_Data_Ultima_Venda":{"top":306,"left":762.796875,"width":72,"hidden":0},"D001_Aplicacao":{"top":305.984375,"left":1030.546875,"width":104,"hidden":1},"D009_ICF_2":{"top":305.9375,"left":1086.96875,"width":75,"hidden":1},"D001_Peso_Total_Kg":{"top":305.9375,"left":1289.265625,"width":56,"hidden":1},"D001_Peso_Unitario_Kg":{"top":328.984375,"left":18.59375,"width":88,"hidden":0},"D049_Quantidade_Embalagem_Compra":{"top":328.984375,"left":356.453125,"width":56,"hidden":0},"D009_ICF_1":{"top":329,"left":634.359375,"width":72,"hidden":0},"D009_Valor_Custo_Unitario":{"top":329,"left":770.78125,"width":72,"hidden":0},"D009_Valor_Preco_Importado":{"top":328.9375,"left":1033.15625,"width":72,"hidden":1},"D001_Peso_Unitario_Bruto":{"top":351.984375,"left":28.265625,"width":88,"hidden":0},"D001_Qte_Embalagem":{"top":351.984375,"left":364.453125,"width":56,"hidden":0},"D009_Preco_1":{"top":352,"left":613.25,"width":72,"hidden":0},"D009_IPT_1":{"top":352,"left":736,"width":40,"hidden":0},"D009_IPV_1":{"top":352,"left":800.734375,"width":72,"hidden":0},"D009_Valor_Custo_Compra":{"top":351.90625,"left":1052.109375,"width":72,"hidden":1},"D009_Flag_Usar_Custo":{"top":351.953125,"left":1245.125,"width":256,"hidden":1},"D001_Cubagem_Unitaria":{"top":375,"left":91.546875,"width":88,"hidden":0},"D001_Flag_Etiqueta_Pedido":{"top":375,"left":398.234375,"width":64,"hidden":0},"D009_Preco_2":{"top":375,"left":613.25,"width":72,"hidden":0},"D009_IPT_2":{"top":375,"left":736,"width":40,"hidden":0},"D009_IPV_2":{"top":375,"left":800.734375,"width":72,"hidden":0},"D049_Flag_Nao_Comprar":{"top":397.984375,"left":96.109375,"width":96,"hidden":0},"D001_Flag_Pre_Cadastro":{"top":397.984375,"left":404.578125,"width":64,"hidden":0},"D009_Preco_3":{"top":398,"left":613.25,"width":72,"hidden":0},"D009_IPT_3":{"top":398,"left":736,"width":40,"hidden":0},"D009_IPV_3":{"top":398,"left":800.734375,"width":72,"hidden":0},"cad002GridHistoricoPrecos":{"top":395.984375,"left":960,"width":null,"hidden":1},"D001_Descricao_Comercial":{"top":397.984375,"left":1205.390625,"width":392,"hidden":1},"D049_Data_Cadastro":{"top":420.984375,"left":68.578125,"width":88,"hidden":0},"D001_Flag_Ativo":{"top":420.984375,"left":418.546875,"width":64,"hidden":0},"D009_Preco_4":{"top":421,"left":613.25,"width":72,"hidden":0},"D009_IPT_4":{"top":421,"left":736,"width":40,"hidden":0},"D009_IPV_4":{"top":421,"left":800.734375,"width":72,"hidden":0},"D009_Preco_Pauta_ST":{"top":420.984375,"left":1006.953125,"width":72,"hidden":1},"D001_Quantidade_Tributavel":{"top":443.984375,"left":18.6875,"width":88,"hidden":0},"D001A_Flag_Validade":{"top":443.984375,"left":409.546875,"width":63,"hidden":0},"D009A_Preco_5":{"top":444,"left":613.25,"width":71,"hidden":0},"D009A_IPT_5":{"top":444,"left":736,"width":40,"hidden":0},"D009A_IPV_5":{"top":444,"left":800.734375,"width":71,"hidden":0},"D049_IPV1":{"top":443.953125,"left":1246.953125,"width":78,"hidden":1},"D001A_Codigo_IMPA":{"top":466.984375,"left":75.125,"width":88,"hidden":0},"D001A_Flag_Produto_Emite_Certificado":{"top":466.984375,"left":391.96875,"width":63,"hidden":0},"D009_Preco_Loja":{"top":467,"left":598.609375,"width":72,"hidden":0},"D009_Preco_Loja_Real":{"top":467,"left":750.625,"width":72,"hidden":0},"D001A_D187_Id":{"top":490,"left":75.296875,"width":400,"hidden":0},"D001_Flag_Ecommerce":{"top":490,"left":1198.984375,"width":57,"hidden":1},"D001A_Flag_Kanban":{"top":512.984375,"left":437.421875,"width":64,"hidden":1},"D001_Flag_Tipo":{"top":512.984375,"left":885.90625,"width":400,"hidden":1},"D041_Valor_Preco_Revenda":{"top":535.984375,"left":21.265625,"width":78,"hidden":1},"D041_Valor_Base":{"top":535.984375,"left":229.265625,"width":78,"hidden":1},"D049_Valor_Preco_KG":{"top":536,"left":638.484375,"width":86,"hidden":1},"D001_CNPJ_Fabricante":{"top":558.984375,"left":-5,"width":117,"hidden":1},"D041_Valor_Custo":{"top":558.984375,"left":3.046875,"width":78,"hidden":1},"D041_Valor_Preco_Consumidor":{"top":558.984375,"left":197.265625,"width":78,"hidden":1},"D001_C004_Id":{"top":559,"left":639.390625,"width":141,"hidden":1},"D001_Preco_3":{"top":581.984375,"left":451.953125,"width":86,"hidden":1},"D001_Preco_2":{"top":582,"left":645.25,"width":86,"hidden":1},"D001_Flag_Multimarcas":{"top":582,"left":853.453125,"width":64,"hidden":1},"D009_Quantidade_OP":{"top":605,"left":60,"width":98,"hidden":1},"D001_Preco_1":{"top":627.984375,"left":883.953125,"width":86,"hidden":1}}'
		);
		
		$D009 = mysql_query("SELECT D009_Id, D009_Flag_Preco_Tabelado, D009_Flag_Promocao, D001_C008_Id, D001_D005_Id FROM D009 LEFT JOIN D049 ON D049_Id=D009_D049_Id LEFT JOIN D001 ON D001_Id=D049_D001_Id WHERE D049_D001_Id='{$acaoId}' AND D009_C004_Id='{$g['empresaAtual']}'");
		$mD009 = mysql_fetch_array($D009);
		if($mD009['D009_Flag_Preco_Tabelado'] == 'S'){
			$checkedTabelado="checked";
		} else {
			$checkedTabelado="";
		}

		if($mD009['D009_Flag_Promocao'] == 'S'){
			$checked="checked";
		} else {
			$checked="";
		}

		$D037 = mysql_query("SELECT D037_Unidade FROM D037 LEFT JOIN D005 ON D005_D037_Id_Tributavel=D037_Id WHERE D005_Id='{$mD009['D001_D005_Id']}'");
		$mD037 = mysql_fetch_array($D037);
		if(strlen($mD037['D037_Unidade']) > 2){
			$mD037['D037_Unidade'] = substr($mD037['D037_Unidade'], 0, 2);
		}else if(empty($mD037['D037_Unidade'])){
			$mD037['D037_Unidade'] = "??";
		}

		$g['form2Comum_D001_C008_Id'] = $mD009['D001_C008_Id'];

		// Array de definição do formulário
		// Veja documentação ou exemplos para entender todas as variavéis de definição disponivéis (automacaoFormulario.php)
		$form = new Formulario('form_cad002_form2Comum');
		$formArray = array(
			'campos' => array(
				'geral' => array(
		        	'D001_Codigo_Produto' => array('name' => 'D001_Codigo_Produto', 'label'=>'Código Produto', 'type' => 'text','length' => array(30,20), 'readonly' => (empty($r_acaoId) && !empty($g['C031']['mascaraCodigoProduto'])) ? true : false, 'restricao'=>  empty($r_acaoId) ? false : true),
					'D001_Flag_Pre_Cadastro' => array('name' => 'D001_Flag_Pre_Cadastro', 'label'=>'Pré-cadastro', 'type' => 'select', 'style'=>'float:right;', 'align'=>'right', 'select' =>
						array(
							array('title' => 'Sim', 'value'=>'S', 'selected'=>true),
							array('title' => 'Não', 'value' =>'N')
						),
						//'extra' => "onchange=\"if ($(this).val() == 'S') { $('#{$g['divId']} .itemForm-D001_Codigo_Barras').val('').attr('readonly', 'readonly'); } else { $('#{$g['divId']} .itemForm-D001_Codigo_Barras').removeAttr('readonly'); } \""
					),
					'D001_Descricao_Produto' => array('name' => 'D001_Descricao_Produto', 'label'=>'Descrição', 'type' => 'text', 'length' => array(64, 120)),
					'D001_Descricao_Ingles' => array('name' => 'D001_Descricao_Ingles', 'label'=>'Descrição Inglês', 'type' => 'text', 'length' => array(64, 60)),
					'D001_Descricao_Comercial' => array('name' => 'D001_Descricao_Comercial', 'label'=>'Descrição Comercial', 'type' => 'text', 'length' => array(64, 40)),
					'D001_Codigo_Barras' => array('name' => 'D001_Codigo_Barras', 'label'=>'EAN', 'type' => 'text', 'length' => array(13, 13), 'readonly' => (empty($r_acaoId) && !empty($g['C031']['mascaraCodigoProduto'])) ? true : false, 'restricao'=>  empty($r_acaoId) ? false : true), //, 'extra' => 'onchange="if (!isempty($(this).val())) { $(this).val(strPad($(this).val(), 13, \'0\')); }"'
		        	'D001_C008_Id' => array('name' => 'D001_C008_Id', 'label'=>'Tipo', 'type' => 'select', 'select'=>
	                	gGeraSelect('C008', 'C008_Id', 'C008_Tipo', 'order by C008_Id', true)
	                ),
	                'D001_C004_Id' => array('name' => 'D001_C004_Id', 'label'=>'Empresa', 'type' => 'select', 'select' =>
						gGeraSelect('C004', 'C004_Id', 'C004_Nome_Empresa', 'where C004_flag_Ativo="S" order by C004_Nome_Empresa', true, @$r_C004_Id)
					),
					'D001_D003_Id' => array('name' => 'D001_D003_Id', 'label'=>'Linha', 'restricao' => false, 'type' => 'select',  'select' => 
						gGeraSelect('D003', 'D003_Id', 'D003_Nome_Linha', 'order by D003_Nome_Linha', true)
					),
					'D001_D060_Id' => array('name' => 'D001_D060_Id', 'label'=>'Grupo', 'type' => 'select', 'select' => 
                		gGeraSelect('D060', 'D060_Id', 'D060_Descricao_Grupo', ' order by D060_Descricao_Grupo ', true)
                	),
					'D001_D002_Id' => array('name' => 'D001_D002_Id', 'label'=>'Sub-Grupo', 'restricao' => false, 'type' => 'select', 'select' => 
						gGeraSelect('D002', 'D002_Id', 'D002_Descricao_Produto, D002_Codigo_Padrao', ' order by D002_Descricao_Produto', true)
					),
					'D001_D015_Id' => array('name' => 'D001_D015_Id', 'label'=>'Aplicação', 'type' => 'select','select' =>
						gGeraSelect('D015', 'D015_Id', 'D015_Nome_Grupo, D015_Codigo_Padrao', 'order by D015_Nome_Grupo', true)
					),
					'D001_Peso_Unitario_Kg' => array('name' => 'D001_Peso_Unitario_Kg', 'casasDecimais' => 3,'label'=>'Peso Unitário Liquido Kg', 'type' => 'moeda', 'value' => '0,000', 'length' => array(20, 30)),
					'D001_Peso_Unitario_Bruto' => array('name' => 'D001_Peso_Unitario_Bruto', 'casasDecimais' => 3,'label'=>'Peso Unitário Bruto Kg', 'type' => 'moeda', 'value' => '0,000', 'length' => array(20, 30)),
					'D001_Qte_Embalagem' => array('name' => 'D001_Qte_Embalagem', 'casasDecimais' => 3,'label'=>'Qtd embalagem venda', 'type' => 'moeda', 'value' => '0,000', 'length' => array(15, 30), 'align' => 'right', 'style'=>'float:right;'),
					'D001_Cubagem_Unitaria' => array('name' => 'D001_Cubagem_Unitaria', 'casasDecimais' => 6, 'label'=>'Cubagem', 'type' => 'moeda', 'value' => '0,000000', 'length' => array(20, 30)),
					'D001_Peso_Total_Kg' => array('name' => 'D001_Peso_Total_Kg','casasDecimais' => 3, 'label'=>'Peso Total Emb. Kg', 'type' => 'moeda', 'value' => '0,000', 'length' => array(20, 30)),
					'D001_D037_Id' => array('name' => 'D001_D037_Id', 'label'=>'Unidades', 'type' => 'select','select' =>
						gGeraSelect('D037', 'D037_Id', 'D037_Descricao_Unidade', 'order by D037_Unidade', true)
					),
					'D001_D005_Id' => array('name' => 'D001_D005_Id', 'label'=>'NCM', 'type' => 'select', 'select' =>
						gGeraSelect('D149', 'D005_Id', 'D005_Classificacao_Fiscal, D005_Flag_Aplicacao, D005_Observacao, D149_Aliquota_IPI', "LEFT JOIN D005 ON D149_D005_Id=D005_Id AND D149_C004_Id='{$g['empresaAtual']}' WHERE D005_Flag_Ativo='S' ORDER BY D005_Classificacao_Fiscal, D005_Observacao", true)
                	),
					'D001_Especificacoes' => array('name' => 'D001_Especificacoes', 'label'=>'Especificações', 'type' => 'textarea', 'length' => array(3, 61) ),
					'D001_Flag_Tipo' => array('name' => 'D001_Flag_Tipo', 'label'=>'Tipo', 'type' => 'select', 'select' =>
						array(
							array('title' => 'ADQUIRIDO', 'value' =>'COMPRADO', 'selected' => true),
							array('title' => 'PRODUZIDO', 'value' =>'PRODUZIDO')
						)
					),
					'D001_Flag_Ativo' => array('name' => 'D001_Flag_Ativo', 'label'=>'Item ativo', 'type' => 'select', 'style'=>'float:right;', 'align'=>'right', 'select' =>
						array(
							array('title' => 'Sim', 'value'=>'S', 'selected'=>true),
							array('title' => 'Não', 'value' =>'N')
						)
					),
					'D001_Aplicacao' => array('name' => 'D001_Aplicacao', 'label'=>'Referência', 'type' => 'text', 'length' => array(64, 40)),
					'D049_Id' => array('name' => 'D049_Id', 'type' => 'hidden', 'reject' => true),
					'D049_D082_Id' => array('name' => 'D049_D082_Id', 'label'=>'Marca', 'type' => 'select', 'select'=>
						//gGeraSelect($tabela, $campoId, $campoTxt, $sqlExtra = '')
						gGeraSelect('D082', 'D082_Id', 'D082_Marca', 'order by D082_Marca', true), 'reject' => true,
					),
					'D049_Flag_Tipo' =>  array('name' => 'D049_Flag_Tipo', 'label' => 'Tipo', 'type'=>'select','align' => 'right', 'reject' => true, 'select' => 
					    array(
					       array('title' => '...', 'value' => ''),
					       array('title' => 'COMPRADO', 'value' => 'CP', 'selected' => true),
					       array('title' => 'PRODUZIDO', 'value' => 'PD'),
					       array('title' => 'MONTADO', 'value' => 'MT'),
					       array('title' => 'MODIFICADO', 'value' => 'MD'),
					    ),
					),
					'D001_Flag_Tipo_Item' => array('name' => 'D001_Flag_Tipo_Item', 'label' => 'Categoria', 'type' => 'select', 'select' =>
						array(
							array('title' => '00-Mercadoria para Revenda', 'value'=>'00'),
							array('title' => '01-Matéria-Prima', 'value' =>'01'),
							array('title' => '02-Embalagem', 'value' =>'02'),
							array('title' => '03-Produto em Processo', 'value' =>'03'),
							array('title' => '04-Produto Acabado', 'value' =>'04'),
							array('title' => '05-Subproduto', 'value' =>'05'),
							array('title' => '06-Produto Intermediário', 'value' =>'06'),
							array('title' => '07-Material de Uso e Consumo', 'value' =>'07'),
							array('title' => '08-Ativo Imobilizado', 'value' =>'08'),
							array('title' => '09-Serviços', 'value' =>'09'),
							array('title' => '10-Outros insumos', 'value' =>'10'),
							array('title' => '99-Outras', 'value' =>'99')
						)
					),
					'D049_Modelo' => array('name' => 'D049_Modelo', 'label' => 'Modelo', 'type'=>'text', 'reject' => true, 'length' => array(26,30)),
					'D049_Codigo_Produto_Fornecedor' => array('name' => 'D049_Codigo_Produto_Fornecedor', 'label' => 'Código Original','align' => 'right','style' => 'float:right;', 'reject' => true, 'type'=>'text', 'length' => array(26,30)),
					//'D049_Valor_Preco_Tabela' => array('name' => 'D049_Valor_Preco_Tabela', 'label' => 'Preço Tabela', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15)),
					//'D049_Percentual_Desconto_Tabela' => array('name' => 'D049_Percentual_Desconto_Tabela', 'label' => 'Desconto Tabela', 'type'=>'moeda', 'reject' => true, 'value' => '0,00',	'length' => array(10,15), 'align' => 'right', 'style'=>'float:right;'),
					'D049_Percentual_Acrescimo_Tabela' => array('name' => 'D049_Percentual_Acrescimo_Tabela', 'label' => 'Acrescimo Tabela', 'type'=>'moeda', 'reject' => true, 'value' => '0,00',	'length' => array(10,15), 'align' => 'right', 'style'=>'float:right;'),
					//'D049_Aliquota_ICMS_Tabela' => array('name' => 'D049_Aliquota_ICMS_Tabela', 'label' => 'ICMS Tabela', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15)),
					'D049_IPV' => array('name' => 'D049_IPV', 'label' => 'IPV 1', 'type'=>'moeda','casasDecimais'	=> 4, 'value' => '0,0000', 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D049_IPV1' => array('name' => 'D049_IPV1', 'label' => 'IPV 2', 'type'=>'moeda','casasDecimais'	=> 4, 'value' => '0,0000', 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D049_IPV2' => array('name' => 'D049_IPV2', 'label' => 'IPV 3', 'type'=>'moeda','casasDecimais'	=> 4, 'value' => '0,0000', 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					//'D049_ICF' => array('name' => 'D049_ICF', 'label' => 'ICF 1', 'type'=>'moeda','casasDecimais' => 4, 'value' => '0,0000', 'reject' => true, 'length' => array(10,15)),
					//'D049_Flag_Promocao' => array('name' => 'D049_Flag_Promocao', 'label' => 'Promoção', 'extra'=>$checked, 'type'=>'checkbox','reject' => true),
					//'D049_ICF1' => array('name' => 'D049_ICF1', 'label' => 'ICF 2', 'type'=>'moeda','casasDecimais'	=> 4, 'value' => '0,0000', 'reject' => true, 'length' => array(10,15), 'align' => 'right', 'style'=>'float:right;'),
					//'D049_ICF2' => array('name' => 'D049_ICF2', 'label' => 'ICF 3', 'type'=>'moeda','casasDecimais'	=> 4, 'value' => '0,0000', 'reject' => true, 'length' => array(10,15)),
					'D049_Valor_Preco_KG' => array('name' => 'D049_Valor_Preco_KG', 'label' => 'Preço Kg', 'type'=>'moeda', 'casasDecimais'	=> 4, 'value' => '0,0000', 'reject' => true, 'length' => array(10,15)),
					'D049_Observacao_Compra' => array('name' => 'D049_Observacao_Compra', 'label' => 'Observação Compra', 'type'=>'textarea', 'reject' => true, 'length' => array(1,30)),
					'D049_Flag_Nacional_Importado' => array('name' => 'D049_Flag_Nacional_Importado', 'label' => 'Nacional/Importado', 'reject' => true, 'type'=>'select', 'select' => 
						array(
							array('title' => '...', 'value' => ''),
							array('title' => 'NACIONAL', 'value' => 'N'),
							array('title' => 'IMPORTADO', 'value' => 'I'),
						),
					),
					'D049_D024_Id' => array('name' => 'D049_D024_Id','label'=>'Fornecedor', 'type' => 'pesquisa', 'reject'=>true, 'pesquisa' => array('D024','D024_Id','D024_Nome_Empresa','WHERE D024_Flag_Fornecedor = "S"'), 'length' => array(80,30)),
					'D049_Data_Cadastro' => array('name' => 'D049_Data_Cadastro', 'label' => 'Data Cadastro', 'type'=>'data', 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_Data_Ultima_Entrada' => array('name' => 'D009_Data_Ultima_Entrada', 'label' => 'Data entrada', 'type'=>'data', 'length' => array(10,15), 'reject' => true, 'disabled' => true),
					'D009_Data_Alteracao_Custo' => array('name' => 'D009_Data_Alteracao_Custo', 'label' => 'Data alteração', 'type'=>'data', 'length' => array(10,15), 'reject' => true, 'disabled' => true),
					'D009_Data_Atualizacao_Custo_Fornecedor' => array('name' => 'D009_Data_Atualizacao_Custo_Fornecedor', 'label' => 'Data alt. tabela', 'type'=>'data', 'length' => array(10,15), 'reject' => true, 'disabled' => true),
					'D049_Flag_Ativo' => array('name' => 'D049_Flag_Ativo', 'label' => 'Ativo', 'type'=>'select', 'reject' => true, 'select' => 
						array(
							array('title' => '...'),
							array('title' => 'SIM', 'value' => 'S','selected'=>true),
							array('title' => 'NÃO', 'value' => 'N'),
						),
					),
					'D049_Flag_Substituicao_Tributaria' => array('name' => 'D049_Flag_Substituicao_Tributaria', 'reject' => true, 'label' => 'Substituição Tributária', 'type'=>'select', 'align' => 'right','style' => 'float:right;', 'select' => 
						array(
							array('title' => '...'),
							array('title' => 'SIM', 'value' => 'S'),
							array('title' => 'NÃO', 'value' => 'N','selected'=>true),
						),
					),
					'D009_Flag_Usar_Custo' => array('name' => 'D009_Flag_Usar_Custo', 'label' => 'Custo', 'type' => 'select', 'reject' => true, 'select' =>
						array(
							array('title' => '1 - Usar custo médio', 'value'=>'1', 'selected'=>true),
							array('title' => '2 - Usar custo tabela', 'value'=>'2'),
						),
					),
					'D009_Valor_Custo_Unitario' => array('name' => 'D009_Valor_Custo_Unitario', 'label' => 'Custo x ICF', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),					
					'D009_Valor_Custo_Medio_Unitario' => array('name' => 'D009_Valor_Custo_Medio_Unitario', 'label' => '<u>Custo médio</u>', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Valor_Custo_Compra' => array('name' => 'D009_Valor_Custo_Compra', 'label' => 'Custo', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Valor_Custo_Ultima_Compra' => array('name' => 'D009_Valor_Custo_Ultima_Compra', 'label' => 'Custo compra', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),					
					'D009_Valor_Custo_Tabela' => array('name' => 'D009_Valor_Custo_Tabela', 'label' => '<u>Custo tabela</u>', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),	
					'D009_Valor_Custo_Importado' => array('name' => 'D009_Valor_Custo_Importado', 'label' => 'Custo ME', 'type'=>'hidden', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),	

					'D009_Preco_Loja' => array('name' => 'D009_Preco_Loja', 'label' => '<u>Preço Loja</u>', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15)),
					'D009_Preco_Loja_Real' => array('name' => 'D009_Preco_Loja_Real', 'label' => '<u>Preço Loja Real</u>', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),

					// novos campos de preços da D009
					'D009_Valor_Preco_Importado' => array('name' => 'D009_Valor_Preco_Importado', 'label' => 'Preço importado',  'type' => 'moeda', 'reject' => true, 'disabled' => true, 'length' => array(10,15)),

					'D009_Preco_1' => array('name' => 'D009_Preco_1', 'label' => 'Preço 1', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D009_Preco_2' => array('name' => 'D009_Preco_2', 'label' => 'Preço 2', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D009_Preco_3' => array('name' => 'D009_Preco_3', 'label' => 'Preço 3', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D009_Preco_4' => array('name' => 'D009_Preco_4', 'label' => 'Preço 4', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D009A_Preco_5' => array('name' => 'D009A_Preco_5', 'label' => 'Preço 5', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),

					'D001_Preco_1' => array('name' => 'D001_Preco_1', 'label' => 'Preço 1', 'type'=>'moeda', 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D001_Preco_2' => array('name' => 'D001_Preco_2', 'label' => 'Preço 2', 'type'=>'moeda', 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D001_Preco_3' => array('name' => 'D001_Preco_3', 'label' => 'Preço 3', 'type'=>'moeda', 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D001_Preco_4' => array('name' => 'D001_Preco_4', 'label' => 'Preço 4', 'type'=>'moeda', 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),

					

					// Campos de preços da D041
					'D041_Valor_Base' => array('name' => 'D041_Valor_Base', 'label' => 'Preço 1', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
					'D041_Valor_Preco_Consumidor' => array('name' => 'D041_Valor_Preco_Consumidor', 'label' => 'Preço 2', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
					'D041_Valor_Preco_Revenda' => array('name' => 'D041_Valor_Preco_Revenda', 'label' => 'Preço 3', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
					'D041_Valor_Custo' => array('name' => 'D041_Valor_Custo', 'label' => 'Novo custo', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),

					'D009_ICF_1' => array('name' => 'D009_ICF_1', 'label' => 'ICF 1', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
					'D009_ICF_2' => array('name' => 'D009_ICF_2', 'label' => 'ICF 2', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
					'D009_ICF_3' => array('name' => 'D009_ICF_3', 'label' => 'ICF 3', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),

					'D009_IPV_1' => array('name' => 'D009_IPV_1', 'label' => 'IPV 1', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPV_2' => array('name' => 'D009_IPV_2', 'label' => 'IPV 2', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPV_3' => array('name' => 'D009_IPV_3', 'label' => 'IPV 3', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPV_4' => array('name' => 'D009_IPV_4', 'label' => 'IPV 4', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009A_IPV_5' => array('name' => 'D009A_IPV_5', 'label' => 'IPV 5', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),

					'D009_Preco_Pauta_ST' => array('name' => 'D009_Preco_Pauta_ST', 'label' => 'Preço Pauta', 'type' => 'moeda','casasDecimais' => 4, 'length' => array(10,15),'reject' => true, 'align' => 'right', 'style'=>'float:right;'),

					'D009_IPT_1' => array('name' => 'D009_IPT_1', 'label' => '', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPT_2' => array('name' => 'D009_IPT_2', 'label' => '', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPT_3' => array('name' => 'D009_IPT_3', 'label' => '', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009_IPT_4' => array('name' => 'D009_IPT_4', 'label' => '', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),
					'D009A_IPT_5' => array('name' => 'D009A_IPT_5', 'label' => '', 'type'=>'moeda','casasDecimais' => 4, 'length' => array(10,15), 'reject' => true, 'align' => 'right', 'style'=>'float:right;'),

					'D009_Data_Ultima_Venda' => array('name' => 'D009_Data_Ultima_Venda', 'label' => 'Última venda', 'type' => 'data', 'length' => array(10,15), 'reject' => true, 'align' => 'right'),
					'D009_Valor_Preco_Tabela' => array('name' => 'D009_Valor_Preco_Tabela', 'label' => 'Custo manual', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15)),
					'D009_Data_Atualizacao_Preco_Tabela' => array('name' => 'D009_Data_Atualizacao_Preco_Tabela', 'label' => 'Data alt. custo', 'type'=>'data', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Aliquota_ICMS_Tabela' => array('name' => 'D009_Aliquota_ICMS_Tabela', 'label' => 'ICMS Tabela', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15)),
					'D009_Percentual_Desconto_Tabela' => array('name' => 'D009_Percentual_Desconto_Tabela', 'label' => 'Desconto Tabela', 'type'=>'moeda', 'reject' => true, 'value' => '0,00',	'length' => array(10,15), 'align' => 'right', 'style'=>'float:right;'),
					'D009_Flag_Promocao' => array('name' => 'D009_Flag_Promocao', 'label' => 'Promoção', 'extra'=>$checked, 'type'=>'checkbox','reject' => true),

					'cad002GridHistoricoPrecos' => array('name' => 'cad002GridHistoricoPrecos', 'type' => 'button','value' =>'Histórico Preços','reload' => false,'reject'=>true),
					//'D009_Flag_Preco_Tabelado' => array('name' => 'D009_Flag_Preco_Tabelado', 'label' => 'Preço tabelado', 'extra'=>@$checkedTabelado, 'type'=>'checkbox','reject' => true),
					'D009_Quantidade_Estoque_Liquido' => array('name' => 'D009_Quantidade_Estoque_Liquido', 'label' => 'Estoque Liquido', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Quantidade_Estoque_Loja' => array('name' => 'D009_Quantidade_Estoque_Loja', 'label' => 'Loja', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Quantidade_Estoque_Fora' => array('name' => 'D009_Quantidade_Estoque_Fora', 'label' => "<a href=\"#\" onclick=\"abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Estoque Fora', '/cad/cad002/content/cad002ContentEstoqueExterno/', '&acaoId=' + encodeURIComponent('{$acaoId}'), [700,400]); return false;\">Fora</a>", 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Quantidade_Estoque_Similar' => array('name' => 'D009_Quantidade_Estoque_Similar', 'label' => "<a href=\"#\" onclick=\"abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Estoque Similares', '/cad/cad002/content/cad002ContentEstoqueSimilares/', '&acaoId=' + encodeURIComponent('{$acaoId}'), [850,400]); return false;\">Similares</a>", 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Quantidade_Estoque' => array('name' => 'D009_Quantidade_Estoque', 'label' => 'Estoque', 'type'=>'moeda', 'value' => '0,00', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D009_Quantidade_OC' => array('name' => 'D009_Quantidade_OC', 'label' => "<a href=\"#\" onclick=\"abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Ordens de Compra', '/ven/ven001/content/ven001contentProdutoOC/', '&acaoId=' + encodeURIComponent('{$mD009['D009_Id']}'), [700,400]); return false;\">OC</a>", 'type' => 'moeda', 'reject' => true, 'disabled' => true),
					'D009_Quantidade_OP' => array('name' => 'D009_Quantidade_OP', 'label' => "<a href=\"#\" onclick=\"abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Ordens de Produção', '/ven/ven001/content/ven001contentProdutoOP/', '&acaoId=' + encodeURIComponent('{$mD009['D009_Id']}'), [700,400]); return false;\">OP</a>", 'type' => 'moeda', 'reject' => true, 'disabled' => true),
					'Pedido' => array('name' => 'Pedido', 'label' => 'Pedidos', 'type'=>'moeda', 'value' => '0', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'Separado' => array('name' => 'Separado', 'label' => 'Separado', 'type'=>'texto', 'value' => '0', 'reject' => true, 'length' => array(10,15), 'disabled' => true),
					'D049_Origem_Mercadoria' => array('name' => 'D049_Origem_Mercadoria', 'label' => 'Origem mecadoria', 'type'=>'select', 'reject' => true, 'select' => 
						array(
							array('title' => '...', 'value'=>''),
							array('title' => '0 - Nacional - exceto as indicadas nos códigos 3 a 5', 'value'=>'0'),
							array('title' => '1 - Estrangeira - Importação direta, exceto a indicada no código 6', 'value' =>'1'),
							array('title' => '2 - Estrangeira - Adquirida no mercado interno, exceto a indicada no código 7', 'value'=>'2'),
							array('title' => '3 - Nacional - mercadoria ou bem com Conteúdo de Importação superior a 40%', 'value' =>'3'),
							array('title' => '4 - Nacional - cuja produção tenha sido feita em conformidade com os processos produtivos básicos de que tratam as legislações citadas nos Ajustes', 'value'=>'4'),
							array('title' => '5 - Nacional - mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%', 'value' =>'5'),
							array('title' => '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX', 'value'=>'6'),
							array('title' => '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante em lista da CAMEX', 'value' =>'7'),
							array('title' => '8 - Nacional - Mercadoria ou bem com Conteúdo de Importação superior a 70% (setenta por cento)', 'value' =>'8')
						)
					),
					'D009_Origem_Mercadoria' => array('name' => 'D009_Origem_Mercadoria', 'label' => 'Origem mecadoria', 'type'=>'select', 'reject' => true, 'select' => 
						array(
							array('title' => '...', 'value'=>''),
							array('title' => '0 - Nacional - exceto as indicadas nos códigos 3 a 5', 'value'=>'0'),
							array('title' => '1 - Estrangeira - Importação direta, exceto a indicada no código 6', 'value' =>'1'),
							array('title' => '2 - Estrangeira - Adquirida no mercado interno, exceto a indicada no código 7', 'value'=>'2'),
							array('title' => '3 - Nacional - mercadoria ou bem com Conteúdo de Importação superior a 40%', 'value' =>'3'),
							array('title' => '4 - Nacional - cuja produção tenha sido feita em conformidade com os processos produtivos básicos de que tratam as legislações citadas nos Ajustes', 'value'=>'4'),
							array('title' => '5 - Nacional - mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%', 'value' =>'5'),
							array('title' => '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX', 'value'=>'6'),
							array('title' => '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante em lista da CAMEX', 'value' =>'7'),
							array('title' => '8 - Nacional - Mercadoria ou bem com Conteúdo de Importação superior a 70% (setenta por cento)', 'value' =>'8')
						)
					),
					'D001_Flag_Multimarcas' => array('name' => 'D001_Flag_Multimarcas', 'label'=>'Multimarcas', 'type' => 'select', 'select' =>
						array(
							array('title' => 'Sim', 'value'=>'S'),
							array('title' => 'Não', 'value' =>'N')
						)
					),
					'infoCustoVendaProduto' => array('name' => 'infoCustoVendaProduto', 'label' => '', 'type' => 'titulo', 'length' => array(5,20), 'reject' => true, 'value' => ''),
					//Campos utilizado quando o produto é cadastrado a partir do e-mail
					'T139_Id' => array('name' => 'T139_Id', 'type' => 'hidden', 'reject' => true, 'value' => @$r_T139_Id),
					'C007_Id' => array('name' => 'C007_Id', 'type' => 'hidden', 'reject' => true, 'value' => @$r_C007_Id),
					'file' => array('name' => 'file', 'type' => 'hidden', 'reject' => true, 'value' => @$r_file),
					'divId' => array('name' => 'divId', 'type' => 'hidden', 'reject' => true, 'value' => $g['divId']),
					'D001_Flag_Ecommerce' => array('name' => 'D001_Flag_Ecommerce', 'label'=>'Disponível E-commerce?','type' => 'select', 'style'=>'float:right;', 'align'=>'right', 'select' =>
						array(
							array('title' => 'Sim', 'value'=>'S'),
							array('title' => 'Não', 'value' =>'N')
						)
					),
					'D001_Flag_Etiqueta_Pedido' => array('name' => 'D001_Flag_Etiqueta_Pedido', 'label'=>'Etiqueta pedido','type' => 'select', 'style'=>'float:right;', 'align'=>'right', 'select' =>
						array(
							array('title' => 'Sim', 'value'=>'S'),
							array('title' => 'Não', 'value' =>'N')
						)
					),
					'D049_Quantidade_Embalagem_Compra' => array('name' => 'D049_Quantidade_Embalagem_Compra', 'label' => 'Qtd embalagem compra', 'type'=>'moeda', 'reject' => true, 'length' => array(10,15)),
                	'D001_Codigo_Barras_Tributavel' => array('name' => 'D001_Codigo_Barras_Tributavel', 'label'=>'EAN Tributável', 'type' => 'text', 'length' => array(13, 13)), 
                	'D001_Quantidade_Tributavel' => array('name' => 'D001_Quantidade_Tributavel', 'casasDecimais' => 3,'label'=>"Quantidade Tributável {$mD037['D037_Unidade']}", 'type' => 'moeda', 'value' => '0,000', 'length' => array(20, 30)),
					'D001_CNPJ_Fabricante' => 	array('name' => 'D001_CNPJ_Fabricante', 'label' => 'CNPJ Fabricante', 'type' => 'cnpj', 'length' => array(18,18)),
					//flag nao comprar esta definido como default (nao), alterado de nao->sim e sim->nao para ficar mais facil compreensao do cliente
					'D049_Flag_Nao_Comprar' => 	array('name' => 'D049_Flag_Nao_Comprar', 'label' => 'Comprar', 'reject' => true, 'type' => 'select', 'select' =>
						array(
							array('title' => 'Sim', 'value' =>'N', 'selected' => true),
							array('title' => 'Não', 'value'=>'S')
						)
					),
					'D001A_Flag_Kanban' => array(
						'name' => 'D001A_Flag_Kanban', 'label' => 'Kanban', 'type' => 'select', 'style' => 'float:right;', 'align' => 'right', 'select' =>
						array(
							array('title' => 'Não', 'value' => 'N', 'selected' => true),
							array('title' => 'Sim', 'value' => 'S')
						)
					),
					'D001A_Flag_Produto_Emite_Certificado' => array(
						'name' => 'D001A_Flag_Produto_Emite_Certificado', 'label' => 'Envia Certificado', 'type' => 'select', 'style' => 'float:right;', 'align' => 'right', 'select' =>
						array(
							array('title' => 'Não', 'value' => 'N', 'selected' => true),
							array('title' => 'Sim', 'value' => 'S')
						)
					),
					'D001A_Flag_Validade' => array('name' => 'D001A_Flag_Validade', 'label' => 'Tem validade','type' => 'select', 'select' =>
						array(
							array('title' => 'Não', 'value' =>'N','selected' => true),
							array('title' => 'Sim', 'value' =>'S')
						)
					),
					'D001A_Codigo_IMPA' => array('name' => 'D001A_Codigo_IMPA', 'label'=>'Código IMPA', 'type' => 'text', 'length' => array(20,20)),
					'D001A_Preco_Nuvemshop' => array('name' => 'D001A_Preco_Nuvemshop', 'label' => 'Preço Nuvemshop', 'type'=>'moeda', 'length' => array(10,15), 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco']),
					'D001A_D187_Id' => array('name' => 'D001A_D187_Id', 'label'=>'Tipo Produto', 'type' => 'select','select' =>
						gGeraSelect('D187', 'D187_Id', 'D187_Codigo,D187_Descricao', 'order by D187_Codigo', true)
					),
					'D001_Observacao_Compra' => array('name' => 'D001_Observacao_Compra', 'label'=>'Observação Venda', 'type' => 'textarea', 'length' => array(3, 61)),
					'D001A_Tipo_Zincagem' => array('name' => 'D001A_Tipo_Zincagem', 'label' => 'Tipo de Zincagem','type' => 'select', 'select' =>
						array(
							array('title' => '...', 'value' =>'','selected' => true),
							array('title' => 'Branco', 'value' =>'BR'),
							array('title' => 'Amarelo', 'value' =>'AM'),
							array('title' => 'Azul', 'value' =>'AZ'),
							array('title' => 'Fogo', 'value' =>'FG'),
							array('title' => 'Ácido', 'value' =>'AC'),
							array('title' => 'Preto', 'value' =>'PT')
						)
					),
					'D009_Quantidade_Compra_Emergencia' => array('name' => 'D009_Quantidade_Compra_Emergencia', 'label'=>'Emergência', 'type' => 'moeda', 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco'], 'disabled' => true, 'reject' => true),
					'D009A_Quantidade_Meses_Estoque' => array('name' => 'D009A_Quantidade_Meses_Estoque', 'label'=>'Meses Estq', 'type' => 'moeda', 'casasDecimais' => $g['c004']['C004_Casas_Decimais_Preco'], 'disabled' => true, 'reject' => true),
					'D009A_Pedido_Minimo' => array('name' => 'D009A_Pedido_Minimo', 'label'=>'Ped Minimo/MOQ', 'type' => 'text', 'reject' => true),
                ),
            ) // 'campos'
		);

		/* tab [geral]
		field [D009_Quantidade_Compra_Emergencia]
		name [D009_Quantidade_Compra_Emergencia]
		label [Emergência]
		type [moeda]
		disabled [S]
		---
		tab [geral]
		field [D009A_Quantidade_Meses_Estoque]
		name [D009A_Quantidade_Meses_Estoque]
		label [Meses Estq]
		type [moeda]
		disabled [S]
		--- */

		if($g['C031']['multimarcas'] == 'ambas'){
			$sql = mysql_query("SELECT * FROM D049 WHERE D049_D001_Id = '{$r_acaoId}'");
			if(mysql_num_rows($sql) == 1){
				$formArray['campos']['geral']['D001_Flag_Multimarcas'] = array('name' => 'D001_Flag_Multimarcas', 'label'=>'Multimarcas', 'type' => 'select', 'select' =>
					array(
						array('title' => 'Sim', 'value'=>'S'),
						array('title' => 'Não', 'value' =>'N')
					)
				);
			}
		}

		$form->definirFormularioArray($formArray);

		echo <<<EOT
		    <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
		    	if(parseFloat($('#{$g['divId']}').css('height')) < 250){
		    		$('#{$g['divId']}').css('overflow', 'auto');
		    	}
				$('#{$g['divId']} #D001_Flag_Multimarcas').change(function() {
					if (($(this).val() == 1)) {
						$.getJSON('/cad/cad002/form_func-ajax/flagMultimarcas/?ajax=true&D001_Id=' + encodeURIComponent({$acaoId}) + '&Flag=S&callback=?', function(request) {
							if (request.code) {
								divRefresh('{$r_divIdMain}', true);
							}
						});
					}
				});
				$('#{$g['divId']} #D001_Flag_Multimarcas').trigger('change');
				$('#cad002GridHistoricoPrecos').unbind('click');
				$('#cad002GridHistoricoPrecos').bind('click', function(data) {
					var id = '{$mD009['D009_Id']}';
					abrirJanela(data, '{$g['divIdRoot']}', '{$g['divId']}' , unique(), '', 'Histórico Preços', '/cad/cad002/content/cad002contentHistóricoPrecos/', '&D009_Id=' + encodeURIComponent(id), [1000,553]);
					return false;
				});

				$('#{$g['divId']} .itemForm-D001_Flag_Pre_Cadastro').trigger('onchange');
			} </script></div>
EOT;
		if(empty($r_acaoId)){
			echo <<<EOT
			    <div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
					setTimeout(function(){
						$('#{$g['divId']} #D001_Descricao_Produto').focus();
					}, 100);
				} </script></div>
EOT;
		}
		/**
		* Na maioria dos casos não será necessário alterar nada daqui para baixo
		* Somente em casos especiais, para alterar pequenos detalhes ou modificações do visual
		*/

		/**
		* PARTE 3: Processamento do formulário (se foi enviado)
		* 		   E carregamento dos valores (se não foi enviado e possui acaoId)
		*/
		if ($formCodigo = $form->processarFormulario($form_tabela)) {
			// Form com erros
			if ($formCodigo == 1) {
				$g['conteudo']['pagina'] = gGeraAlertMsgErroForm($form);
			// Form sem erros
			} else if ($formCodigo == 2) {
				// Insert ou Update?
				if (!empty($acaoId)) {
					// Update
					$retorno = $CAD002->verificarCodigoProduto($form,$acaoId);
					if($retorno !== true){
						echo gGeraAlertMsg('Erro!', $retorno);
						echo  <<<EOT
							<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
								$('#D001_Codigo_Produto').focus();
							} </script></div>
EOT;
					}else{
						if ($sql = $g['sqlAuto']->gerarSQLUpdate($form_tabela, $form_campo_pk, $acaoId, $form)) {
							$CAD002->atualizarMarca($form, $acaoId);
							//$retorno = $CAD002->D001_reprocessa_historico($r_acaoId, 0, 10);
							$CAD002->cad002AtualizarAtributosIntegracaoTray($acaoId);
							if ($retorno == TRUE)
							{
								echo gGeraAlertMsg('Operação OK', 'Registro Alterado com Sucesso!');
								echo  <<<EOT
									<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
										//divRefresh('{$g['divId']}', true);
										//console.log('{$r_divIdContentTabProduto}');
										//divSetarAcaoId('{$r_divIdContentTabProduto}', 'true', 'modoFormLeve');
										divRefresh('{$r_divIdMain}', true);
									}</script></div>
EOT;
							}
							else
							{
								echo gGeraAlertMsg('Operação OK', 'Registro Alterado com Sucesso! Problemas na atualizacao do estoque!');
							}
						}
					}
				} else {
					// Insert
					require_once("bibliotecas/classes/CAD002.php");
					$CAD002 = new CAD002();

					// Personalizado a pedido do Miguel para que o EAN seja o mesmo numero do Codigo de Barras
					/* if($g['C031']['gravarCodProdAoCadastrar'] == 'S'){
						$proxEAN = $CAD002->obterNovoCodigoEAN();
						$form->campoSetarValor('D001_Codigo_Barras', $proxEAN, true);
					} */
					if ($sql = $g['sqlAuto']->gerarSQLInsert($form_tabela, $form)) {
						// Ajuste para pegar D001_Id devido a campos da tabela D001A no formulário
						$D001A_Id = $g['mysqlLastId'];
						$D001A = mysql_query("select D001A_D001_Id from D001A where D001A_Id='{$D001A_Id}'");
						$mD001A = mysql_fetch_array($D001A);
						$D001_Id = $mD001A['D001A_D001_Id'];
						$g['mysqlLastId'] = $D001_Id;
						//--------------------------------------------------------------------------------
						$CAD002->incluirMarca($form);
						if($g['C031']['gravarCodProdAoCadastrar'] == 'S'){
							require_once("bibliotecas/classes/CAD002.php");
							$CAD002 = new CAD002();
							$proxCodigo = $CAD002->obterNovoCodigoProduto($D001_Id);
							mysql_query("UPDATE D001 SET D001_Codigo_Produto = '{$proxCodigo}', D001_Codigo_Barras = '{$proxCodigo}' WHERE D001_Id = '{$D001_Id}'");
						}
						// Imprime uma mensagem e fecha a janela
						$alert .= gGeraAlertMsg('Operação OK', 'Registro Incluido com Sucesso!', false);
						if($form->campoValorEnviado('T139_Id') > 0){
							echo  <<<EOT
								<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
									if('{$form->campoValorEnviado('file')}' != ''){
										var buttons = {
											"Sim": function() {
												$('#{$form->campoValorEnviado('divId')}').showLoading();
												$.ajax({
													url: '/etc/etc006/form_func-ajax/arquivarAnexo/',
													dataType: 'jsonp',
													type: 'post',
													data: 'ajax=true&C007_Id={$form->campoValorEnviado('C007_Id')}&file={$form->campoValorEnviado('file')}&Id={$D001_Id}&nomeArquivo={$form->campoValorEnviado('nomeArquivo')}&T139_Id={$form->campoValorEnviado('T139_Id')}&tipo=produto',
													success: function(request) {
														if (request.code) {
															$('#{$form->campoValorEnviado('divId')}').hideLoading();
															setTimeout(function(){
																abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Leitura de Anexo', '/etc/etc006/content/etc006ContentLeituraAnexo/', $('#{$g['divId']}').closest('.ui-dialog-content').data('janelaVars') + '&D001_Id=' + encodeURIComponent('{$D001_Id}'), [1000,600]);
															}, 100);
															dialogConfirm("Ok!", 'Anexo arquivado com Sucesso!');
															fecharJanela("{$g['divId']}");
														} else {
															dialogConfirm("Erro!", request.data);
														}
													}
												});
												$(this).dialog("destroy").remove();
											},
											"Não": function() {
												setTimeout(function(){
													abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Leitura de Anexo', '/etc/etc006/content/etc006ContentLeituraAnexo/', $('#{$g['divId']}').closest('.ui-dialog-content').data('janelaVars') + '&D001_Id=' + encodeURIComponent('{$D001_Id}'), [1000,600]);
												}, 100);
												fecharJanela("{$g['divId']}");
												$(this).dialog("destroy").remove();
											}
										};
										dialogConfirm("Atenção", "Deseja vincular este anexo ao produto?", buttons);
									} else {
										setTimeout(function(){
											abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Leitura de Anexo', '/etc/etc006/content/etc006ContentLeituraAnexo/', $('#{$g['divId']}').closest('.ui-dialog-content').data('janelaVars') + '&D001_Id=' + encodeURIComponent('{$D001_Id}'), [1000,600]);
										}, 100);
										fecharJanela("{$g['divId']}");
									}
								} </script></div>
EOT;
						} else {
							echo  <<<EOT
								<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
									fecharJanela("{$g['divId']}");
									divRefresh("{$g['divIdParent']}", true);
									abrirJanela(false, '{$g['divIdRoot']}', '{$g['divId']}', unique(), '', 'Cadastro Produto', '/cad/cad002/content/form2/', '&acaoId=' + encodeURIComponent('{$D001_Id}') + '&tabela=D001', [1000,635]);
								} </script></div>
EOT;
						}
				 	}
				}
			}
		} else {
			// Carrega os valores caso seja um update
			if (!empty($acaoId)) {
				/**
				* Modifica campos em caso de update 
				*/
				$cClausula = '';
				if ($form->campoValor('D001_C008_Id') > 9999)
				{
                    $cClausula .= " and D003_C008_Id='" . $form->campoValor('D001_C008_Id') . "' ";
				}
                if ($form->campoValor('C008_Inclusao_Produto_Mostrar_Codigo_Grupos') == 'S')
                {
		   		    $cClausula .= " order by D003_Codigo_Padrao ";
                }
                else
                {
		   		    $cClausula .= " order by D003_Nome_Linha ";
                }
				$form->campoSetarSelect('D001_D003_Id', gGeraSelect('D003', 'D003_Id', 'D003_Nome_Linha', $cClausula, true));
				
				$processado = $form->carregarValoresCampos($form_tabela, $form_campo_pk, $acaoId);
				$sql = mysql_query("SELECT *, D009_Quantidade_Pedido(D009_Id,1) as Pedido,
											  D009_Quantidade_Separado_3(D009_Id) as Separado
				                      FROM D049 
				                 LEFT JOIN D009 ON D009_D049_Id=D049_Id
								 LEFT JOIN D009A ON D009_Id=D009A_D009_Id
				                 LEFT JOIN D041 ON D041_D049_Id=D049_Id     
				                 LEFT JOIN D024 ON D024_Id=D049_D024_Id
								 LEFT JOIN D082 ON D082_Id=D049_D082_Id
				                     WHERE D049_D001_Id = '{$acaoId}' AND D009_C004_Id = '{$g['empresaAtual']}'");
				$res = mysql_fetch_array($sql);

				$form->campoSetarValor('D049_IPV', gCorrigeNumero($res['D049_IPV'], 4));
				$form->campoSetarValor('D049_IPV1', gCorrigeNumero($res['D049_IPV1'], 4));
				$form->campoSetarValor('D049_IPV2', gCorrigeNumero($res['D049_IPV2'], 4));
				$form->campoSetarValor('D049_Id', $res['D049_Id']);
				$form->campoSetarValor('D049_Flag_Nao_Comprar', $res['D049_Flag_Nao_Comprar']);
				$form->campoSetarValor('D049_D082_Id', $res['D049_D082_Id']);
				$form->formArray['campos']['geral']['D049_D082_Id']['value_Pesquisa'] = $res['D082_Marca'];				
				//$form->campoSetarValor('D049_Flag_Tipo', $g['conf']->camposValorPadrao('D049_Flag_Tipo'));
				$form->campoSetarValor('D049_Flag_Tipo', $res['D049_Flag_Tipo']);
				$form->campoSetarValor('D049_Modelo', $res['D049_Modelo']);
				$form->campoSetarValor('D049_Codigo_Produto_Fornecedor', $res['D049_Codigo_Produto_Fornecedor']);
				$form->campoSetarValor('D049_Origem_Mercadoria', $res['D049_Origem_Mercadoria']);
				$form->campoSetarValor('D009_Origem_Mercadoria', $res['D009_Origem_Mercadoria']);
				$form->campoSetarValor('D049_Valor_Preco_KG', gCorrigeNumero($res['D049_Valor_Preco_KG']));
				$form->campoSetarValor('D009_Valor_Preco_Tabela', gCorrigeNumero($res['D009_Valor_Preco_Tabela'],4));
				$form->campoSetarValor('D009_Percentual_Desconto_Tabela', gCorrigeNumero($res['D009_Percentual_Desconto_Tabela']));
				$form->campoSetarValor('D009_Percentual_Acrescimo_Tabela', gCorrigeNumero($res['D009_Percentual_Acrescimo_Tabela']));
				$form->campoSetarValor('D009_Aliquota_ICMS_Tabela', gCorrigeNumero($res['D009_Aliquota_ICMS_Tabela']));
				$form->campoSetarValor('D009_IPV_1', gCorrigeNumero($res['D009_IPV_1'], 4));
				$form->campoSetarValor('D009_IPV_2', gCorrigeNumero($res['D009_IPV_2'], 4));
				$form->campoSetarValor('D009_IPV_3', gCorrigeNumero($res['D009_IPV_3'], 4));
				$form->campoSetarValor('D009_IPV_4', gCorrigeNumero($res['D009_IPV_4'], 4));
				$form->campoSetarValor('D009A_IPV_5', gCorrigeNumero($res['D009A_IPV_5'], 4));
				$form->campoSetarValor('D009_Preco_Pauta_ST', gCorrigeNumero($res['D009_Preco_Pauta_ST'],4));
				$form->campoSetarValor('D009_IPT_1', gCorrigeNumero($res['D009_IPT_1'], 4));
				$form->campoSetarValor('D009_IPT_2', gCorrigeNumero($res['D009_IPT_2'], 4));
				$form->campoSetarValor('D009_IPT_3', gCorrigeNumero($res['D009_IPT_3'], 4));
				$form->campoSetarValor('D009_IPT_4', gCorrigeNumero($res['D009_IPT_4'], 4));
				$form->campoSetarValor('D009A_IPT_5', gCorrigeNumero($res['D009A_IPT_5'], 4));
				$form->campoSetarValor('D009_ICF_1', gCorrigeNumero($res['D009_ICF_1'], 4));
				$form->campoSetarValor('D009_ICF_2', gCorrigeNumero($res['D009_ICF_2'], 4));
				$form->campoSetarValor('D009_ICF_3', gCorrigeNumero($res['D009_ICF_3'], 4));
				$form->campoSetarValor('D049_Quantidade_Embalagem_Compra', gCorrigeNumero($res['D049_Quantidade_Embalagem_Compra'], 2));
				$form->campoSetarValor('D049_Observacao_Compra', $res['D049_Observacao_Compra']);
				$form->campoSetarValor('D049_Flag_Nacional_Importado', $res['D049_Flag_Nacional_Importado']);
				$form->campoSetarValor('D049_Data_Cadastro', gCorrigeData($res['D049_Data_Cadastro']));
				$form->campoSetarValor('D049_Flag_Ativo', $res['D049_Flag_Ativo']);
				$form->campoSetarValor('D049_Flag_Substituicao_Tributaria', $res['D049_Flag_Substituicao_Tributaria'],4);
				$form->campoSetarValor('D009_Valor_Custo_Unitario', gCorrigeNumero($res['D009_Valor_Custo_Unitario'],4));
				$form->campoSetarValor('D009_Valor_Custo_Medio_Unitario', gCorrigeNumero($res['D009_Valor_Custo_Medio_Unitario'],4));
				$form->campoSetarValor('D009_Valor_Custo_Compra', gCorrigeNumero($res['D009_Valor_Custo_Compra'],4));
				$form->campoSetarValor('D009_Valor_Custo_Ultima_Compra', gCorrigeNumero($res['D009_Valor_Custo_Ultima_Compra'],4));
				$form->campoSetarValor('D009_Valor_Custo_Tabela', gCorrigeNumero($res['D009_Valor_Custo_Tabela'],4));
				$form->campoSetarValor('D009_Valor_Custo_Importado', gCorrigeNumero($res['D009_Valor_Custo_Importado'],2));
				$form->campoSetarValor('D009_Preco_Loja', gCorrigeNumero($res['D009_Preco_Loja'],2));
				$form->campoSetarValor('D009_Preco_Loja_Real', gCorrigeNumero($res['D009_Preco_Loja_Real'],2));
				$form->campoSetarValor('D009_Quantidade_Estoque', gCorrigeNumero($res['D009_Quantidade_Estoque']));
				$form->campoSetarValor('D009_Quantidade_OC', gCorrigeNumero($res['D009_Quantidade_OC']));
				$form->campoSetarValor('D009_Quantidade_OP', gCorrigeNumero($res['D009_Quantidade_OP']));
				$form->campoSetarValor('D009_Quantidade_Estoque_Liquido', gCorrigeNumero($res['D009_Quantidade_Estoque_Liquido']));
				$form->campoSetarValor('D009_Quantidade_Estoque_Loja', gCorrigeNumero($res['D009_Quantidade_Estoque_Loja']));
				$form->campoSetarValor('D009_Quantidade_Estoque_Fora', gCorrigeNumero($res['D009_Quantidade_Estoque_Fora']));
				$form->campoSetarValor('D009_Quantidade_Estoque_Similar', gCorrigeNumero($res['D009_Quantidade_Estoque_Similar']));
				$form->campoSetarValor('D009_Preco_1', gCorrigeNumero($res['D009_Preco_1'],$g['c004']['C004_Casas_Decimais_Preco']));
				$form->campoSetarValor('D009_Preco_2', gCorrigeNumero($res['D009_Preco_2'],$g['c004']['C004_Casas_Decimais_Preco']));
				$form->campoSetarValor('D009_Preco_3', gCorrigeNumero($res['D009_Preco_3'],$g['c004']['C004_Casas_Decimais_Preco']));
				$form->campoSetarValor('D009_Preco_4', gCorrigeNumero($res['D009_Preco_4'],$g['c004']['C004_Casas_Decimais_Preco']));
				$form->campoSetarValor('D009A_Preco_5', gCorrigeNumero($res['D009A_Preco_5'],$g['c004']['C004_Casas_Decimais_Preco']));
				$form->campoSetarValor('D009_Valor_Preco_Importado', gCorrigeNumero($res['D009_Valor_Preco_Importado'],4));
				$form->campoSetarValor('D009_Data_Ultima_Entrada', gCorrigeData($res['D009_Data_Ultima_Entrada']));
				$form->campoSetarValor('D009_Data_Atualizacao_Custo_Fornecedor', gCorrigeData($res['D009_Data_Atualizacao_Custo_Fornecedor']));
				$form->campoSetarValor('D009_Data_Atualizacao_Preco_Tabela', gCorrigeData($res['D009_Data_Atualizacao_Preco_Tabela']));
				$form->campoSetarValor('D009_Data_Ultima_Venda', gCorrigeData($res['D009_Data_Ultima_Venda']));
				$form->campoSetarValor('D009_Data_Alteracao_Custo', gCorrigeData($res['D009_Data_Alteracao_Custo'], true));
				$form->campoSetarValor('D049_D024_Id', $res['D049_D024_Id']);
				$form->campoSetarValor('D009_Flag_Usar_Custo', $res['D009_Flag_Usar_Custo']);
    			$form->formArray['campos']['geral']['D049_D024_Id']['value_Pesquisa'] = $res['D024_Nome_Empresa'];

				$form->campoSetarValor('D041_Valor_Base', gCorrigeNumero($res['D041_Valor_Base']));
				$form->campoSetarValor('D041_Valor_Preco_Consumidor', gCorrigeNumero($res['D041_Valor_Preco_Consumidor']));
				$form->campoSetarValor('D041_Valor_Preco_Revenda', gCorrigeNumero($res['D041_Valor_Preco_Revenda']));
				$form->campoSetarValor('D041_Valor_Custo', gCorrigeNumero($res['D041_Valor_Custo']));

				$form->campoSetarValor('Separado', gCorrigeNumero($res['Separado']));
				$form->campoSetarValor('Pedido', gCorrigeNumero($res['Pedido']));
				$form->campoSetarValor('D009_Quantidade_Compra_Emergencia', gCorrigeNumero($res['D009_Quantidade_Compra_Emergencia']));
				$form->campoSetarValor('D009A_Quantidade_Meses_Estoque', gCorrigeNumero($res['D009A_Quantidade_Meses_Estoque']));
				$form->campoSetarValor('D009A_Pedido_Minimo', gCorrigeNumero($res['D009A_Pedido_Minimo']));
				// mostra erros de formulário uteis ao desenvolvedor
				if ($processado !== true) {
					reset($form->formArray['campos']);
					if ($tab == key($form->formArray['campos'])) {
						echo  $processado;
					}
				}
			}
		}
		
		/**
		* PARTE 4: Gerar o formulário
		*/
		$gerado = gGeraCamposForm($form, $tab, $acaoId, $enviar_btnId, $enviar_acaoHref, $formId, $form_campo_pk);
		
		/**
		* PARTE 5: Exibir o formulário
		*/
		$g['smarty']->assign('formularioID', $form->formularioID());
		$g['smarty']->assign('table', $gerado['table']); $g['smarty']->assign('form_tabela', isset($form_tabela) ? $form_tabela : '');
		echo  $gerado['pagina'];
		if (!$formMultiTab) { $g["smarty"]->assign("formId", $formId); }
		echo $g['smarty']->fetch('formEditor.tpl');

		$botaoModoLeve = "";
        
		if (isset($r_modoFormLeve) && $r_modoFormLeve && $acaoId) {
			//Modo form leve (r_modoFormLeve) e ao montar formulario deu problema para montar o botao habilitar edição na função do framework
			$botaoModoLeve = <<<EOT
				if($("#{$enviar_btnId}").find('span').text()=="Enviar"){
					$("#{$enviar_btnId}").unbind('click.enviaForm').bind('click.enviaForm', function() {
						console.log('Muda botão Enviar para Habilitar Edição');
						console.log('{$g['divId']}');
						divSetarAcaoId('{$g['divId']}', '', 'modoFormLeve');
						divRefresh('{$g['divId']}');
						$(this).find('span').css('font-weight', 'normal').html('Enviar');
					}).find('span').css('font-weight', 'bold').html('Habilitar Edição');
				}					
EOT;
		}
		
		$id = 'popover-' . uniqid();
		$id2 = 'popover-' . uniqid();
		$id3 = 'popover-' . uniqid();
		$id4 = 'popover-' . uniqid();
		$id5 = 'popover-' . uniqid();
		echo <<<EOT
			<div class="divHidden"><script type="text/javascript"> if (typeof jQuery != 'undefined') {
                console.log("r_modoFormLeve:");
                console.log({$r_modoFormLeve});
                console.log("acaoId:");
                console.log({$acaoId});
				{$botaoModoLeve}
				$('.formEditor-D009_Valor_Custo_Ultima_Compra').css('cursor', 'pointer');
				$('.formEditor-D009_Valor_Custo_Ultima_Compra').unbind('click').bind('click', function(data) {
					console.log($(this));
					$('.formEditor-D009_Valor_Custo_Ultima_Compra').attr('id', '{$id}');
					if ($('#{$id}').data('show')) {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
					} else {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
						$.ajax({
							url: '/cad/cad002/outro/demonstrativoCusto/',
							dataType: 'html',
							data: 'ajax=true&divIdRoot={$g['divIdRoot']}&divIdParent={$g['divIdParent']}&acaoId=' + encodeURIComponent('{$res['D009_T001_Id']}'),
							type: 'post',
							success: function(response) {
								$('#{$id}').popover('destroy');
								$('#{$id}').popover({
									html: true,
									placement: 'left',
									title: 'Demonstrativo de Custo',
									content: response
								});
								$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
								$('#{$id}').popover('show');
								$('#{$id}').data('show', true);
								$('<span class=\'ui-icon ui-icon-closethick\' style=\'float: right; margin: -3px -8px;\'>close</span>').bind('click', function() {
									$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
									$('body').unbind('click');
								}).appendTo($('.popover-title'));
								$('body').unbind('mousedown').bind('mousedown', function(event) { 
									if (!$(event.target).closest('.popover').size()) {
										$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('#{$id}').next().find('input:visible:last').focus();
								$('.popover').css('width', 320);
								var left = $('.popover').position().left - 85;
								$('.popover').css('left', left);
								
								var table = $('.popover').find('table');
								$(table).css('font-size', 14);
							}
						});
					}
				});
				$('.formEditor-D009_Valor_Custo_Medio_Unitario').css('cursor', 'pointer');
				$('.formEditor-D009_Valor_Custo_Medio_Unitario').unbind('click').bind('click', function(data) {
					$('.formEditor-D009_Valor_Custo_Medio_Unitario').attr('id', '{$id2}');
					if ($('#{$id2}').data('show')) {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
					} else {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
						$.ajax({
							url: '/cad/cad002/outro/demonstrativoCusto/',
							dataType: 'html',
							data: 'ajax=true&divIdRoot={$g['divIdRoot']}&divIdParent={$g['divIdParent']}&acaoId=' + encodeURIComponent('{$res['D009_T001_Id']}'),
							type: 'post',
							success: function(response) {
								$('#{$id2}').popover('destroy');
								$('#{$id2}').popover({
									html: true,
									placement: 'left',
									title: 'Demonstrativo de Custo',
									content: response
								});
								$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
								$('#{$id2}').popover('show');
								$('#{$id2}').data('show', true);
								$('<span class=\'ui-icon ui-icon-closethick\' style=\'float: right; margin: -3px -8px;\'>close</span>').bind('click', function() {
									$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
									$('body').unbind('click');
								}).appendTo($('.popover-title'));
								$('body').unbind('mousedown').bind('mousedown', function(event) { 
									if (!$(event.target).closest('.popover').size()) {
										$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('#{$id2}').next().find('input:visible:last').focus();
								$('.popover').css('width', 320);
								var left = $('.popover').position().left - 85;
								$('.popover').css('left', left);
								
								var table = $('.popover').find('table');
								$(table).css('font-size', 14);
							}
						});
					}
				});
				
				$('.formEditor-D009_Valor_Custo_Tabela').css('cursor', 'pointer');
				$('.formEditor-D009_Valor_Custo_Tabela').unbind('click').bind('click', function(data) {
					$('.formEditor-D009_Valor_Custo_Tabela').attr('id', '{$id3}');
					if ($('#{$id3}').data('show')) {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
					} else {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
						$.ajax({
							url: '/cad/cad002/outro/demonstrativoCustoTabela/',
							dataType: 'html',
							data: 'ajax=true&divIdRoot={$g['divIdRoot']}&divIdParent={$g['divIdParent']}&D009_Id=' + encodeURIComponent('{$res['D009_Id']}'),
							type: 'post',
							success: function(response) {
								$('#{$id3}').popover('destroy');
								$('#{$id3}').popover({
									html: true,
									placement: 'left',
									title: 'Demonstrativo de Custo',
									content: response
								});
								$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
								$('#{$id3}').popover('show');
								$('#{$id3}').data('show', true);
								$('<span class=\'ui-icon ui-icon-closethick\' style=\'float: right; margin: -3px -8px;\'>close</span>').bind('click', function() {
									$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
									$('body').unbind('click');
								}).appendTo($('.popover-title'));
								$('body').unbind('mousedown').bind('mousedown', function(event) { 
									if (!$(event.target).closest('.popover').size()) {
										$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('#{$id3}').next().find('input:visible:last').focus();
								$('.popover').css('width', 320);
								var left = $('.popover').position().left - 85;
								$('.popover').css('left', left);
								
								var table = $('.popover').find('table');
								$(table).css('font-size', 14);
							}
						});
					}
				});

/*				$('.formEditor-D009_Preco_Loja').css('cursor', 'pointer');
				$('.formEditor-D009_Preco_Loja').unbind('click').bind('click', function(data) {
					$('.formEditor-D009_Preco_Loja').attr('id', '{$id4}');
					if ($('#{$id4}').data('show')) {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
					} else {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
						$.ajax({
							url: '/ven/ven001/outros/demonstrativoPrecoVenda/',
							dataType: 'html',
							data: 'ajax=true&divIdRoot={$g['divIdRoot']}&divIdParent={$g['divIdParent']}&extra=D009&acaoId=' + encodeURIComponent('{$res['D009_Id']}'),
							type: 'post',
							success: function(response) {
								$('#{$id4}').popover('destroy');
								$('#{$id4}').popover({
									html: true,
									placement: 'top',
									title: 'Demonstrativo Preço',
									content: response
								});
								$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
								$('#{$id4}').popover('show');
								$('#{$id4}').data('show', true);
								$('<span class=\'ui-icon ui-icon-closethick\' style=\'float: right; margin: -3px -8px;\'>close</span>').bind('click', function() {
									$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
									$('body').unbind('click');
								}).appendTo($('.popover-title'));
								$('body').unbind('mousedown').bind('mousedown', function(event) { 
									if (!$(event.target).closest('.popover').size()) {
										$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('#{$id4}').next().find('input:visible:last').focus();
								$('.popover').css('width', 560);
								var left = $('.popover').position().left - 34;
								$('.popover').css('left', left);
								
								var table = $('.popover').find('table');
								$(table).css('font-size', 12);
							}
						});
					}
				});  

*/

				$('.formEditor-D009_Preco_Loja_Real').css('cursor', 'pointer');
				$('.formEditor-D009_Preco_Loja_Real').unbind('click').bind('click', function(data) {
					$('.formEditor-D009_Preco_Loja_Real').attr('id', '{$id5}');
					if ($('#{$id5}').data('show')) {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
					} else {
						$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
						$.ajax({
							url: '/ven/ven001/outro/demonstrativoPrecoVenda/',
							dataType: 'html',
							data: 'ajax=true&divIdRoot={$g['divIdRoot']}&divIdParent={$g['divIdParent']}&extra=D009_Real&acaoId=' + encodeURIComponent('{$res['D009_Id']}'),
							type: 'post',
							success: function(response) {
								$('#{$id5}').popover('destroy');
								$('#{$id5}').popover({
									html: true,
									placement: 'left',
									title: 'Demonstrativo Preço',
									content: response
								});
								$('.popover').prev().popover('destroy').data('clicked', false).data('show', false);
								$('#{$id5}').popover('show');
								$('#{$id5}').data('show', true);
								$('<span class=\'ui-icon ui-icon-closethick\' style=\'float: right; margin: -3px -8px;\'>close</span>').bind('click', function() {
									$('#{$id5}').popover('destroy').data('clicked', false).data('show', false);
									$('body').unbind('click');
								}).appendTo($('.popover-title'));
								$('body').unbind('mousedown').bind('mousedown', function(event) {
									if (!$(event.target).closest('.popover').size()) {
										$('#{$id5}').popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('body').unbind('mousedown').bind('mousedown', function(event) {
									if (!$(event.target).closest('.popover').size()) {
										$('#{$id5}').popover('destroy').data('clicked', false).data('show', false);
										$('body').unbind('mousedown');
									}
								});
								$('#{$id5}').next().find('input:visible:last').focus();

								var table = $('.popover').find('table');
								$(table).css('font-size', 12);

								var x = $('.popover').offset().top-23; var y = $('.popover').offset().left+8;
								$('.popover').appendTo('body').css({'z-index': '10000', 'top': x+'px', 'left': y+'px'});
								$('.popover').find('.arrow').css('top', $('.popover').find('.arrow').position().top+23);
								setTimeout(function(){
									if(($('body').height() - $('.popover').position().top) < $('.popover').height()){
										var ajusteX = $('.popover').height() - ($('body').height() - $('.popover').position().top) + 10;
										$('.popover').css('top', $('.popover').position().top - ajusteX + 'px');
										$('.popover').find('.arrow').css('top', $('.popover').find('.arrow').position().top + ajusteX);
									}
								}, 50);
							}
						});
					}
				});
				$.getJSON('/cad/cad002/form_func-ajax/infoCustoVendaProduto/?ajax=true&D009_Data_Alteracao_Custo=' + encodeURIComponent('{$res['D009_Data_Alteracao_Custo']}') + '&D009_Data_Ultima_Venda=' + encodeURIComponent('{$res['D009_Data_Ultima_Venda']}') + '&D009_Flag_Custo_Utilizado=' + encodeURIComponent('{$res['D009_Flag_Custo_Utilizado']}') + '&callback=?', function(request) {
					if (request.code) {
						$('.formEditor-infoCustoVendaProduto').html(request.data);
					}
				});
			} </script></div>
EOT;









