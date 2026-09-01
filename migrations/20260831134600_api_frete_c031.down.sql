-- Remove C031 keys registered by 20260831_134600_api_frete_c031.sql
DELETE FROM C031
WHERE C031_Campo IN ('apiFreteUrl', 'apiFreteToken', 'apiFreteTimeout', 'apiFreteMapaD022');
