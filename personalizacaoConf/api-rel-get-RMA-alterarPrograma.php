<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-RMA/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

    $retorno = $API003->auth($_GET['API_AUTH'], false);

    if(is_array($retorno)){
        echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
        die();
    }

    $dataInicio = $_GET['DATAINICIAL']; 
    $dataFinal = $_GET['DATAFINAL'];
    $extraFiltro = extraFiltro();		

    $grid = array(
        array('colunaValor' => 'T182_Id', 'colunaTitulo' => 'id_rma'),
        array('colunaValor' => 'if(T182_Data="0000-00-00","",T182_Data)', 'colunaTitulo' => 'data_abertura_rma'),
        array('colunaValor' => 'if(T182_Data_Conclusao="0000-00-00","",T182_Data_Conclusao)', 'colunaTitulo' => 'data_conclusao_rma'),
        array('colunaValor' => 'D001_Id', 'colunaTitulo' => 'id_produto'),
        array('colunaValor' => 'T182_Quantidade', 'colunaTitulo' => 'quantidade_rma'),
        array('colunaValor' => 'D164_Tipo', 'colunaTitulo' => 'tipo_rma'),
        array('colunaValor' => 'T182_D024_Id_Cliente', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T182_D024_Id_Fornecedor', 'colunaTitulo' => 'codigo_fornecedor'),
        array('colunaValor' => 'format(T182_Preco,2,"pt_BR")', 'colunaTitulo' => 'preco_rma'),
        array('colunaValor' => 'D162_Status', 'colunaTitulo' => 'status_rma'),
        array('colunaValor' => 'T182_NF_Venda', 'colunaTitulo' => 'numero_nf_saida'),
        array('colunaValor' => 'T182_Pedido', 'colunaTitulo' => 'pedido'),
        array('colunaValor' => 'TransCli.D022_Nome_Empresa', 'colunaTitulo' => 'transportadora_nf_saida'),
        array('colunaValor' => 'T182_Data_Conclusao_Cliente', 'colunaTitulo' => 'data_conclusao_cliente'),
        array('colunaValor' => 'T005_Data_Emissao', 'colunaTitulo' => 'data_emissao_nota'),
        array('colunaValor' => 'T007_Data_Emissao', 'colunaTitulo' => 'data_emissao_pedido'),
        array('colunaValor' => 'D199_Descricao', 'colunaTitulo' => 'origem_atendimento'),
        array('colunaValor' => 'D197_Motivo', 'colunaTitulo' => 'motivo'),
        array('colunaValor' => 'T005_Canal_Vendas_Ecommerce', 'colunaTitulo' => 'canal_vendas'),
        array('colunaValor' => 'C004_Id', 'colunaTitulo' => 'id_empresa'),			
        array('colunaValor' => 'T182_C007_Id', 'colunaTitulo' => 'id_usuario'),			
        array('colunaValor' => 'T182_Data_Previsao_Fechamento', 'colunaTitulo' => 'data_previsao_fechamento'),			
        array('colunaValor' => 'format((select sum(T312_Valor) from T312 where T312_T182_Id=T182_Id),2,"pt_BR")', 'colunaTitulo' => 'total_custo'),
        // array('colunaValor' => 'T182_Campos->Protocolo_Marketplace', 'colunaTitulo' => 'protocolo_marketplace'),
        // array('colunaValor' => 'T182_Campos->Protocolo_MAXBOT', 'colunaTitulo' => 'protocolo_maxbot'),						 
        // array('colunaValor' => 'T182_Campos->Protocolo_Reclame_Aqui', 'colunaTitulo' => 'protocolo_reclame_aqui'),						 			
        array('colunaValor' => 'T182_Data_Retorno_Cliente', 'colunaTitulo' => 'data_retorno_cliente'),	
        array('colunaValor' => 'format((select sum(T312_Valor) from T312 where T312_T182_Id=T182_Id and T312_Flag_Reversao = "S"),2,"pt_BR")', 'colunaTitulo' => 'total_revertido'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T182_Data",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T182_Data","<=",$dataFinal);
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T182';

    $extra = <<<EOT
        LEFT JOIN D009 ON D009_Id=T182_D009_Id
        LEFT JOIN D049 ON D049_Id=D009_D049_Id
        LEFT JOIN D001 ON D001_Id=D049_D001_Id
        LEFT JOIN D162 ON D162_Id=T182_D162_Id
        LEFT JOIN D164 ON D164_Id=T182_D164_Id
        LEFT JOIN T005 on T005_Id=T182_Pedido
        LEFT JOIN T007 on T007_Id = IF(T182_T007_Id = 0, T005_T007_Id, T182_T007_Id)
        LEFT JOIN D022 as TransCli on TransCli.D022_Id=T007_D022_Id
        LEFT JOIN D199 on D199_Id = T182_D199_Id
        LEFT JOIN D197 on D197_Id = T182_D197_Id
        LEFT JOIN C004 on C004_Id = T182_C004_Id			
        {$where}
EOT;
    list($geradoSql, $geradoDados) = $g['sqlAuto']->gerarSQLGrid($grid, $from, $extra,0.00,false,false,false,45000,true);

    //validando se retornaram dados da query
    $semDados = $API001->tratarRetornoVazio($geradoDados);
    if(is_array($semDados)){
        echo json_encode($semDados, JSON_UNESCAPED_UNICODE);
        die();
    }

    echo json_encode($geradoDados, JSON_UNESCAPED_UNICODE);

    die();
