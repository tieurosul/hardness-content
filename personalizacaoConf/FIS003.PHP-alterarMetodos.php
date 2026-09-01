<?php
namespace hardness;
/**
* Alterar (sobre-escrever) Métodos de uma Classe
*/

class FIS003 extends FIS003_ {
	// defina os métodos para sobreescrever

    const PARANA_COMPETITIVO_AJUSTE_VENDA = 'PR021074';
    const PARANA_COMPETITIVO_AJUSTE_ENTRADA = 'PR020212';

    /** Regras do Paraná Competitivo usadas nas notas de entrada e saída. */
    public function paranaCompetitivoPrevisualizar($tipo, array $ids = array(), $extra = '')
    {
        global $g;
        if (empty($g['c029Ids'][303])) return array('ok' => false, 'erro' => 'Você não possui permissão para preencher observações do Paraná Competitivo.');
        $ids = $this->paranaCompetitivoResolverIds($tipo, $ids, $extra);
        if (!$ids) return array('ok' => true, 'ids' => array(), 'linhas' => array(), 'resumo' => $this->paranaCompetitivoNovoResumo());
        $cadastro = $this->paranaCompetitivoBuscarCadastro($tipo === 'venda' ? self::PARANA_COMPETITIVO_AJUSTE_VENDA : self::PARANA_COMPETITIVO_AJUSTE_ENTRADA);
        if (!$cadastro['ok']) return array('ok' => false, 'erro' => $cadastro['erro']);
        $linhas = $tipo === 'venda' ? $this->paranaCompetitivoItensVenda($ids, $cadastro) : $this->paranaCompetitivoItensEntrada($ids, $cadastro);
        return array('ok' => true, 'ids' => $ids, 'linhas' => $linhas, 'resumo' => $this->paranaCompetitivoResumir($linhas, $ids));
    }

    public function paranaCompetitivoProcessar($tipo, array $ids = array(), $extra = '')
    {
        $previa = $this->paranaCompetitivoPrevisualizar($tipo, $ids, $extra);
        if (!$previa['ok']) return $previa;
        $porNota = array();
        foreach ($previa['linhas'] as $linha) if ($linha['situacao'] === 'apta para preenchimento') $porNota[$linha['nota_id']][] = $linha;
        $resultado = $previa['resumo'];
        foreach ($porNota as $notaId => $linhas) {
            $retorno = $this->paranaCompetitivoGravarNota($tipo, $notaId, $linhas);
            if (!$retorno['ok']) { $resultado['erros']++; $resultado['mensagens'][] = 'NF ' . $notaId . ': ' . $retorno['erro']; continue; }
            $resultado['notas_atualizadas']++; $resultado['observacoes_incluidas'] += $retorno['incluidos'];
        }
        return array('ok' => true, 'resumo' => $resultado, 'linhas' => $previa['linhas']);
    }

    private function paranaCompetitivoItensVenda(array $ids, array $cadastro)
    {
        $lista = implode(',', array_map('intval', $ids));
        $existentes = $this->paranaCompetitivoAjustesExistentesVenda($ids, $cadastro['D189_Id']);
        $sql = "SELECT T007.T007_Id AS nota_id, T007.T007_Numero_Nota_Fiscal AS numero, T007.T007_Serie AS serie, T007.T007_Data_Emissao AS emissao, T007.T007_Flag_Cancelada AS cancelada, T007.T007_Flag_Inutilizado AS inutilizada, D024.D024_Nome_Empresa AS participante, D018.D018_UF AS uf, T008.T008_Id AS item_id, T008.T008_Descricao_Produto AS produto, COALESCE(T008.T008_D005_Id, D001.D001_D005_Id) AS d005_id, D005.D005_Classificacao_Fiscal AS ncm, D149.D149_Flag_Parana_Competitivo AS flag_competitivo, T008.T008_Valor_Base_ICMS AS base_icms FROM T007 INNER JOIN T008 ON T008.T008_T007_Id=T007.T007_Id LEFT JOIN D024 ON D024.D024_Id=T007.T007_D024_Id LEFT JOIN D018 ON D018.D018_Id=D024.D024_D018_Id LEFT JOIN D009 ON D009.D009_Id=T008.T008_D009_Id LEFT JOIN D049 ON D049.D049_Id=D009.D009_D049_Id LEFT JOIN D001 ON D001.D001_Id=D049.D049_D001_Id LEFT JOIN D005 ON D005.D005_Id=COALESCE(T008.T008_D005_Id,D001.D001_D005_Id) LEFT JOIN D149 ON D149.D149_D005_Id=D005.D005_Id AND D149.D149_C004_Id=T007.T007_C004_Id WHERE T007.T007_Id IN ({$lista})";
        return $this->paranaCompetitivoMontarLinhas($sql, 'venda', $existentes, $cadastro);
    }

    private function paranaCompetitivoItensEntrada(array $ids, array $cadastro)
    {
        $lista = implode(',', array_map('intval', $ids));
        $existentes = $this->paranaCompetitivoAjustesExistentesEntrada($ids, $cadastro['D189_Id']);
        $sql = "SELECT T013.T013_Id AS nota_id, T013.T013_Numero_Nota_Fiscal AS numero, T013.T013_Serie AS serie, T013.T013_Data_Emissao AS emissao, T013.T013_Flag_Cancelada AS cancelada, D024.D024_Nome_Empresa AS participante, D018.D018_UF AS uf, T014.T014_Id AS item_id, T014.T014_Descricao_Produto AS produto, COALESCE(D001.D001_D005_Id,D005XML.D005_Id) AS d005_id, COALESCE(D005.D005_Classificacao_Fiscal,D005XML.D005_Classificacao_Fiscal) AS ncm, D149.D149_Flag_Parana_Competitivo AS flag_competitivo, T014.T014_Valor_Base_ICMS AS base_icms, T014.T014_Aliquota_ICMS AS aliquota_icms, T014.T014_Valor_ICMS AS valor_icms, D006.D006_Flag_Entrada_Saida AS entrada_saida FROM T013 INNER JOIN T014 ON T014.T014_T013_Id=T013.T013_Id LEFT JOIN D024 ON D024.D024_Id=T013.T013_D024_Id LEFT JOIN D018 ON D018.D018_Id=D024.D024_D018_Id LEFT JOIN D006 ON D006.D006_Id=T014.T014_D006_Id LEFT JOIN D009 ON D009.D009_Id=T014.T014_D009_Id LEFT JOIN D049 ON D049.D049_Id=D009.D009_D049_Id LEFT JOIN D001 ON D001.D001_Id=D049.D049_D001_Id LEFT JOIN D005 ON D005.D005_Id=D001.D001_D005_Id LEFT JOIN D005 AS D005XML ON D005XML.D005_Classificacao_Fiscal=T014.T014_Classificacao_Fiscal LEFT JOIN D149 ON D149.D149_D005_Id=COALESCE(D001.D001_D005_Id,D005XML.D005_Id) AND D149.D149_C004_Id=T013.T013_C004_Id WHERE T013.T013_Id IN ({$lista})";
        return $this->paranaCompetitivoMontarLinhas($sql, 'entrada', $existentes, $cadastro);
    }

    private function paranaCompetitivoMontarLinhas($sql, $tipo, array $existentes, array $cadastro)
    {
        $retorno = array(); $resultado = mysql_query($sql);
        while ($row = mysql_fetch_assoc($resultado)) {
            $linha = array('nota_id'=>(int)$row['nota_id'], 'item_id'=>(int)$row['item_id'], 'numero'=>$row['numero'], 'serie'=>$row['serie'], 'emissao'=>$row['emissao'], 'participante'=>$row['participante'], 'produto'=>$row['produto'], 'ncm'=>$row['ncm'], 'uf'=>$row['uf'], 'base_icms'=>(float)$row['base_icms'], 'aliquota_original'=>isset($row['aliquota_icms'])?(float)$row['aliquota_icms']:null, 'valor_icms_original'=>isset($row['valor_icms'])?(float)$row['valor_icms']:null, 'codigo_ajuste'=>$cadastro['codigo'], 'd005_id'=>(int)$row['d005_id'], 'situacao'=>'apta para preenchimento', 'mensagem'=>'', 'valor_ajuste'=>0, 'percentual_ajuste'=>0);
            if ($row['cancelada']==='S' || (!empty($row['inutilizada']) && $row['inutilizada']==='S')) $linha['situacao']='nota cancelada';
            elseif (empty($row['d005_id']) || empty($row['ncm'])) $linha['situacao']='NCM não localizada';
            elseif ($row['flag_competitivo']!=='S') $linha['situacao']='NCM sem flag';
            elseif ($tipo==='venda' && (float)$row['base_icms']<=0) $linha['situacao']='base de ICMS zerada';
            elseif ($tipo==='entrada' && $row['entrada_saida']!=='E') $linha['situacao']='operação não é entrada';
            elseif ($tipo==='entrada' && round((float)$row['aliquota_icms'],2)!==4.00) $linha['situacao']='alíquota diferente de 4%';
            elseif ($tipo==='entrada' && (float)$row['valor_icms']<=0) $linha['situacao']='valor do ICMS zerado';
            elseif (($tipo==='venda' && isset($existentes[$linha['nota_id']])) || ($tipo==='entrada' && isset($existentes[$linha['item_id']]))) $linha['situacao']='observação já existente';
            if ($linha['situacao']==='apta para preenchimento') { $linha['percentual_ajuste']=$tipo==='venda'?($row['uf']==='PR'?2.5:1.5):8.0; $base=$tipo==='venda'?(float)$row['base_icms']:(float)$row['valor_icms']; $linha['valor_ajuste']=round($base*$linha['percentual_ajuste']/100,2); }
            $retorno[]=$linha;
        } return $retorno;
    }

    private function paranaCompetitivoGravarNota($tipo, $notaId, array $linhas)
    {
        global $g;
        $cadastro = $this->paranaCompetitivoBuscarCadastro($tipo === 'venda' ? self::PARANA_COMPETITIVO_AJUSTE_VENDA : self::PARANA_COMPETITIVO_AJUSTE_ENTRADA);
        if (!$cadastro['ok']) return array('ok'=>false, 'erro'=>$cadastro['erro']);
        mysql_query('START TRANSACTION'); $campoNota=$tipo==='venda'?'T297_T007_Id':'T297_T013_Id'; $notaId=(int)$notaId;
        $consulta=mysql_query("SELECT T297_Id FROM T297 WHERE {$campoNota}='{$notaId}' AND T297_D188_Id='{$cadastro['D188_Id']}' LIMIT 1"); $observacao=mysql_fetch_assoc($consulta);
        if (!$observacao) {
            if (!mysql_query("INSERT INTO T297 ({$campoNota},T297_D188_Id,T297_Descricao_Complementar_Observacao) VALUES ('{$notaId}','{$cadastro['D188_Id']}','Preenchimento automático — Paraná Competitivo.')")) { mysql_query('ROLLBACK'); return array('ok'=>false, 'erro'=>mysql_error()); }
            $consulta=mysql_query("SELECT T297_Id FROM T297 WHERE {$campoNota}='{$notaId}' AND T297_D188_Id='{$cadastro['D188_Id']}' ORDER BY T297_Id DESC LIMIT 1"); $observacao=mysql_fetch_assoc($consulta);
        }
        $t297Id=$observacao?(int)$observacao['T297_Id']:0;
        if (!$t297Id) { mysql_query('ROLLBACK'); return array('ok'=>false, 'erro'=>'Não foi possível identificar a observação fiscal para vincular os itens.'); }
        $incluidos=0; $campoItem=$tipo==='venda'?'T298_T008_Id':'T298_T014_Id';
        foreach ($linhas as $linha) {
            $itemId=(int)$linha['item_id'];
            if ($this->paranaCompetitivoPossuiCampoT298('T298_Origem_Ajuste')) mysql_query("UPDATE T298 LEFT JOIN T297 ON T297.T297_Id=T298.T298_T297_Id SET T298.T298_T297_Id='{$t297Id}' WHERE T298.{$campoItem}='{$itemId}' AND T298.T298_D189_Id='{$cadastro['D189_Id']}' AND T298.T298_Origem_Ajuste='PARANA_COMPETITIVO' AND T297.T297_Id IS NULL");
            $duplicado=mysql_query("SELECT T298_Id FROM T298 WHERE {$campoItem}='{$itemId}' AND T298_D189_Id='{$cadastro['D189_Id']}' LIMIT 1"); if (mysql_num_rows($duplicado)) continue;
            $descricao=mysql_real_escape_string('Preenchimento automático — Paraná Competitivo. Item '.$itemId.', NCM '.$linha['ncm'].'.');
            $base=$tipo==='venda'?number_format($linha['base_icms'],2,'.',''):'NULL'; $aliquota=$tipo==='venda'?number_format($linha['percentual_ajuste'],2,'.',''):'NULL'; $valor=number_format($linha['valor_ajuste'],2,'.',''); $original=$tipo==='entrada'?number_format($linha['valor_icms_original'],2,'.',''):'NULL';
            $campos=array($campoItem,'T298_D189_Id','T298_T297_Id','T298_Descricao_Complementar_Ajuste','T298_Valor_Base_ICMS','T298_Aliquota_ICMS','T298_Valor_ICMS','T298_Outros_Valores'); $valores=array("'{$itemId}'","'{$cadastro['D189_Id']}'","'{$t297Id}'","'{$descricao}'",$base,$aliquota,"'{$valor}'","'0.00'");
            $auditoria=array('T298_D005_Id'=>"'{$linha['d005_id']}'",'T298_C007_Id_Responsavel'=>"'{$g['usuarioAtual']}'",'T298_Data_Hora_Origem'=>'NOW()','T298_Origem_Ajuste'=>"'PARANA_COMPETITIVO'",'T298_Valor_Original_ICMS'=>$original,'T298_Percentual_Ajuste'=>"'{$linha['percentual_ajuste']}'");
            foreach ($auditoria as $campo=>$valorAuditoria) if ($this->paranaCompetitivoPossuiCampoT298($campo)) { $campos[]=$campo; $valores[]=$valorAuditoria; }
            if (!mysql_query('INSERT INTO T298 ('.implode(', ',$campos).') VALUES ('.implode(', ',$valores).')')) { mysql_query('ROLLBACK'); return array('ok'=>false, 'erro'=>mysql_error()); }
            $vinculo=mysql_query("SELECT T298_Id FROM T298 WHERE {$campoItem}='{$itemId}' AND T298_D189_Id='{$cadastro['D189_Id']}' AND T298_T297_Id='{$t297Id}' LIMIT 1");
            if (!$vinculo || !mysql_num_rows($vinculo)) { mysql_query('ROLLBACK'); return array('ok'=>false, 'erro'=>'A obrigação tributária foi criada sem o vínculo esperado com o item da nota.'); } $incluidos++;
        }
        if (!mysql_query('COMMIT')) { mysql_query('ROLLBACK'); return array('ok'=>false, 'erro'=>mysql_error()); } return array('ok'=>true,'incluidos'=>$incluidos);
    }

    private function paranaCompetitivoPossuiCampoT298($campo) { static $campos=array(); if (!array_key_exists($campo,$campos)) { $campo=mysql_real_escape_string($campo); $resultado=mysql_query("SHOW COLUMNS FROM T298 LIKE '{$campo}'"); $campos[$campo]=$resultado && mysql_num_rows($resultado)>0; } return $campos[$campo]; }
    private function paranaCompetitivoBuscarCadastro($codigo) { $codigo=mysql_real_escape_string($codigo); $sql=mysql_query("SELECT D189.D189_Id,D188.D188_Id FROM D189 LEFT JOIN D018 ON D018.D018_Id=D189.D189_D018_Id LEFT JOIN D188 ON D188.D188_D189_Id=D189.D189_Id WHERE D189.D189_Codigo_Ajuste='{$codigo}' AND (D018.D018_UF='PR' OR D189.D189_D018_Id IS NULL) ORDER BY D188.D188_Id DESC LIMIT 1"); $row=mysql_fetch_assoc($sql); if (!$row || empty($row['D189_Id'])) return array('ok'=>false,'erro'=>"Não foi possível executar a rotina porque o código de ajuste {$codigo} não está cadastrado."); if (empty($row['D188_Id'])) return array('ok'=>false,'erro'=>"O código de ajuste {$codigo} não possui uma observação fiscal vinculada."); return array('ok'=>true,'codigo'=>$codigo,'D189_Id'=>(int)$row['D189_Id'],'D188_Id'=>(int)$row['D188_Id']); }
    private function paranaCompetitivoResolverIds($tipo, array $ids, $extra) { global $g; $ids=array_values(array_unique(array_filter(array_map('intval',$ids)))); $tabela=$tipo==='venda'?'T007':'T013'; $campo=$tabela.'_Id'; if ($ids) { $lista=implode(',',$ids); $resultado=mysql_query("SELECT {$campo} FROM {$tabela} WHERE {$campo} IN ({$lista}) AND {$tabela}_C004_Id='{$g['empresaAtual']}'"); $retorno=array(); while ($row=mysql_fetch_assoc($resultado)) $retorno[]=(int)$row[$campo]; return $retorno; } $extra=base64_decode($extra,true); if (!$extra || strpos($extra,"{$tabela}_C004_Id='{$g['empresaAtual']}'")===false) return array(); $resultado=mysql_query("SELECT DISTINCT {$campo} FROM {$tabela} {$extra}"); $retorno=array(); while ($row=mysql_fetch_assoc($resultado)) $retorno[]=(int)$row[$campo]; return $retorno; }
    private function paranaCompetitivoAjustesExistentesVenda(array $ids,$ajusteId) { $lista=implode(',',array_map('intval',$ids)); $retorno=array(); $sql=mysql_query("SELECT T297.T297_T007_Id AS nota_id FROM T298 INNER JOIN T297 ON T297.T297_Id=T298.T298_T297_Id WHERE T298.T298_D189_Id='{$ajusteId}' AND T297.T297_T007_Id IN ({$lista}) GROUP BY T297.T297_T007_Id"); while ($row=mysql_fetch_assoc($sql)) $retorno[(int)$row['nota_id']]=true; return $retorno; }
    private function paranaCompetitivoAjustesExistentesEntrada(array $ids,$ajusteId) { $lista=implode(',',array_map('intval',$ids)); $retorno=array(); $sql=mysql_query("SELECT T298.T298_T014_Id AS item_id FROM T298 INNER JOIN T297 ON T297.T297_Id=T298.T298_T297_Id WHERE T298.T298_D189_Id='{$ajusteId}' AND T297.T297_T013_Id IN ({$lista}) GROUP BY T298.T298_T014_Id"); while ($row=mysql_fetch_assoc($sql)) $retorno[(int)$row['item_id']]=true; return $retorno; }
    private function paranaCompetitivoNovoResumo() { return array('notas_analisadas'=>0,'notas_atualizadas'=>0,'observacoes_incluidas'=>0,'ja_existentes'=>0,'ncm_sem_flag'=>0,'aliquota_diferente'=>0,'erros'=>0,'mensagens'=>array()); }
    private function paranaCompetitivoResumir(array $linhas,array $ids) { $resumo=$this->paranaCompetitivoNovoResumo(); $resumo['notas_analisadas']=count($ids); foreach ($linhas as $linha) { if ($linha['situacao']==='observação já existente') $resumo['ja_existentes']++; if ($linha['situacao']==='NCM sem flag') $resumo['ncm_sem_flag']++; if ($linha['situacao']==='alíquota diferente de 4%') $resumo['aliquota_diferente']++; } return $resumo; }

    /**
     * cadastrarNCM
     *
     * @param String $D005_Id
     * @return String
     */	
    public function cadastrarNCM($D005_Id)
    {
        global $g;
        $sqlC004 = mysqli_query("SELECT C004_Id FROM C004");
        while($resC004 = mysql_fetch_array($sqlC004)){
            $D149=mysqli_query("select D149_Id from D149 where D149_D005_Id='{$D005_Id}' and D149_C004_Id='{$resC004['C004_Id']}'");
            if (mysql_num_rows($D149)<=0) {
                mysqli_query("INSERT INTO D149 (D149_D005_Id, D149_C004_Id) VALUES ('{$D005_Id}', '{$resC004['C004_Id']}')");
                if($resC004['C004_Id'] == $g['empresaAtual']){
                    $D149_Id = $g['mysqlLastId'];
                }
            }
        }
        return $D149_Id;
    }

    public function verificarNCMDuplicadas()
    {
        global $g;
        $sqlIndex = mysql_query("SHOW INDEXES FROM D005 WHERE Key_name='I_UNIQUE_D005'");
        if(mysql_num_rows($sqlIndex)<=0){
            $sqlD005 = mysql_query("SELECT D005_Id,D005_Flag_Aplicacao, D005_Classificacao_Fiscal, COUNT(*) c FROM D005 WHERE D005_Flag_Ativo='S' GROUP BY D005_Classificacao_Fiscal, D005_Flag_Aplicacao HAVING c > 1 limit 20");
            if(mysql_num_rows($sqlD005)<=0){
                mysql_query("ALTER TABLE D005 ADD CONSTRAINT I_UNIQUE_D005 UNIQUE (D005_Flag_Aplicacao,D005_Classificacao_Fiscal)");
                mysql_query("ALTER TABLE D149 ADD CONSTRAINT I_UNIQUE_D149 UNIQUE (D149_D005_Id,D149_C004_Id)");
                return false;
            }else{
                $NCMDuplicadas = array();
                while($D005 = mysql_fetch_array($sqlD005)){
                    $NCMDuplicadas[] = $D005['D005_Classificacao_Fiscal']." - ".$D005['D005_Flag_Aplicacao'];
                }
                $NCMRetorno = implode(", ",$NCMDuplicadas);
                $mensagem = <<<EOT
                    Atenção! NCM's abaixo estão duplicadas: {$NCMRetorno}
EOT;
                return $mensagem;
            }
        }
        return false;
    }

    /**
     * demonstrativoNCM
     *
     * @param String $D005_Id
     * @return String
     */ 
    public function demonstrativoNCM($D005_Id)
    {
        global $g;
        $sql = mysql_query("SELECT D005_Classificacao_Fiscal,
                                   D005_Classificacao_Fiscal,
                                   D005_Observacao,
                                   D149_Aliquota_IPI,
                                   D149_Aliquota_PIS,
                                   D149_Aliquota_COFINS,
                                   D149_Flag_ST,
                                   D149_Valor_VA,
                                   D149_Percentual_Trib_Transparencia,
                                   D149_Imposto_Importacao,
                                   D149_Importacao_IPI,
                                   D149_Importacao_PIS,
                                   D149_Importacao_COFINS,
                                   D149_UF_ST,
                                   D149_Regras_MVA,
                                   D149_Aliquota_IRPJ,
                                   D149_Aliquota_CSLL,
                                   D037_Unidade 
                              FROM D005 
                         LEFT JOIN D149 ON D149_D005_Id=D005_Id AND D149_C004_Id = '{$g['empresaAtual']}' 
                         LEFT JOIN D037 ON D037_Id=D005_D037_Id_Tributavel
                             WHERE D005_Id = '{$D005_Id}'");
        $res = mysql_fetch_array($sql);
        $res['D149_Aliquota_IPI']                  = gCorrigeNumero($res['D149_Aliquota_IPI']);
        $res['D149_Valor_VA']                      = gCorrigeNumero($res['D149_Valor_VA']);
        $res['D149_Aliquota_PIS']                  = gCorrigeNumero($res['D149_Aliquota_PIS']);
        $res['D149_Aliquota_COFINS']               = gCorrigeNumero($res['D149_Aliquota_COFINS']);
        $res['D149_Percentual_Trib_Transparencia'] = gCorrigeNumero($res['D149_Percentual_Trib_Transparencia']);
        $res['D149_Imposto_Importacao']            = gCorrigeNumero($res['D149_Imposto_Importacao']);
        $res['D149_Importacao_IPI']                = gCorrigeNumero($res['D149_Importacao_IPI']);
        $res['D149_Importacao_PIS']                = gCorrigeNumero($res['D149_Importacao_PIS']);
        $res['D149_Importacao_COFINS']             = gCorrigeNumero($res['D149_Importacao_COFINS']);
        $res['D149_Importacao_IRPJ']               = gCorrigeNumero($res['D149_Importacao_IRPJ']);
        $res['D149_Importacao_CSLL']               = gCorrigeNumero($res['D149_Importacao_CSLL']);
        $res['D149_Flag_ST']                       = ($res['D149_Flag_ST'] == 'S') ? 'Sim' : 'Não';

        $arrayCST = array();
        $arrayCST["1"] = "00 - ENTRADA COM RECUPERAÇÃO DE CRÉDITO";
        $arrayCST["2"] = "01 - ENTRADA TRIBUTADA COM ALÍQUOTA ZERO";
        $arrayCST["3"] = "02 - ENTRADA ISENTA";
        $arrayCST["4"] = "03 - ENTRADA NÃO-TRIBUTADA";
        $arrayCST["5"] = "04 - ENTRADA IMUNE";
        $arrayCST["6"] = "05 - ENTRADA COM SUSPENSÃO";
        $arrayCST["7"] = "49 - OUTRAS ENTRADAS";
        $arrayCST["8"] = "50 - SAÍDA TRIBUTADA";
        $arrayCST["9"] = "51 - SAÍDA TRIBUTADA COM ALÍQUOTA ZERO";
        $arrayCST["10"] = "52 - SAÍDA ISENTA";
        $arrayCST["11"] = "53 - SAÍDA NÃO-TRIBUTADA";
        $arrayCST["12"] = "54 - SAÍDA IMUNE";
        $arrayCST["13"] = "55 - SAÍDA COM SUSPENSÃO";
        $arrayCST["14"] = "99 - OUTRAS SAÍDAS</option>";

        $dataHora = date('d/m/Y H:i:s');
        
        $retorno = <<<EOT
            <style>
                .tableDemonstrativo td {
                    border: 1px solid #ccc;
                    width: 25%;
                    background: #ede7de url(/hardness3/static/css/temas/padrao/images/ui-bg_dots-small_100_ede7de_2x2.png) 50% 50% repeat;
                }
                .tableDemonstrativo {
                    -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
                    -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
                    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
                    //border-radius: 25px 25px 25px 25px;
                    padding: 3px 5px;
                }
            </style>
            <table class="tableDemonstrativo" cellpading=0 cellspacing=0 style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td colspan="4" style="text-align: center; font-weight: bold; background: #eee;">NCM: {$res['D005_Classificacao_Fiscal']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">Observação:</td>
                    <td colspan="3" style="white-space: normal;">{$res['D005_Observacao']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">IPI:</td>
                    <td>{$res['D149_Aliquota_IPI']}</td>
                    <td style="text-align: right;">Unidade Tributável:</td>
                    <td>{$res['D037_Unidade']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">Substituição tributária:</td>
                    <td>{$res['D149_Flag_ST']}</td>
                    <td style="text-align: right;">Margem Valor Agregado:</td>
                    <td>{$res['D149_Valor_VA']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">PIS:</td>
                    <td>{$res['D149_Aliquota_PIS']}</td>
                    <td style="text-align: right;">COFINS:</td>
                    <td>{$res['D149_Aliquota_COFINS']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">IRPJ:</td>
                    <td>{$res['D149_Aliquota_IRPJ']}</td>
                    <td style="text-align: right;">CSLL:</td>
                    <td>{$res['D149_Aliquota_CSLL']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">Excessões<br />ICM/IPI/MVA:</td>
                    <td colspan="3">{$res['D149_Regras_MVA']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">UF ST:</td>
                    <td colspan="3">{$res['D149_UF_ST']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">Percentual tributos<br />(Lei transp.):</td>
                    <td colspan="3">{$res['D149_Percentual_Trib_Transparencia']}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: center; background: #eee;"><b>Importação</b></td>
                </tr>
                <tr>
                    <td style="text-align: right;">II%:</td>
                    <td>{$res['D149_Imposto_Importacao']}</td>
                    <td style="text-align: right;">IPI%:</td>
                    <td>{$res['D149_Importacao_IPI']}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">PIS%:</td>
                    <td>{$res['D149_Importacao_PIS']}</td>
                    <td style="text-align: right;">COFINS%:</td>
                    <td>{$res['D149_Importacao_COFINS']}</td>
                </tr>
            </table><br />
            <span style="float: right; font-weight: bold;">{$dataHora}</span>      
EOT;
        return $retorno;
    }

}
