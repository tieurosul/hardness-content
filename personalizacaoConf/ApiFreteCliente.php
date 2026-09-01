<?php
namespace hardness;

/**
 * Cliente HTTP para API externa de cotação de frete (orçamento VEN001).
 *
 * Configuração via C031 (CAD058): apiFreteUrl (endpoint único que retorna todas as opções).
 * Autenticação (.env / token) pode ser adicionada depois.
 *
 * Ajuste montarBody() e parseOpcoes() quando o contrato da API estiver definido.
 */
class ApiFreteCliente
{
    /**
     * @param array $payload Payload montado por VEN001::montarPayloadFreteApi()
     * @return array{ok:bool, opcoes?:array, erro?:string, raw?:mixed}
     */
    public function cotar(array $payload)
    {
        global $g;

        $url = trim(isset($g['C031']['apiFreteUrl']) ? $g['C031']['apiFreteUrl'] : '');
        $timeout = 15;

        if ($url === '') {
            return array('ok' => false, 'erro' => 'Configure apiFreteUrl em CAD058 (Configurações globais / C031).');
        }

        $body = $this->montarBody($payload);
        $jsonBody = json_encode($body);
        if ($jsonBody === false) {
            return array('ok' => false, 'erro' => 'Não foi possível montar o JSON da requisição de frete.');
        }

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            return array('ok' => false, 'erro' => 'Falha na comunicação com a API de frete: ' . $curlError);
        }

        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'ok' => false,
                'erro' => 'Resposta inválida da API de frete (HTTP ' . $httpCode . ').',
                'raw' => $response,
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = isset($decoded['message']) ? $decoded['message'] : (isset($decoded['erro']) ? $decoded['erro'] : 'HTTP ' . $httpCode);
            return array('ok' => false, 'erro' => 'API de frete retornou erro: ' . $msg, 'raw' => $decoded);
        }

        $opcoes = $this->parseOpcoes($decoded);
        if (empty($opcoes)) {
            return array('ok' => false, 'erro' => 'A API não retornou opções de frete.', 'raw' => $decoded);
        }

        return array('ok' => true, 'opcoes' => $opcoes, 'raw' => $decoded);
    }

    /**
     * Monta o body enviado à API. Adapte quando o contrato real estiver disponível.
     */
    public function montarBody(array $payload)
    {
        return array(
            'origem' => array(
                'cep' => $payload['cep_origem'],
                'cidade' => $payload['cidade_origem'],
                'uf' => $payload['uf_origem'],
            ),
            'destino' => array(
                'cep' => $payload['cep_destino'],
                'cidade' => $payload['cidade_destino'],
                'uf' => $payload['uf_destino'],
            ),
            'valor_total' => $payload['valor_total'],
            'peso_total_kg' => $payload['peso_total'],
            'cubagem_total' => $payload['cubagem_total'],
            'orcamento_id' => $payload['orcamento_id'],
            'itens' => $payload['itens'],
        );
    }

    /**
     * Normaliza a resposta da API para o formato interno do ERP.
     * Formato esperado (stub): { "opcoes": [ { "id", "transportadora", "servico", "prazo_dias", "valor", "codigo_d022" } ] }
     * ou lista direta em data / quotes / fretes.
     */
    public function parseOpcoes($decoded)
    {
        $lista = array();

        if (isset($decoded['opcoes']) && is_array($decoded['opcoes'])) {
            $lista = $decoded['opcoes'];
        } elseif (isset($decoded['data']['opcoes']) && is_array($decoded['data']['opcoes'])) {
            $lista = $decoded['data']['opcoes'];
        } elseif (isset($decoded['data']) && is_array($decoded['data']) && isset($decoded['data'][0])) {
            $lista = $decoded['data'];
        } elseif (isset($decoded['quotes']) && is_array($decoded['quotes'])) {
            $lista = $decoded['quotes'];
        } elseif (isset($decoded['fretes']) && is_array($decoded['fretes'])) {
            $lista = $decoded['fretes'];
        } elseif (is_array($decoded) && isset($decoded[0])) {
            $lista = $decoded;
        }

        $opcoes = array();
        foreach ($lista as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $valor = isset($item['valor']) ? $item['valor'] : (isset($item['price']) ? $item['price'] : 0);
            $transportadora = isset($item['transportadora']) ? $item['transportadora'] : (isset($item['carrier']) ? $item['carrier'] : '');
            $servico = isset($item['servico']) ? $item['servico'] : (isset($item['service']) ? $item['service'] : (isset($item['name']) ? $item['name'] : ''));
            $prazo = isset($item['prazo_dias']) ? $item['prazo_dias'] : (isset($item['prazo']) ? $item['prazo'] : (isset($item['delivery_days']) ? $item['delivery_days'] : ''));
            $id = isset($item['id']) ? $item['id'] : (isset($item['code']) ? $item['code'] : (string) $idx);
            $codigoD022 = isset($item['codigo_d022']) ? $item['codigo_d022'] : (isset($item['codigoD022']) ? $item['codigoD022'] : '');

            $opcoes[] = array(
                'id' => (string) $id,
                'transportadora' => trim((string) $transportadora),
                'servico' => trim((string) $servico),
                'prazo' => $prazo,
                'valor' => (float) str_replace(',', '.', (string) $valor),
                'codigoD022' => trim((string) $codigoD022),
            );
        }

        return $opcoes;
    }
}
