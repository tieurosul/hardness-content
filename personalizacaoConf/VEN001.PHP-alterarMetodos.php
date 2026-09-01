<?php
namespace hardness;

require_once dirname(__FILE__) . '/ApiFreteCliente.php';

class VEN001 extends VEN001_
{
    /**
     * Monta payload para cotação de frete a partir do orçamento T003/T004.
     *
     * @param int|string $T003_Id
     * @return array{ok:bool, payload?:array, erro?:string}
     */
    public function montarPayloadFreteApi($T003_Id)
    {
        global $g;

        $T003_Id = (int) $T003_Id;
        if ($T003_Id <= 0) {
            return array('ok' => false, 'erro' => 'Orçamento inválido.');
        }

        $sql = mysql_query("SELECT T003.*,
                                   D024.D024_Cep AS D024_Cep,
                                   D024.D024_Nome_Empresa,
                                   D020.D020_Nome_Cidade AS D024_Cidade,
                                   D018.D018_UF AS D024_UF,
                                   C004.C004_Cep AS C004_Cep,
                                   C004.C004_Cidade AS C004_Cidade,
                                   C004.C004_Uf AS C004_UF
                              FROM T003
                         LEFT JOIN D024 ON D024_Id = T003_D024_Id
                         LEFT JOIN D020 ON D020_Id = D024_D020_Id
                         LEFT JOIN D018 ON D018_Id = D024_D018_Id
                         LEFT JOIN C004 ON C004_Id = T003_C004_Id
                             WHERE T003_Id = '{$T003_Id}'");

        if (!$sql || !($mT003 = mysql_fetch_assoc($sql))) {
            return array('ok' => false, 'erro' => 'Orçamento não encontrado.');
        }

        $cepDestino = preg_replace('/\D/', '', (string) $mT003['D024_Cep']);
        if (strlen($cepDestino) < 8) {
            return array('ok' => false, 'erro' => 'Cliente sem CEP cadastrado. Informe o CEP em D024 antes de cotar frete.');
        }

        $cepOrigem = preg_replace('/\D/', '', (string) $mT003['C004_Cep']);
        if (strlen($cepOrigem) < 8) {
            return array('ok' => false, 'erro' => 'Empresa sem CEP cadastrado (C004). Configure o CEP da empresa.');
        }

        $sqlItens = mysql_query("SELECT T004.T004_Id,
                                        T004.T004_Quantidade,
                                        T004.T004_Valor_Total_Preco,
                                        D009.D009_Peso_Unitario_Kg,
                                        D001.D001_Codigo_Produto,
                                        D001.D001_Descricao_Produto,
                                        D001.D001_Cubagem_Unitaria,
                                        D001.D001_Peso_Unitario_Kg
                                   FROM T004
                              LEFT JOIN D009 ON D009_Id = T004_D009_Id
                              LEFT JOIN D049 ON D049_Id = D009_D049_Id
                              LEFT JOIN D001 ON D001_Id = D049_D001_Id
                                  WHERE T004_T003_Id = '{$T003_Id}'
                                    AND T004_Quantidade > 0");

        $itens = array();
        $temItens = false;
        while ($sqlItens && ($row = mysql_fetch_assoc($sqlItens))) {
            $temItens = true;
            $qtd = (float) $row['T004_Quantidade'];
            $pesoUnit = (float) (isset($row['D009_Peso_Unitario_Kg']) && $row['D009_Peso_Unitario_Kg'] > 0
                ? $row['D009_Peso_Unitario_Kg']
                : $row['D001_Peso_Unitario_Kg']);

            $itens[] = array(
                'codigo' => $row['D001_Codigo_Produto'],
                'descricao' => $row['D001_Descricao_Produto'],
                'quantidade' => $qtd,
                'peso_unitario_kg' => $pesoUnit,
                'peso_total_kg' => $pesoUnit * $qtd,
                'cubagem_unitaria' => (float) $row['D001_Cubagem_Unitaria'],
                'valor_total' => (float) $row['T004_Valor_Total_Preco'],
            );
        }

        if (!$temItens) {
            return array('ok' => false, 'erro' => 'Inclua ao menos um produto no orçamento antes de cotar frete.');
        }

        $pesoTotal = (float) $mT003['T003_Peso_Total'];
        if ($pesoTotal <= 0) {
            foreach ($itens as $item) {
                $pesoTotal += $item['peso_total_kg'];
            }
        }

        if ($pesoTotal <= 0) {
            return array('ok' => false, 'erro' => 'Peso total do orçamento está zerado. Verifique peso dos produtos.');
        }

        $payload = array(
            'orcamento_id' => $T003_Id,
            'cep_origem' => $cepOrigem,
            'cidade_origem' => $mT003['C004_Cidade'],
            'uf_origem' => $mT003['C004_UF'],
            'cep_destino' => $cepDestino,
            'cidade_destino' => $mT003['D024_Cidade'],
            'uf_destino' => $mT003['D024_UF'],
            'valor_total' => (float) $mT003['T003_Valor_Total'],
            'peso_total' => $pesoTotal,
            'cubagem_total' => (float) $mT003['T003_Total_Cubagem'],
            'cliente_nome' => $mT003['D024_Nome_Empresa'],
            'itens' => $itens,
        );

        return array('ok' => true, 'payload' => $payload);
    }

    /**
     * Consulta opções de frete na API externa.
     *
     * @param int|string $T003_Id
     * @return array{ok:bool, opcoes?:array, erro?:string}
     */
    public function cotarFreteApi($T003_Id)
    {
        $montagem = $this->montarPayloadFreteApi($T003_Id);
        if (empty($montagem['ok'])) {
            return array('ok' => false, 'erro' => $montagem['erro']);
        }

        $cliente = new ApiFreteCliente();
        $resultado = $cliente->cotar($montagem['payload']);

        if (empty($resultado['ok'])) {
            return array('ok' => false, 'erro' => $resultado['erro']);
        }

        return array('ok' => true, 'opcoes' => $resultado['opcoes']);
    }

    /**
     * Grava a opção de frete escolhida no cabeçalho T003.
     *
     * @param int|string $T003_Id
     * @param array $opcao
     * @return array{ok:bool, aviso?:string, erro?:string}
     */
    public function aplicarOpcaoFrete($T003_Id, array $opcao)
    {
        global $g;

        $T003_Id = (int) $T003_Id;
        if ($T003_Id <= 0) {
            return array('ok' => false, 'erro' => 'Orçamento inválido.');
        }

        $valor = isset($opcao['valor']) ? (float) $opcao['valor'] : 0;
        if ($valor < 0) {
            return array('ok' => false, 'erro' => 'Valor de frete inválido.');
        }

        $transportadora = isset($opcao['transportadora']) ? trim($opcao['transportadora']) : '';
        $servico = isset($opcao['servico']) ? trim($opcao['servico']) : '';
        $codigoOpcao = isset($opcao['id']) ? trim((string) $opcao['id']) : '';
        $codigoD022 = isset($opcao['codigoD022']) ? trim($opcao['codigoD022']) : '';

        $textoFrete = trim($transportadora . ($servico !== '' ? ' - ' . $servico : ''));
        if ($textoFrete === '' && $codigoOpcao !== '') {
            $textoFrete = $codigoOpcao;
        }
        $textoFrete = substr($textoFrete, 0, 40);

        $d022Id = $this->resolverD022FreteApi($codigoD022, $codigoOpcao, $transportadora);
        $aviso = '';

        $valorSql = number_format($valor, 2, '.', '');
        $textoFreteSql = mysql_real_escape_string($textoFrete);

        $sets = array(
            "T003_Valor_Frete = '{$valorSql}'",
            "T003_Valor_Frete_Calculado = '{$valorSql}'",
            "T003_Frete = '{$textoFreteSql}'",
        );

        if ($d022Id > 0) {
            $sqlD022 = mysql_query("SELECT D022_Nome_Empresa FROM D022 WHERE D022_Id = '{$d022Id}' LIMIT 1");
            $mD022 = $sqlD022 ? mysql_fetch_assoc($sqlD022) : false;
            $nomeTransp = $mD022 ? mysql_real_escape_string($mD022['D022_Nome_Empresa']) : mysql_real_escape_string($transportadora);
            $sets[] = "T003_D022_Id = '{$d022Id}'";
            $sets[] = "T003_Nome_Transportadora = '{$nomeTransp}'";
        } else {
            $sets[] = "T003_D022_Id = NULL";
            $sets[] = "T003_Nome_Transportadora = NULL";
            if ($transportadora !== '') {
                $aviso = 'Frete aplicado sem vínculo com transportadora (D022). Cadastre ou configure apiFreteMapaD022.';
            }
        }

        mysql_query('UPDATE T003 SET ' . implode(', ', $sets) . " WHERE T003_Id = '{$T003_Id}'");

        mysql_query("CALL T003_Gravar_Totalizacao_4('{$T003_Id}')");

        $sqlT003 = mysql_query("SELECT T003_Flag_Frete, T003A_Flag_Ratear_Frete_Custo
                                  FROM T003
                             LEFT JOIN T003A ON T003A_T003_Id = T003_Id
                                 WHERE T003_Id = '{$T003_Id}'");
        if ($sqlT003 && ($mT003 = mysql_fetch_assoc($sqlT003))) {
            $ratear = isset($g['C031']['ratearFreteVendaNoCusto']) ? $g['C031']['ratearFreteVendaNoCusto'] : 'N';
            if ($ratear === 'S' && (string) $mT003['T003_Flag_Frete'] === '0') {
                $this->atualizarPrecoTabela($T003_Id);
            }
        }

        $retorno = array('ok' => true);
        if ($aviso !== '') {
            $retorno['aviso'] = $aviso;
        }

        return $retorno;
    }

    /**
     * Resolve D022_Id a partir do mapa C031, código da opção ou nome da transportadora.
     */
    protected function resolverD022FreteApi($codigoD022, $codigoOpcao, $nomeTransportadora)
    {
        global $g;

        $mapaJson = isset($g['C031']['apiFreteMapaD022']) ? trim($g['C031']['apiFreteMapaD022']) : '{}';
        $mapa = json_decode($mapaJson, true);
        if (!is_array($mapa)) {
            $mapa = array();
        }

        $candidatos = array();
        if ($codigoD022 !== '') {
            $candidatos[] = $codigoD022;
        }
        if ($codigoOpcao !== '') {
            $candidatos[] = $codigoOpcao;
        }

        foreach ($candidatos as $codigo) {
            if (isset($mapa[$codigo]) && (int) $mapa[$codigo] > 0) {
                return (int) $mapa[$codigo];
            }
        }

        if ($nomeTransportadora !== '') {
            $nomeEsc = mysql_real_escape_string($nomeTransportadora);
            $sql = mysql_query("SELECT D022_Id FROM D022 WHERE D022_Nome_Empresa = '{$nomeEsc}' LIMIT 1");
            if ($sql && ($row = mysql_fetch_assoc($sql))) {
                return (int) $row['D022_Id'];
            }

            $sql = mysql_query("SELECT D022_Id FROM D022 WHERE D022_Nome_Empresa LIKE '%{$nomeEsc}%' LIMIT 1");
            if ($sql && ($row = mysql_fetch_assoc($sql))) {
                return (int) $row['D022_Id'];
            }
        }

        return 0;
    }
}
