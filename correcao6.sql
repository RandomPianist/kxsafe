DELETE FROM log WHERE tabela = 'empresas_setores';
DROP TABLE empresas_setores;
ALTER TABLE setores DROP COLUMN padrao;

ALTER TABLE atribuicoes ADD COLUMN id_empresa INT DEFAULT 0;