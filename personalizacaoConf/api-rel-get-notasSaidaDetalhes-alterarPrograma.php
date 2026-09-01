<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /api-rel-get-notasSaidaDetalhes/
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
        array('colunaValor' => 'T007_Id', 'colunaTitulo' => 'id_nf_saida'),
        array('colunaValor' => 'T007_Prazos(T007_Id)', 'colunaTitulo' => 'prazo_nf_saida'),
        array('colunaValor' => 'T007_Prazo_Medio', 'colunaTitulo' => 'prazo_medio_nf_saida'),
        array('colunaValor' => 'format(T007_Valor_Frete,2,"pt_BR")', 'colunaTitulo' => 'valor_frete_nf_saida'),
    );

    //montando clausula where
    $where = $API001->montarWhere($where,"T007_Data_Emissao",">=",$dataInicio);
    $where = $API001->montarWhere($where,"T007_Data_Emissao","<=",$dataFinal);
    $where = $API001->montarWhere($where,"T007_Flag_Entrada_Saida","=",'S');
    $where = $API001->montarWhere($where,"T007_Flag_Cancelada","!=",'S');
    $where = gInsertExtraWhere($where, "$extraFiltro", true);

    //Definição da SQL
    $from = 'T007';

    $extra = <<<EOT
        LEFT JOIN C007 AS codigo_usuario ON codigo_usuario.C007_Id=T007_C007_Id
        LEFT JOIN C007 AS codigo_vendedor_interno ON codigo_vendedor_interno.C007_Id=T007_C007_Id_Vendedor_Interno
        LEFT JOIN C007 AS codigo_supervisor ON codigo_supervisor.C007_Id=codigo_vendedor_interno.C007_C007_Id
        LEFT JOIN C007 AS codigo_supervisor2 ON codigo_supervisor2.C007_Id=codigo_vendedor_interno.C007_C007_Id_2
        LEFT JOIN C007 AS codigo_vendedor_externo ON codigo_vendedor_externo.C007_Id=T007_C007_Id_Vendedor_Externo
        LEFT JOIN C007 AS codigo_vendedor_substituto ON codigo_vendedor_substituto.C007_Id=codigo_vendedor_interno.C007_Id_Vendedor_Substituto
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
