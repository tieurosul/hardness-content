<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-orcamentos/
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
        array('colunaValor' => 'T003_Id', 'colunaTitulo' => 'numero_orcamento'),
        array('colunaValor' => 'T003_D024_Id', 'colunaTitulo' => 'codigo_cliente'),
        array('colunaValor' => 'T003_C004_Id', 'colunaTitulo' => 'id_empresa'),
        array('colunaValor' => 'codigo_usuario.C007_Id', 'colunaTitulo' => 'codigo_usuario'),
        array('colunaValor' => 'codigo_vendedor_interno.C007_Id', 'colunaTitulo' => 'codigo_vendedor_interno'),
        array('colunaValor' => 'codigo_vendedor_externo.C007_Id', 'colunaTitulo' => 'codigo_vendedor_externo'),
        array('colunaValor' => 'codigo_supervisor.C007_Id', 'colunaTitulo' => 'codigo_supervisor'),
        array('colunaValor' => 'codigo_supervisor2.C007_Id', 'colunaTitulo' => 'codigo_supervisor2'),
        array('colunaValor' => 'codigo_vendedor_substituto.C007_Id', 'colunaTitulo' => 'codigo_vendedor_substituto'),
        array('colunaValor' => 'if(T003_Data_Emissao="0000-00-00","",T003_Data_Emissao)', 'colunaTitulo' => 'data_emissao_orcamento'),
        array('colunaValor' => 'format(T003_Valor_Total,2,"pt_BR")', 'colunaTitulo' => 'valor_total_orcamento'),
        array('colunaValor' => 'T003_Flag_Status_Orcamento', 'colunaTitulo' => 'status_orcamento','callback' => 'campoStatusOrcamento'),
        array('colunaValor' => 'D047_Descricao_Motivo', 'colunaTitulo' => 'motivo_cancelamento'),
        // array('colunaValor' => 'T003_Status_Orcamento(T003_Flag_Status_Orcamento)', 'colunaTitulo' => 'nome_status_orcamento'),
    );

    //montando clausula where
    $where = gInsertExtraWhere($where, "T003_Data_Emissao>='$dataInicio'");
    $where = gInsertExtraWhere($where, "T003_Data_Emissao<='$dataFinal'");
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //  Definição da SQL
    $from = 'T003';

    $extra = <<<EOT
        LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T003_C007_Id
        LEFT JOIN C007 AS codigo_vendedor_interno ON codigo_vendedor_interno.C007_Id=T003_C007_Id_Vendedor_Interno
        LEFT JOIN C007 AS codigo_supervisor ON codigo_supervisor.C007_Id=codigo_vendedor_interno.C007_C007_Id
        LEFT JOIN C007 AS codigo_supervisor2 ON codigo_supervisor2.C007_Id=codigo_vendedor_interno.C007_C007_Id_2
        LEFT JOIN C007 AS codigo_vendedor_externo ON codigo_vendedor_externo.C007_Id=T003_C007_Id_Vendedor_Externo
        LEFT JOIN C007 AS codigo_vendedor_substituto ON codigo_vendedor_substituto.C007_Id=codigo_vendedor_interno.C007_Id_Vendedor_Substituto
        LEFT JOIN D047 ON D047_Id = T003_D047_Id
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

