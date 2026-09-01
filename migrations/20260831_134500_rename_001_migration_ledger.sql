-- One-time: align ledger after renaming 001_* files to timestamp format.
UPDATE E998_Migration
SET E998_Migration_Name = '20260831_134600_api_frete_c031.sql'
WHERE E998_Migration_Name = '001_api_frete_c031.sql';
