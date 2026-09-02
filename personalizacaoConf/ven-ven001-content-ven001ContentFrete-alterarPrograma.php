<?php
namespace hardness;
/**
 * Este codigo PHP sera executado ao acessar: /ven/ven001/content/ven001ContentFrete/
 * Ele ira substituir o original (que fica dentro do .PHP, no switch/case)
 */

$content1 = uniqid();
$g['smarty']->assign('content1', $content1);
$g['smarty']->assign('tipo', 'unico');

$actions = array(
    array($content1, '/ven/ven001/outro/ven001OutroFrete/', '&acaoId=' . urlencode($r_acaoId)),
);

echo gProcessaAcoes($actions);
