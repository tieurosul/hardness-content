<?php
namespace hardness;
/**
* Este código PHP será executado ao acessar: /outros-apacheErros-apacheErros-outros/
* Ele irá substituir o original (que fica dentro do .PHP, no switch/case)
*
* Caso seja uma AJAX, é necessário acrescentar este código:
* No inicio: $resposta = array('code' => true, 'data' => array());
* No final: echo $_REQUEST['callback'] . "(" . json_encode($resposta) . ");";
*/

    global $confUsuario,$g;

    $subDominio = $_SERVER['HTTP_HOST'] == 'localhost' ? $_SERVER['HTTP_HOST'] : substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], "."));
    $dominio    = $_SERVER['HTTP_HOST'] == 'localhost' ? $_SERVER['HTTP_HOST'] : substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], ".")+1);

    log("subDominio: {$subDominio}");
    log("dominio: {$dominio}");

    ob_start();

    $dirLog = "./dados_usuarios/log/";
    is_dir($dirLog) OR mkdir($dirLog, 0777, true);
    passthru("tail -n 15 {$dirLog}{$subDominio}.{$dominio}-error.log");
    log("tail -n 15 {$dirLog}{$subDominio}.{$dominio}-error.log");

    $log = ob_get_contents();

    ob_end_clean();

    $dia = substr(date('l'),0,3);

    $log = explode('['.$dia,$log);

    unset($log[0]);

    krsort($log);

    foreach ($log as $key => $row){
            echo "<p style='font-size:14px;border-bottom:1px solid silver;padding:10px'>[".$dia.$row."</p>";
    }

