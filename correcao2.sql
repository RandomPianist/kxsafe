ALTER TABLE gestor_estoque RENAME maquinas_produtos;

ALTER TABLE estoque ADD COLUMN id_mp INT AFTER qtd;

UPDATE estoque
JOIN maquinas_produtos AS mp
    ON mp.id_maquina = estoque.id_maquina AND mp.id_produto = estoque.id_produto
SET id_mp = mp.id;

ALTER TABLE estoque DROP id_maquina;
ALTER TABLE estoque DROP id_produto;

ALTER TABLE log CHANGE tabela tabela VARCHAR(32);
UPDATE log SET tabela = 'maquinas_produtos' WHERE tabela = 'gestor_estoque';