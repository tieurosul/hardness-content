<?php
namespace hardness;

/**
 * Register C031 keys for external freight quote API (VEN001).
 */

return function () {
    adicionarConfGlobal('apiFreteUrl', '', 'URL da API externa de cotação de frete (orçamento VEN001).');
    adicionarConfGlobal('apiFreteToken', '', 'Token/chave de autenticação da API de frete (Bearer ou conforme contrato).');
    adicionarConfGlobal('apiFreteTimeout', '15', 'Timeout em segundos para chamadas à API de frete.');
    adicionarConfGlobal('apiFreteMapaD022', '{}', 'JSON opcional: código da opção da API => D022_Id (ex: {"PAC":"12","SEDEX":"13"}).');
};
